<?php
function generateUrlEnc($file, $key) {
    require 'configs.php';
    $url = str_replace("{key}", $key, $storage_url_encrypted);
    $url = str_replace("{file}", $file, $url);
    return $url;
}

function formatBytes($size, $precision = 2)
{
    $base = log($size) / log(1024);
    $suffixes = array('', 'KB', 'MB', 'GB', 'TB');   

    return round(pow(1024, $base - floor($base)), $precision) . $suffixes[floor($base)];
}

function shorten($url) {
	if ($GLOBALS['praviusEnabled'] == true) {
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
		$returned = file_get_contents('https://pravi.us/api/shortenurl', false, $context);
		$returnedResult = json_decode($returned, true);
		$GLOBALS['praviusResult'] = $returnedResult;
		//var_dump($returnedResult);
		if ($returnedResult['status'] != "ok") {
			$GLOBALS['praviusResult'] = false;
			return $url;
		} else {
			return $returnedResult['data']['pravius'];
		}

	} else { 
		$GLOBALS['praviusResult'] = false;
		return $url;
	}
	
}
function encrypt($file, $key)
{
	$key = hash('sha512', $key);
	$key = substr($key, 0, 32);
	$iv = substr($key, 33, 49);
    $encrypted = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key, $file, MCRYPT_MODE_CBC, $iv);
    return $encrypted;
}

function decrypt($file, $key) {
	$key = hash('sha512', $key);
	$key = substr($key, 0, 32);
	$iv = substr($key, 33, 49);
	$decrypted = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key, $file, MCRYPT_MODE_CBC, $iv);
	return $decrypted;
}


function logToSql($filename, $encrypted, $fileSizeInBytes, $pravius) {
	$db = mysqli_connect("localhost","ScreenShottr","<password>","ScreenShottr");
	if (mysqli_connect_errno()) {
	  return;
	  //Don't want to affect the User
	}
	if ($pravius == false) {
		$pravius = 0;
		$praviusId = 0;
		$praviusAdmin = 0;
	} else {
		$praviusId = $pravius['data']['id'];
		$praviusAdmin = $pravius['data']['secret'];
		$pravius = 1;
	}
	mysqli_query($db,"INSERT INTO imageuploads (FileName, Encrypted, UploadTimeStamp, LastViewedTimeStamp, FilesizeInBytes, TimesViewed, PraviUS, PraviUSID, PraviUSAdmin) VALUES ('" . $filename . "', '" . $encrypted . "', '" . time() . "', '0', '" . $fileSizeInBytes . "', '0', '" . $pravius . "', '" . $praviusId . "', '" . $praviusAdmin . "')");
	
	
	mysqli_close($db);
}

function logView($image, $encrypted) {
	$db = mysqli_connect("localhost","ScreenShottr","<password>","ScreenShottr");
	if (mysqli_connect_errno()) {
	return;
	  //Don't want to affect the User
	}
	cleanUp();
	mysqli_query($db, "UPDATE imageuploads SET LastViewedTimeStamp='" . time() ."', TimesViewed=TimesViewed+1 WHERE Filename='". $image ."'");
	mysqli_query($db, "INSERT INTO users (IP, FirstVisited, LastVisited, TimesVisited) VALUES ('" . $_SERVER['REMOTE_ADDR'] . "', '" . time() . "', '" . time() . "', '1') ON DUPLICATE KEY UPDATE LastVisited = '" . time() . "', TimesVisited = TimesVisited+1;");
	mysqli_close($db);
}

function getStats() {
	$db = mysqli_connect("localhost","ScreenShottr","<password>","ScreenShottr");
	if (mysqli_connect_errno()) {
	return;
	  //Don't want to affect the User
	}
	$imagesServed = mysqli_fetch_assoc(mysqli_query($db, "SELECT SUM(TimesVisited) FROM ScreenShottr.users"));
	$imageStats = mysqli_fetch_assoc(mysqli_query($db, "SELECT SUM(FileSizeInBytes),COUNT(FileName) FROM ScreenShottr.imageuploads"));
	
	
	$stats['imagesServed'] = $imagesServed['SUM(TimesVisited)'];
	$stats['totalFileSize'] = $imageStats['SUM(FileSizeInBytes)'];
	$stats['totalImages'] = $imageStats['COUNT(FileName)'];
	return $stats;	
}

