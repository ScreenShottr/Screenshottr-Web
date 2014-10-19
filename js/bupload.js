var holder = document.getElementById('holder'),
    tests = {
      filereader: typeof FileReader != 'undefined',
      dnd: 'draggable' in document.createElement('span'),
      formdata: !!window.FormData,
      progress: "upload" in new XMLHttpRequest
    }, 
    support = {
      filereader: document.getElementById('filereader'),
      formdata: document.getElementById('formdata'),
      progress: document.getElementById('progress')
    },
    acceptedTypes = {
      'image/png': true,
      'image/jpeg': true,
      'image/gif': true
    },
    progress = document.getElementById('uploadprogress'),
    fileupload = document.getElementById('upload');

"filereader formdata progress".split(' ').forEach(function (api) {
  if (tests[api] === false) {
    support[api].className = 'fail';
  } else {
    support[api].className = 'hidden';
  }
});

function previewfile(file) {
  if (tests.filereader === true && acceptedTypes[file.type] === true) {
    var reader = new FileReader();
    reader.onload = function (event) {
      var image = new Image();
      image.src = event.target.result;
      image.width = 250; // a fake resize
      holder.appendChild(image);
    };

    reader.readAsDataURL(file);
  }  else {
    holder.innerHTML += '<p>Uploaded ' + file.name + ' ' + (file.size ? (file.size/1024|0) + 'K' : '');
    console.log(file);
  }
}

function readfiles(files) {
    var formData = tests.formdata ? new FormData() : null;
    for (var i = 0; i < files.length; i++) {
      if (tests.formdata) formData.append('imagedata', files[i]);
      previewfile(files[i]);
    }

    // now post a new XHR request
    if (tests.formdata) {
      var xhr = new XMLHttpRequest();
      xhr.open('POST', 'upload.php?return=json');
      xhr.onload = function() {
        progress.value = progress.innerHTML = 100;
		document.getElementById('holder').innerHTML='';
		var parsedScreenshottrResult = JSON.parse(xhr.responseText);
		console.log(parsedScreenshottrResult);
		document.getElementById('url').innerHTML="<a href='" + parsedScreenshottrResult.url + "'>" + parsedScreenshottrResult.url + "</a>";
		document.getElementById('fullurl').innerHTML='ScreenShottr URL: <span style="font-size: 10px;"><a href="' + parsedScreenshottrResult.ScreenShottr.url + '"><abbr title="' + parsedScreenshottrResult.ScreenShottr.url + '">' + parsedScreenshottrResult.ScreenShottr.url + '</abbr></a></span>';
		document.getElementById('praviusSecret').innerHTML='Pravius Secret: <span style="color: blue">' + parsedScreenshottrResult.pravius.secret + '</span>&nbsp;&#124;&nbsp;Delete';
		document.getElementById('ScreenShottrFilename').innerHTML='ScreenShottr Filename: <span style="color: blue">' + parsedScreenshottrResult.ScreenShottr.image + '</span>&nbsp;&#124;&nbsp;<abbr onclick="deleteImage(\'' + parsedScreenshottrResult.ScreenShottr.image + '\', \'' + parsedScreenshottrResult.ScreenShottr.secret + '\');" title="Will delete from ScreenShottr and Pravius">Delete</abbr>';
		document.getElementById('ScreenShottrSecret').innerHTML='ScreenShottr Secret: <span style="color: blue">' + parsedScreenshottrResult.ScreenShottr.secret + '</span>&nbsp;&#124;&nbsp;<abbr onclick="deleteImage(\'' + parsedScreenshottrResult.ScreenShottr.image + '\', \'' + parsedScreenshottrResult.ScreenShottr.secret + '\');" title="Will delete from ScreenShottr and Pravius">Delete</abbr>';
      };

      if (tests.progress) {
        xhr.upload.onprogress = function (event) {
          if (event.lengthComputable) {
            var complete = (event.loaded / event.total * 100 | 0);
            progress.value = progress.innerHTML = complete;
          }
        }
      }

      xhr.send(formData);
    }
}

function deleteImage(filename, secret) {
	$.get( "action?action=delete&img=" + filename + "&secret=" + secret, function( data ) {
		if (data == "") {
			document.getElementById('url').innerHTML='Image has been deleted';
			document.getElementById('fullurl').innerHTML='';
			document.getElementById('praviusSecret').innerHTML='';
			document.getElementById('ScreenShottrFilename').innerHTML='';
			document.getElementById('ScreenShottrSecret').innerHTML='';
		} else {
			document.getElementById('url').innerHTML=data;
		}
	});
}

if (tests.dnd) { 
  //fileupload.className = 'hidden';
  fileupload.querySelector('input').onchange = function () {
    readfiles(this.files);
  };
  holder.ondragover = function () { this.className = 'hover'; return false; };
  holder.ondragend = function () { this.className = ''; return false; };
  holder.ondrop = function (e) {
    this.className = '';
    e.preventDefault();
    readfiles(e.dataTransfer.files);
  }
} else {
  fileupload.className = 'hidden';
  fileupload.querySelector('input').onchange = function () {
    readfiles(this.files);
  };
}