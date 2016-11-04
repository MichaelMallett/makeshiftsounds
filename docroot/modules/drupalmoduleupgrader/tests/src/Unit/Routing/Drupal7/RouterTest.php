<?php

/**
 * @file
 * Contains \Drupal\Tests\drupalmoduleupgrader\Unit\Converter\Routing\Drupal7\RouterTest.
 */

namespace Drupal\Tests\drupalmoduleupgrader\Unit\Routing\Drupal7;

use Drupal\drupalmoduleupgrader\Routing\Drupal7\Router;
use Drupal\drupalmoduleupgrader\Routing\Drupal7\RouteWrapper;
use Drupal\Tests\UnitTestCase;

/**
 * @group DMU.Routing
 */
class RouterTest extends UnitTestCase {

  private $router;

  public function __construct() {
    $this->router = new Router();

    foreach ($this->hookMenu() as $path => $item) {
      $route = new RouteWrapper($path, $item);
      $this->router->addRoute($route);
    }
  }

  public function testOfType() {
    $this->assertCount(8, $this->router->ofType('MENU_LOCAL_TASK, MENU_DEFAULT_LOCAL_TASK'));
  }

  public function testGetAllLinks() {
    $this->assertCount(9, $this->router->getAllLinks());
  }

  public function testGetLinks() {
    $this->assertCount(1, $this->router->getLinks());
  }

  public function testGetLocalTasks() {
    $this->assertCount(5, $this->router->getLocalTasks());
  }

  public function testGetDefaultLocalTasks() {
    $this->assertCount(3, $this->router->getDefaultLocalTasks());
  }

  public function testGetLocalActions() {
    $this->assertCount(0, $this->router->getLocalActions());
  }

  public function testGetContextualLinks() {
    $this->assertCount(0, $this->router->getContextualLinks());
  }

  public function testFinalize() {
    $this->router->finalize();

    $list_revisions = $this->router['node/%node/revisions/list'];
    $this->assertFalse($list_revisions->hasParent());
    $this->assertFalse($list_revisions->hasChildren());
    $this->assertTrue($list_revisions->hasSiblings());
    $this->assertTrue($list_revisions->getSiblings()->containsKey('node/%node/revisions/view'));

    $view_revisions = $this->router['node/%node/revisions/view'];
    $this->assertFalse($view_revisions->hasParent());
    $this->assertTrue($view_revisions->hasChildren());
    $this->assertTrue($view_revisions->getChildren()->containsKey('node/%node/revisions/view/latest'));
    $this->assertTrue($view_revisions->hasSiblings());
    $this->assertTrue($view_revisions->getSiblings()->containsKey('node/%node/revisions/list'));

    $diff_fields = $this->router['admin/config/content/diff/fields'];
    $this->assertTrue($diff_fields->hasParent());
    $this->assertEquals('admin/config/content/diff', $diff_fields->getParent()->getPath());
  }

  /**
   * The Diff module's hook_menu() implementation. It's a nice mix of things
   * to test on.
   *
   * @return array
   */
  private function hookMenu() {
    $items = [];
    $items['node/%node/revisions/list'] = array(
      'title' => 'List revisions',
      'page callback' => 'diff_diffs_overview',
      'type' => 'MENU_DEFAULT_LOCAL_TASK',
      'access callback' => 'diff_node_revision_access',
      'access arguments' => array(1),
      'file' => 'diff.pages.inc',
    );
    $items['node/%node/revisions/view'] = array(
      'title' => 'Compare revisions',
      'page callback' => 'diff_diffs_show',
      'page arguments' => array(1, 4, 5, 6),
      'type' => 'MENU_LOCAL_TASK',
      'access callback' => 'diff_node_revision_access',
      'access arguments' => array(1),
      'tab_parent' => 'node/%/revisions/list',
      'file' => 'diff.pages.inc',
    );
    $items['node/%node/revisions/view/latest'] = array(
      'title' => 'Show latest difference',
      'page callback' => 'diff_latest',
      'page arguments' => array(1),
      'type' => 'MENU_LOCAL_TASK',
      'access arguments' => array('access content'),
      'tab_parent' => 'node/%/revisions/view',
      'file' => 'diff.pages.inc',
    );
    $items['admin/config/content/diff'] = array(
      'title' => 'Diff',
      'description' => 'Diff settings.',
      'file' => 'diff.admin.inc',
      'page callback' => 'drupal_get_form',
      'page arguments' => array('diff_admin_settings'),
      'access arguments' => array('administer site configuration'),
    );
    $items['admin/config/content/diff/settings'] = array(
      'title' => 'Settings',
      'type' => 'MENU_DEFAULT_LOCAL_TASK',
      'weight' => -10,
    );
    $items['admin/config/content/diff/fields'] = array(
      'title' => 'Fields',
      'description' => 'Field support and settings overview.',
      'file' => 'diff.admin.inc',
      'page callback' => 'diff_admin_field_overview',
      'access arguments' => array('administer site configuration'),
      'type' => 'MENU_LOCAL_TASK',
    );
    $items['admin/config/content/diff/fields/%'] = array(
      'title' => 'Global field settings',
      'page callback' => 'drupal_get_form',
      'page arguments' => array('diff_admin_global_field_settings', 5),
      'access arguments' => array('administer site configuration'),
      'type' => 'MENU_VISIBLE_IN_BREADCRUMB',
      'file' => 'diff.admin.inc',
    );
    $items['admin/config/content/diff/entities'] = array(
      'title' => 'Entities',
      'description' => 'Entity settings.',
      'file' => 'diff.admin.inc',
      'page callback' => 'drupal_get_form',
      'page arguments' => array('diff_admin_global_entity_settings', 'node'),
      'access arguments' => array('administer site configuration'),
      'type' => 'MENU_LOCAL_TASK',
    );
    $items['admin/config/content/diff/entities/node'] = array(
      'title' => 'Nodes',
      'description' => 'Node comparison settings.',
      'type' => 'MENU_DEFAULT_LOCAL_TASK',
      'weight' => -10,
    );
    $items['admin/config/content/diff/entities/user'] = array(
      'title' => 'Users',
      'description' => 'User diff settings.',
      'file' => 'diff.admin.inc',
      'page callback' => 'drupal_get_form',
      'page arguments' => array('diff_admin_global_entity_settings', 'user'),
      'access arguments' => array('administer site configuration'),
      'type' => 'MENU_LOCAL_TASK',
    );

    return $items;
  }

}
