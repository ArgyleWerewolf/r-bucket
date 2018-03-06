<?php
session_start();

// Presentation
define('SITE_TITLE', 'Tandye Bucket');
define('BUCKET_URL', 'https://s3-us-west-2.amazonaws.com/tandye-art-bucket/');

// AWS access info
define('ACCESS_KEY', 'AKIAI5FZWZLA67ZXCOUA');
define('SECRET_KEY', '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');
define('BUCKET_NAME', 'tandye-art-bucket');
define('USER_NAME', 'tandye');
define('USER_PASS', '7e098c088fe9aa89a9dbbae9d14333b21948a5df');

// Thumbnails
define('THUMBNAIL_DIR', 'thumbnails');
define('THUMBNAIL_MAX_DIM', 600);
define('THUMBNAIL_QUALITY', 75);

require_once('lib/functions.php');
require_once('lib/S3.php');

$s3 = new S3(ACCESS_KEY, SECRET_KEY);
$loginError = false;
$uploadError = false;
$uploadErrorMessage = '';
$uploadSuccess = false;
$uploadSuccessMessage = '';
$deleteError = false;
$deleteErrorMessage = '';
$deleteSuccess = false;
$deleteSuccessMessage = '';

if ($_POST) {

  // authenticate
  if ($_POST["login"] ) {
    if (trim($_POST["username"]) == USER_NAME &&  sha1($_POST["password"]) == USER_PASS) {
      $_SESSION['loggedin'] = true;
    } else {
      $loginError = true;
    }

  // log out
  } elseif (isset($_POST['logout']) && $_SESSION['loggedin']){
    $_SESSION['loggedin'] = false;

  // upload
  } elseif (isset($_POST['upload']) && $_SESSION['loggedin']){
    try {
      $fileName = $_FILES['file']['name'];

      if (!isset($_FILES['file']['error']) || is_array($_FILES['file']['error'])) {
        throw new RuntimeException('Invalid parameters.');
      }

      // Check $_FILES['upfile']['error'] value.
      switch ($_FILES['file']['error']) {
        case UPLOAD_ERR_OK:
            break;
        case UPLOAD_ERR_NO_FILE:
            throw new RuntimeException('No file sent.');
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            throw new RuntimeException('Exceeded filesize limit.');
        default:
            throw new RuntimeException('Unknown errors.');
      }

      // DO NOT TRUST $_FILES['upfile']['mime'] VALUE!
      // Check MIME Type by yourself.
      $finfo = new finfo(FILEINFO_MIME_TYPE);
      if (false === $ext = array_search(
          $finfo->file($_FILES['file']['tmp_name']),
          array(
              'jpg' => 'image/jpeg',
              'png' => 'image/png'
          ),
          true
      )) {
          throw new RuntimeException('Invalid file format. JPEGs and PNGs only, please.');
      }

      $tempFile = $_FILES['file']['tmp_name'];
      $safeName = sha1_file($tempFile) . "." . $ext;

      // process thumbnail
      list($width, $height, $type, $attr) = getimagesize( $tempFile );
      $ratio = $width/$height;
      if( $ratio > 1) {
        $new_width = THUMBNAIL_MAX_DIM;
        $new_height = THUMBNAIL_MAX_DIM/$ratio;
      } else {
        $new_width = THUMBNAIL_MAX_DIM*$ratio;
        $new_height = THUMBNAIL_MAX_DIM;
      }
      $src = imagecreatefromstring( file_get_contents( $tempFile ) );
      $dst = imagecreatetruecolor( $new_width, $new_height );
      imagecopyresampled( $dst, $src, 0, 0, 0, 0, $new_width, $new_height, $width, $height );
      imagedestroy( $src );
      if (!imagejpeg( $dst, THUMBNAIL_DIR . '/' . sha1_file($tempFile) . ".jpg", THUMBNAIL_QUALITY)) {
        throw new RuntimeException('Couldn\'t generate a thumbnail image.');
      }
      imagedestroy( $dst );

      // save image record to db
      if (false === storeImageRecord($fileName, $safeName)) {
        throw new RuntimeException('Couldn\'t store the image record.');
      }

      // upload file to S3
      if (false === uploadPhoto($s3, BUCKET_NAME, $tempFile, $safeName, $_FILES['file']['type'])) {
        throw new RuntimeException('Couldn\'t upload the original image to S3.');
      }

      $uploadSuccess = true;
      $uploadSuccessMessage = "The image was successfully uploaded.";

    } catch (RuntimeException $e) {
      $uploadError = true;
      $uploadErrorMessage = $e->getMessage();
    }

  // delete
  } elseif (isset($_POST['delete']) && isset($_POST['s3key']) && $_SESSION['loggedin']) {
    try {
      if (false === is_numeric($_POST['delete'])) {
        throw new RuntimeException('Not a valid image ID.');
      }

      // delete thumbnail
      if (false === deleteImageThumbnail($_POST['delete'])) {
        throw new RuntimeException('Couldn\'t delete the image thumbnail.');
      }

      $s3->deleteObject(BUCKET_NAME, baseName($_POST['s3key']));

      // delete DB entry
      if (false === deleteImageRecord($_POST['delete'])) {
        throw new RuntimeException('Couldn\'t delete the database record.');
      }

      $deleteSuccess = true;
      $deleteSuccessMessage = 'The image was successfully deleted.';

    } catch (RuntimeException $e) {
      $deleteError = true;
      $deleteErrorMessage = $e->getMessage();
    }

  }

}

