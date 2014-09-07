<?php
require 'configs.php';

if(!isset($_GET['img'])) {
	die('Enter a Valid IMAGE ID');
}


if (isset($_GET['k'])) {
	$file = file_get_contents($encrypted_storage_folder . $_GET['img']);
	$key = $_GET['k'];
	$file = decrypt($file, $key);
	$enc = 1;
} else {
	$enc = 0;
	$file = file_get_contents($file_storage_folder . $_GET['img']);
}
header("Content-type: image/png");
getImageStatsForHeaders($_GET['img']);
echo $file;
logView($_GET['img'], $enc);
exit;
?>