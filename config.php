<?php
$sql = array(
	'server' => 'localhost',
	'username' => 'ScreenShottr',
	'password' => 'PASSWORD',
	'database' => 'ScreenShottr',
	'port' => '3306'
);
$config = array(
	'hashing_algorithm' => 'ripemd160',
	'encryption_algorithm' => MCRYPT_RIJNDAEL_256,
	'temp_directory' => '/var/www/html/ScreenShottr/tmp/',
	'encrypted_directory' => '/var/www/html/ScreenShottr/encrypted/',
	'unencrypted_directory' => '/var/www/html/ScreenShottr/i/',
	'encrypted_URL' => 'https://www.screenshottr.us/v/{key}/{file}',
	'unencrypted_URL' => 'https://www.screenshottr.us/{file}',
	'pravius_enabled' => true,
	'Encryption_Key_Length' => '32',
	'secret_key_length' => '64',
	'filenameLength' => '32'
);
?>