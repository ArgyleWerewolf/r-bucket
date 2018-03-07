<?php
// Presentation
define('SITE_TITLE', 'Tandye Bucket');
define('BUCKET_URL', 'https://s3-us-west-2.amazonaws.com/tandye-art-bucket/');

// AWS access info
define('ACCESS_KEY', 'AKIAI5FZWZLA67ZXCOUA');
define('SECRET_KEY', '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');
define('BUCKET_NAME', 'tandye-art-bucket');
define('USER_NAME', 'tandye');
define('USER_PASS', '7e098c088fe9aa89a9dbbae9d14333b21948a5df');

// Thumbnails
define('THUMBNAIL_DIR', 'thumbnails');
define('THUMBNAIL_MAX_DIM', 600);
define('THUMBNAIL_QUALITY', 75);

// Endpoints
define('UPLOAD_ACTION', 'index.php');

require_once('lib/functions.php');
require_once('lib/S3.php');
?>
