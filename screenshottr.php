<?php
class ScreenShottr
{
    private $_connection;
    public $_config;
    public $_extensions;

    public function __construct($config, $sql)
    {
        if ($config['cloudflare_enabled'])
        {
            $ip = $_SERVER["HTTP_CF_CONNECTING_IP"];
        }
        else
        {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        $this->_config = array(
            'Server' => $sql['server'],
            'Username' => $sql['username'],
            'Password' => $sql['password'],
            'Database' => $sql['database'],
            'Port'     => $sql['Port'],
            'Tableprefix' => $sql['tableprefix'],
            'crypt' => array(
                'hashingAlgorithm' => $config['hashing_algorithm'],
                'encryptionAlgorithm' => $config['encryption_algorithm']
            ),
            'tmpDir' => $config['temp_directory'],
            'encDir' => $config['encrypted_directory'],
            'unencDir' => $config['unencrypted_directory'],
            'encryptedUrl' => $config['encrypted_URL'],
            'unencryptedURL' => $config['unencrypted_URL'],
            'encryptedLandingUrl' => $config['encrypted_landing_URL'],
            'unencryptedLandingUrl' => $config['unencrypted_landing_URL'],
            'twitterEncryptedURL' => $config['twitter_Encrypted_URL'],
            'twitterUnEncryptedURL' => $config['twitter_Unencrypted_URL'],
            'twitterCardWidth' => $config['Twitter_Thumb_Width'],
            'twitterCardHeight' => $config['Twitter_Thumb_Height'],
            'praviusEnabled' => $config['pravius_enabled'],
            'encryptionKeyLength' => $config['Encryption_Key_Length'],
            'secretLength' => $config['secret_key_length'],
            'filenameLength' => $config['filenameLength'],
            'twitterCardsIP' => $config['twitter_card_ip'],
            'cloudFlare_Enabled' => $config['cloudflare_enabled'],
            'clientIP' => $ip,
            'landingPageLocation' => $config['landing_page_location']
        );

        $this->_extensions = array(
            IMAGETYPE_GIF => "gif",
            IMAGETYPE_JPEG => "jpg",
            IMAGETYPE_PNG => "png",
            IMAGETYPE_SWF => "swf",
            IMAGETYPE_PSD => "psd",
            IMAGETYPE_BMP => "bmp",
            IMAGETYPE_TIFF_II => "tiff",
            IMAGETYPE_TIFF_MM => "tiff",
            IMAGETYPE_JPC => "jpc",
            IMAGETYPE_JP2 => "jp2",
            IMAGETYPE_JPX => "jpx",
            IMAGETYPE_JB2 => "jb2",
            IMAGETYPE_SWC => "swc",
            IMAGETYPE_IFF => "iff",
            IMAGETYPE_WBMP => "wbmp",
            IMAGETYPE_XBM => "xbm",
            IMAGETYPE_ICO => "ico"
        );

        $this->_connection = @new mysqli($this->_config['Server'], $this->_config['Username'], $this->_config['Password'], $this->_config['Database'], $this->_config['Port']);

        if ($this->_connection->connect_error)
        {
        	// Will add proper error handling
            exit('Error connecting to MySQL: ' . $this->_connection->connect_error);
        }

        return $this->_config;
    }

    public function Connection()
    {
        return $this->_connection;
    }

    public function sqlQuery($sql)
    {
        $result = $this->Connection()->query($sql);
        return $result;
    }
    
    /**
     * Will create separate classes, but for the mean time, this will work fine.
     */
     private function refValues($arr)
     {
     	if (strnatcmp(phpversion(), '5.3') >= 0)
     	{
     		$refs = array();
     		foreach ($arr as $key => $value)
     		{
     			$refs[$key] =& $arr[$key];
     		}
     		return $refs;
     	}
     	return $arr;
     }
     
     protected function statement($sql, $params)
     {
     	if ( ! $stmt = $this->Connection()->prepare($sql))
     	{
     		// Need proper error handling
     		return FALSE;
     	}
     	if ( ! empty($params))
     	{
     		call_user_func_array(array($stmt, 'bind_param'), $this->RefValues($params));
     	}
     	if ( ! $stmt->execute())
     	{
     		// Need proper error handling
     		return FALSE;
     	}
     	return $stmt;
     }

    public function sanitizeSQL($string)
    {
        return $this->Connection()->real_escape_string($string);
    }

    public function saveImage($file, $filename, $encrypted)
    {
        if ($encrypted == "TRUE")
        {
            file_put_contents($this->_config['encDir'] . $filename, $file);
        }
        else if ($encrypted == "FALSE")
        {
            file_put_contents($this->_config['unencDir'] . $filename, $file);
        }
        else if ($encrypted == "temp")
        {
            file_put_contents($this->_config['tmpDir'] . $filename, $file);
        }
        else
        {
            exit('Error');
        }

    }
    public function loadImage($filename, $encrypted)
    {
        if ($encrypted == "TRUE")
        {
            return file_get_contents($this->_config['encDir'] . $filename);
        }
        else if ($encrypted == "FALSE")
        {
            return file_get_contents($this->_config['unencDir'] . $filename);
        }
        else
        {
            exit('Error');
        }

    }

    public function deleteImage($encrypted, $filename)
    {
        if ($encrypted == "TRUE")
        {
            unlink($this->_config['encDir'] . $filename);
        }
        else if ($encrypted == "FALSE")
        {
            unlink($this->_config['unencDir'] . $filename);
        }
        else if ($encrypted == "temp")
        {
            unlink($this->_config['tmpDir'] . $filename);
        }
        $this->sqlQuery("DELETE FROM ScreenShottr.imageuploads WHERE FileName='" . $this->sanitizeSQL($filename) . "';");
        return;
    }

    public function createFilename()
    {
        return md5(hash('sha512', time()));
    }

    public function createEncryptionKey()
    {
        $random = openssl_random_pseudo_bytes($this->_config['encryptionKeyLength']);
        return substr(hash($this->_config['crypt']['hashingAlgorithm'], $random), 0, $this->_config['encryptionKeyLength']);
    }

    public function createSecret()
    {
        return $this->createEncryptionKey($this->_config['secretLength']);
    }

    public function generateScreenShottrURL($filename, $key = null)
    {
        if (isset($key))
        {
            $url['main'] = str_replace('{key}', $key, str_replace('{file}', $filename, $this->_config['encryptedUrl']));
            $url['landing'] = str_replace('{key}', $key, str_replace('{file}', $filename, $this->_config['encryptedLandingUrl']));
        }
        else
        {
            $url['main'] = str_replace('{file}', $filename, $this->_config['unencryptedURL']);
            $url['landing'] = str_replace('{file}', $filename, $this->_config['unencryptedLandingUrl']);
        }
        return $url;
    }

    public function generatePublicURL($filename, $key = null)
    {
        $url    = $this->generateScreenShottrURL($filename, $key);
        $output = array();
        if ($this->_config['praviusEnabled'])
        {
            $postdata = http_build_query(array(
                'link' => $url['main']
            ));
            $opts     = array(
                'http' => array(
                    'method' => 'POST',
                    'timeout' => 1,
                    'header' => 'Content-type: application/x-www-form-urlencoded',
                    'content' => $postdata
                )
            );
            $context  = stream_context_create($opts);
			$praviusRAW = file_get_contents('https://pravi.us/api/shortenurl', FALSE, $context);
            $returned = json_decode($praviusRAW, TRUE);

            if (!$praviusRAW OR $returned['status'] != "ok")
            {
                $output['pravius'] = FALSE;
                $output['URL']     = $url;
            }
            else
            {
                $output['pravius'] = $returned['data'];
                $output['URL']     = $returned['data']['pravius'];
                $output['landingURL'] = $url['landing'];
            }
        }
        else
        {
            $output['pravius'] = FALSE;
            $output['URL']     = $url['main'];
			$output['landingURL'] = $url['landing'];
        }
        return $output;
    }

    public function encrypt($file, $key)
    {
        $key       = hash('sha512', $key);
        $key       = substr($key, 0, 32);
        $iv        = substr($key, 33, 49);
        $encrypted = mcrypt_encrypt($this->_config['crypt']['encryptionAlgorithm'], $key, $file, MCRYPT_MODE_CBC, $iv);
        return $encrypted;
    }

    public function decrypt($file, $key)
    {
        $key       = hash('sha512', $key);
        $key       = substr($key, 0, 32);
        $iv        = substr($key, 33, 49);
        $decrypted = mcrypt_decrypt($this->_config['crypt']['encryptionAlgorithm'], $key, $file, MCRYPT_MODE_CBC, $iv);
        return $decrypted;
    }

    public function logUpload($filename, $encrypted, $fileSizeInBytes, $pravius, $secret)
    {
        if (!$pravius)
        {
            $pravius      = $this->sanitizeSQL('0');
            $praviusId    = $this->sanitizeSQL('0');
            $praviusAdmin = $this->sanitizeSQL('0');
        }
        else
        {
            $praviusId    = $this->sanitizeSQL($pravius['id']);
            $praviusAdmin = $this->sanitizeSQL($pravius['secret']);
            $pravius      = $this->sanitizeSQL('1');
        }
        $this->sqlQuery("INSERT INTO imageuploads (FileName, Encrypted, UploadTimeStamp, LastViewedTimeStamp, FilesizeInBytes, TimesViewed, PraviUS, PraviUSID, PraviUSAdmin, secret) VALUES ('" . $this->sanitizeSQL($filename) . "', '" . $this->sanitizeSQL($encrypted) . "', '" . time() . "', '0', '" . $this->sanitizeSQL($fileSizeInBytes) . "', '0', '" . $pravius . "', '" . $praviusId . "', '" . $praviusAdmin . "', '" . $this->sanitizeSQL($secret) . "')");

        return;
    }

    public function logView($image)
    {
        $this->sqlQuery("UPDATE imageuploads SET LastViewedTimeStamp='" . time() . "', TimesViewed=TimesViewed+1 WHERE Filename='" . $this->sanitizeSQL($image) . "'");
        $this->sqlQuery("INSERT INTO users (IP, FirstVisited, LastVisited, TimesVisited) VALUES ('" . $_SERVER['REMOTE_ADDR'] . "', '" . time() . "', '" . time() . "', '1') ON DUPLICATE KEY UPDATE LastVisited = '" . time() . "', TimesVisited = TimesVisited+1;");
        return TRUE;
    }

    public function getStats()
    {
        $imagesServed = mysqli_fetch_assoc($this->sqlQuery("SELECT SUM(TimesVisited) FROM ScreenShottr.users"));
        $imageStats   = mysqli_fetch_assoc($this->sqlQuery("SELECT SUM(FileSizeInBytes),COUNT(FileName) FROM ScreenShottr.imageuploads"));

        $stats['imagesServed']  = $imagesServed['SUM(TimesVisited)'];
        $stats['totalFileSize'] = $imageStats['SUM(FileSizeInBytes)'];
        $stats['totalImages']   = $imageStats['COUNT(FileName)'];
        return $stats;
    }

    public function getImageType($imageLocation)
    {
        $imageType = exif_imagetype($imageLocation);
        if ($imageType == IMAGETYPE_GIF)
        {
            return 'gif';
        }
        else if ($imageType == IMAGETYPE_JPEG)
        {
            return 'jpg';
        }
        else if ($imageType == IMAGETYPE_PNG)
        {
            return 'png';
        }
        else if ($imageType == IMAGETYPE_BMP)
        {
            return 'bmp';
        }
        else if ($imageType == IMAGETYPE_ICO)
        {
            return 'ico';
        }
        else
        {
            return FALSE;
        }

    }

    public function getImageStats($id)
    {
        $id   = $this->sanitizeSQL($id);
        $data = $this->sqlQuery("SELECT * FROM ScreenShottr.imageuploads WHERE FileName='" . $id . "'")->fetch_assoc();

        $output = array(
            'id' => $data['ID'],
            'filesizeBytes' => $data['FilesizeInBytes'],
            'filesizeHuman' => $this->formatBytes($data['FilesizeInBytes']),
            'uploadTimeStamp' => $data['UploadTimeStamp'],
            'uploadTimeHuman' => date("D d M Y H:i", $data['UploadTimeStamp']),
            'timesViewed' => $data['TimesViewed'],
            'totalBandwidthBytes' => $data['FilesizeInBytes'] * $data['TimesViewed'],
            'totalBandwidthHuman' => $this->formatBytes($data['FilesizeInBytes'] * $data['TimesViewed']),
            'pravusURL' => $data['PraviUSID']
        );

        return $output;
    }

    public function getImageSQLData($filename)
    {
        $filename = $this->sanitizeSQL($filename);
        $data     = $this->sqlQuery("SELECT * FROM ScreenShottr.imageuploads WHERE FileName='" . $filename . "'")->fetch_assoc();
        return $data;
    }

    public function cleanUp()
    {
        $query = $this->sqlQuery("SELECT * FROM ScreenShottr.imageuploads WHERE LastViewedTimeStamp+7884000 < " . time() . " AND LastViewedTimeStamp != 0;");
        if ($query)
        {
            while ($row = mysqli_fetch_array($query))
            {
                if ($row['Encrypted'] == "1")
                {
                    header('X-Encrypted: True');
                    $this->deleteImage(TRUE, $row['FileName']);
                }
                else
                {
                    $this->deleteImage(FALSE, $row['FileName']);
                }
            }
        }
        return;
    }

    public function formatBytes($size, $precision = 2)
    {
        $base     = log($size) / log(1024);
        $suffixes = array(
            '',
            'KB',
            'MB',
            'GB',
            'TB'
        );
        return round(pow(1024, $base - floor($base)), $precision) . $suffixes[floor($base)];
    }

    public function createTwitterCard($filename, $key = null)
    {
        if (array_search($this->_config['clientIP'], $this->_config['twitterCardsIP']))
        {
            if (isset($key))
            {
                $urlImage = str_replace('{key}', $key, str_replace('{file}', $filename, $this->_config['twitterEncryptedURL']));
                $url      = str_replace('{key}', $key, str_replace('{file}', $filename, $this->_config['encryptedUrl']));
            }
            else
            {
                $urlImage = str_replace('{file}', $filename, $this->_config['twitterUnEncryptedURL']);
                $url      = str_replace('{file}', $filename, $this->_config['unencryptedURL']);
            }

            $twitterMeta = '<!DOCTYPE html><html><head>
			<meta name="twitter:card" content="photo" />
			<meta name="twitter:site" content="@ScreenShottr" />
			<meta name="twitter:title" content="ScreenShottr image" />
			<meta name="twitter:description" content="Free encrypted ScreenShot tool" />
			<meta name="twitter:image" content="' . trim($urlImage) . '" />
			<meta name="twitter:url" content="' . trim($url) . '" />
			</head><body></body></html>';
            return $twitterMeta;
        }
        else
        {
            return FALSE;
        }
    }

    public function generateImageThumb($filename, $key = null)
    {
        // Function modified from http://salman-w.blogspot.co.uk/2008/10/resize-images-using-phpgd-library.html
        if (isset($key))
        {
            $imageData = $this->loadImage($filename, "TRUE");
            if ($imageData == null)
            {
                die('Image does not exist.');
            }
            $imageData = $this->decrypt($imageData, $_GET['k']);
        }
        else
        {
            $imageData = $this->loadImage($_GET['img'], "FALSE");
            $key       = FALSE;
            if ($imageData == null)
            {
                die('Image does not exist.');
            }
        }

        $this->saveImage($imageData, $filename, "temp");
        $source_image_path = $this->_config['tmpDir'] . '/' . $filename;

        list($source_image_width, $source_image_height, $source_image_type) = getimagesize($source_image_path);
        switch ($source_image_type)
        {
            case IMAGETYPE_GIF:
                $source_gd_image = imagecreatefromgif($source_image_path);
                break;
            case IMAGETYPE_JPEG:
                $source_gd_image = imagecreatefromjpeg($source_image_path);
                break;
            case IMAGETYPE_PNG:
                $source_gd_image = imagecreatefrompng($source_image_path);
                break;
        }
        if ($source_gd_image === FALSE)
        {
            return FALSE;
        }
        $source_aspect_ratio    = $source_image_width / $source_image_height;
        $thumbnail_aspect_ratio = $this->_config['twitterCardWidth'] / $this->_config['twitterCardHeight'];
        if ($source_image_width <= $this->_config['twitterCardWidth'] && $source_image_height <= $this->_config['twitterCardHeight'])
        {
            $thumbnail_image_width  = $source_image_width;
            $thumbnail_image_height = $source_image_height;
        }
        elseif ($thumbnail_aspect_ratio > $source_aspect_ratio)
        {
            $thumbnail_image_width  = (int) ($this->_config['twitterCardHeight'] * $source_aspect_ratio);
            $thumbnail_image_height = $this->_config['twitterCardHeight'];
        }
        else
        {
            $thumbnail_image_width  = $this->_config['twitterCardWidth'];
            $thumbnail_image_height = (int) ($this->_config['twitterCardWidth'] / $source_aspect_ratio);
        }
        $thumbnail_gd_image = imagecreateTRUEcolor($thumbnail_image_width, $thumbnail_image_height);
        imagecopyresampled($thumbnail_gd_image, $source_gd_image, 0, 0, 0, 0, $thumbnail_image_width, $thumbnail_image_height, $source_image_width, $source_image_height);
        imagejpeg($thumbnail_gd_image, $this->_config['tmpDir'] . '/thumb_' . $filename, 90);
        imagedestroy($source_gd_image);
        imagedestroy($thumbnail_gd_image);
        $this->deleteImage("temp", $filename);
        $thumb = file_get_contents($this->_config['tmpDir'] . '/thumb_' . $filename);
        $this->deleteImage("temp", 'thumb_' . $filename);
        return $thumb;
    }
}

?>
