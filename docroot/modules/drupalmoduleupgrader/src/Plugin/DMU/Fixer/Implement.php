<?php

/**
 * @file
 * Contains \Drupal\drupalmoduleupgrader\Plugin\DMU\Fixer\Implement.
 */

namespace Drupal\drupalmoduleupgrader\Plugin\DMU\Fixer;

use Drupal\drupalmoduleupgrader\FixerBase;
use Pharborist\DocCommentNode;
use Pharborist\Objects\ClassMethodNode;

/**
 * @Fixer(
 *  id = "implement"
 * )
 */
class Implement extends FixerBase {

  public function execute() {
    /** @var \Pharborist\Objects\ClassNode $class */
    $class = $this
      ->target
      ->getIndexer('class')
      ->get($this->configuration['target']);

    // Use reflection to get the method definition.
    list ($interface, $method) = explode('::', $this->configuration['definition']);
    $interface = new \ReflectionClass($interface);
    $method = $interface->getMethod($method);

    $node = ClassMethodNode::create($method->getName());
    $node->setDocComment(DocCommentNode::create('@inheritdoc'));
    $class->appendMethod($node);
    $node->matchReflector($method);

    // @TODO There needs to be a way to implement the method body!

    $this->target->save($class);
  }

}
