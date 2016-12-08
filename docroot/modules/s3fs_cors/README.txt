This module is a fork of AmazonS3 CORS Upload, re-written to work with the
S3 File System module, rather than AmazonS3.

Drupal 8 version also works with multi file upload.

===================
    INSTALLATON
===================


===================
       SETUP
===================
To configure your S3 bucket so that it will accept CORS uploads, go to the
/admin/config/media/s3fs/cors page on your admin site, fill in the "CORS Origin"
field with your site's domain name, and submit it.

Currently this works with only Upload Destination "Amazon S3 files". For any
file or image field change the storage settings to the same. Then from "Manage
Form Display" change to "S3 CORS File Upload" widget for File Field or "S3 CORS
Image Upload" for Image Field.

===================
   Known Issues
===================
CORS uploading is not supported on older browsers. Your users must use
Internet Explorer 10+ (or Edge), Chrome 30+, Firefox 28+, or Safari 7+.

MAINTAINERS
-----------

Current maintainers:

  * webankit (https://www.drupal.org/u/webankit)
