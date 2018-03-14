<fieldset>
  <div>
    <form action="<?php echo UPLOAD_ACTION; ?>" method="post">
      <input type="hidden" name="multiMove" value="1" />
      <div class="input-group">
      <span class="input-group-label">Move selected to</span>
      <?php renderFoldersSelectBox(); ?>
      <div class="input-group-button">
        <input type="submit" class="button" value="Submit">
      </div>
    </div>
  </form>
</fieldset>
