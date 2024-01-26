<?php

class Bot {
  const URL_TELEGRAM = 'https://api.telegram.org/bot';
  const URL_FILE_TELEGRAM = 'https://api.telegram.org/file/bot';
  const TOKEN_TELEGRAM = '6985649319:AAEm0yWTVN1EJd4_QN2AQLFZFAW9pj5lKBU';

  const SUBSCRIBE_PATH = __DIR__ . '/../../storage/subscribeList.json';
  const UPLOAD_PATH    = __DIR__ . '/../../storage/upload/';

  const SEND_ERROR_TG = [
    '1' => '❌: Адресат обязателен',
    '2' => '❌: Адресат недоступен',
    '3' => '❌: Серверу не удалось загрузить файл',
  ];

  /**
   * @var object
   */
  private $original;

  /**
   * @var object
   */
  private $data;

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
  private $msgId;
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

  /**
   * @var 'message'|'start'|'stop'|'error'
   */
  private $action;
  private $method = 'sendMessage';
  private $sendData = [
    'chat_id' => 1,
    'parse_mode' => 'HTML',
  ];

  private $errors = [];

  public function __construct(array $data, bool $check = true) {
    $this->original = new class {
      var $message = [];
      var $file = [];
      var $callback = [];
    };
    $this->data = new class {};

    $data = $this->checkKeyCallBack($data);

    $message = $data['message'];
    if (array_key_exists('photo', $message)) {
      $this->original->file = $message['photo'];

      if (array_key_exists('caption', $message)) $message['text'] = $message['caption'];
    }

    $this->original->message = $message;

    $this->host   = $message['host'] ?? null;
    $this->msgId  = $message['message_id'] ?? '';
    $this->chatId = $message['chat']['id'] ?? '';
    $this->type   = $message['type'] ?? null;

    $check && $this->checkRequirements();
  }

  private function checkKeyCallBack(array $data): array {
    if (!array_key_exists('callback_query', $data)) return $data;

    $callback = $data['callback_query'];

    $this->original->callback = $callback;
    $this->data->callbackAction = $callback['data'];

    return $callback;
  }
  private function checkRequirements() {
    $action = $this->getAction();

    if ($action === 'callback') return;

    // Для сообщений должен быть адресат
    if ($action === 'message' && $this->getChatKey() === '-1') {
      $this->sendErrorMessage(1); return;
    }

    // Сообщение не содержит текста
    if ($this->getType() === 'text' && empty($this->getContent())) {
      $this->method = 'deleteMessage';
      $this->sendData['message_id'] = $this->msgId;
      $this->addChatId($this->chatId)->send();
      $this->action = 'error';
      return;
    }
  }

  private function addChatId($id): Bot {
    if (is_array($id)) $this->sendChatId = array_merge($this->sendChatId, $id);
    else $this->sendChatId[] = $id;

    return $this;
  }

  private function getContentFilePath(): string {
    $index = count($this->original->file) - 1;
    $file  = $this->original->file[$index];

    $result = httpRequest(self::URL_TELEGRAM . self::TOKEN_TELEGRAM . '/getFile?file_id=' . $file['file_id']);
    if (!$result['ok']) def('getContentFilePath error', false);

    return $result['result']['file_path'] ?? '';
  }
  private function copyContentFile(): string {
    $file = $this->getContentFilePath();
    $localFile = uniqid() . '.' . pathinfo($file, PATHINFO_EXTENSION);

    $from = self::URL_FILE_TELEGRAM . self::TOKEN_TELEGRAM . '/' . $file;
    $to   = self::UPLOAD_PATH . $localFile;

    $result = copy($from, $to);
    if (!$result) def('getContentFilePath error', false);

    return $result ? $localFile : '';
  }

