<?php

/**
 * @file
 * Contains \Drupal\Tests\drupalmoduleupgrader\Unit\Converter\Routing\RouterBaseTest.
 */

namespace Drupal\Tests\drupalmoduleupgrader\Unit\Routing;

use Drupal\drupalmoduleupgrader\Routing\Drupal8\RouteWrapper;
use Drupal\drupalmoduleupgrader\Routing\RouterBase;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * @group DMU.Routing
 */
class RouterBaseTest extends UnitTestCase {

  private $userEdit, $userView, $userRoot, $routeProvider;

  public function __construct() {
    $this->userEdit = new Route('/user/{user}/edit');
    $this->userView = new Route('/user/{user}');
    $this->userRoot = new Route('/user');

    $route_collection = new RouteCollection();
    $route_collection->add('user', $this->userRoot);

    $this->routeProvider = $this->getMock('\Drupal\Core\Routing\RouteProviderInterface');
    $this->routeProvider
      ->expects($this->any())
      ->method('getRoutesByPattern')
      ->with('/user')
      ->will($this->returnValue($route_collection));
  }

  public function testAddRoute() {
    $router = new RouterBase();
    $this->assertCount(0, $router);

    $route = new RouteWrapper('user.edit', $this->userEdit, $this->routeProvider);
    $router->addRoute($route);
    $this->assertCount(1, $router);
  }

  /**
   * @depends testAddRoute
   */
  public function testFinalize() {
    $router = new RouterBase();

    $user_edit = new RouteWrapper('user.edit', $this->userEdit, $this->routeProvider);
    $router->addRoute($user_edit);

    $user_view = new RouteWrapper('user.view', $this->userView, $this->routeProvider);
    $router->addRoute($user_view);

    $router->finalize();

    $this->assertTrue($user_edit->hasParent());
    $this->assertSame($user_view, $user_edit->getParent());
    $this->assertTrue($user_view->hasParent());
    $this->assertEquals('/user', $user_view->getParent()->getPath());
  }

}
