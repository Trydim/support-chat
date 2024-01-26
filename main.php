<?php

require __DIR__ . '/php/libs/vendor/autoload.php';
require __DIR__ . '/php/app.php';

$main = new Main(['DEBUG' => true]);

$action = $main->getParam('action');
$result[STORAGE_SUPPORT_KEY] = $main->getParam(STORAGE_SUPPORT_KEY);

switch ($action) {
  case 'addMessage':
    $request = $main->request;
    $result['msgId'] = $main->db->addMessage();

    $data = [
      'host'    => $request->server->get('HTTP_ORIGIN'),
      'chatKey' => $main->getParam(STORAGE_SUPPORT_KEY),
      'type'    => $request->request->get('type'),
      'text'    => $main->getParam('content'),
    ];

    $result['error'] = (new Bot(['message' => $data], false))->sendToBot()->getError();
    break;
  case 'loadMessages':
    $result['data'] = $main->db->loadMessages();
    break;

  default: die('default action');
}

$main->send($result);
