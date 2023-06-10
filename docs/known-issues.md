# Known issues

Do you encouner problems using this plugin?
You may be able to find the solution here!

## The plugin deactivates automatically

This is caused because of a plugin conflict.
To prevent your site from crashing, Image Crop Positioner will deactivate itself.
One of the plugins known to cause this, is [My Eyes Are Up Here](https://wordpress.org/plugins/my-eyes-are-up-here/).

To make sure everything works as expected, follow these steps when activating Image Crop Positioner:

1. Deactivate every plugin that conflicts with Image Crop Positioner. If you are using a multisite, make sure it's deactivated both on network level, and for every site separately.
2. Now you may uninstall the conflicting plugins.
3. Activate Image Crop Positioner.

## Uploading new images to the media library fails

This is probably caused by server face detection (with PHP). This process can be quite heavy for small webservers.
To fix this issue, go to the plugin settings, and disable the "Server face detection - Auto detect and crop" setting.
This will prevent automatically attempting to detect faces and crop the image after uploading.

## Server face detection (with PHP) takes a long time and eventually crashes

The process of server face detection can be quite a heavy process.
Small webservers may not be able to handle such load, and take too long.
You have a few things you can do:

1. Disable server face detection.
2. Upgrade your webserver with more RAM and/or a better processor.
3. Fiddle around with your PHP settings, for example the max_execution_time.