?>

<html>
<head>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/foundation/6.4.1/css/foundation.min.css" />
  <title><?php echo SITE_TITLE; ?></title>
</head>
<body>
<div class="grid-container">
  <div class="grid-x grid-padding-x">
    <div class="small-8 cell">
      <h1><?php echo SITE_TITLE; ?></h1>
    </div>
    <div class="small-4 cell text-right">
    <?php if (isset($_SESSION['loggedin'])) { ?>
      <form action="index.php" method="post">
        <input type="hidden" name="logout" value="1" />
        <br />
        <input class="button tiny warning" value="Log Out" type="submit">
      </form>
    <?php } ?>
    </div>
  </div>

  <?php if (isset($_SESSION['loggedin'])) { ?>

  <div class="grid-x grid-padding-x">
    <div class="small-6 cell">

      <?php if ($uploadError) { ?>
      <div class="callout alert">
        <span><?php echo $uploadErrorMessage; ?></span>
      </div>
      <?php } ?>

      <?php if ($uploadSuccess) { ?>
      <div class="callout success">
        <span><?php echo $uploadSuccessMessage; ?></span>
      </div>
      <?php } ?>

      <?php if ($deleteError) { ?>
      <div class="callout alert">
        <span><?php echo $deleteErrorMessage; ?></span>
      </div>
      <?php } ?>

      <?php if ($deleteSuccess) { ?>
      <div class="callout success">
        <span><?php echo $deleteSuccessMessage; ?></span>
      </div>
      <?php } ?>

      <form action="index.php" method="post" enctype="multipart/form-data">
        <input type="hidden" name="upload" value="1" />
        <input id="file" type="file" name="file" />
        <input class="button success" value="Upload File" type="submit">
      </form>
    </div>
  </div>

  <hr />

  <div class="grid-x grid-padding-x small-up-2 medium-up-4">
    <?php
      $recentUploads = listRecentUploads();
      if (count($recentUploads) > 0) {
        foreach($recentUploads as $upload) {
    ?>
    <div class="cell">
      <div class="card">
        <a href="<?php echo BUCKET_URL . $upload['s3key']; ?>" target="_blank">
          <img src="thumbnails/<?php echo str_replace('.png', '.jpg', $upload['s3key']); ?>" />
        </a>
        <div class="card-section">
          <input type="text" value="<?php echo BUCKET_URL . $upload['s3key']; ?>" />
          <form action="index.php" method="post">
            <input type="hidden" name="delete" value="<?php echo $upload['id']; ?>" />
            <input type="hidden" name="s3key" value="<?php echo $upload['s3key']; ?>" />
            <input class="button tiny warning" value="Delete" type="submit" />
          </form>
        </div>
      </div>
    </div>
    <?php
        }
      }
    ?>
  </div>

  <? } else { ?>

  <div class="grid-x grid-padding-x">
    <div class="small-6 cell">

      <?php if ($loginError) { ?>
      <div class="callout alert">
        <span>Wrong credentials.</span>
      </div>
      <?php } ?>
      <form action="index.php" method="post">
        <input type="hidden" name="login" value="1" />

        <label for="username">Username</label>
        <input id="username" name="username" type="text" value="<?php echo (isset($_POST["username"])) ? $_POST["username"] : ''; ?>" maxlength="255" />

        <label for="password">Password</label>
        <input id="password" name="password" type="password" autocomplete="off" value="<?php echo (isset($_POST["password"])) ? $_POST["password"] : ''; ?>" maxlength="255" />

        <button class="button" type="submit">Sign In</button>
      </form>
    </div>
  </div>

  <? } ?>

</div>
</body>
</html>
