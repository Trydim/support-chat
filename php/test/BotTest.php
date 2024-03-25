<?php


use PHPUnit\Framework\TestCase;

const CORE = __DIR__ . '/../';

//require CORE . 'classes/Bot.php';

class BotTest extends TestCase {
  const BOT_PATH = CORE . 'app.php';

  private $data = [];

  private function loadBotData(string $file) {
    $this->data = json_decode(file_get_contents(__DIR__ . '/' . $file), true);
  }
  private function getMethod(string $methodName) {
    $class = new ReflectionClass('Bot');
    $method = $class->getMethod($methodName);
    $method->setAccessible(true); // Use this if you are running PHP older than 8.1.0

    return $method;
  }

  public function testCheckReply() {
    require_once self::BOT_PATH;
    $this->loadBotData('botStart.json');
    $bot = new Bot($this->data, true, null);
    $this->assertSame(false, $bot->checkReply());

    $this->loadBotData('botReply.json');
    $bot = new Bot($this->data, true, null);
    $this->assertSame(true, $bot->checkReply());
  }

  public function testGetUser() {
    require_once self::BOT_PATH;
    $this->loadBotData('botStart.json');
    $bot = new Bot($this->data, true, null);

    $send = $this->getMethod('getUser');

    $this->assertSame('trydim85', $send->invoke($bot));
  }

  public function testGetComplete() {

  }

  public function testGetContentFileUri() {

  }

  public function testPrepareMessageId() {

  }

  public function testGetContent() {

  }

  public function testSendErrorMessage() {

  }

  public function testExecCallBack() {

  }

  public function testGetDBMessageId() {

  }

  public function testGetChatKey() {

  }

  public function testGetType() {

  }

  public function testGetAction() {

  }

  public function testAddSupportUser() {

  }

  public function testRemoveSupportUser() {

  }

  public function testSendToBot() {

  }

  public function testGetError() {

  }
}
