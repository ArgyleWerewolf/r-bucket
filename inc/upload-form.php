<?php
if ($thisFolderData['id'] > 0) {
  $uploadFormActionUrl = PATH_INDEX . "?folderId=" . $thisFolderData['id'];
} else {
  $uploadFormActionUrl = PATH_INDEX;
}
?>

<form action="<?php echo $uploadFormActionUrl; ?>" method="post" enctype="multipart/form-data">
  <input type="hidden" name="upload" value="1" />
  <input type="hidden" name="inFolderId" value="<?php echo $thisFolderData['id']; ?>" />
  <input id="file" type="file" name="file" />
  <input class="button success" value="Upload File" type="submit">
</form>
