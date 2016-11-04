<?php

/**
 * @file
 * Contains \Drupal\drupalmoduleupgrader\FixerBase.
 */

namespace Drupal\drupalmoduleupgrader;

use Drupal\Core\Plugin\PluginBase as CorePluginBase;
use Pharborist\Constants\ConstantNode;
use Pharborist\Functions\ParameterNode;
use Pharborist\Node;
use Pharborist\NodeInterface;

/**
 * Base class for fixers, containing a lot of helpful utilities.
 */
abstract class FixerBase extends CorePluginBase implements FixerInterface {

  /**
   * @var \Drupal\drupalmoduleupgrader\TargetInterface
   */
  protected $target;

  /**
   * {@inheritdoc}
   */
  public function setTarget(TargetInterface $target) {
    $this->target = $target;
  }

  protected function getUnaliasedPath($path) {
    return preg_replace('/^~/', $this->target->getBasePath(), $path);
  }

  /**
   * Returns if a node uses a specific trait anywhere in its lineage.
   *
   * @param \Pharborist\NodeInterface $node
   *
   * @return boolean
   */
  protected function usesTrait($trait, NodeInterface $node) {
    $hierarchy = class_parents($node);
    array_unshift($hierarchy, get_class($node));

    $traits = [];
    foreach ($hierarchy as $parent) {
      $this->collectTraits($parent, $traits);
    }

    return in_array($trait, $traits);
  }

  private function collectTraits($class, array &$all_traits = []) {
    $traits = class_uses($class);

    foreach ($traits as $trait) {
      $this->collectTraits($trait, $traits);
    }

    $all_traits += $traits;
  }

}
