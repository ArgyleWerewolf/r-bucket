<?php
session_start();

require_once('settings.php');
require_once('credentials.php');

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
$multiError = false;
$multiErrorMessage = '';
$multiSuccess = false;
$multiSuccessMessage = '';
$thisFolderId = 0;
$thisFolderData = array(
    "id" => 0,
    "folderName" => "Tandye Bucket",
    "inFolderId" => 0
);

if ($_GET) {
  if (isset($_GET["folderId"]) && true === is_numeric($_GET['folderId'])) {
    $thisFolderId = $_GET['folderId'];
    $requestedFolderData = getFolderData($thisFolderId);
    if (count($requestedFolderData) > 0) {
      $thisFolderData = $requestedFolderData;
    }
  }
}

require_once('inc/header.php');

// $_POST actions
if ($_POST) {
  // authenticate
  if (isset($_POST["login"])) {
    if (trim($_POST["username"]) == USER_NAME && sha1($_POST["password"]) == sha1(USER_PASS)) {
      $_SESSION['loggedin'] = true;
    } else {
      $loginError = true;
    }

  // log out
  } elseif (isset($_POST['logout']) && isset($_SESSION['loggedin'])){
    session_unset();
  // upload
  } elseif (isset($_POST['upload']) && isset($_SESSION['loggedin'])){
    try {
      $fileName = $_FILES['file']['name'];
      $inFolderId = $_POST['inFolderId'];

      if ( false === count(getFolderData($inFolderId)) > 0 && $inFolderId !== '0') {
        throw new RuntimeException('Invalid destination folder specified.');
      }

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

      if (true === thumbnailAlreadyExists($safeName)) {
          throw new RuntimeException('An image with these exact contents already exists. You cannot upload the same image more than once.');
      }

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
      $imageStoreResult = storeImageRecord($fileName, $safeName, $inFolderId);
      if (true !== $imageStoreResult) {
        throw new RuntimeException('Couldn\'t store the image record. '. $imageStoreResult);
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
  } elseif (isset($_POST['delete']) && isset($_POST['s3key']) && isset($_SESSION['loggedin'])) {
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
  // multi-move
  } elseif (isset($_POST['multiMove'])) {
    $moveIds = idListToArray($_POST['multiMoveIds']);
    $toFolderId = $_POST['selectedFolder'];

    try {

      if ($thisFolderData['id'] == $toFolderId) {
        throw new RuntimeException('Can\'t move images to a folder they\'re already in. Well, it\'s <em>possible</em>, but why would you want to?');
      }

      if (count($moveIds) < 1) {
        throw new RuntimeException('Couldn\'t find any valid image IDs to move.');
      }

      if (false === count(getFolderData($toFolderId)) > 0 && $toFolderId !== '0') {
        throw new RuntimeException('Invalid destination folder specified.');
      }

      if (count(moveUploadIdsToFolderId($moveIds, $toFolderId)) !== count($moveIds)) {
        throw new RuntimeException('Some images couldn\'t be moved because of an error.');
      }

      if ($toFolderId > 0) {
        $destinationFolderPath = PATH_INDEX . "?folderId=" . $toFolderId;
      } else {
        $destinationFolderPath = PATH_INDEX;
      }
      $multiSuccess = true;
      $multiSuccessMessage = 'The images were successfully moved to the <a href="' . $destinationFolderPath . '">folder you chose</a>.';
    } catch (RuntimeException $e) {
      $multiError = true;
      $multiErrorMessage = $e->getMessage();
    }

  // multi-delete
  } elseif (isset($_POST['multiDelete'])) {
    $deleteIds = idListToArray($_POST['multiDeleteIds']);
    $deleted = [];

    try {

      if (count($deleteIds) < 1) {
        throw new RuntimeException('Couldn\'t find any valid image IDs to delete.');
      }

      foreach($deleteIds as $id) {

        $d = getUploadDetails($id);

        // delete thumbnail
        if (false === deleteImageThumbnail($id)) {
          throw new RuntimeException('Couldn\'t delete the image thumbnail.');
        }

        $s3->deleteObject(BUCKET_NAME, baseName($d['s3key']));

        // delete DB entry
        if (false === deleteImageRecord($id)) {
          throw new RuntimeException('Couldn\'t delete the database record.');
        }

        $deleted[] = $id;
      }

      if (count($deleted) !== count($deleteIds)) {
        throw new RuntimeException('Some images couldn\'t be deleted because of an error.');
      }

      $multiSuccess = true;
      $multiSuccessMessage = 'The images were successfully deleted.';
    } catch (RuntimeException $e) {
      $multiError = true;
      $multiErrorMessage = $e->getMessage();
    }
  }
}
?>

<div class="grid-container">

  <?php if (isset($_SESSION['loggedin'])) { ?>

  <?php require_once('inc/upload-form.php'); ?>

  <div class="grid-x grid-padding-x">
    <div class="small-6 medium-3 large-2 cell">
      <?php
        require_once('inc/folders-list.php');
        require_once('inc/logout-form.php');
      ?>
    </div>

    <div class="small-6 medium-9 large-10 cell">
      <h2>
        <?php echo $thisFolderData["folderName"]; ?>
        <?php if (!$thisFolderData['id'] == 0) { ?>
          <a href="folder.php?folderId=<?php echo $thisFolderData['id']; ?>">
            <i class="fa fa-pencil" aria-hidden="true"></i>
          </a>
        <? } ?>
      </h2>

      <?php require_once('inc/upload-action-form.php'); ?>
      <hr />

      <div class="grid-x grid-padding-x small-up-2 medium-up-4">
      <?php
        $uploads = listUploadsInFolderId($thisFolderId);
        if (count($uploads) > 0) {
          foreach($uploads as $upload) {
            include('inc/upload-card.php');
          }
        } else {
      ?>
        <div class="cell">
          <p>This folder is empty.</p>
        </div>
      <?php } ?>
      </div>
    </div>
  </div>

<? } else { ?>

  <div class="grid-x grid-padding-x">
    <div class="small-6 cell">
      <h2>Log In</h2>
      <?php
        if ($loginError) { renderCallout('alert', 'Wrong credentials.'); }
        require_once('inc/login-form.php');
      ?>
    </div>
  </div>

<? } ?>

</div>

<?php require_once('inc/footer.php'); ?>
