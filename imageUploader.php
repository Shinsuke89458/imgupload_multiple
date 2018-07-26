<?php

namespace MyApp;

class ImageUploader {

  private $_imageFileName;
  private $_imageType;
  private $_errorFlag = TRUE;
  private $_errorMessage = '';
  private $_successMessage = '';

  /********** upload **********/
  public function upload() {
    try {

      $files = $this->_devideImages();

      foreach ($files as $file) {
        $this->_validateUpload($file);
        if ($this->_errorFlag === FALSE) {
          $this->_errorFlag = TRUE;
          continue;
        }
        $ext = $this->_validateImageType($file);
        if ($this->_errorFlag === FALSE) {
          $this->_errorFlag = TRUE;
          continue;
        }
        $savePath = $this->_save($file, $ext);
        $this->_createTumbnail($savePath);
        $this->_errorFlag = TRUE;
        $this->_successMessage .= '<p>'.$file['image']['name'].' is Upload Done!</p>';
        // $this->_successMessage .= ($this->_successMessage === '')? $file['image']['name'].' is Upload Done!': '<br>'.$file['image']['name'].' is Upload Done!';
      }

      // $_SESSION['success'] = 'Upload Done!';
      $_SESSION['success'] = $this->_successMessage;

      if ($this->_errorMessage !== '') {
        throw new \Exception($this->_errorMessage);
      }

    } catch (\Exception $e) {
      $_SESSION['error'] = $e->getMessage();
      // echo $e->getMessage();
      // exit;
    }
    header('Location: http://'.$_SERVER['HTTP_HOST']);
    exit;
  }

  private function _devideImages() {
    $imagesCount = count($_FILES['image']['error']);
    for ($i = 0; $i < $imagesCount; $i++) {
      $files[$i]['image'] = [
        'name' => $_FILES['image']['name'][$i],
        'type' => $_FILES['image']['type'][$i],
        'tmp_name' => $_FILES['image']['tmp_name'][$i],
        'error' => $_FILES['image']['error'][$i],
        'size' => $_FILES['image']['size'][$i],
      ];
    }
    return $files;
  }

  private function _createTumbnail($savePath) {
    $imageSize = getimagesize($savePath);
    $width = $imageSize[0];
    $height = $imageSize[1];
    if ($width > THUMBNAIL_WIDTH) {
      $this->_createTumbnailMain($savePath, $width, $height);
    }
  }
  private function _createTumbnailMain($savePath, $width, $height) {
    switch ($this->_imageType) {
      case IMAGETYPE_GIF:
        $srcImage = imagecreatefromgif($savePath);
        break;
      case IMAGETYPE_JPEG:
        $srcImage = imagecreatefromjpeg($savePath);
        break;
      case IMAGETYPE_PNG:
        $srcImage = imagecreatefrompng($savePath);
        break;
    }
    $thumbHeight = round($height * THUMBNAIL_WIDTH / $width);
    $thumbImage = imagecreatetruecolor(THUMBNAIL_WIDTH, $thumbHeight);
    imagecopyresampled($thumbImage, $srcImage, 0, 0, 0, 0, THUMBNAIL_WIDTH, $thumbHeight, $width, $height);
    switch ($this->_imageType) {
      case IMAGETYPE_GIF:
        imagegif($thumbImage, THUMBNAILS_DIR.'/'.$this->_imageFileName);
        break;
      case IMAGETYPE_JPEG:
        imagejpeg($thumbImage, THUMBNAILS_DIR.'/'.$this->_imageFileName);
        break;
      case IMAGETYPE_PNG:
        imagepng($thumbImage, THUMBNAILS_DIR.'/'.$this->_imageFileName);
        break;
    }
  }

  private function _save($file, $ext) {
    $this->_imageFileName = sprintf('%s_%s.%s', time(), sha1(uniqid(mt_rand(), true)), $ext);
    $savePath = IMAGES_DIR.'/'.$this->_imageFileName;
    // $res = move_uploaded_file($_FILES['image']['tmp_name'], $savePath);
    $res = move_uploaded_file($file['image']['tmp_name'], $savePath);
    if ($res === false) {
      // throw new \Exception('Could not upload!');
      $this->_errorFlag = FALSE;
      $this->_errorMessage .= '<p>'.$file['image']['name'].' is Could not upload!</p>';
    }
    return $savePath;
  }

  private function _validateImageType($file) {
    // $this->_imageType = exif_imagetype($_FILES['image']['tmp_name']);
    $this->_imageType = exif_imagetype($file['image']['tmp_name']);
    switch ($this->_imageType) {
      case IMAGETYPE_GIF:
        return 'gif';
        break;
      case IMAGETYPE_JPEG:
        return 'jpg';
        break;
      case IMAGETYPE_PNG:
        return 'png';
        break;
      default:
        // throw new \Exception('PNG/JPEG/GIF only!');
        $this->_errorFlag = FALSE;
        $this->_errorMessage .= '<p>'.$file['image']['name'].' is not PNG/JPEG/GIF!</p>';
    }
  }

  private function _validateUpload($file) {
    // if (!isset($_FILES['image']) || !isset($_FILES['image']['error'])) {
    if (!isset($file['image']) || !isset($file['image']['error'])) {
      // throw new \Exception('Upload Error!');
      $this->_errorFlag = FALSE;
      $this->_errorMessage .= '<p>'.$file['image']['name'].' is Upload Error!</p>';
    }
    // switch ($_FILES['image']['error']) {
    switch ($file['image']['error']) {
      case UPLOAD_ERR_OK:
        return true;
        break;
      case UPLOAD_ERR_INI_SIZE:
      case UPLOAD_ERR_FORM_SIZE:
        // throw new \Exception('File too large!');
        $this->_errorFlag = FALSE;
        $this->_errorMessage .= '<p>'.$file['image']['name'].' is File too large!</p>';
        break;
      default:
        // throw new \Exception('Err: '.$_FILES['image']['error']);
        // throw new \Exception('Err: '.$file['image']['error']);
        $this->_errorFlag = FALSE;
        $this->_errorMessage .= $file['image']['name'].' is '.$file['image']['error'].'<br>';
    }
  }


  /********** getResults **********/
  public function getResults() {
    $success = NULL;
    $error = NULL;
    if (isset($_SESSION['success'])) {
      $success = $_SESSION['success'];
      unset($_SESSION['success']);
    }
    if (isset($_SESSION['error'])) {
      $error = $_SESSION['error'];
      unset($_SESSION['error']);
    }
    return [$success, $error];
  }


  /********** getImages **********/
  public function getImages() {
    $images = [];
    $files = [];
    $imageDir = opendir(IMAGES_DIR);
    while (false !== ($file = readdir($imageDir))) {
      if ($file === '.' || $file === '..') {
        continue;
      }
      $files[] = $file;
      if (file_exists(THUMBNAILS_DIR.'/'.$file)) {
        $images[] = basename(THUMBNAILS_DIR).'/'.$file;
      } else {
        $images[] = basename(IMAGES_DIR).'/'.$file;
      }
    }
    array_multisort($files, SORT_DESC, $images);
    return $images;
  }


}
