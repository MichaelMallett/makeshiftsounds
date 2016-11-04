<?php

/**
 * @file
 * Contains \Drupal\Tests\drupalmoduleupgrader\Unit\Converter\Routing\Drupal7\RouteWrapperTest.
 */

namespace Drupal\Tests\drupalmoduleupgrader\Unit\Routing\Drupal7;

use Drupal\drupalmoduleupgrader\Routing\Drupal7\RouteWrapper;
use Drupal\Tests\UnitTestCase;

/**
 * @group DMU.Routing
 */
class RouteWrapperTest extends UnitTestCase {

  private function getMockRouteWrapper() {
    $route = [
      'title' => 'List revisions',
      'page callback' => 'diff_diffs_overview',
      'type' => 'MENU_DEFAULT_LOCAL_TASK',
      'access callback' => 'diff_node_revision_access',
      'access arguments' => [1],
      'file' => 'diff.pages.inc',
    ];
    return new RouteWrapper('node/%node/revisions/list', $route);
  }

  public function testGetIdentifier() {
    $this->assertEquals('node/%node/revisions/list', $this->getMockRouteWrapper()->getIdentifier());
  }

  public function testGetPath() {
    $wrapper = $this->getMockRouteWrapper();
    $this->assertInstanceOf('\Drupal\drupalmoduleupgrader\Utility\Path\Drupal7\PathUtility', $wrapper->getPath());
    $this->assertEquals('node/%node/revisions/list', $wrapper->getPath());
  }

  public function testUnwrap() {
    $route = [
      'title' => 'List revisions',
      'page callback' => 'diff_diffs_overview',
      'type' => 'MENU_DEFAULT_LOCAL_TASK',
      'access callback' => 'diff_node_revision_access',
      'access arguments' => [1],
      'file' => 'diff.pages.inc',
    ];

    $unwrapped_route = $this->getMockRouteWrapper()->unwrap();
    $this->assertTrue(is_array($unwrapped_route));

    foreach ($route as $key => $value) {
      $this->assertArrayHasKey($key, $unwrapped_route);
      $this->assertEquals($value, $unwrapped_route[$key]);
    }
  }

  public function testIsAbsoluteAccess() {
    $wrapper = $this->getMockRouteWrapper();

    $this->assertFalse($wrapper->isAbsoluteAccess());
    $wrapper['access callback'] = TRUE;
    $this->assertTrue($wrapper->isAbsoluteAccess());
    $wrapper['access callback'] = FALSE;
    $this->assertTrue($wrapper->isAbsoluteAccess());
  }

  public function testIsPermissionBased() {
    $wrapper = $this->getMockRouteWrapper();

    $this->assertFalse($wrapper->isPermissionBased());
    $wrapper['access callback'] = 'user_access';
    $this->assertTrue($wrapper->isPermissionBased());
  }

  public function testHasLink() {
    $this->assertTrue($this->getMockRouteWrapper()->hasLink());
  }

  public function testIsLink() {
    $wrapper = $this->getMockRouteWrapper();
    $this->assertFalse($wrapper->isLink());

    $wrapper['type'] = 'MENU_NORMAL_ITEM';
    $this->assertTrue($wrapper->isLink());
  }

  public function testIsLocalTask() {
    $wrapper = $this->getMockRouteWrapper();
    $this->assertFalse($wrapper->isLocalTask());

    $wrapper['type'] = 'MENU_LOCAL_TASK';
    $this->assertTrue($wrapper->isLocalTask());
  }

  public function testIsDefaultLocalTask() {
    $wrapper = $this->getMockRouteWrapper();
    $this->assertTrue($wrapper->isDefaultLocalTask());

    $wrapper['type'] = 'MENU_NORMAL_ITEM';
    $this->assertFalse($wrapper->isDefaultLocalTask());
  }

  public function testIsLocalAction() {
    $wrapper = $this->getMockRouteWrapper();
    $this->assertFalse($wrapper->isLocalAction());

    $wrapper['type'] = 'MENU_LOCAL_ACTION';
    $this->assertTrue($wrapper->isLocalAction());
  }

  public function testIsContextualLink() {
    $wrapper = $this->getMockRouteWrapper();
    $this->assertFalse($wrapper->isContextualLink());

    $wrapper['type'] = 'MENU_LOCAL_ACTION';
    $this->assertTrue($wrapper->isLocalAction());
    $this->assertFalse($wrapper->isContextualLink());

    $wrapper['context'] = 'MENU_CONTEXT_INLINE';
    $this->assertTrue($wrapper->isContextualLink());

    $wrapper['type'] = 'MENU_NORMAL_ITEM';
    $this->assertFalse($wrapper->isContextualLink());
  }

}
