<?php

require_once __DIR__ . '/func.php';

const SUPPORT_HOST = 'https://vistegra.by/support/';
const STORAGE_SUPPORT_KEY = 'support-user-key';
const TG_SECRET_TOKEN_KEY = 'X-Telegram-Bot-Api-Secret-Token';
const POOLING_TIME_OUT = 30;

const DB_CONFIG = [
  'dbHost'     => 'MariaDB-10.3',
  'dbName'     => 'support',
  'dbUsername' => 'root',
  'dbPass'     => ''
];

spl_autoload_register('cmsAutoloader');
