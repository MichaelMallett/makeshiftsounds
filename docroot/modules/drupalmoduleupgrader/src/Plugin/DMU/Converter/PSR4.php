<?php

namespace Drupal\drupalmoduleupgrader\Plugin\DMU\Converter;

use Drupal\drupalmoduleupgrader\ConverterBase;
use Drupal\drupalmoduleupgrader\TargetInterface;
use Pharborist\Objects\ClassNode;
use Pharborist\RootNode;
use Pharborist\WhitespaceNode;

/**
 * @Converter(
 *  id = "PSR4",
 *  description = @Translation("Moves classes into PSR-4 directory structure.")
 * )
 */
class PSR4 extends ConverterBase {

  /**
   * {@inheritdoc}
   */
  public function isExecutable(TargetInterface $target) {
    return (boolean) $target->getIndexer('class')->getQuery()->countQuery()->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function convert(TargetInterface $target) {
    $target
      ->getIndexer('class')
      ->getAll()
      ->each(function(ClassNode $class) use ($target) {
        $this->writeClass($target, self::toPSR4($target, $class));
      });
  }

  /**
   * Utility method to PSR4-ify a class. It'll move the class into its own file
   * in the given module's namespace. The class is modified in-place, so you
   * should clone it before calling this function if you want to make a PSR-4
   * *copy* of it.
   *
   * @param \Drupal\drupalmoduleupgrader\TargetInterface $target
   *  The module which will own the class.
   * @param \Pharborist\ClassNode $class
   *  The class to modify.
   *
   * @return \Pharborist\ClassNode
   *  The modified class, returned for convenience.
   */
  public static function toPSR4(TargetInterface $target, ClassNode $class) {
    $ns = 'Drupal\\' . $target->id();
    RootNode::create($ns)->getNamespace($ns)->append($class->remove());
    WhitespaceNode::create("\n\n")->insertBefore($class);

    return $class;
  }

}
