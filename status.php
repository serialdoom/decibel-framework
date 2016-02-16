<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel;

$db_username = env('DB_USERNAME');
$db_password = env('DB_PASSWORD');
$db_database = env('DB_DATABASE');
$db_hostname = env('DB_HOSTNAME');

// Try to connect to database.
$connection = @mysql_connect($db_hostname, $db_username, $db_password);
if (!$connection) {
    die('DATABASE ERROR 1016');
}
// Try to select database.
$database = @mysql_select_db($db_database);
if (!$database) {
    die('DATABASE ERROR 1017');
}
// Check file system.
$filename = '../../_temp/test';
$data = 'decibel';
if (file_put_contents($filename, $data) !== 7
    || !file_exists($filename)
    || file_get_contents($filename) !== $data
    || !unlink($filename)
) {
    die('FILESYSTEM ERROR');
}
// No issues.
die('OK');
