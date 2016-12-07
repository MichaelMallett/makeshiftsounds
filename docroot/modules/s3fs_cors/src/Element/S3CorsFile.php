<?php
/**
 * @file
 * Contains \Drupal\Core\Render\Element\Textfield.
 */

//namespace Drupal\Core\Render\Element;
namespace Drupal\s3fs_cors\Element;

use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Element\ManagedFile;
use Drupal\Core\Site\Settings;
use Drupal\Core\Url;
use Drupal\file\Entity\File;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provides a S3 Cors File Element.
 *
 * @FormElement("s3cors_file")
 */
class S3CorsFile extends ManagedFile {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = get_class($this);
    $parent = get_parent_class($this);
    return [
      '#input' => TRUE,
      '#process' => [
          [$class, 'processManagedFile'],
        ],
      '#element_validate' => [
        [$parent, 'validateManagedFile'],
      ],
      '#pre_render' => [
        [$parent, 'preRenderManagedFile'],
      ],
      '#progress_indicator' => 'throbber',
      '#progress_message' => NULL,
      '#theme' => 'file_managed_file',
      '#theme_wrappers' => array('form_element'),
      '#size' => 22,
      '#multiple' => FALSE,
      '#extended' => FALSE,
      '#attached' => [
        'library' => [
          //'file/drupal.file',
          's3fs_cors/cors.file'
        ],
      ],
    ];
  }
  /**
   * {@inheritdoc}
   */
  public static function valueCallback(&$element, $input, FormStateInterface $form_state) {
    // Find the current value of this field.
    $fids = !empty($input['fids']) ? explode(' ', $input['fids']) : [];
    //ksm($form_state->getValues());
    foreach ($fids as $key => $fid) {
      $fids[$key] = (int) $fid;
    }
    $force_default = FALSE;

    // @FIXME: This can certainly be improved. We have copied code from core file module
    //Process any input and save new uploads.
    if ($input !== FALSE) {
      $input['fids'] = $fids;
      $return = $input;
      // Check for #filefield_value_callback values.
      // Because FAPI does not allow multiple #value_callback values like it
      // does for #element_validate and #process, this fills the missing
      // functionality to allow File fields to be extended through FAPI.
      if (isset($element['#file_value_callbacks'])) {
        foreach ($element['#file_value_callbacks'] as $callback) {
          $callback($element, $input, $form_state);
        }
      }

      // Load files if the FIDs have changed to confirm they exist.
      if (!empty($input['fids'])) {
        $fids = [];
        foreach ($input['fids'] as $fid) {
          if ($file = File::load($fid)) {
            $fids[] = $file->id();
            // Temporary files that belong to other users should never be
            // allowed.
            if ($file->isTemporary()) {
              if ($file->getOwnerId() != \Drupal::currentUser()->id())  {
                $force_default = TRUE;
                break;
              }
              // Since file ownership can't be determined for anonymous users,
              // they are not allowed to reuse temporary files at all. But
              // they do need to be able to reuse their own files from earlier
              // submissions of the same form, so to allow that, check for the
              // token added by $this->processManagedFile().
              elseif (\Drupal::currentUser()->isAnonymous()) {
                $token = NestedArray::getValue($form_state->getUserInput(), array_merge($element['#parents'], array('file_' . $file->id(), 'fid_token')));
                if ($token !== Crypt::hmacBase64('file-' . $file->id(), \Drupal::service('private_key')->get() . Settings::getHashSalt())) {
                  $force_default = TRUE;
                  break;
                }
              }
            }
          }
        }
        if ($force_default) {
          $fids = [];
        }
      }
    }

    // If there is no input or if the default value was requested above, use the
    // default value.
    if ($input === FALSE || $force_default) {
      if ($element['#extended']) {
        $default_fids = isset($element['#default_value']['fids']) ? $element['#default_value']['fids'] : [];
        $return = isset($element['#default_value']) ? $element['#default_value'] : ['fids' => []];
      }
      else {
        $default_fids = isset($element['#default_value']) ? $element['#default_value'] : [];
        $return = ['fids' => []];
      }

      // Confirm that the file exists when used as a default value.
      if (!empty($default_fids)) {
        $fids = [];
        foreach ($default_fids as $fid) {
          if ($file = File::load($fid)) {
            $fids[] = $file->id();
          }
        }
      }
    }

    $return['fids'] = $fids;
    return $return;
  }



  public static function processManagedFile(&$element, FormStateInterface $form_state, &$complete_form) {
    //Get ManagedFile Process Form Element and Alter it
    $element = parent::processManagedFile($element, $form_state, $complete_form);
    //Alter Upload - Input File element
    $element['upload']['#attributes'] = ['class' => ['s3fs-cors-upload']];

    $config = \Drupal::config('s3fs.settings');
    $cors_config = \Drupal::config('s3fs_cors.settings');

    // Create Configurations needed for AWS S3 CORS Upload
    $acl = $cors_config->get('s3fs_access_type');
    $bucket = $config->get('bucket');
    $policy = json_encode(array(
        'expiration' => date('Y-m-d\TG:i:s\Z', strtotime('+6 hours')),
        'conditions' => array(
            array(
                'bucket' => $bucket
            ),
            array(
                'acl' => $acl
            ),
            array(
                'starts-with',
                '$key',
                ''
            ),
            array(
                'success_action_status' => '201'
            )
        )
    ));

    $base64Policy = base64_encode($policy);
    $signature = base64_encode(hash_hmac("sha1", $base64Policy, $config->get('secret_key'), $raw_output = true));

    $js_settings = [];
    // Add the extension list to the page as JavaScript settings.
    if (isset($element['#upload_validators']['file_validate_extensions'][0])) {
      $js_settings['extension_list'] = implode(',', array_filter(explode(' ', $element['#upload_validators']['file_validate_extensions'][0])));
      //
    }

    $js_settings['max_size'] = $element['#upload_validators']['file_validate_size'][0];
    $js_settings['upload_location'] = $element['#upload_location'];
    $js_settings['cors_form_data'] = [
      'AWSAccessKeyId' => $config->get('access_key'),
      'acl' => $acl,
      'success_action_status' => 201,
      'policy' => $base64Policy,
      'signature' => $signature
    ];
    $js_settings['cors_form_action'] = $cors_config->get('s3fs_https') . '://s3-' . $config->get('region') . '.amazonaws.com/' . $bucket . '/';
    $element['upload']['#attached']['drupalSettings']['s3fs_cors'][$element['#field_name']] = $js_settings;
    return $element;
  }
}
