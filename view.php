<?php
require_once('screenshottr.php');
require_once('config.php');

$ScreenShottr = new ScreenShottr($config, $sql);

# Check that atleast an ?img parameter is provided
if (!isset($_GET['img']))
{
    die('No image provided');
}

if (isset($_GET['k']))
{
    $imageData = $ScreenShottr->loadImage($_GET['img'], "TRUE");
    $key       = $_GET['k'];
    if ($imageData == null)
    {
        die('Image does not exist: ' . $_SERVER['QUERY_STRING']);
    }
    $imageData = $ScreenShottr->decrypt($imageData, $_GET['k']);
}
else
{
    $imageData = $ScreenShottr->loadImage($_GET['img'], "FALSE");
    $key       = NULL;
    if ($imageData == null)
    {
        die('Image does not exist: ' . $_SERVER['QUERY_STRING']);
    }
}

$ScreenShottr->cleanUp();
$ScreenShottr->logView($_GET['img']);
$stats = $ScreenShottr->getImageStats($_GET['img']);
header('X-ScreenShottr-H-UploadTime: ' . $stats['uploadTimeHuman']);
header('X-ScreenShottr-H-TimesViewed: ' . $stats['timesViewed']);
header('X-ScreenShottr-H-Filesize: ' . $stats['filesizeHuman']);
header('X-ScreenShottr-H-TotalBandwidth: ' . $stats['totalBandwidthHuman']);
header('X-ScreenShottr-H-Pravius: ' . $stats['pravusURL']);
header('X-ScreenShottr-M-ID: ' . $stats['id']);
header('X-ScreenShottr-M-FileSize: ' . $stats['filesizeBytes']);
header('X-ScreenShottr-M-TotalBandwidth: ' . $stats['totalBandwidthBytes']);
header('X-ScreenShottr-M-UploadTime: ' . $stats['uploadTimeStamp']);

$imageCard = $ScreenShottr->createTwitterCard($_GET['img'], $key);


if (!$imageCard OR isset($_GET['noBot']))
{
    if (isset($_GET['noBot']))
    {
        header('Content-type: ' . image_type_to_mime_type(array_search(substr($_GET['img'], -3), $ScreenShottr->_extensions)));
        echo $ScreenShottr->generateImageThumb($_GET['img'], $key);
    }
    else
    {
        if (isset($_GET['landing']))
        {
            $url = $ScreenShottr->generateScreenShottrURL($_GET['img'], $key);
            echo str_replace("{url}", $url['main'], file_get_contents($ScreenShottr->_config['landingPageLocation']));
        }
        else
        {
            header('Content-type: ' . image_type_to_mime_type(array_search(substr($_GET['img'], -3), $ScreenShottr->_extensions)));
            echo $imageData;
        }
    }
}
else
{
    echo $imageCard;
}

?>
