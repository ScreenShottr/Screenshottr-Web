<?php
class ScreenShottr {
	private $_connection;
	public $_config;
	public $_extensions;
	
	public function __construct($config, $sql) {
		if ($config['cloudflare_enabled']) {
			$ip = $_SERVER["HTTP_CF_CONNECTING_IP"];
		} else {
			$ip = $_SERVER['REMOTE_ADDR'];
		}
			$this->_config = array(
				'Server' => $sql['server'],
				'Username' => $sql['username'],
				'Password' => $sql['password'],
				'Database' => $sql['database'],
				'crypt' => array(
					'hashingAlgorithm' => $config['hashing_algorithm'],
					'encryptionAlgorithm' => $config['encryption_algorithm']
				),
				'tmpDir' => $config['temp_directory'],
				'encDir' => $config['encrypted_directory'],
				'unencDir' => $config['unencrypted_directory'],
				'encryptedUrl' => $config['encrypted_URL'],
				'unencryptedURL' => $config['unencrypted_URL'],
				'twitterEncryptedURL' => $config['twitter_Encrypted_URL'],
				'twitterUnEncryptedURL' => $config['twitter_Unencrypted_URL'],
				'praviusEnabled' => $config['pravius_enabled'],
				'encryptionKeyLength' => $config['Encryption_Key_Length'],
				'secretLength' => $config['secret_key_length'],
				'filenameLength' => $config['filenameLength'],
				'twitterCardsIP' => $config['twitter_card_ip'],
				'cloudFlare_Enabled' => $config['cloudflare_enabled'],
				'clientIP' => $ip
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
			
			$this->_connection = new mysqli(
				$this->_config['Server'],
				$this->_config['Username'],
				$this->_config['Password'],
				$this->_config['Database']
			);

			if ($this->_connection->connect_error)
			{
				exit('Error connecting to MySQL: ' . $this->_connection->connect_error);
			}

			return $this->_config;
		}
	
	public function Connection() {
		return $this->_connection;
	}
	
	public function sqlQuery($sql) {
		$result = $this->Connection()->query($sql);
        return $result;
	}
	
	public function sanitizeSQL($string) {
		return $this->Connection()->real_escape_string($string);
	}
	
	public function saveImage($file, $filename, $encrypted) {		
		if ($encrypted == "true") {
			file_put_contents($this->_config['encDir'] . $filename, $file);
		} else if ($encrypted == "false") {
			file_put_contents($this->_config['unencDir'] . $filename, $file);
		} else if ($encrypted == "temp") {
			file_put_contents($this->_config['tmpDir'] . $filename, $file);
		} else {
			exit('Error');
		}
		
	}
		public function loadImage($filename, $encrypted) {		
		if ($encrypted == "true") {
			return file_get_contents($this->_config['encDir'] . $filename);
		} else if ($encrypted == "false") {
			return file_get_contents($this->_config['unencDir'] . $filename);
		} else {
			exit('Error');
		}
		
	}
	
	public function deleteImage($encrypted, $filename) {
		if ($encrypted == "true") {
			unlink($this->_config['encDir'] . $filename );
		} else if ($encrypted == "false") {
			unlink($this->_config['unencDir'] . $filename);
		} else if ($encrypted == "temp") {
			unlink($this->_config['tmpDir'] . $filename);
		}
		$this->sqlQuery("DELETE FROM ScreenShottr.imageuploads WHERE FileName='" . $this->sanitizeSQL($filename) . "';");
		return;
	}
	
	public function createFilename() {
		return md5(hash('sha512', time()));
	}
	
	public function createEncryptionKey() {
		$random = openssl_random_pseudo_bytes($this->_config['encryptionKeyLength']);
		return substr(hash($this->_config['crypt']['hashingAlgorithm'], $random), 0, $this->_config['encryptionKeyLength']);
	}
	
	public function createSecret() {
		return $this->createEncryptionKey($this->_config['secretLength']);
	}
	
	public function generatePublicURL($filename, $key = null) {
		if (isset($key)) {
			$url = str_replace('{key}', $key, str_replace('{file}', $filename, $this->_config['encryptedUrl']));
		} else {
			$url = str_replace('{file}', $filename, $this->_config['unencryptedURL']);
		}
		
		$output = array();
		if ($this->_config['praviusEnabled']) {
			$postdata = http_build_query(
				array(
					'link' => $url,
				)
			);
			$opts = array('http' =>
				array(
					'method'  => 'POST',
					'timeout' => 1,
					'header'  => 'Content-type: application/x-www-form-urlencoded',
					'content' => $postdata
				)
			);
			$context  = stream_context_create($opts);
			$returned = json_decode(file_get_contents('https://pravi.us/api/shortenurl', false, $context), true);
			
			if ($returned['status'] != "ok" OR !$returned) {
				$output['pravius'] = false;
				$output['url'] = $url;
			} else {
				$output['pravius'] = $returned['data'];
				$output['url'] = $returned['data']['pravius'];
			}
		} else {
			$output['pravius'] = false;
			$output['url'] = $url;
		}
		return $output;
	}
	
	public function encrypt($file, $key) {
		$key = hash('sha512', $key);
		$key = substr($key, 0, 32);
		$iv = substr($key, 33, 49);
		$encrypted = mcrypt_encrypt($this->_config['crypt']['encryptionAlgorithm'], $key, $file, MCRYPT_MODE_CBC, $iv);
		return $encrypted;
	}
	
	public function decrypt($file, $key) {
		$key = hash('sha512', $key);
		$key = substr($key, 0, 32);
		$iv = substr($key, 33, 49);
		$decrypted = mcrypt_decrypt($this->_config['crypt']['encryptionAlgorithm'], $key, $file, MCRYPT_MODE_CBC, $iv);
		return $decrypted;
	}
	
	public function logUpload($filename, $encrypted, $fileSizeInBytes, $pravius, $secret) {
		if (!$pravius) {
			$pravius = $this->sanitizeSQL('0');
			$praviusId = $this->sanitizeSQL('0');
			$praviusAdmin = $this->sanitizeSQL('0');
		} else {
			$praviusId = $this->sanitizeSQL($pravius['id']);
			$praviusAdmin = $this->sanitizeSQL($pravius['secret']);
			$pravius = $this->sanitizeSQL('1');
		}
		$this->sqlQuery("INSERT INTO imageuploads (FileName, Encrypted, UploadTimeStamp, LastViewedTimeStamp, FilesizeInBytes, TimesViewed, PraviUS, PraviUSID, PraviUSAdmin, secret) VALUES ('" . $this->sanitizeSQL($filename) . "', '" . $this->sanitizeSQL($encrypted) . "', '" . time() . "', '0', '" . $this->sanitizeSQL($fileSizeInBytes) . "', '0', '" . $pravius . "', '" . $praviusId . "', '" . $praviusAdmin . "', '" . $this->sanitizeSQL($secret) . "')");
		
		return;
	}
	
	public function logView($image) {
		$this->sqlQuery("UPDATE imageuploads SET LastViewedTimeStamp='" . time() ."', TimesViewed=TimesViewed+1 WHERE Filename='". $this->sanitizeSQL($image) ."'");
		$this->sqlQuery("INSERT INTO users (IP, FirstVisited, LastVisited, TimesVisited) VALUES ('" . $_SERVER['REMOTE_ADDR'] . "', '" . time() . "', '" . time() . "', '1') ON DUPLICATE KEY UPDATE LastVisited = '" . time() . "', TimesVisited = TimesVisited+1;");
		return true;
	}
	
	public function getStats() {
		$imagesServed = mysqli_fetch_assoc($this->sqlQuery("SELECT SUM(TimesVisited) FROM ScreenShottr.users"));
		$imageStats = mysqli_fetch_assoc($this->sqlQuery("SELECT SUM(FileSizeInBytes),COUNT(FileName) FROM ScreenShottr.imageuploads"));
				
		$stats['imagesServed'] = $imagesServed['SUM(TimesVisited)'];
		$stats['totalFileSize'] = $imageStats['SUM(FileSizeInBytes)'];
		$stats['totalImages'] = $imageStats['COUNT(FileName)'];
		return $stats;
	}
	
	public function getImageType($imageLocation) {
		$imageType = exif_imagetype($imageLocation);
		if ($imageType == IMAGETYPE_GIF) {
			return 'gif';
		} else if ($imageType == IMAGETYPE_JPEG) {
			return 'jpg';
		} else if ($imageType == IMAGETYPE_PNG) {
			return 'png';
		} else if ($imageType == IMAGETYPE_BMP) {
			return 'bmp';
		} else if ($imageType == IMAGETYPE_ICO) {
			return 'ico';
		} else {
			return false;
		}
				
	}
	
	public function getImageStats($id) {
		$id = $this->sanitizeSQL($id);
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
	
	public function getImageSQLData($filename) {
		$filename = $this->sanitizeSQL($filename);
		$data = $this->sqlQuery("SELECT * FROM ScreenShottr.imageuploads WHERE FileName='" . $filename . "'")->fetch_assoc();
		return $data;
	}
	
	public function cleanUp() {
		$query = $this->sqlQuery("SELECT * FROM ScreenShottr.imageuploads WHERE LastViewedTimeStamp+7884000 < " . time() . ";");
		if ($row) {
			while($row = mysqli_fetch_array($query)) {
				if ($row['Encrypted'] == "1") {
					header('X-Encrypted: True');
					$this->deleteImage(true, $row['FileName']);
				} else {
					$this->deleteImage(false, $row['FileName']);
				}
			}
		}
		return;
	}
	
	public function formatBytes($size, $precision = 2) {
		$base = log($size) / log(1024);
		$suffixes = array('', 'KB', 'MB', 'GB', 'TB');   
		return round(pow(1024, $base - floor($base)), $precision) . $suffixes[floor($base)];
	}
	
	public function createTwitterCard($filename, $key = null) {
		if (array_search($this->_config['clientIP'], $this->_config['twitterCardsIP'])) {
			if (isset($key)) {
				$urlImage = str_replace('{key}', $key, str_replace('{file}', $filename, $this->_config['twitterEncryptedURL']));
				$url = str_replace('{key}', $key, str_replace('{file}', $filename, $this->_config['encryptedUrl']));
			} else {
				$urlImage = str_replace('{file}', $filename, $this->_config['twitterUnEncryptedURL']);
				$url = str_replace('{file}', $filename, $this->_config['unencryptedURL']);
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
		} else {
			return false;
		}
	}
}

?>