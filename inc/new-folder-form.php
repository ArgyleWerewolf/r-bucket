<fieldset>
  <div>
    <form action="<?php echo PATH_FOLDER; ?>" method="post">
      <input type="hidden" name="createNewFolder" value="1" />

      <label for="newFolderName">Folder Name</label>
      <input id="newFolderName" required name="newFolderName" type="text" value="<?php echo (isset($_POST["createNewFolder"])) ? $_POST["newFolderName"] : ''; ?>" maxlength="255" />

      <label for="selectedFolder">Inside Folder</label>
      <?php renderFoldersSelectBox(); ?>

      <button class="button" type="submit">Create</button>
      <a class="button secondary" href="<?php echo PATH_INDEX; ?>">Cancel</a>
    </div>
  </form>
</fieldset>

