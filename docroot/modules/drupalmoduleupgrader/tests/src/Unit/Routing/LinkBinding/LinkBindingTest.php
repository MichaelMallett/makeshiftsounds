<?php

/**
 * @file
 * Contains \Drupal\Tests\drupalmoduleupgrader\Unit\Converter\Routing\LinkBinding\LinkBindingTest.
 */

namespace Drupal\Tests\drupalmoduleupgrader\Unit\Routing\LinkBinding;

use Drupal\drupalmoduleupgrader\Routing\Drupal7\RouteWrapper as Drupal7Route;
use Drupal\drupalmoduleupgrader\Routing\Drupal8\RouteWrapper as Drupal8Route;
use Drupal\drupalmoduleupgrader\Routing\LinkBinding\LinkBinding;
use Drupal\drupalmoduleupgrader\Routing\LinkIndex;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\Routing\Route;

/**
 * @group DMU.Routing
 */
class LinkBindingTest extends UnitTestCase {

  private $source, $destination;

  public function __construct() {
    $item = array(
      'title' => 'Diff',
      'description' => 'Diff settings.',
      'file' => 'diff.admin.inc',
      'page callback' => 'drupal_get_form',
      'page arguments' => array('diff_admin_settings'),
      'access arguments' => array('administer site configuration'),
    );
    $this->source = new Drupal7Route('admin/config/content/diff', $item);
    $this->destination = new Drupal8Route('diff.settings', new Route('/admin/config/content/diff'), $this->getMock('\Drupal\Core\Routing\RouteProviderInterface'));
  }

  private function getMockBinding() {
    return new LinkBinding($this->source, $this->destination);
  }

  public function testGetSource() {
    $this->assertSame($this->source, $this->getMockBinding()->getSource());
  }

  public function testGetDestination() {
    $this->assertSame($this->destination, $this->getMockBinding()->getDestination());
  }

  public function testGetIdentifier() {
    $this->assertSame('diff.settings', $this->getMockBinding()->getIdentifier());
  }

  public function testOnIndexed() {
    $binding = $this->getMockBinding();
    $index = new LinkIndex();
    $index->addBinding($binding);
    $this->assertSame('diff.settings', $binding->getIdentifier());

    // If a binding is added with the same identifier (which could easily happen,
    // depending on how the binding computes its identifier), _0, _1, etc. should
    // be appended to it by the index.
    $clone = clone $binding;
    $index->addBinding($clone);
    $this->assertSame('diff.settings_0', $clone->getIdentifier());
  }

  public function testBuild() {
    $link = $this->getMockBinding()->build();
    $this->assertEquals('diff.settings', $link['route_name']);
    $this->assertEquals('Diff', $link['title']);
    $this->assertArrayNotHasKey('weight', $link);
  }

}
