<h4>Folders</h4>
<ul>
  <li><a href="./"><?php echo SITE_TITLE; ?></a></li>
<?php
  $rootFolders = listFoldersInFolderId();
  if (count($rootFolders) > 0) {
    foreach($rootFolders as $folder) {
?>
    <li><a href="?folderId=<?php echo $folder['id']; ?>"><?php echo $folder['folderName']; ?></a></li>
<?php
    }
  }
?>
</ul>

<hr />

<p><a href="folder.php">Add New</a></p>

<hr />
