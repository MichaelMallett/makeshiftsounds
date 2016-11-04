<?php

/**
 * @file
 * Contains \Drupal\drupalmoduleupgrader\Converter\Routing\Drupal7\Route.
 */

namespace Drupal\drupalmoduleupgrader\Routing\Drupal7;

use Doctrine\Common\Collections\ArrayCollection;
use Drupal\drupalmoduleupgrader\Routing\RouterBuiltEvent;
use Drupal\drupalmoduleupgrader\Routing\RouteWrapperInterface;
use Drupal\drupalmoduleupgrader\Utility\Path\Drupal7\PathUtility;

/**
 * Encapsulates a Drupal 7 route (including the link, if any).
 */
class RouteWrapper extends ArrayCollection implements RouteWrapperInterface {

  /**
   * @var \Drupal\drupalmoduleupgrader\Utility\Path\Drupal7\PathUtility
   */
  protected $path;

  /**
   * @var \Drupal\drupalmoduleupgrader\Routing\RouterInterface
   */
  protected $router;

  /**
   * @var static|NULL
   */
  protected $parent;

  /**
   * @var \Drupal\drupalmoduleupgrader\Routing\Drupal7\Router
   */
  protected $children;

  /**
   * @var \Drupal\drupalmoduleupgrader\Routing\Drupal7\Router
   */
  protected $siblings;

  /**
   * Constructs a Route object.
   */
  public function __construct($path, array $item) {
    $this->path = new PathUtility($path);

    // Merge in hook_menu() defaults to normalize things.
    $item += [
      'title callback' => 't',
      'title arguments' => [],
      'access callback' => 'user_access',
      'access arguments' => [],
      'page arguments' => [],
      'type' => 'MENU_NORMAL_ITEM',
    ];
    parent::__construct($item);
  }

  /**
   * {@inheritdoc}
   */
  public function getIdentifier() {
    return $this->getPath()->__toString();
  }

  /**
   * {@inheritdoc}
   */
  public function getPath() {
    return $this->path;
  }

  /**
   * {@inheritdoc}
   */
  public function hasParent() {
    return isset($this->parent);
  }

  /**
   * {@inheritdoc}
   */
  public function getParent() {
    return $this->parent;
  }

  /**
   * {@inheritdoc}
   */
  public function unwrap() {
    return $this->toArray();
  }

  /**
   * {@inheritdoc}
   */
  public function onRouterBuilt(RouterBuiltEvent $event) {
    $this->router = $event->getRouter();

    $my_path = $this->getPath();
    $my_length = sizeof($my_path);
    $my_path = (string) $my_path;

    // If trying to get the parent raises an exception, we're going to
    // bail out. But we don't need the parent in order to find our own
    // children, so search for them before searching for the parent.
    $this->children = $this->router
      ->filter(function(RouteWrapper $route) use ($my_path, $my_length) {
        $path = $route->getPath();
        // <WTF>$path needs to be explicitly cast to a string, 'cause strPos() won't do
        // it, even though trim() and similar functions will.</WTF>
        return (sizeof($path) == ($my_length + 1) && strPos((string) $path, $my_path) === 0);
      })
      ->ofType('MENU_LOCAL_TASK, MENU_DEFAULT_LOCAL_TASK, MENU_LOCAL_ACTION');

    try {
      $parent = $this->getPath()->getParent();
      $this->parent = $this->router->get($parent->__toString());
    }
    catch (\LengthException $e) {
      // Because there's no parent path, we can't effectively search for siblings.
      // Time to die.
      return;
    }

    $this->siblings = $this->router
      ->filter(function(RouteWrapper $route) use ($parent, $my_path, $my_length) {
        $path = $route->getPath();
        // <WTF>strPos(), <sarcasm>in its wisdom</sarcasm>, won't cast to string.</WTF>
        return ($path !== $my_path && sizeof($path) == $my_length && strPos((string) $path, (string) $parent) === 0);
      });
  }

  /**
   * Returns if this route has an absolute access flag (TRUE or FALSE).
   *
   * @return boolean
   */
  public function isAbsoluteAccess() {
    return is_bool($this->get('access callback'));
  }

  /**
   * Returns if this route has permission-based access.
   *
   * @return boolean
   */
  public function isPermissionBased() {
    return ($this->get('access callback') == 'user_access');
  }

  /**
   * Returns if this route exposes a link of any kind.
   *
   * @return boolean
   */
  public function hasLink() {
    return ($this->isLink() || $this->isLocalTask() || $this->isDefaultLocalTask() || $this->isLocalAction());
  }

  /**
   * Returns if this route is a normal link.
   *
   * @return boolean
   */
  public function isLink() {
    return $this->get('type') == 'MENU_NORMAL_ITEM';
  }

  /**
   * Returns if this route is a local task (NOT a default local task).
   *
   * @return boolean
   */
  public function isLocalTask() {
    return $this->get('type') == 'MENU_LOCAL_TASK';
  }

  /**
   * Gets the closest default local task, if there is one.
   *
   * @return static|NULL
   */
  public function getDefaultTask() {
    if ($this->hasSiblings()) {
      return $this->getSiblings()->ofType('MENU_DEFAULT_LOCAL_TASK')->first();
    }
  }

  /**
   * Returns if this route is a default local task.
   *
   * @return boolean
   */
  public function isDefaultLocalTask() {
    return $this->get('type') == 'MENU_DEFAULT_LOCAL_TASK';
  }

  /**
   * Returns if this route is a local action.
   *
   * @return boolean
   */
  public function isLocalAction() {
    return $this->get('type') == 'MENU_LOCAL_ACTION';
  }

  /**
   * Returns if this route is a contextual link.
   *
   * @return boolean
   */
  public function isContextualLink() {
    return ($this->isLocalAction() && $this->containsKey('context') && $this->get('context') == 'MENU_CONTEXT_INLINE');
  }

  /**
   * Returns if this route has children.
   *
   * @return boolean
   */
  public function hasChildren() {
    return $this->getChildren()->count() > 0;
  }

  /**
   * Returns the immediate children of this route.
   *
   * @return \Drupal\drupalmoduleupgrader\Routing\Drupal7\Router
   */
  public function getChildren() {
    return $this->children;
  }

  /**
   * Returns if this route has siblings.
   *
   * @return boolean
   */
  public function hasSiblings() {
    return $this->getSiblings()->count() > 0;
  }

  /**
   * Gets the siblings of this route.
   *
   * @return \Drupal\drupalmoduleupgrader\Routing\Drupal7\Router
   */
  public function getSiblings() {
    return $this->siblings;
  }

}
