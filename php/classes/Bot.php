<?php

class Bot {
  const URL_TELEGRAM = 'https://api.telegram.org/bot';
  const TOKEN_TELEGRAM = '6985649319:AAEm0yWTVN1EJd4_QN2AQLFZFAW9pj5lKBU';

  const SUBSCRIBE_PATH = __DIR__ . '/../../storage/subscribeList.json';

  const METHOD_SEND = 'sendMessage';

  /**
   * @var array
   */
  private $original;
  /**
   * @var string
   */
  private $chatId;
  /**
   * @var string
   */
  private $username;
  /**
   * @var string
   */
  private $type;
  /**
   * @var string
   */
  private $content;

  private $sendChatId = [];

  private $subscribes = null;

  private $method = self::METHOD_SEND;
  private $sendData = ['chat_id' => 1];

  public function __construct($data) {
    $this->original = $data;

    $this->chatId   = $data['chat']['id'] ?? '';
    $this->username = $data['chat']['username'];
    $this->type     = $data['type'] ?? 'text';
    $this->content  = $data['text'];
  }

  private function addChatId($id): Bot {
    if (is_array($id)) $this->sendChatId = array_merge($this->sendChatId, $id);
    else $this->sendChatId[] = $id;

    return $this;
  }

  private function setContent(): Bot {
    $this->sendData['text'] = $this->content;

    return $this;
  }
  private function send() {
    $result = [];
    $url = self::URL_TELEGRAM . self::TOKEN_TELEGRAM . '/' . $this->method;
    $send = $this->sendData;

    foreach ($this->sendChatId as $id) {
      $send['chat_id'] = $id;
      $result[$id] = httpRequest($url, ['method' => 'post'], json_encode($send));
    }

    def($result);
  }

  public function getUser(): string {
    return $this->username;
  }
  public function getType(): string {
    return $this->type;
  }
  public function getContent(): string {
    return $this->content;
  }
  public function getAction(): string {
    $isCommand = ($this->original['entities']['0']['type'] ?? '') === 'bot_command';

    if ($isCommand) {
      $match = [];

      $res = preg_match('/^(.?)(\w+)(.?)/', $this->content, $match);
      if ($res === 0) def('getAction: regExp not found');

      return $match[2];
    }

    return 'message';
  }

  private function loadSubscribe() {
    $subscribes = file_get_contents(self::SUBSCRIBE_PATH);

    $this->subscribes = json_decode(is_string($subscribes) ? $subscribes : '{}', true);
  }
  private function saveSubscribe() {
    file_put_contents(self::SUBSCRIBE_PATH, json_encode($this->subscribes));
  }
  private function checkUser(): bool {
    if ($this->subscribes === null) $this->loadSubscribe();

    return isset($this->subscribes[$this->chatId]);
  }
  private function toggleUser(bool $addUser = false) {
    if ($this->subscribes === null) $this->loadSubscribe();

    if ($addUser) $this->subscribes[$this->chatId] = $this->username;
    else unset($this->subscribes[$this->chatId]);

    $this->saveSubscribe();
  }

  public function addSupportUser() {
    // Проверить есть ли такой пользователь
    if ($this->checkUser()) {
      $this->sendData['text'] = 'Пользователь ' . $this->username . ' уже подписан.';
    } else {
      $this->toggleUser(true);
      $this->sendData['text'] = 'Подписан пользователь: ' . $this->username;
    }

    $this->addChatId($this->chatId)->send();
  }
  public function removeSupportUser() {
    $this->toggleUser();
    $this->sendData['text'] =  'Подписка отключена';
    $this->addChatId($this->chatId)->send();
  }

  public function sendToBot() {
    if ($this->subscribes === null) $this->loadSubscribe();

    $this->setContent()
         ->addChatId(array_keys($this->subscribes))
         ->send();
  }
}
