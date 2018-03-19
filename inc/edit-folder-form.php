<fieldset>
  <div>
    <form action="<?php echo PATH_FOLDER; ?>?folderId=<?php echo $thisFolderData['id']; ?>" method="post">
      <input type="hidden" name="editFolder" value="1" />

      <input type="hidden" name="folderId" value="<?php echo $thisFolderData['id']; ?>" />
      <label for="editFolderName">Folder Name</label>
      <input id="editFolderName" required name="editFolderName" type="text" value="<?php echo (isset($_POST["editFolder"])) ? $_POST["editFolderName"] : $thisFolderData['folderName']; ?>" maxlength="255" />

      <label for="selectedFolder">Inside Folder</label>
      <?php renderFoldersSelectBox(false, $thisFolderData["inFolderId"], $thisFolderData['id']); ?>

      <button class="button" type="submit">Save Changes</button>
      <a class="button secondary" href="<?php echo PATH_INDEX; ?>?folderId=<?php echo $thisFolderData['id']; ?>">Cancel</a>

    </div>
  </form>
</fieldset>

