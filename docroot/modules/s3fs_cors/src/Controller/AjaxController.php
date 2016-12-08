<?php /**
 * @file
 * Contains \Drupal\s3fs_cors\Controller\DefaultController.
 */

namespace Drupal\s3fs_cors\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\file\Entity\File;
use Aws\S3\S3Client;


/**
 * Default controller for the s3fs_cors module.
 */
class AjaxController extends ControllerBase {
  public function getKey($directory, $file_name, $replace = FILE_EXISTS_RENAME ) {
   $file_key = $directory . '/' . $file_name;
   $file_exists = $this->s3FileExists($file_key);
   if ($file_exists) {
     switch ($replace) {
       case FILE_EXISTS_REPLACE:
         // Do nothing here, we want to overwrite the existing file.
         break;

       case FILE_EXISTS_RENAME:
         $file_key = $this->createFileName($directory, $file_name);
         break;

       case FILE_EXISTS_ERROR:
         // Error reporting handled by calling function.
         return FALSE;
     }
   }
    //Core file_destination is not able to check remoe file existience
    return new JsonResponse(['file_key' => $file_key]);
  }
  private function createFileName($directory, $basename) {
    // Strip control characters (ASCII value < 32). Though these are allowed in
    // some filesystems, not many applications handle them well.
    $basename = preg_replace('/[\x00-\x1F]/u', '_', $basename);
    if (substr(PHP_OS, 0, 3) == 'WIN') {
      // These characters are not allowed in Windows filenames
      $basename = str_replace(array(':', '*', '?', '"', '<', '>', '|'), '_', $basename);
    }

    // A URI or path may already have a trailing slash or look like "public://".
    if (substr($directory, -1) == '/') {
      $separator = '';
    }
    else {
      $separator = '/';
    }

    $destination = $directory . $separator . $basename;

    if ($this->s3FileExists($destination)) {
      // Destination file already exists, generate an alternative.
      $pos = strrpos($basename, '.');
      if ($pos !== FALSE) {
        $name = substr($basename, 0, $pos);
        $ext = substr($basename, $pos);
      }
      else {
        $name = $basename;
        $ext = '';
      }

      $counter = 0;
      do {
        $destination = $directory . $separator . $name . '_' . $counter++ . $ext;
      } while ($this->s3FileExists($destination));
    }

    return $destination;
  }
  private function s3FileExists($key) {
    $config = \Drupal::config('s3fs.settings');
    $bucket = $config->get('bucket');
    $config = \Drupal::config('s3fs.settings');
    $client = S3Client::factory(array(
       'credentials' => array(
         'key'    => $config->get('access_key'),
         'secret' => $config->get('secret_key'),
       ),
       'region'  => $config->get('region'),
       'version' => 'latest'
   ));
   $bucket = $config->get('bucket');
   return $client->doesObjectExist($bucket, $key);
  }
  public function saveFile($directory, $file_name, $file_size, $field_name) {
    $user = \Drupal::currentUser();
    $values = array(
      'uid' => $user->id(),
      'status' => 0,
      'filename' => $file_name,
      'uri' => "s3fs://$directory/$file_name",
      'filesize' => $file_size,
    );
    $values['filemime'] = \Drupal::service('file.mime_type.guesser')->guess($file_name);
    $file = File::create($values);
    $file->source = $field_name;
    $file->save();
    return new JsonResponse(['fid' => $file->id()]);
  }
}
