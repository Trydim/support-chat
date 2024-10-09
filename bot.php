<?php

require __DIR__ . '/php/libs/vendor/autoload.php';
require __DIR__ . '/php/app.php';

try {
  $data = file_get_contents('php://input');
  //file_put_contents(__DIR__ . '/logs/botLog.json', $data);
  $data = json_decode($data, true);

  //def($data);

  $result = [];

  $main = new Main(['DEBUG' => true]);
  $bot  = new Bot($data, true, $main->getParam('token'));
  $action = $bot->getAction();

  switch ($action) {
    case 'start': $bot->addSupportUser(); break;
    case 'stop' : $bot->removeSupportUser(); break;

    case 'callback': $bot->execCallback(); break;

    case 'message':
      $msgId = $main->db->addMessageFromTG($bot);

      //$msg = $bot->checkReply() ? $main->db->loadMessageById($bot->getDBMessageId()) : [];
      $msg = $main->db->loadMessageById($bot->getDBMessageId());

      // расскидать всем подписчикам в боте
      $bot->sendToBot('', $msg['message_id'] ?? []);

      //if (count($bot->getComplete())) $main->db->setMessageId($msgId, $bot->prepareMessageId());
      break;

    case 'loadMessages':
      //$result['data']    = $main->db->loadMessages();
      break;

    case 'error':
      $bot->sendToBot('');
      break;

    default: die('default action');
  }
} catch (Exception $exception) {
  def($exception->getMessage());
}

echo 'ok';
