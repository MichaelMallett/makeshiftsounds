<?php
/**
 * @file
 * Contains \Drupal\Tests\drupalmoduleupgrader\Unit\Plugin\DMU\Converter\Functions\FunctionCallModifierTestBase.
 */
namespace Drupal\Tests\drupalmoduleupgrader\Unit\Plugin\DMU\Converter\Functions;

use Drupal\Tests\drupalmoduleupgrader\Unit\TestBase;

/**
 * Base class for testing function call modifiers.
 *
 * @package Drupal\Tests\drupalmoduleupgrader\Unit\Plugin\DMU\Converter\Functions
 */
abstract class FunctionCallModifierTestBase extends TestBase {

  /**
   * The plugin object under test.
   *
   * @var \Drupal\drupalmoduleupgrader\Plugin\DMU\Converter\Functions\FunctionCallModifier
   */
  protected $plugin;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->plugin = $this->getPlugin();
  }

}
