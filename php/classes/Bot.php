<?php

class Bot {
  const URL_TELEGRAM = 'https://api.telegram.org/bot';
  const TOKEN_TELEGRAM = '6985649319:AAEm0yWTVN1EJd4_QN2AQLFZFAW9pj5lKBU';

  const SUBSCRIBE_PATH = __DIR__ . '/../../storage/subscribeList.json';

  const METHOD_SEND = 'sendMessage';

  /**
   * @var array
   */
  private $originalMessage;
  /**
   * @var string
   */
  private $host;
  /**
   * @var string
   */
  private $chatId;
  /**
   * @var string
   */
  private $chatKey;
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
  private $sendData = [
    'chat_id' => 1,
    'parse_mode' => 'HTML',
  ];

  private $errors = [];

  public function __construct($data) {
    $message = [];

    if (isset($data['message'])) $message = $data['message'];

    $this->originalMessage = $message;

    $this->host     = $message['host'] ?? '';
    $this->chatId   = $message['chat']['id'] ?? '';
    $this->username = $message['chat']['username'] ?? '';
    $this->type     = $message['type'] ?? 'text';
  }

  private function addChatId($id): Bot {
    if (is_array($id)) $this->sendChatId = array_merge($this->sendChatId, $id);
    else $this->sendChatId[] = $id;

    return $this;
  }

  private function setContent(): Bot {
    $host = $this->host;
    $key = substr($this->originalMessage['chatKey'], -7, 7); // Последние 7 символов
    $content = $this->getContent();

    $this->sendData['text'] = "Сайт <b>$host</b>:\ $content";

    //def($key);

    //
    $this->sendData['reply_markup'] = [
      "inline_keyboard" => [
        [
          [
            "text" => "Х",
            "callback_data" => "hideBtn"
          ],
          [
            "text" => "Ответить",
            "switch_inline_query_current_chat" => ">$key<:\n\n"
          ],
        ]
      ]
    ];

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

    $result = array_filter($result, function ($item) { return $item['ok'] !== true; });
    if (count($result) !== 0) {
      def($result, false);
      $this->errors[] = 'send error';
    }
  }

  public function getChatKey(): string {
    if (empty($this->chatKey)) {
      $match = [];
      // '....>12345<...' -> '12345'
      $res = preg_match('/[>](.+)[<]/', $this->originalMessage['text'], $match);

      $this->chatKey = $res === 0 ? '-1' : $match[1];
    }

    return $this->chatKey;
  }
  public function getUser(): string { return $this->username; }
  public function getType(): string { return $this->type; }
  public function getContent(): string {
    if (empty($this->content)) {
      $chatKey = $this->getChatKey();
      $text = $this->originalMessage['text'];

      // '....>12345<:\n\nText..' -> 'Text..'
      if ($chatKey !== '-1') $text = preg_replace("/^.+>$chatKey<:\n\n/", '', $text);

      $this->content = $text;
    }

    return $this->content;
  }
  public function getAction(): string {
    $isCommand = ($this->originalMessage['entities']['0']['type'] ?? '') === 'bot_command';

    if ($isCommand) {
      $match = [];

      $res = preg_match('/^(.?)(\w+)(.?)/', $this->getContent(), $match);
      if ($res === 0) def('getAction: regExp not found');

      return $match[2];
    }

    return 'message';
  }
  public function getError() { return $this->errors; }

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
    $this->sendData['text'] = 'Подписка отключена';
    $this->addChatId($this->chatId)->send();
  }
  public function sendErrorMessage() {
    $this->sendData['text'] = '❌: Адресат обязателен';
    $this->addChatId($this->chatId)->send();
  }

  public function sendToBot(): Bot {
    if ($this->subscribes === null) $this->loadSubscribe();

    $this->setContent()
         ->addChatId(array_keys($this->subscribes))
         ->send();

    return $this;
  }
}