<div class="cell">
  <div class="card">
    <a href="<?php echo BUCKET_URL . $upload['s3key']; ?>" target="_blank" class="uploadThumbnail">
      <img src="thumbnails/<?php echo str_replace('.png', '.jpg', $upload['s3key']); ?>" />
    </a>
    <div class="card-section uploadActions">
      <input type="text" value="<?php echo BUCKET_URL . $upload['s3key']; ?>" />
      <form action="index.php" method="post">
        <input type="hidden" name="delete" value="<?php echo $upload['id']; ?>" />
        <input type="hidden" name="s3key" value="<?php echo $upload['s3key']; ?>" />
        <button class="tiny" value="Delete" type="submit">
          <i class="fa fa-trash" aria-hidden="true"></i>
        </button>
      </form>
    </div>
  </div>
</div>
