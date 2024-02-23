<?php

require __DIR__ . '/php/libs/vendor/autoload.php';
require __DIR__ . '/php/app.php';

//$data = json_encode($_REQUEST);
//file_put_contents(__DIR__ . '/logs/botLog.json', $data);

//def($_SERVER);

$main = new Main(['DEBUG' => true]);

$action = $main->getParam('action');
$result[STORAGE_SUPPORT_KEY] = $main->getParam(STORAGE_SUPPORT_KEY);

switch ($action) {
  case 'addMessage':
    $request = $main->request;
    $result['msgId'] = $main->db->addMessage();

    $data = [
      'fromSite' => $request->request->get('from'),
      'chatKey' => $main->getParam(STORAGE_SUPPORT_KEY),
      'type'    => $request->request->get('type'),
      'text'    => $main->getParam('content'),
    ];

    $bot = new Bot(['message' => $data], false,  $main->getParam('token'));
    $result['error'] = $bot->sendToBot()->getError();
    break;
  case 'loadMessages':
    $result['data'] = $main->db->loadMessages();
    break;

  default: die('default action');
}

$main->send($result);
