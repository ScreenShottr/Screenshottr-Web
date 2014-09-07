<?php
$praviusEnabled = true;
$storage_url_unencrypted = 'https://www.screenshottr.us/';
$storage_url_encrypted = 'https://www.screenshottr.us/v/{key}/{file}';
$script_folder = '/home/screenshottr';
$file_storage_folder = '/home/screenshottr/i/';
$encrypted_storage_folder = '/home/screenshottr/encrypted/';
$tmp_image_folder = '/home/ScreenShottr/tmp/';
$hash = 'ripemd160'; //Choose hash length of encrption key.
//Remember to chmod these folders as 777.

//Ignore everything below here.//
require_once('functions.php');
?>