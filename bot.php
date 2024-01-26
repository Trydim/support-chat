<?php

require __DIR__ . '/php/libs/vendor/autoload.php';
require __DIR__ . '/php/app.php';

try {
  $data = file_get_contents('php://input');
  //file_put_contents(__DIR__ . '/logs/botLog.json', $data);
  $data = json_decode($data, true);

  $result = [];
  $bot    = new Bot($data);
  $action = $bot->getAction();

  switch ($action) {
    case 'start': $bot->addSupportUser(); break;
    case 'stop' : $bot->removeSupportUser(); break;

    case 'callback': $bot->execCallback(); break;

    case 'message':
      $main = new Main(['DEBUG' => true]);

      $result = $main->db->addMessageFromTG($bot);

      // расскидать всем подписчикам в боте
      $result = $bot->sendToBot();
      break;

    case 'loadMessages':
      //$result['data']    = $main->db->loadMessages();
      break;

    default: die('default action');
  }
} catch (Exception $exception) {
  def($exception->getMessage());
}

echo 'ok';
