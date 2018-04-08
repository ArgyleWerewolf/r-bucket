<?php
if ($thisFolderData['id'] > 0) {
  $multiActionUrl = PATH_INDEX . "?folderId=" . $thisFolderData['id'];
} else {
  $multiActionUrl = PATH_INDEX;
}
if ($multiError) { renderCallout('alert', $multiErrorMessage); }
if ($multiSuccess) { renderCallout('success', $multiSuccessMessage); }
?>

<div id="uploadMultiActions" class="hide">
  <div class="grid-x grid-padding-x">
    <div class="small-6 medium-8 cell">
      <form action="<?php echo $multiActionUrl; ?>" method="post">
        <input type="hidden" name="multiMove" value="1" />
        <input type="hidden" name="multiMoveIds" class="multiIds" value="" />
        <div class="input-group">
          <div class="input-group-button">
            <button class="button" type="submit">
              Move Selected <i class="fa fa-files-o" aria-hidden="true"></i>
            </button>
          </div>
          <?php renderFoldersSelectBox(); ?>
        </div>
      </form>
    </div>
    <div class="small-6 medium-4 cell text-right">
      <form action="<?php echo $multiActionUrl; ?>" method="post">
        <input type="hidden" name="multiDelete" value="1" />
        <input type="hidden" name="multiDeleteIds" class="multiIds" value="" />
        <button class="button" type="submit" onclick="return confirm('Are you sure you want to delete the selected item(s)? This cannot be undone!');">
          Delete Selected <i class="fa fa-trash" aria-hidden="true"></i>
        </button>
      </form>
    </div>
  </div>
</div>
