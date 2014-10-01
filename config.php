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
	'filenameLength' => '32',
	'twitter_Encrypted_URL' => 'https://www.screenshottr.us/v/noBot/{key}/{file}',
	'twitter_Unencrypted_URL' => 'https://www.screenshottr.us/{file}/noBot',
	'cloudflare_enabled' => false,
	'twitter_card_ip' => array(
		'199.16.156.124',
		'199.16.156.125',
		'199.16.156.126',
		'199.59.148.209',
		'199.59.148.210',
		'199.59.148.211',
		'199.59.149.21',
		'199.59.149.45'
	)
);
?>