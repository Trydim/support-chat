<?php

class Bot {
  const URL_TELEGRAM = 'https://api.telegram.org/bot';
  const URL_FILE_TELEGRAM = 'https://api.telegram.org/file/bot';
  const TOKEN_TELEGRAM = '6985649319:AAEm0yWTVN1EJd4_QN2AQLFZFAW9pj5lKBU';

  const BOT_PATH       = __DIR__ . '/../../storage/botList.json';
  const SUBSCRIBE_PATH = __DIR__ . '/../../storage/subscribeList';
  const UPLOAD_PATH    = __DIR__ . '/../../storage/upload/';

  const SEND_ERROR_TG = [
    '1' => '❌: Адресат обязателен',
    '2' => '❌: Адресат недоступен',
    '3' => '❌: Серверу не удалось загрузить файл',
  ];

  /**
   * @var string
   */
  private $useBotToken = '';
  private $subscribePostfix = '';

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
  private $from;
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

  private $complete = [];
  private $errors = [];

  public function __construct(array $data, bool $check, $botKey) {
    $this->setParam($data);
    $this->setBotToken($botKey);

    $check && $this->checkRequirements();
  }

  private function setParam(array $data) {
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

    $this->from   = $message['fromSite'] ?? null; // Не очень
    $this->msgId  = $message['message_id'] ?? '';
    $this->chatId = $message['chat']['id'] ?? '';
    $this->type   = $message['type'] ?? null;
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
      $this->action = 'error';
      $this->method = 'deleteMessage';
      $this->sendData['message_id'] = $this->msgId;
      return;
    }
  }
  private function setBotToken($botKey) {
    $botList = [];

    if ($botKey !== null && file_exists(self::BOT_PATH)) {
      $botList = json_decode(file_get_contents(self::BOT_PATH), true);
    }

    if ($botKey !== null && isset($botList[$botKey])) {
      $this->useBotToken = $botList[$botKey];
      $this->subscribePostfix = '-' . substr($botList[$botKey], -7, 7) . '.json';
    } else {
      $this->useBotToken = self::TOKEN_TELEGRAM;
      $this->subscribePostfix = '.json';
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

    $result = httpRequest(self::URL_TELEGRAM . $this->useBotToken . '/getFile?file_id=' . $file['file_id']);
    if (!$result['ok']) def('getContentFilePath error', false);

    return $result['result']['file_path'] ?? '';
  }
  private function copyContentFile(): string {
    $file = $this->getContentFilePath();
    $localFile = uniqid() . '.' . pathinfo($file, PATHINFO_EXTENSION);

    $from = self::URL_FILE_TELEGRAM . $this->useBotToken . '/' . $file;
    $to   = self::UPLOAD_PATH . $localFile;

    $result = copy($from, $to);
    if (!$result) def('getContentFilePath error', false);

    return $result ? $localFile : '';
  }

  private function setContent(string $msgId): Bot {
    $from = $this->from;
    $key  = substr($this->getChatKey(), -7, 7); // Последние 7 символов
    $type = $this->getType();
    $content = htmlspecialchars($this->getContent());

    if ($type === 'text') {
      $this->sendData['text'] = $from ? "<b>$from</b>:\n$content"
                                      : $this->getUser() . ":\n$content";
    } else {
      // Определить тип файла
      $fileType = pathinfo($content, PATHINFO_EXTENSION);
      $typeKey  = in_array($fileType, ['png', 'jpg', 'webp']) ? 'photo' : 'document';

      $this->method = $typeKey === 'photo' ? 'sendPhoto' : 'sendDocument';

      $this->sendData[$typeKey] = $content;
    }

    if ($from) {
      $this->sendData['reply_markup'] = [
        "inline_keyboard" => [
          [
            [
              "text" => "Х",
              "callback_data" => "hideBtn"
            ],
            [
              "text" => "Ответить",
              "switch_inline_query_current_chat" => ">$msgId-$key<:\n\n",
            ],
          ]
        ]
      ];
    }

    return $this;
  }
  private function send(array $reply = []) {
    $result = [];
    $error  = [];
    $url = self::URL_TELEGRAM . $this->useBotToken . '/' . $this->method;
    $send = $this->sendData;

    foreach ($this->sendChatId as $id) {
      if (!in_array($this->getAction(), ['error', 'start', 'stop']) && $this->chatId === $id) continue; // Самому себе не отправлять
      $send['chat_id'] = $id;
      // Добавить ссылку на ответ
      if (isset($reply[$id])) $send['reply_parameters']['message_id'] = $reply[$id];

      $result[$id] = httpRequest($url, ['method' => 'post'], json_encode($send));

      if ($result[$id]['ok']) $this->complete[] = $result[$id]['result'];
      else $error[] = $result[$id];
    }

    if (count($error)) {
      def($error, false);
      $this->errors[] = 'Send error';
    }
  }

  public function checkReply(): bool { return array_key_exists('reply_to_message', $this->original->message); }
  public function getDBMessageId(): string {
    $match = [];

    $text = $this->checkReply()
      ? $this->original->message['reply_to_message']['reply_markup']['inline_keyboard'][0][1]['switch_inline_query_current_chat']
      : $this->original->message['text'];

    $res = preg_match('/[>](\d+)[-]/', $text, $match); // '....>12345-...' -> '12345'
    $res = $res === 1 ? $match[1] : '';

    return $res;
  }
  public function getChatKey(): string {
    if ($this->chatKey === null) {
      $match = [];

      $text = $this->checkReply()
        ? $this->original->message['reply_to_message']['reply_markup']['inline_keyboard'][0][1]['switch_inline_query_current_chat']
        : $this->original->message['text'];

      $res = preg_match('/[-](.+)[<]/', $text, $match); // '....-12345<...' -> '12345'
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
      if ($chatKey !== '-1') $text = preg_replace("/^.+-$chatKey<:/", '', $text);

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
  public function getComplete(): array { return $this->complete; }
  public function getError(): array { return $this->errors; }
  public function prepareMessageId(): string {
    if (count($this->complete) === 0) return '';

    return json_encode(array_reduce($this->complete, function ($r, $item) {
      $r[$item['chat']['id']] = $item['message_id'];
      return $r;
    }, []));
  }

  private function loadSubscribe() {
    $subscribes = file_get_contents(self::SUBSCRIBE_PATH . $this->subscribePostfix);

    $this->subscribes = json_decode(is_string($subscribes) ? $subscribes : '{}', true);
  }
  private function saveSubscribe() {
    file_put_contents(self::SUBSCRIBE_PATH . $this->subscribePostfix, json_encode($this->subscribes));
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
  public function sendToBot(string $msgId, array $reply = []): Bot {
    if ($this->subscribes === null) $this->loadSubscribe();

    if ($this->getAction() === 'error') $this->addChatId($this->chatId);
    else $this->setContent($msgId)->addChatId(array_keys($this->subscribes));

    $this->send($reply);

    return $this;
  }
}
