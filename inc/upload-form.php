<?php
if ($thisFolderData['id'] > 0) {
  $uploadFormActionUrl = PATH_INDEX . "?folderId=" . $thisFolderData['id'];
} else {
  $uploadFormActionUrl = PATH_INDEX;
}
?>

<div class="grid-x grid-padding-x">
  <div class="small-6 cell">
    <?php
      if ($uploadError) { renderCallout('alert', $uploadErrorMessage); }
      if ($uploadSuccess) { renderCallout('success', $uploadSuccessMessage); }
      if ($deleteError) { renderCallout('alert', $deleteErrorMessage); }
      if ($deleteSuccess) { renderCallout('success', $deleteSuccessMessage); }
    ?>
  </div>
</div>

<div class="uploadForm">
  <div class="grid-x grid-padding-x">
    <div class="small-12 cell">

      <form action="<?php echo $uploadFormActionUrl; ?>" method="post" enctype="multipart/form-data">
        <input type="hidden" name="upload" value="1" />
        <input type="hidden" name="inFolderId" value="<?php echo $thisFolderData['id']; ?>" />
        <input id="file" type="file" name="file" style="display:none" onchange="handleFiles(this.files)" />
        <p id="fileSelectorLabelWrapper"><label id="fileSelectorLabel" for="file">Choose a file</label> to upload.</p>
        <span id="fileNamePreview"></span>
        <button id="fileUploadButton" class="button small success hide" type="submit">
          Upload <i class="fa fa-upload" aria-hidden="true"></i>
        </button>
      </form>

    </div>
  </div>
</div>
