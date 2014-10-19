ScreenShottr
=========

Online image hosting service

  - Encryption
  - Privacy
  - Control over your image
  - Open Source


Requirements
-----------

Dillinger uses a number of open source projects to work properly:

* PHP 5.4 or higher
* PHP MCrypt
* PHP GD
* Read/Write access to the filesystem
* MySQL Database
* Apache web server (Optional but .htaccess provided is for Apache)

Multipart Form POST Requests
--------------

To upload using a Multipart request you can use by default the `imagehost` form name attribute, however you can change this by adding the `?uploadAr=<name>` GET parameter. 


If you are building your own application to work around the ScreenShottr-Web service, you need to construct a HTTP request that looks like the following
```sh
POST /upload.php HTTP/1.1
User-Agent: ScreenShottr-Java/1.0
Content-Type: multipart/form-data; boundary=----BOUNDARYBOUNDARY----
Host: screenshottr.us
Content-Length: 70847
Cache-Control: no-cache

------BOUNDARYBOUNDARY----
content-disposition: form-data; name="id"

1b6af40dbd6f969fc4ff500d76b29a9a
------BOUNDARYBOUNDARY----
content-disposition: form-data; name="imagedata"; filename="ScreenShottr"

<PNG IMAGE DATA>
```
##### Possible GET Parameters

* `?unencrypted=true` - Uploads the image without encryption, results in a shorter URL
* `?uploadAr=<name>` - Allows you to use a custom form-data name when uploading
* `?return=json` Returns a JSON output, instead of the single URL

Here you can see an example of the JSON that would be returned with the `?return=json` parameter.
```json
{
   "pravius":{
      "link":"https:\/\/www.screenshottr.us\/v\/7cd0a5abdb69271419bf1d9484ccdc5a\/bc7af33bfd9aa201206560bacd72f020.jpg",
      "id":"6DX",
      "pravius":"https:\/\/pravi.us\/6DX",
      "secret":"lSlDg",
      "existed":false
   },
   "url":"https:\/\/pravi.us\/6DX",
   "ScreenShottr":{
      "image":"bc7af33bfd9aa201206560bacd72f020.jpg",
      "secret":"91de35f578c769c250f54cbb4991745d",
      "url":"https:\/\/www.screenshottr.us\/v\/7cd0a5abdb69271419bf1d9484ccdc5a\/bc7af33bfd9aa201206560bacd72f020.jpg",
      "key":"7cd0a5abdb69271419bf1d9484ccdc5a"
   }
}
```

#####Deleting an image
You can only delete an image if you have the secret, which is returned only with JSON. If you have the secret and image filename, send a `GET` request to

`action?action=delete&img=<filename>&secret=<secret>`.

If successful this will return nothing, otherwise an error will be displayed.

#####Getting image Stats
To recieve statistics on an image send a GET request to
`action?action=stats&img=<filename>`

This will return something in the following format

```json
{
   "id":"6453",
   "filesizeBytes":"2428730",
   "filesizeHuman":"2.32MB",
   "uploadTimeStamp":"1411772498",
   "uploadTimeHuman":"Sat 27 Sep 2014 03:01",
   "timesViewed":"1",
   "totalBandwidthBytes":2428730,
   "totalBandwidthHuman":"2.32MB",
   "pravusURL":"6DX"
}
```

##### EXIF Data
Currently ScreenShottr does not remove EXIF data.