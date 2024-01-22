<?php

require __DIR__ . '/php/libs/vendor/autoload.php';
require __DIR__ . '/php/app.php';

$main = new Main([
  'DEBUG' => true,
], [
  'dbHost'     => 'localhost',
  'dbName'     => 'support',
  'dbUsername' => 'root',
  'dbPass'     => ''
]);

$result = [];
$action = $main->getParam('action');

switch ($action) {
  case 'start':
    //$result = $main->db->
    break;
  case 'addMessage':
    $request = $main->request->request;

    $result = $main->db->addMessage();

    (new Bot([
      'type' => $request->get('type') ?? 'text',
      'text' => $request->get('content'),
    ]))->sendToBot();

    break;
  case 'loadMessages':
    $result['data']    = $main->db->loadMessages();
    $result['userKey'] = $main->getParam('userKey');
    break;

  default: die('action error');
}

$main->send($result);
