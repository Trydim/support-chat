<?php

use RedBeanPHP\QueryWriter\AQueryWriter as AQueryWriter;

require __DIR__ . '/Rb-mysql.php';

class Db extends R {
  const DB_DATE_FORMAT = 'Y-m-d H:i:s',
        DB_DATE_FROM   = '2000-01-01 00:00:00',
        DB_DATE_TO     = '2100-01-01 00:00:00',
        SHOW_DATE_FORMAT = 'H:i d-m-Y';

  const DB_DATE_FIELDS = [
    'createDate', 'lastEditDate', 'registerDate'
  ];

  const DB_JSON_FIELDS = ['message_id'];

  const DB_BLOB_FIELDS = []; //

  /**
   * @var Main
   */
  private $main;

  /**
   * @var bool
   */
  private $connected = false;

  /**
   * Plugin readBean for special name
   * @param $type
   * @param $count
   *
   * @return array|\RedBeanPHP\OODBBean|null
   */
  private function dis($type, $count) {
    return self::getRedBean()->dispense($type, $count);
  }

  private function setting() {
    self::ext('xdispense', function ($type, $count = 1) {
      return $this->dis($type, $count);
    });
  }

  private function convertDateFormatField(string $value): string {
    return date_format(date_create($value), self::DB_DATE_FORMAT);
  }

  /**
   * @param array $arr
   * @return array
   */
  private function jsonParseField(array $arr): array {
    $result = [];

    foreach ($arr as $key => $value) {
      if (is_array($value)) {
        $result[$key] = $this->jsonParseField($value);
      } else if (in_array($key, self::DB_JSON_FIELDS)) {
        $result[$key] = json_decode($value, true);
      } else if (in_array($key, self::DB_BLOB_FIELDS)) {
        $result[$key] = empty($value) ? [] : json_decode(gzuncompress($value), true);
      } else {
        $result[$key] = $value;
      }
    }

    return $result;
  }

  public function __construct(Main $main) {
    $this->main = $main;
  }

  private function checkConnection() {
    if ($this->connected === false) $this->connect();
  }

  public function connect() {
    $dbConfig = $this->main->getSettings(VC::DB_CONFIG);

    self::setup(
      'mysql:host=' . $dbConfig['dbHost'] . ';dbname=' . $dbConfig['dbName'],
      $dbConfig['dbUsername'],
      $dbConfig['dbPass']
    );

    $this->connected = self::testConnection();
    !$this->connected && die('Data Base connect error!');

    $this->setting();

    //self::fancyDebug(DEBUG);
    self::freeze(true);
  }

  /**
   * Select a database,
   * @param string $key
   * @return $this
   */
  public function selectDb(string $key): Db {
    self::selectDatabase($key);

    return $this;
  }

  /**
   * What does this function do?
   * @param $varName
   * @return string
   */
  public function setQueryAs($varName): string {
    return AQueryWriter::camelsSnake($varName) . " AS '$varName'";
  }

  /**
   * @param string|integer $date
   * @return false|string|null
   */
  public function getDbDateString($date) {
    $date = trim($date, '"\'');

    if (empty($date)) return null;
    if (is_numeric($date) && strlen($date) >= 10) {
      return date($this::DB_DATE_FORMAT, intval(substr($date, 0, 10)));
    }
    $date = date_create($date);
    return $date ? $date->format($this::DB_DATE_FORMAT) : null;
  }

  // MAIN query
  //------------------------------------------------------------------------------------------------------------------

  /**
   * @param string $dbTable name of table
   * @param array|string $columns of columns, if size of array is 1 (except all column '*') return simple array,
   * @param $filters string filter
   *
   * @return array
   */
  public function selectQuery(string $dbTable, $columns = '*', string $filters = ''): array {
    $simple = false;
    if (!is_array($columns)) {
      $simple = $columns !== '*';
      $columns = [$columns];
    }

    $columns[0] !== '*' && $columns = array_map(function ($item) { return $this->setQueryAs($item); }, $columns);
    $sql = 'SELECT ' . implode(', ',  $columns) . ' FROM ' . $dbTable;
    if (strlen($filters)) $sql .= ' WHERE ' . $filters;

    return $simple ? self::getCol($sql) : self::getAll($sql);
  }

  //------------------------------------------------------------------------------------------------------------------

  public function addMessage(): string {
    $this->checkConnection();

    $request = $this->main->request;

    $type = $request->request->get('type') ?? 'text';
    $content = $request->request->get('content');
    $bean = self::dispense('messages');

    if ($type === 'file') {
      $fs = new FS($this->main);
      $content = $fs->prepareFile($request->files->get('content'))->getUri();
    }

    $bean->chatKey = $bean->userKey = $this->main->getParam(STORAGE_SUPPORT_KEY);
    $bean->type    = $type;
    $bean->content = $content;

    $this->main->setParam('content', $content);
    return self::store($bean);
  }

  public function addMessageFromTG(Bot $bot): string {
    $this->checkConnection();

    $bean = self::dispense('messages');
    $chatKey = $bot->getChatKey();
    $user    = $bot->getUser();

    $chatKey = self::findOne('messages', ' chat_key LIKE ? ', ["%$chatKey%"])->chatKey;
    if (empty($chatKey)) {
      $bot->sendErrorMessage(2);
      return '0';
    }

    if ($bot->getType() === 'file') {
      $bean->chatKey = $chatKey;
      $bean->userKey = $user;
      $bean->type    = 'file';
      $bean->content = $bot->getContentFileUri();

      if (empty($bean->content)) {
        $bot->sendErrorMessage(3);
        return ['id' => false];
      }

      R::store($bean);
      $bean = self::dispense('messages');
    }

    $bean->chatKey = $chatKey;
    $bean->userKey = $user;
    $bean->type    = 'text';
    $bean->content = $bot->getContent();

    return R::store($bean);
  }

  public function setMessageId(string $id, string $messageId) {
    $bean = self::dispense('messages');

    $bean->id = $id;
    $bean->message_id = $messageId;

    return self::store($bean);
  }

  // Добавить аргумент загрузки по времени
  public function loadMessages(): array {
    $this->checkConnection();

    $key = $this->main->getParam(STORAGE_SUPPORT_KEY);

    $sql = "SELECT id, user_key AS userKey, date, type, content FROM messages";
    $sql .= " WHERE chat_key = '" . $key . "'";

    $date = $this->main->request->request->get('date');
    if ($date) $sql .= " AND date > '" . $this->convertDateFormatField($date) . "'";

    return R::getAll($sql);
  }

  public function loadMessageById(string $id): array {
    $row = $this->selectQuery('messages', '*', " id = '$id' ");

    if (count($row) === 1) $row = $row[0];

    return $this->jsonParseField($row);
  }
}
