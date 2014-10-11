<?php
if ($_GET['type'] == 'invalid')
{
    die('Image is Invalid.');
}
if ($_GET['type'] == "error")
{
    die("An error occurred while saving the image");
}
?>