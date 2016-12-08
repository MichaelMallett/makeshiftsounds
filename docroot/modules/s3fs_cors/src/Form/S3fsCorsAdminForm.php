<?php

/**
 * @file
 * Contains \Drupal\s3fs_cors\Form\S3fsCorsAdminForm.
 */

namespace Drupal\s3fs_cors\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Aws\S3\S3Client;

class S3fsCorsAdminForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 's3fs_cors_admin_form';
  }
  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['s3fs_cors.settings'];
  }

  public function buildForm(array $form, \Drupal\Core\Form\FormStateInterface $form_state) {
    $config = $this->config('s3fs_cors.settings');
    $form['s3fs_cors_origin'] = [
      '#type' => 'textfield',
      '#title' => t('CORS Origin'),
      '#description' => t('Please enter the URL from which your users access this website, e.g. <i>www.example.com</i>.
      You may optionally specifiy up to one wildcard, e.g. <i>*.example.com</i>.<br>
      Upon submitting this form, if this field is filled, your S3 bucket will be configured to allow CORS
      requests from the specified origin. If the field is empty, your bucket\'s CORS config will be deleted.'),
      '#default_value' => !empty($config->get('s3fs_cors_origin')) ? $config->get('s3fs_cors_origin') : '',
    ];
    $form['s3fs_https'] = [
      '#type' => 'radios',
      '#title' => t('Use Https/Http'),
      '#description' => t('Select what method you will like to use with your bucket'),
      '#default_value' => !empty($config->get('s3fs_https')) ? $config->get('s3fs_https') : 'http',
      '#options' => ['http' => 'HTTP', 'https' => 'HTTPS']
    ];
    $form['s3fs_access_type'] = [
      '#type' => 'radios',
      '#title' => t('Access Type on File Uploads'),
      '#description' => t('Select what access permission should be there on File Upload.'),
      '#default_value' => !empty($config->get('s3fs_access_type')) ? $config->get('s3fs_access_type') : 'public-read',
      '#options' => ['public-read' => 'Public Read', 'private' => 'Private']
    ];

    $form = parent::buildForm($form, $form_state);
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $cors_origin = $form_state->getValue('s3fs_cors_origin');
    $this->config('s3fs_cors.settings')
      ->set('s3fs_cors_origin', $cors_origin)
      ->set('s3fs_https', $form_state->getValue('s3fs_https'))
      ->set('s3fs_access_type', $form_state->getValue('s3fs_access_type'))
      ->save();

    //parent::submitForm($form, $form_state);
    //Get S3FS Settings
    $s3_config = \Drupal::config('s3fs.settings');
    if(!empty($s3_config)) {
      $client = S3Client::factory(array(
        'credentials' => array(
          'key'    => $s3_config->get('access_key'),
          'secret' => $s3_config->get('secret_key'),
        ),
        'region'  => $s3_config->get('region'),
        'version' => 'latest'
      ));
      if (!empty($cors_origin)) {
        $client->putBucketCors([
          'Bucket' => $s3_config->get('bucket'), // REQUIRED
          'CORSConfiguration' => [ // REQUIRED
              'CORSRules' => [ // REQUIRED
                [
                  'AllowedHeaders' => array('*'),
                  'ExposeHeaders' => array('x-amz-version-id'),
                  'AllowedMethods' => array('POST'),
                  'MaxAgeSeconds' => 3000,
                  'AllowedOrigins' => array("http://$cors_origin", "https://$cors_origin"),
                ],
                [
                  'AllowedMethods' => array('GET'),
                  'AllowedOrigins' => array('*'),
                ],
                // ...
              ],
          ],
        ]);
        drupal_set_message(t("CORS settings have been succesfully updated at AWS CORS"));
      }
      else {
        // If $form_state['values']['s3fs_cors_origin'] is empty, that means we
        // need to delete their bucket's CORS config.
        $client->deleteBucketCors(array(
          'Bucket' => $s3_config->get('bucket'),
        ));
        drupal_set_message(t("CORS settings have been deleted succesfully"));
      }
    }
    else {
      drupal_set_message(t('No values have been saved. Please check S3 Settings First'));
    }

  }
}
