<?php
session_start();

if (!isset($_SESSION['loggedin'])) {
  die("You shouldn't be here.");
};

require_once('settings.php');

$s3 = new S3(ACCESS_KEY, SECRET_KEY);
$newFolderError = false;
$newFolderErrorMessage = '';
$editFolderError = false;
$ediFolderErrorMessage = '';
$editMode = false;
$thisFolderId = 0;
$thisFolderData = array(
    "id" => null,
    "folderName" => "Make a New Folder",
    "inFolderId" => 0
);

if ($_GET) {
  if (isset($_GET["folderId"]) && true === is_numeric($_GET['folderId'])) {
    $thisFolderId = $_GET['folderId'];
    $requestedFolderData = getFolderData($thisFolderId);
    if (count($requestedFolderData) > 0) {
      $thisFolderData = $requestedFolderData;
      $editMode = true;
    }
  }
}

// $_POST actions
if ($_POST) {
  // make a new folder if the entered values are valid
  if (isset($_POST["createNewFolder"])) {
    try {
      $newFolderName = sanitizeString($_POST["newFolderName"]);
      $selectedFolderId = $_POST["selectedFolder"];

      if (false === is_numeric($selectedFolderId)) {
        throw new RuntimeException('The parent folder is invalid.');
      }

      if (false === validFolderName($newFolderName)) {
        throw new RuntimeException('The name you entered is not valid because it contains weird characters. Please try a different name.');
      }

      $storedFolder = storeNewFolder($newFolderName, $selectedFolderId);

      if (false === $storedFolder) {
        throw new RuntimeException('Couldn\'t create the folder because of a database error.');
      } else {
        header('Location: ' . PATH_INDEX . '?folderId=' . $storedFolder);
        die();
      }

    } catch (RuntimeException $e) {
      $newFolderError = true;
      $newFolderErrorMessage = $e->getMessage();
    }
  }

  // update an existing folder if the entered values aren't garbage from hell
  if (isset($_POST["editFolder"])) {
    try {
      $editFolderName = sanitizeString($_POST["editFolderName"]);
      $selectedFolderId = $_POST["selectedFolder"];
      $folderId = $_POST["folderId"];

      if (false === is_numeric($folderId)) {
        throw new RuntimeException('The folder ID is invalid.');
      }

      if (false === is_numeric($selectedFolderId)) {
        throw new RuntimeException('The parent folder is invalid.');
      }

      if ($selectedFolderId === $folderId) {
        throw new RuntimeException('You can\'t move a folder inside itself. That would be weird.');
      }

      if (false === validFolderName($editFolderName)) {
        throw new RuntimeException('The name you entered is not valid because it contains weird characters. Please try a different name.');
      }

      $updatedFolder = updateFolder($editFolderName, $selectedFolderId, $folderId);

      if (false === $updatedFolder) {
        throw new RuntimeException('Couldn\'t update the folder because of a database error.');
      } else {
        header('Location: ' . PATH_INDEX . '?updated=true&folderId=' . $folderId);
        die();
      }

    } catch (RuntimeException $e) {
      $editFolderError = true;
      $editFolderErrorMessage = $e->getMessage();
    }
  }
}

require_once('inc/header.php');
?>

<div class="grid-container">
  <div class="grid-x grid-padding-x">
    <div class="small-6 medium-3 large-2 cell">
      <?php
        require_once('inc/folders-list.php');
        require_once('inc/logout-form.php');
      ?>
    </div>

    <div class="small-6 medium-9 large-10 cell">
      <h2>
        <?php
          if ($editMode) { echo 'Edit '; }
          echo $thisFolderData["folderName"];
        ?>
      </h2>
      <hr />

      <div class="grid-x grid-padding-x">
        <div class="cell small-12 medium-6">
          <?php
            if ($editMode) {
              if ($editFolderError) { renderCallout('alert', $editFolderErrorMessage); }
              require_once('inc/edit-folder-form.php');
            } else {
              if ($newFolderError) { renderCallout('alert', $newFolderErrorMessage); }
              require_once('inc/new-folder-form.php');
            }
          ?>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require_once('inc/footer.php'); ?>
