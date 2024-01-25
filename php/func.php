<?php

/**
 * @param string $class
 */
function cmsAutoloader(string $class) {
  $path = str_replace('\\', DIRECTORY_SEPARATOR, __DIR__ . '/classes/' . $class . '.php');
  if (file_exists($path)) require_once $path;
}

/**
 * @param string $url
 * @param array $config - 'method', 'json' => true (as default) or any, 'json_assoc', 'auth', 'contentType', 'timeout'
 * @param string|array<string, string> $params - assoc array
 * @return string|array
 */
function httpRequest(string $url, array $config = [], $params = []) {
  $myCurl = curl_init();

  $curlConfig = [
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [],
    CURLOPT_TIMEOUT => $config['timeout'] ?? 45,
  ];

  if (isset($config['auth'])) {
    $curlConfig[CURLOPT_HTTPHEADER][] = 'Authorization:' . $config['auth'];
  }

  if (strtolower($config['method'] ?? 'get') === 'get') {
    $curlConfig[CURLOPT_HTTPGET] = true;
    !empty($params) && $curlConfig[CURLOPT_URL] .= '?' . http_build_query($params);
  } else {
    $curlConfig[CURLOPT_HTTPGET] = false;
    $curlConfig[CURLOPT_POST] = true;
    $curlConfig[CURLOPT_HTTPHEADER][] = 'Content-Type: ' . ($config['contentType'] ?? 'application/json; charset=utf-8');
    $curlConfig[CURLOPT_POSTFIELDS]   = $params;
  }

  curl_setopt_array($myCurl, $curlConfig);
  $response = curl_exec($myCurl);

  if ($error = $response === false) {
    $response = [
      'code' => curl_getinfo($myCurl, CURLINFO_HTTP_CODE),
      'error' => curl_error($myCurl),
    ];
  }

  curl_close($myCurl);

  if ($error === false && ($config['json'] ?? true) === true) {
    $res = json_decode($response, $config['json_assoc'] ?? true);

    return json_last_error() === JSON_ERROR_NONE ? $res : 'Json error: ' . $response;
  }

  return $response;
}

if (!function_exists('def')) {
  /**
   * @param $var
   * @param bool $die
   */
  function def($var, bool $die = true) {
    if (is_array($var) || is_object($var)) $var = json_encode($var);
    file_put_contents(__DIR__ . '/debug.json', $var);
    if ($die) die($var);
  }
}
