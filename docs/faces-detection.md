# Faces detection

This plugin has the option to detect faces in images.
There are two ways to detect faces in your image.
You can detect faces with PHP or with JavaScript.

![Example of faces detection](./assets/face-detection.jpg "Example of face detection")

## Server face detection (with PHP) (only supports 1 face)

When using server face detection with PHP, the face detection is all done on your webserver.
Your images will not be uploaded to any external platform.
Our PHP face detection code is a modified version of the [softon/laravel-face-detect library](https://github.com/softon/laravel-face-detect).

## Browser faces detection (with JavaScript) (supports multiple faces)

When using browser face detection with JavaScript, the face detection is all done on your own device.
Your images will not be uploaded to any external platform.
Our JavaScript faces detection uses the [jaysalvat/jquery.facedetection](https://github.com/jaysalvat/jquery.facedetection).
