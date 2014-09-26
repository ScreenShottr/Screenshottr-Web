<?php
require_once('screenshottr.php');
require_once('config.php');

$ScreenShottr = new ScreenShottr($config, $sql);

if (!isset($_GET['action'])) {
	die('Include action');
}

if ($_GET['action'] == "delete" && isset($_GET['img']) && isset($_GET['secret'])) {
	$imageData = $ScreenShottr->getImageSQLData($_GET['img']);
	if ($imageData['secret'] != $_GET['secret']) {
		die('Incorrect secret');
	}
	
	if ($imageData['Encrypted'] == "1") {
		$encrypted = "true";
	} else {
		$encrypted = "false";
	}
	
	$ScreenShottr->deleteImage($encrypted, $_GET['img']);
}

if ($_GET['action'] == "stats" && isset($_GET['img'])) {
	echo json_encode(getImageStats($img));
}
?>