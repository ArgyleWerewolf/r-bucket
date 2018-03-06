<?php

// Database
define('DB_ADDRESS', 'localhost');
define('DB_USERNAME', 'werewolf_tBucket');
define('DB_PASSWORD', '4yH92ROV*}FWU}yA3h7|A9me`bPC7?UssJIAc3"(77logyREp_pQnQFXJviyF]Vq');
define('DB_DATABASE', 'werewolf_tandyeBucket');

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

function storeImageRecord($fileName, $safeName) {
    $mysqli = open_db();
    $result = $mysqli->query("INSERT INTO bucket (id, filename, s3key, uploadedWhen) VALUES (NULL, '$fileName', '$safeName', now());");
    mysqli_close($mysqli);
    return $result;
}

function listRecentUploads() {
    $results = array();
    $mysqli = open_db();
    $query = $mysqli->query("SELECT id, filename, s3key FROM bucket ORDER BY uploadedWhen LIMIT 200");
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

?>
