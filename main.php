<?php

require __DIR__ . '/php/libs/vendor/autoload.php';
require __DIR__ . '/php/app.php';

$main = new Main(['DEBUG' => true]);

$result = [];
$action = $main->getParam('action');

$supportKey = $main->request->request->get(COOKIE_SUPPORT_KEY);

switch ($action) {
  case 'addMessage':
    $request = $main->request;
    $result  = $main->db->addMessage($supportKey);

    $data = [
      'host'    => $request->server->get('HTTP_HOST'),
      'chatKey' => $supportKey ?? $request->cookies->get(COOKIE_SUPPORT_KEY),
      'type'    => $request->request->get('type'),
      'text'    => $main->getParam('content'),
    ];

    $result['error'] = (new Bot(['message' => $data], false))->sendToBot()->getError();
    break;
  case 'loadMessages':
    $result['data']    = $main->db->loadMessages($supportKey);
    $result['userKey'] = $main->getParam('userKey');
    break;

  default: die('default action');
}

$result[COOKIE_SUPPORT_KEY] = $main->getParam(COOKIE_SUPPORT_KEY);
$main->send($result);
