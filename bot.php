<?php

require __DIR__ . '/php/libs/vendor/autoload.php';
require __DIR__ . '/php/app.php';

try {
  $data = file_get_contents('php://input');
  file_put_contents(__DIR__ . '/logs/botLog.json', $data);
  $data = json_decode($data, true);

  $result = [];
  $bot = new Bot($data);
  $action = $bot->getAction();

  // Для сообщений должен быть адресат
  if ($action === 'message' && $bot->getChatKey() === '-1') {
    $action = 'errorMessage';
  }

  switch ($action) {
    case 'start': $bot->addSupportUser(); break;
    case 'stop' : $bot->removeSupportUser(); break;
    case 'errorMessage': $bot->sendErrorMessage(); break;

    case 'message':
      $main = new Main(['DEBUG' => true]);

      $result = $main->db->addTGMessage(
        $bot->getChatKey(), $bot->getUser(), $bot->getType(), $bot->getContent()
      );

      // расскидать всем подписчикам в боте $bot->sendToBot();
      break;

    case 'loadMessages':
      //$result['data']    = $main->db->loadMessages();
      //$result['userKey'] = $main->getParam('userKey');
      break;

    default:
      die('action error');
  }
} catch (Exception $exception) {
  def($exception->getMessage());
}

echo 'ok';
