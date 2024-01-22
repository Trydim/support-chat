<?php

require __DIR__ . '/php/libs/vendor/autoload.php';
require __DIR__ . '/php/app.php';

try {
  $data = file_get_contents('php://input');
  //file_put_contents(__DIR__ . '/logs/botLog.json', $data);
  $data = json_decode($data, true)['message'];

  $result = [];
  $bot = new Bot($data);
  $action = $bot->getAction();

  switch ($action) {
    case 'start':
      $bot->addSupportUser();
      break;
    case 'stop' :
      $bot->removeSupportUser();
      break;

    case 'message':
      $main = new Main([
        'DEBUG' => true,
      ], [
        'dbHost'     => 'localhost',
        'dbName'     => 'support',
        'dbUsername' => 'root',
        'dbPass'     => ''
      ]);

      $result = $main->db->addTGMessage(
        '65ae784db186d3.11291976', $bot->getUser(), $bot->getType(), $bot->getContent()
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
