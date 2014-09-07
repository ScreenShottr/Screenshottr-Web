<?php
require 'configs.php';
$path = $file_storage_folder;
$uri  = $storage_url_unencrypted;


$tmpFilename = sha1(openssl_random_pseudo_bytes(128)) . ".png";
header('X-ScreenShottr-TmpFilename: ' . $tmpFilename);
# Check if input is through Base64 or Multipart Form
if (isset($_GET['base64'])) {
	#So we will assume that Base64 data is being sent via Post in the imagedata parameter
	if (!isset($_POST['imagedata'])) {
		die('No data supplied');
	}
	$rawImage = base64_decode($_POST['imagedata']);
	if ($rawImage == "") {
		die('Image is empty');
	}
	file_put_contents($tmp_image_folder . $tmpFilename, $rawImage);
} else {
	# We can assume that it is coming through Multipart Form Data
	if ($_GET['uploadAr'] == "file") {
		$uploadAr = 'file';
	} else {
		$uploadAr = 'imagedata';
	}
	
	if(!file_exists($_FILES[$uploadAr]['tmp_name']) || !is_uploaded_file($_FILES[$uploadAr]['tmp_name'])) {
		die('Error - No file uploaded.');
	}
	file_put_contents($tmp_image_folder . $tmpFilename, file_get_contents($_FILES[$uploadAr]['tmp_name']));
}

if(!exif_imagetype($tmp_image_folder . $tmpFilename)) {
	echo $tmpFilename;
	unlink($tmp_image_folder . $tmpFilename);
	var_dump($_FILES);
    die('Your image is Invalid.');
}

$filename = md5(openssl_random_pseudo_bytes(128)) . ".png";
$image = file_get_contents($tmp_image_folder . $tmpFilename);
unlink($tmp_image_folder . $tmpFilename);
if (!isset($_GET['unencrypted'])) {
	$key = hash('ripemd160', rand(1, 1523542352352354) . openssl_random_pseudo_bytes(3840));
    $encrypted = encrypt($image, $key);
    file_put_contents($encrypted_storage_folder . $filename, $encrypted);
	$url = str_replace("{file}", $filename, str_replace("{key}", $key, $storage_url_encrypted));
} else {
	file_put_contents($file_storage_folder . $filename, $image);
	$url = $storage_url_unencrypted . $filename;
}
echo shorten($url);
logToSql($filename, $encrypted, $filesize, $praviusResult);


?>