<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class Main {
  /**
   * @var array
   */
  private $setting = [];

  /**
   * @var array
   */
  private $param = [];

  /**
   * @var boolean
   */
  public $frontSettingInit = false;
  /**
   * @var Request
   */
  public $request;
  /**
   * @var Response
   */
  public $response;
  /**
   * @var Db
   */
  public $db;


  /**
   * Main constructor.
   * @param array $param
   */
  public function __construct(array $param) {
    $this->setSettings(VC::DB_CONFIG, DB_CONFIG);

    $this->db = new Db($this);
    $this->request = Request::createFromGlobals();
    $this->response = new Response();

    $this->setParam($this->request->request->all());
    $this->updateUniqueKey();
  }

  private function updateUniqueKey() {
    $userKey = $this->request->request->get(STORAGE_SUPPORT_KEY);
    $userKey = $userKey ?? uniqid('', true);

    $this->setParam(STORAGE_SUPPORT_KEY, $userKey);
  }

  /* -------------------------------------------------------------------------------------------------------------------
    Request
  --------------------------------------------------------------------------------------------------------------------*/

  /* -------------------------------------------------------------------------------------------------------------------
    Params
  --------------------------------------------------------------------------------------------------------------------*/

  /**
   * Set param
   * @param string[]|string $key
   * @param $value
   *
   * @return Main
   */
  public function setParam($key, $value = null): Main {
    if (is_array($key)) {
      array_walk($key, function ($item, $key) {
        $this->param[$key] = $item;
      });
    }

    else if ($value !== null) {
      $this->param[$key] = $value;
    }

    return $this;
  }

  /**
   * @param string $key
   * @return mixed
   */
  public function getParam(string $key) {
    return $this->param[$key] ?? null;
  }

  /* -------------------------------------------------------------------------------------------------------------------
    Settings
  --------------------------------------------------------------------------------------------------------------------*/

  /**
   * Load setting from file
   *
   * @return Main
   */
  /*private function loadSetting(): Main {
    $setting = [];
    $settingPath = $this->url->getPath(true) . self::SETTINGS_PATH;

    if (file_exists($settingPath)) {
      $setting = json_decode(file_get_contents($settingPath), true);
    }

    return $this;
  }*/

  /**
   * @param string $key
   * @param mixed $value
   * @return $this
   */
  public function setSettings(string $key, $value): Main {
    $this->setting[$key] = $value;

    return $this;
  }

  /**
   * Save cms setting to file
   */
  /*public function saveSettings() {
    $content = $this->setting;

    unset($content['permission'], $content[VC::DB_CONFIG]);

    file_put_contents($this->url->getPath(true) . self::SETTINGS_PATH, json_encode($content));
  }*/

  /**
   * Get one setting or array if it has
   * @param string $key [
   * 'json' - return json, <p>
   * 'managerFields' - return managers custom fields, <p>
   * 'mailTarget' - <p>
   * 'mailTargetCopy' - <p>
   * 'mailSubject' - <p>
   * 'mailFromName' - <p>
   * 'optionProperties' - <p>
   * @param boolean $front if true - ready html input
   * @return mixed
   */
  public function getSettings(string $key = '', bool $front = false) {
    $data = $this->setting[$key] ?? null;
    $jsonData = $key === 'json' || $front ? json_encode($data ?: $this->setting) : '';

    if ($front) {
      $this->frontSettingInit = true;
      return "<input type='hidden' id='dataSettings' value='$jsonData'>";
    }
    else if ($key === 'json') return $jsonData;

    return empty($key) ? $this->setting : ($this->setting[$key] ?? null);
  }

  public function reDirect(string $target = '') {
    if ($target === '') {
      $target = $_SESSION['target'] ?? '';
      isset($_GET['orderId']) && $target .= '?orderId=' . $_GET['orderId'];
    }
    header('Location: ' . $this->url->getUri() . $target, true, 303);
    die;
  }


  /**
   * Check if there is an error
   * Deep search for all error messages and return as an array
   * @param array $result
   */
  private function checkError(array &$result): void {
    $error = [];
    if (!empty($result['error'])) {
      if (is_array($result['error'])) {
        array_walk_recursive($result['error'], function ($v, $k) use (&$error) {
          if (empty($v)) return;
          $error[] = [$k => $v];
        });
      } else $error = $result['error'];
    }

    if ($result['status'] = empty($error)) unset($result['error']);
    else $result['error'] = $error;
  }

  /**
   * Set the content on the response.
   *
   * @param mixed $content
   * @return string
   */
  public function prepareContent($content): string {
    if ($content !== null && !is_string($content) && !is_array($content) && !is_object($content) && !is_callable([$content, '__toString'])) {
      die(sprintf('The Response content must be a string or object implementing __toString(), "%s" given.', gettype($content)));
    }

    //$this->original = $content;

    // If the content is "JSON" we will set the appropriate header and convert
    // the content to JSON. This is useful when returning something like models
    // from routes that will be automatically transformed to their JSON form.
    if (is_object($content) || is_array($content)) {
      $this->response->headers->set('Content-Type', 'application/json');

      $this->checkError($content);
      $content = json_encode($content);
    }

    return (string) $content;
  }

  public function send($result) {
    $this->response->setContent($this->prepareContent($result))->send();
  }
}
