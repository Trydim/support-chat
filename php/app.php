<?php

require_once __DIR__ . '/func.php';

const COOKIE_SUPPORT_KEY = 'support-user-key';

const DB_CONFIG = [
  'dbHost'     => 'localhost',
  'dbName'     => 'support',
  'dbUsername' => 'root',
  'dbPass'     => ''
];

spl_autoload_register('cmsAutoloader');