  private function setContent(): Bot {
    $host = $this->host;
    $key  = substr($this->getChatKey(), -7, 7); // Последние 7 символов
    $type = $this->getType();
    $content = htmlspecialchars($this->getContent());

    if ($type === 'text') {
      $this->sendData['text'] = $host ? "Сайт <b>$host</b>:\n$content"
                                      : $this->getUser() . ":\n$content";
    } else {
      // Определить тип файла
      $fileType = pathinfo($content, PATHINFO_EXTENSION);
      $typeKey  = in_array($fileType, ['png', 'jpg', 'webp']) ? 'photo' : 'document';

      $this->method = $typeKey === 'photo' ? 'sendPhoto' : 'sendDocument';

      $this->sendData[$typeKey] = $content;
    }

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
      if ($this->action !== 'error' && $this->chatId === $id) continue; // Самому себе не отправлять
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
    if ($this->chatKey === null) {
      $match = [];
      $res = preg_match('/[>](.+)[<]/', $this->original->message['text'], $match); // '....>12345<...' -> '12345'
      $res = $res === 1 ? $match[1] : $this->original->message['chatKey'] ?? '-1';

      $this->chatKey = $res;
    }

    return $this->chatKey;
  }
  public function getUser(): string {
    if ($this->username === null) {
      $this->username = $this->original->message['chat']['username'] ?? '';
    }

    return $this->username;
  }
  public function getType(): string {
    if ($this->type === null) {
      $this->type = array_key_exists('photo', $this->original->message) ? 'file' : 'text';
    }

    return $this->type;
  }
  public function getContent(): string {
    if ($this->content === null) {
      $chatKey = $this->getChatKey();
      $text = $this->original->message['text'];

      // '....>12345<:\n\nText..' -> 'Text..'
      if ($chatKey !== '-1') $text = preg_replace("/^.+>$chatKey<:/", '', $text);

      $this->content = trim($text);
    }

    return $this->content;
  }
  public function getContentFileUri(): string {
    $url = $_SERVER['HTTP_HOST'] === 'vistegra.by' ? SUPPORT_HOST : 'http://' . $_SERVER['HTTP_HOST'] . '/';

    $localFile = $this->copyContentFile();
    if (empty($localFile)) { def('getContentFilePath error', false); return ''; }

    return $url . 'storage/upload/' . $localFile;
  }
  public function getAction(): string {
    if ($this->action === null) {
      if (isset($this->data->callbackAction)) $this->action = 'callback';
      else {
        $isCommand = ($this->original->message['entities']['0']['type'] ?? '') === 'bot_command';

        if ($isCommand) {
          $match = [];

          $res = preg_match('/^(.?)(\w+)(.?)/', $this->getContent(), $match);
          if ($res === 0) def('getAction: regExp not found');

          $this->action = $match[2];
        } else $this->action = 'message';
      }
    }

    return $this->action;
  }
  public function getError(): array { return $this->errors; }

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

    if ($addUser) $this->subscribes[$this->chatId] = $this->getUser();
    else unset($this->subscribes[$this->chatId]);

    $this->saveSubscribe();
  }

  public function addSupportUser() {
    // Проверить есть ли такой пользователь
    if ($this->checkUser()) {
      $this->sendData['text'] = 'Пользователь ' . $this->getUser() . ' уже подписан.';
    } else {
      $this->toggleUser(true);
      $this->sendData['text'] = 'Подписан пользователь: ' . $this->getUser();
    }

    $this->addChatId($this->chatId)->send();
  }
  public function removeSupportUser() {
    $this->toggleUser();
    $this->sendData['text'] = 'Подписка отключена';
    $this->addChatId($this->chatId)->send();
  }
  public function sendErrorMessage($msg = null) {
    $this->action = 'error';
    $this->sendData['text'] = is_integer($msg) ? self::SEND_ERROR_TG[$msg ?? 1] : $msg;
    $this->addChatId($this->chatId)->send();
  }

  public function execCallBack() {
    switch ($this->data->callbackAction) {
      default: break;
      case 'hideBtn':
        $this->method = 'editMessageReplyMarkup';
        $this->sendData['message_id'] = $this->msgId;
        $this->sendData['reply_markup'] = [];
        $this->addChatId($this->chatId)->send();
        break;
    }
  }
  public function sendToBot(): Bot {
    if ($this->subscribes === null) $this->loadSubscribe();

    $this->setContent()
         ->addChatId(array_keys($this->subscribes))
         ->send();

    return $this;
  }
}
