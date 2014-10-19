<?php
    // Direct-IP
    // Version 1.0 By @JeromSar
    // 13th June 2014
    
    header("Content-Type: text/plain");
    
    $trustProxy = false;
    
    if (!$trustProxy) {
        echo $_SERVER['REMOTE_ADDR'];
        exit();
    }
    
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        echo $_SERVER['HTTP_CLIENT_IP'];
        exit();
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        echo $_SERVER['HTTP_X_FORWARDED_FOR'];
        exit();
    }
    
    echo $_SERVER['REMOTE_ADDR'];
?>
