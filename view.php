<?php
require 'configs.php';

$file = file_get_contents($encrypted_storage_folder . $_GET['img']);
$key = $_GET['k'];
$file = decrypt($file, $key);

header("Content-type: image/png");
echo $file;

exit;
?>