<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$upload_dir = 'uploads/';

// Check if directory exists
echo "Directory exists: " . (file_exists($upload_dir) ? 'Yes' : 'No') . "<br>";

// Check if directory is writable
echo "Directory is writable: " . (is_writable($upload_dir) ? 'Yes' : 'No') . "<br>";

// Try to create a test file
$test_file = $upload_dir . 'test.txt';
$result = file_put_contents($test_file, 'test');
echo "Can create file: " . ($result !== false ? 'Yes' : 'No') . "<br>";

// Display directory permissions
echo "Directory permissions: " . substr(sprintf('%o', fileperms($upload_dir)), -4) . "<br>";

// Display PHP upload settings
echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "<br>";
echo "post_max_size: " . ini_get('post_max_size') . "<br>";
?> 
