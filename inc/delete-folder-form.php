<fieldset>
  <div>
    <form action="<?php echo PATH_FOLDER; ?>?folderId=<?php echo $thisFolderData['id']; ?>" method="post">
      <input type="hidden" name="deleteFolder" value="1" />
      <input type="hidden" name="folderId" value="<?php echo $thisFolderData['id']; ?>" />
      <label>Delete This Folder</label>
      <button class="button" type="submit" onclick="return confirm('Are you sure you want to delete this folder? This cannot be undone!');">Delete</button>

      <?php if (count(listUploadsInFolderId($thisFolderData['id'])) > 0) { ?>
        <p><small>This folder is not empty. If you delete it, its contents will be moved to <a href="./">the root level of <?php echo SITE_TITLE; ?></a>.</small></p>
      <?php } ?>
    </div>
  </form>
</fieldset>

