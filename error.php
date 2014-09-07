<?php
if($_GET['type'] == "error") {
    die('An error occured');
}
if ($_GET['type'] == "invalid") {
    die('Invalid image');
}
?>