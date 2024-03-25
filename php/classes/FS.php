<?php


class FS {
  const UPLOAD_DIR = __DIR__ . '/../../storage/upload';
  const UPLOAD_URL = 'storage/upload';

  const MAX_SIZE_WIDTH       = 1000;
  const MAX_SIZE_HEIGHT      = 1000;
  const PREV_MAX_SIZE_WIDTH  = 300;
  const PREV_MAX_SIZE_HEIGHT = 300;

  /**
   * @var string
   */
  private $absUploadDir;
  /**
   * @var string
   */
  private $fileUrl;
  /**
   * @var object
   */
  private $param;

  public function __construct(Main $main) {
    $url = $main->request->server->get('HTTP_HOST') === 'vistegra.by' ? 'https://vistegra.by/support/' : $main->request->server->get('HTTP_REFERER');

    $this->absUploadDir = $this::UPLOAD_DIR . DIRECTORY_SEPARATOR;
    $this->fileUrl = $url . $this::UPLOAD_URL . '/';
    $this->param = new class {};
  }

  private function setFileParam($file) {
    $ext = $file->getClientOriginalExtension();
    $name = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

    if (file_exists($this->absUploadDir . $name . ".$ext")) $name .= uniqid();

    $this->param->ext  = $ext;
    $this->param->name = $name . ".$ext";
    $this->param->type = $file->getType();
    $this->param->size = $file->getSize();
    $this->param->uri  = $this->fileUrl . $this->param->name;
  }

  private function move($file) {
    if (!move_uploaded_file($file->getRealPath(), $this->absUploadDir . $this->param->name))
      throw new Error('Moving file error: ' . $this->param->name);
  }

  public function optimize($file) {
    $ext = $this->param->ext;
    $filePath = $file->getRealPath();

    $fileResUpload = $fileRes = self::createImageFile($filePath, $ext);

    // размер
    $maxWidth = self::MAX_SIZE_WIDTH;
    $maxHeight = self::MAX_SIZE_HEIGHT;
    if (imagesx($fileResUpload) > $maxWidth || imagesy($fileResUpload) > $maxHeight) {
      $fileRes = self::imageResize($fileResUpload, $maxWidth, $maxHeight, true);
    }
  }

  static function imageResize($resource, $width, $height, $saveRatio = false) {
    if (is_string($resource) && file_exists($resource)) {
      $resource = self::createImageFile($resource);
    }

    $rWidth = imagesx($resource);
    $rHeight = imagesy($resource);

    if ($saveRatio) {
      if ($width < $height) {
        $ratio = $width / $rWidth;
        $height = ceil($rHeight * $ratio);
      } else {
        $ratio = $height / $rHeight;
        $width = ceil($rWidth * $ratio);
      }
    }

    $destination = imagecreatetruecolor($width, $height);
    $backgroundColor = imagecolorallocate($destination, 255, 255, 255);
    imagefill($destination, 0, 0, $backgroundColor);
    imagefilledrectangle($destination, 0, 0, $width, $height, $backgroundColor);
    imagecopyresized($destination, $resource, 0, 0, 0, 0, $width, $height, $rWidth, $rHeight);
    return $destination;
  }

  static function createImageFile($filePath, $ext = null) {
    switch ($ext ?? pathinfo($filePath, PATHINFO_EXTENSION)) {
      default:
      case 'jpg': case 'jpeg': case 'image/jpeg':
      return imagecreatefromjpeg($filePath);
      case 'png': case 'image/png':
      return imagecreatefrompng($filePath);
      case 'webp': case 'image/webp':
      return imagecreatefromwebp($filePath);
    }
  }

  /**
   * @param $file
   * @return FS
   */
  public function prepareFile($file): FS {
    $this->setFileParam($file);
    //$this->optimize($file);
    $this->move($file);

    return $this;
  }

  public function getUri(): string {
    return $this->param->uri;
  }
}
