<?php

namespace Drupal\drupalmoduleupgrader\Utility\Filter;

use Pharborist\ArrayLookupNode;
use Pharborist\Node;
use Pharborist\Objects\ObjectPropertyNode;
use Pharborist\Variables\VariableNode;

/**
 * Filters for things that *look like* field accesses, e.g.
 * $foo->bar[LANGUAGE_NONE][0]['value']. This filter doesn't guarantee that
 * matched nodes actually ARE field accesses -- just that they have the proper
 * formation (S-foils in attack formation!...what, you don't like Star Wars?)
 */
class FieldValueFilter {

  /**
   * @var string
   */
  protected $variable;

  public function __construct($variable) {
    $this->variable = $variable;
  }

  /**
   * @return boolean
   */
  public function __invoke(Node $node) {
    if ($node instanceof ArrayLookupNode) {
      $root = $node->getRootArray();

      if ($root instanceof ObjectPropertyNode) {
        $object = $root->getObject();

        if ($object instanceof VariableNode && $object->getName() == $this->variable) {
          return (sizeof($node->getKeys()) >= 3);
        }
      }
    }
    return FALSE;
  }

}
