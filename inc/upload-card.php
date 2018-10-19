<?php
if ($thisFolderData['id'] > 0) {
  $deleteActionUrl = PATH_INDEX . "?folderId=" . $thisFolderData['id'];
} else {
  $deleteActionUrl = PATH_INDEX;
}
?>
<div class="cell">
  <div class="card upload-id--<?php echo $upload['id']; ?>">
    <a href="<?php echo BUCKET_URL . $upload['s3key']; ?>" target="_blank" class="uploadThumbnail">
      <img src="thumbnails/<?php echo str_replace('.png', '.jpg', $upload['s3key']); ?>" />
    </a>
    <div class="card-section uploadActions">
      <div class="input-group">
        <span class="input-group-label">
          <button class="tiny button--linker" value="Copy URL" type="submit" onclick="copyUrlFor('<?php echo $upload['id']; ?>');">
            <i class="fa fa-link" aria-hidden="true"></i>
          </button>
        </span>
        <input id="url-<?php echo $upload['id']; ?>" class="input-group-field input-url" type="text" value="<?php echo BUCKET_URL . $upload['s3key']; ?>" />
      </div>
      <form action="<?php echo $deleteActionUrl; ?>" method="post">
        <input type="hidden" name="delete" value="<?php echo $upload['id']; ?>" />
        <input type="hidden" name="s3key" value="<?php echo $upload['s3key']; ?>" />
        <button class="tiny" value="Delete" type="submit" onclick="return confirm('Are you sure you want to delete this item? This cannot be undone!');">
          <i class="fa fa-trash" aria-hidden="true"></i>
        </button>
      </form>
      <input class="uploadCheck" type="checkbox" value="<?php echo $upload['id']; ?>" />
    </div>
  </div>
</div>
