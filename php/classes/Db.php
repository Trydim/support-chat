<?php

use RedBeanPHP\QueryWriter\AQueryWriter as AQueryWriter;

require __DIR__ . '/Rb-mysql.php';

class Db extends R {
  const DB_DATE_FORMAT = 'Y-m-d H:i:s',
        DB_DATE_FROM   = '2000-01-01 00:00:00',
        DB_DATE_TO     = '2100-01-01 00:00:00',
        SHOW_DATE_FORMAT = 'H:i d-m-Y';

  const DB_JSON_FIELDS = [
    'inputValue', 'saveValue', 'importantValue', 'reportValue',
    'contacts', 'customerContacts', 'customization',
    'cmsParam', 'properties', 'permissionValue'
  ];

  const DB_DATE_FIELDS = [
    'createDate', 'lastEditDate', 'registerDate'
  ];

  const DB_BLOB_FIELDS = ['reportValue', 'settings'];

  /**
   * @var Main
   */
  private $main;

  /**
   * @var bool
   */
  private $connected = false;


  /**
   * @var string
   */
  private $login;

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

  private function convertDateFormatField(array $arr): array {
    foreach ($arr as &$value) {
      foreach (self::DB_DATE_FIELDS as $dateF) {
        if (isset($value[$dateF])) $value[$dateF] = date_format(date_create($value[$dateF]), self::SHOW_DATE_FORMAT);
      }
    }

    return $arr;
  }


  public function __construct(Main $main, bool $freeze = true) {
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
  public function selectDb(string $key): DbMain {
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
    $sql = 'SELECT ' . implode(', ',  $columns) . ' FROM ' . $this->pf($dbTable);
    if (strlen($filters)) $sql .= ' WHERE ' . $filters;

    return $simple ? self::getCol($sql) : self::getAll($sql);
  }

  /**
   * @param $dbTable
   * @param $columnName
   * @param $value
   *
   * @return integer
   */
  public function checkHaveRows($dbTable, $columnName, $value): int {
    return intval(self::getCell("SELECT count(*) FROM " . $this->pf($dbTable) .
                                    " WHERE $columnName = :value", [':value' => $value]));
  }

  /**
   * @param string $dbTable
   * @param array $ids
   * @param string $primaryKey
   *
   * @return int
   */
  public function deleteItem(string $dbTable, array $ids, string $primaryKey = 'ID'): int {
    $dbTable = $this->pf($dbTable);
    $count = 0;
    if ($primaryKey !== 'ID') {
      foreach ($ids as $id) {
        $count += self::exec("DELETE FROM $dbTable WHERE $primaryKey = '$id'");
      }
      return $count;
    }

    if (count($ids) === 1) {
      $bean = self::xdispense($dbTable);
      $bean->id = $ids[0];
      $count = self::trash($bean);
    } else {
      $beans = self::xdispense($dbTable, count($ids));

      for ($i = 0; $i < count($ids); $i++) {
        $beans[$i]->id = $ids[$i];
      }

      $count = self::trashAll($beans);
    }
    return $count;
  }

  /**
   * @param string $dbTable
   * @param array  $requireParam
   * @return mixed
   */
  public function getLastID(string $dbTable, array $requireParam = []) {
    $bean = self::xdispense($this->pf($dbTable));
    foreach ($requireParam as $field => $value) $bean->$field = $value;
    self::store($bean);

    return $bean->getID();
  }

  //------------------------------------------------------------------------------------------------------------------

  public function addMessage(): array {
    $this->checkConnection();

    $cookies = $this->main->request->cookies;
    $request = $this->main->request->request;
    $type = $request->get('type') ?? 'text';

    $bean = R::dispense('messages');

    if ($type === 'file') {
      // Сохранить файл
    }

    $bean->chatKey = $cookies->get('support-user-key');
    $bean->userKey = $cookies->get('support-user-key');
    $bean->type    = $type;
    $bean->content = $request->get('content');

    return ['id' => R::store($bean)];
  }

  public function addTGMessage(string $chatKey, string $userKey, string $type, string $content): array {
    $this->checkConnection();

    $bean = R::dispense('messages');

    if ($type === 'file') {
      // Сохранить файл
    }

    $bean->chatKey = $chatKey;
    $bean->userKey = $userKey;
    $bean->type    = $type;
    $bean->content = $content;

    return ['id' => R::store($bean)];
  }

  // Добавить аргумент загрузки по времени
  public function loadMessages(): array {
    $this->checkConnection();

    $cookies = $this->main->request->cookies;

    $sql = "SELECT id, user_key AS userKey, date, type, content FROM messages";
    $sql .= " WHERE chat_key = '" . $cookies->get('support-user-key') . "'";

    return R::getAll($sql);
  }
}
