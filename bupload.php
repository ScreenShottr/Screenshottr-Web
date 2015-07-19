<!DOCTYPE html>
<html lang="en">
<head>
<meta charset=utf-8>
<meta name="viewport" content="width=620">
<title>ScreenShottr Uploader</title>
<link rel="stylesheet" href="css/bupload.css">
<script src="js/h5utils.js"></script>
</head>
<body>
<section id="wrapper">
    <header>
      <h1>ScreenShottr Upload</h1>
    </header>
<style>
</style>
<article>
  <div id="holder">
  </div> 
  <p id="upload"><label>Or: <br><input type="file"></label></p>
  <p id="filereader">File API &amp; FileReader API not supported</p>
  <p id="formdata">XHR2&#39;s FormData is not supported</p>
  <p id="progress">XHR2&#39;s upload progress isn&#39;t supported</p>
  <p>Upload progress: <progress id="uploadprogress" min="0" max="100" value="0">0</progress></p>
  <p>Drag an image above to upload it!</p>
  <p id="url"></p>
  <hr />
  <div id="fullurl"></div>
  <div id="praviusSecret"></div>
  <div id="ScreenShottrFilename"></div>
  <div id="ScreenShottrSecret"></div>
  <br />
  
</article>
<script src="js/bupload.js"></script>
<script src="js/prettify.js"></script>
<script src="js/jquery-1.10.2.js"></script>
</body>
</html>
