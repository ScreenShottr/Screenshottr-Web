<?php
#?uploadAr Get Parameter allows using of a different multipart form name.

require_once('screenshottr.php');
require_once('config.php');

$ScreenShottr = new ScreenShottr($config, $sql);

if (isset($_GET['uploadAr']) && $_GET['uploadAr'] == "file")
{
    $uploadAr = 'file';
}
else
{
    $uploadAr = 'imagedata';
}
if (!file_exists($_FILES[$uploadAr]['tmp_name']) || !is_uploaded_file($_FILES[$uploadAr]['tmp_name']))
{
    die('Error - No file uploaded.');
}

# Create a filename
$filenameNoExt = $ScreenShottr->createFilename();
$imageData     = file_get_contents($_FILES[$uploadAr]['tmp_name']);
# Save the image to its temporary directory
$ScreenShottr->saveImage($imageData, $filenameNoExt, "temp");
# Figure out the file type, delete and error if we cannot.
$filesize  = filesize($ScreenShottr->_config['tmpDir'] . $filenameNoExt);
$imageType = $ScreenShottr->getImageType($config['temp_directory'] . $filenameNoExt);
$ScreenShottr->deleteImage("temp", $filenameNoExt);
if ($imageType == FALSE)
{
    die('Image is not supported');
}

# Check if we want to turn encryption off
if (isset($_GET['unencrypted']) && $_GET['unencrypted'] == "true")
{
    $encrypted = FALSE;
}
else
{
    $encrypted = TRUE;
}

$filename = $filenameNoExt . "." . $imageType;
$secret   = $ScreenShottr->createSecret();

# Encrypt if necessary, and move the file to it's actual directory.
if ($encrypted == TRUE)
{
    $key       = $ScreenShottr->createEncryptionKey();
    $imageData = $ScreenShottr->encrypt($imageData, $key);
    $ScreenShottr->saveImage($imageData, $filename, "TRUE");
}
else
{
    $key == NULL;
    $ScreenShottr->saveImage($imageData, $filename, "FALSE");
}
$pravius = 'test';

$url = $ScreenShottr->generatePublicUrl($filename, $key);
if (isset($_GET['return']) && $_GET['return'] == "json")
{
    $url['ScreenShottr']['image']                         = $filename;
    $url['ScreenShottr']['secret']                        = $secret;
    $url['ScreenShottrSettings']['encryptedURL']          = $ScreenShottr->_config['encryptedUrl'];
    $url['ScreenShottrSettings']['unencryptedURL']        = $ScreenShottr->_config['unencryptedURL'];
    $url['ScreenShottrSettings']['encryptedLandingURL']   = $ScreenShottr->_config['encryptedLandingUrl'];
    $url['ScreenShottrSettings']['unencryptedLandingURL'] = $ScreenShottr->_config['unencryptedLandingUrl'];
    if (isset($url['pravius']['link']))
    {
        $url['ScreenShottr']['URL'] = $url['pravius']['link'];
    }
    else
    {
        $url['ScreenShottr']['URL'] = $url['URL'];
    }
    if ($encrypted)
    {
        $url['ScreenShottr']['key'] = $key;
    }
    echo json_encode($url);
}
else
{
    echo $url['URL'];
}

$ScreenShottr->logUpload($filename, $encrypted, $filesize, $url['pravius'], $secret);
$ScreenShottr->cleanUp();



?>
