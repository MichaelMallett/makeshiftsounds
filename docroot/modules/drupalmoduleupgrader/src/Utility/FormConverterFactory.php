<?php

namespace Drupal\drupalmoduleupgrader\Utility;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\drupalmoduleupgrader\TargetInterface;

class FormConverterFactory {

  use StringTranslationTrait;

  /**
   * @var \Drupal\drupalmoduleupgrader\RewriterInterface
   */
  protected $rewriter;

  public function __construct(TranslationInterface $translator, PluginManagerInterface $rewriters) {
    $this->stringTranslation = $translator;
    $this->rewriter = $rewriters->createInstance('form_state');
  }

  /**
   * Creates a FormConverter for a specific form.
   *
   * @param TargetInterface $target
   *  The module which defines the form.
   * @param string $form_id
   *  The original form ID.
   *
   * @return FormConverter
   *
   * @throws \BadMethodCallException if the target module doesn't define
   * the given form.
   */
  public function get(TargetInterface $target, $form_id) {
    $indexer = $target->getIndexer('function');

    if ($indexer->has($form_id)) {
      return new FormConverter($target, $form_id, $this->rewriter);
    }
    else {
      $message = $this->t('@target does not define form @form_id.', [
        '@target' => $target->id(),
        '@form_id' => $form_id,
      ]);
      throw new \BadMethodCallException($message);
    }
  }

}
