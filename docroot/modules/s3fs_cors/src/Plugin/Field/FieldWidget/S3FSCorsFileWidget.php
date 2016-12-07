<?php
/**
 * @file
 * Contains \Drupal\s3fs_cors\Plugin\Field\FieldWidget\S3FSCorsWidget.
 */

namespace Drupal\s3fs_cors\Plugin\Field\FieldWidget;

use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Plugin\Field\FieldWidget\FileWidget;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\s3fs_cors\Element\S3CorsFile;
/**
 * Plugin implementation of the 's3fs_cors_widget' widget.
 *
 * @FieldWidget(
 *   id = "s3fs_cors_file_widget",
 *   label = @Translation("S3 CORS File Upload"),
 *   field_types = {
 *     "file"
 *   }
 * )
 */
class S3FSCorsFileWidget extends FileWidget {
  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);
    $element_info = $this->elementInfo->getInfo('s3cors_file');

    $element['#type'] = 's3cors_file';
    $element['#process'] = [$element_info['#process'][0], $element['#process'][1]];
    return $element;
  }
  /**
   * Form API callback. Retrieves the value for the file_generic field element.
   *
   * This method is assigned as a #value_callback in formElement() method.
   */
  public static function value($element, $input = FALSE, FormStateInterface $form_state) {
    if ($input) {
      // Checkboxes lose their value when empty.
      // If the display field is present make sure its unchecked value is saved.
      if (empty($input['display'])) {
        $input['display'] = $element['#display_field'] ? 0 : 1;
      }
    }
    // We depend on the managed file element to handle uploads.
    $return = S3CorsFile::valueCallback($element, $input, $form_state);

    // Ensure that all the required properties are returned even if empty.
    $return += array(
      'fids' => array(),
      'display' => 1,
      'description' => '',
    );
    return $return;
  }
}
