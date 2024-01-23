<?php

require __DIR__ . '/php/libs/vendor/autoload.php';
require __DIR__ . '/php/app.php';

$main = new Main(['DEBUG' => true]);

$result = [];
$action = $main->getParam('action');

switch ($action) {
  case 'addMessage':
    $request = $main->request;
    $result  = $main->db->addMessage();

    $data = [
      'host'    => $request->server->get('HTTP_HOST'),
      'chatKey' => $request->cookies->get(COOKIE_SUPPORT_KEY),
      'type'    => $request->request->get('type'),
      'text'    => $main->getParam('content'),
    ];

    $result['error'] = (new Bot(['message' => $data]))->sendToBot()->getError();
    break;
  case 'loadMessages':
    $result['data']    = $main->db->loadMessages();
    $result['userKey'] = $main->getParam('userKey');
    break;

  default: die('action error');
}

$main->send($result);