function getImageStat($id) {
	date_default_timezone_set('Europe/London');
	$db = mysqli_connect("localhost","ScreenShottr","<password>","ScreenShottr");
	if (mysqli_connect_errno()) {
	return;
	  //Don't want to affect the User
	}
	$id = mysqli_real_escape_string ($db, $id);
	if (!strpos($id, ".png")) {
		$id = $id . ".png";
	}
	$data = mysqli_fetch_assoc(mysqli_query($db, "SELECT * FROM ScreenShottr.imageuploads WHERE filename='" . $id . "'"));
	
	$output = '<span class="imagedataset">
	Image ID: ' . $data['ID'] . '<br />
	Filesize: ' . formatBytes($data['FilesizeInBytes']) . '<br />
	Upload Date: ' . date("D d M Y H:i", $data['UploadTimeStamp']) .' GMT<br />
	Times Viewed: ' . $data['TimesViewed'] . '<br />
	Total Bandwidth: ' . formatBytes($data['FilesizeInBytes'] * $data['TimesViewed']). '<br />
	PraviUS Url: <a href="https://pravi.us/' . $data['PraviUSID'] . '">https://pravi.us/' . $data['PraviUSID'] . '</a>
	</span>';
	
	return $output;	
}

function getImageStatsForHeaders($id) {
	date_default_timezone_set('Europe/London');
	$db = mysqli_connect("localhost","ScreenShottr","<password>","ScreenShottr");
	if (mysqli_connect_errno()) {
	  return;
	  //Don't want to affect the User
	  header("X-SCREENSHOTTR-STATUS: DB ERROR");
	}
	$id = mysqli_real_escape_string ($db, $id);
	if (!strpos($id, ".png")) {
		$id = $id . ".png";
	}
	$data = mysqli_fetch_assoc(mysqli_query($db, "SELECT * FROM ScreenShottr.imageuploads WHERE filename='" . $id . "'"));
	
	header('X-ScreenShottr-ImageID: ' . $data['ID'] . '');
	header('X-ScreenShottr-FileSize: ' . formatBytes($data['FilesizeInBytes']) . '');
	header('X-ScreenShottr-UploadDate: ' . date("D d M Y H:i", $data['UploadTimeStamp']) . '');
	header('X-ScreenShottr-TimesViewed: ' . $data['TimesViewed'] . '');
	header('X-ScreenShottr-TotalBandwidth: ' . formatBytes($data['FilesizeInBytes'] * $data['TimesViewed']). '');
	header('X-ScreenShottr-PraviUS-URL: https://pravi.us/' . $data['PraviUSID'] . '');
}

function cleanUp() {
	//$db = mysqli_connect("localhost","ScreenShottr","<password>","ScreenShottr");
	//if (mysqli_connect_errno()) {
	//return;
	  //Don't want to affect the User
	//}
	//$query = mysqli_query($db, "SELECT * FROM ScreenShottr.imageuploads WHERE LastViewedTimeStamp+7884000 < " . time() . ";");
	//while($row = mysqli_fetch_array($query)) {
	//	if ($row['Encrypted'] == "1") {
	//		//unlink('encrypted/' . $row['FileName']);
	//	} else {
	//		//unlink('i/' . $row['FileName']);
	//	}
	//}
	//$query = mysqli_query($db, "DELETE FROM ScreenShottr.imageuploads WHERE LastViewedTimeStamp+7884000 < " . time() . ";");
}

function listIPs() {
	date_default_timezone_set('Europe/London');
	$db = mysqli_connect("localhost","ScreenShottr","<password>","ScreenShottr");
	if (mysqli_connect_errno()) {
	return;
	  //Don't want to affect the User
	}
	$result = mysqli_query($db,"SELECT * FROM users");
	return $result;
	var_dump($result);
	$ipInfo = array();
	/*while($row = mysqli_fetch_array($result)) {
	  echo $row['IP'];
	  $i = 0;
	  $info = file_get_contents('http://freegeoip.net/json/' . $row['IP']);
	  $infoVar = json_decode($info);
	  $ipInfo[$i]['json'] = $infoVar;
	  $i++;
	 }	
	$output = json_encode($ipInfo);
	echo $output;*/
}

?>