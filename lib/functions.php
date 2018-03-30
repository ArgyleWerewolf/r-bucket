<?php

// Database
define('DB_ADDRESS', 'localhost');
// define('DB_USERNAME', 'werewolf_tBucket');
// define('DB_PASSWORD', '4yH92ROV*}FWU}yA3h7|A9me`bPC7?UssJIAc3"(77logyREp_pQnQFXJviyF]Vq');
// define('DB_DATABASE', 'werewolf_tandyeBucket');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_DATABASE', 'tandyeBucketDev');

/**
* List bucket's files
* @param object $s3 S3 handler
* @param string $bucket bucket's name
* @param $prefix directory name with forward slash in the end
* @return array files array
*/

function listFiles($s3 = null, $bucket = null , $prefix = null) {
$ls = $s3->getBucket($bucket, $prefix);
if(!empty($ls))  {
foreach($ls as $l) {
$fname = str_replace($prefix,'',$l['name']);
echo $fname;
if(!empty($fname)) { $rv[] = $fname; }
} }
if(!empty($rv)) { return $rv; }
}

/**
* Upload file
* @param object $s3 S3 handler
* @param string $bucket bucket's name
* @param string $file path with file name (it could be also variable $_FILES['file']['tmp_name'])
* @param string $descPath path where file should be written
* @param string $descName file name for uploaded file (with extension)
* @return true on success, false on fail
*/

function uploadPhoto($s3 = null, $bucket  = null, $file = null,  $descName = null, $contentType = null)  {
if(is_file($file))  {
if(empty($descName))  { return false; }
if(!empty($s3) && !empty($bucket)) {
$s3->putObjectFile($file, $bucket, $descName, S3::ACL_PUBLIC_READ, array(), $contentType);return true; }
else { return false; }
}
}

function open_db() {
    return new mysqli(DB_ADDRESS, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
}

function storeImageRecord($fileName, $safeName, $inFolderId) {
    $mysqli = open_db();
    $result = $mysqli->query("INSERT INTO bucket (id, filename, s3key, uploadedWhen, inFolderId) VALUES (NULL, '$fileName', '$safeName', now(), '$inFolderId');");
    mysqli_close($mysqli);
    return $result;
}

function listRecentUploads() {
    $results = array();
    $mysqli = open_db();
    $query = $mysqli->query("SELECT id, filename, s3key FROM bucket ORDER BY uploadedWhen DESC LIMIT 200");
	if ($query->num_rows > 0) {
        while($row = $query->fetch_assoc()){
            $results[] = $row;
        }
	}
	mysqli_close($mysqli);
	return $results;
}

function listUploadsInFolderId( $inFolderId = 0 ) {
    $results = array();
    $mysqli = open_db();
    $query = $mysqli->query("SELECT id, filename, s3key FROM bucket WHERE inFolderId = '$inFolderId' ORDER BY uploadedWhen DESC LIMIT 200");
	if ($query->num_rows > 0) {
        while($row = $query->fetch_assoc()){
            $results[] = $row;
        }
	}
	mysqli_close($mysqli);
	return $results;
}

function deleteImageThumbnail($id) {
    $mysqli = open_db();
    $query = $mysqli->query("SELECT s3key FROM bucket WHERE bucket.id = '$id' LIMIT 1");
	if ($query->num_rows > 0) {
        $row = $query->fetch_assoc();
	}
	mysqli_close($mysqli);
    return unlink(THUMBNAIL_DIR . '/' . str_replace('.png', '.jpg', $row['s3key']));
}

function deleteImageRecord($id) {
    $mysqli = open_db();
    $results = $mysqli->query("DELETE FROM bucket WHERE bucket.id = '$id' LIMIT 1");
	mysqli_close($mysqli);
	return $results;
}

function getFolderData( $folderId ) {
    $mysqli = open_db();
    $query = $mysqli->query("SELECT id, folderName, inFolderId from folders WHERE folders.id = '$folderId' LIMIT 1");
	if ($query->num_rows > 0) {
        return $query->fetch_assoc();
    }
    return array();
}

function listFoldersInFolderId( $inFolderId = 0 ) {
    $results = array();
    $mysqli = open_db();
    $query = $mysqli->query("SELECT id, folderName FROM folders WHERE inFolderId = '$inFolderId' ORDER BY folderName LIMIT 200");
	if ($query->num_rows > 0) {
        while($row = $query->fetch_assoc()){
            $results[] = $row;
        }
	}
	mysqli_close($mysqli);
	return $results;
}

function selectOp($folderId, $selectedId) {
    return ($folderId === $selectedId) ? ' selected ' : '';
};

function renderFoldersSelectBox($permitGrandchilden = false, $selectedFolderId = 0, $omitFolderId = null) {
    // die($omitFolderId);
    $html = '<select id="selectedFolder" name="selectedFolder">';
    $folderTree = listFoldersInFolderId(0);
    $html .= '<option value="0" ' . selectOp(0, $selectedFolderId) . '>Top Level</option>';
    foreach($folderTree as $folder) {
        if ($omitFolderId !== $folder['id']) {
            $html .= '<option value="'. $folder['id'] . '"'. selectOp($folder['id'], $selectedFolderId) .'>' . $folder['folderName'] . '</option>';
        }
        $children = listFoldersInFolderId($folder['id']);
        if (count($children) > 0 && $omitFolderId !== $folder['id']) {
            foreach($children as $child) {
                $html .= '<option value="'. $child['id'] . '"'. selectOp($child['id'], $selectedFolderId) .'>&nbsp;&nbsp;&nbsp;&nbsp;' . $child['folderName'] . '</option>';
                if ($permitGrandchilden) {
                    $grandchildren = listFoldersInFolderId($child['id']);
                    if (count($grandchildren) > 0) {
                        foreach($grandchildren as $grandchild) {
                            $html .= '<option value="'. $grandchild['id'] . '"'. selectOp($grandchild['id'], $selectedFolderId) .'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $grandchild['folderName'] . '</option>';
                        }
                    }
                }
            }
        }
    }
    $html .= '</select>';
    echo $html;
}

function renderCallout($type, $message) {
    echo '<div class="callout ' . $type . '"><span>' . $message . '</span></div>';
}

function sanitizeString($string) {
    $mysqli = open_db();
    $result = $mysqli->real_escape_string(trim(strip_tags($string)));
    mysqli_close($mysqli);
    return $result;
}

function validFolderName($folderName) {
    return strlen($folderName) > 0;
}

function storeNewFolder($folderName, $inFolderId) {
    $mysqli = open_db();
    $result = $mysqli->query("INSERT INTO folders (id, folderName, inFolderId) VALUES (NULL, '$folderName', '$inFolderId');");
    if ($result) {
        $id = $mysqli->insert_id;
        mysqli_close($mysqli);
        return $id;
    }
    return false;
}

function updateFolder($folderName, $inFolderId, $folderId) {
    $mysqli = open_db();
    $result = $mysqli->query("UPDATE folders SET folderName = '$folderName', inFolderId = '$inFolderId' WHERE folders.id = '$folderId';");
    mysqli_close($mysqli);
    return $result;
}

?>
