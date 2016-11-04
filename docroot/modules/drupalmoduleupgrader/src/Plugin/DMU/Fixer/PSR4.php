<?php

/**
 * @file
 * Contains \Drupal\drupalmoduleupgrader\Plugin\DMU\Fixer\PSR4.
 */

namespace Drupal\drupalmoduleupgrader\Plugin\DMU\Fixer;

use Drupal\drupalmoduleupgrader\FixerBase;
use Pharborist\Namespaces\NameNode;
use Pharborist\Parser;
use Pharborist\RootNode;
use Pharborist\WhitespaceNode;

/**
 * @Fixer(
 *  id = "psr4ify"
 * )
 */
class PSR4 extends FixerBase {

  /**
   * {@inheritdoc}
   */
  public function execute() {
    /** @var \Pharborist\Objects\ClassNode $class */
    $class = $this
      ->target
      ->getIndexer('class')
      ->get($this->configuration['source']);

    $ns = substr($this->configuration['destination'], 0, strrpos($this->configuration['destination'], '\\'));
    $doc = RootNode::create($ns);
    $ns = $doc->getNamespace($ns);
    WhitespaceNode::create("\n")->appendTo($ns);

    $import = [];

    if ($parent = $class->getExtends()) {
      $import[] = $parent->getAbsolutePath();
    }

    $interfaces = $class->getImplementList();
    if ($interfaces) {
      foreach ($interfaces->getItems() as $interface) {
        $import[] = $interface->getAbsolutePath();
      }
    }

    foreach ($class->getMethods() as $method) {
      foreach ($method->getParameters() as $parameter) {
        $type_hint = $parameter->getTypeHint();
        if ($type_hint instanceof NameNode) {
          $import[] = $type_hint->getAbsolutePath();
        }
      }
    }

    foreach (array_unique($import) as $i) {
      Parser::parseSnippet('use ' . ltrim($i, '\\') . ';')->appendTo($ns);
      WhitespaceNode::create("\n")->appendTo($ns);
    }

    WhitespaceNode::create("\n")->appendTo($ns);
    $class->remove()->appendTo($ns);

    $search_for = ['Drupal\\' . $this->target->id(), '\\'];
    $replace_with = ['src', '/'];
    $path = str_replace($search_for, $replace_with, $this->configuration['destination']) . '.php';
    file_put_contents($this->target->getPath($path), $doc->getText());
  }

}
