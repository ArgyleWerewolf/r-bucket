<h4><a href="./"><?php echo SITE_TITLE; ?></a></h4>
<ul>
<?php
  $rootFolders = listFoldersInFolderId();
  if (count($rootFolders) > 0) {
    foreach($rootFolders as $folder) {
?>
    <li>
      <a href="<?php echo PATH_INDEX; ?>?folderId=<?php echo $folder['id']; ?>"><?php echo $folder['folderName']; ?></a>
      <?php
        $children = listFoldersInFolderId($folder['id']);
        if (count($children) > 0) {
          echo "<ul>";
          foreach($children as $child) {
          ?>
          <li>
            <a href="<?php echo PATH_INDEX; ?>?folderId=<?php echo $child['id']; ?>"><?php echo $child['folderName']; ?></a>
            <?php
              $grandchildren = listFoldersInFolderId($child['id']);
              if (count($grandchildren) > 0) {
                echo "<ul>";
                foreach($grandchildren as $grandchild) {
                ?>
                <li>
                  <a href="<?php echo PATH_INDEX; ?>?folderId=<?php echo $grandchild['id']; ?>"><?php echo $grandchild['folderName']; ?></a>
                </li>
                <?php }
                echo "</ul>";
              }
          }
          echo "</ul>";
        }
      ?>
    </li>
<?php
    }
  }
?>
</ul>

<hr />

<p><a href="<?php echo PATH_FOLDER; ?>">+ New Folder</a></p>

<hr />
