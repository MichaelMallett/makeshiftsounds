<?php

namespace Drupal\drupalmoduleupgrader\Plugin\DMU\Rewriter;

use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\drupalmoduleupgrader\PluginBase;
use Drupal\drupalmoduleupgrader\RewriterInterface;
use Drupal\drupalmoduleupgrader\Utility\Filter\FieldValueFilter;
use Drupal\drupalmoduleupgrader\Utility\Filter\NodeAssignmentFilter;
use Pharborist\ArrayLookupNode;
use Pharborist\Constants\ConstantNode;
use Pharborist\ExpressionNode;
use Pharborist\Filter;
use Pharborist\Functions\CallNode;
use Pharborist\Functions\EmptyNode;
use Pharborist\Functions\IssetNode;
use Pharborist\Functions\ParameterNode;
use Pharborist\Node;
use Pharborist\NodeCollection;
use Pharborist\Objects\ClassConstantLookupNode;
use Pharborist\Objects\ObjectMethodCallNode;
use Pharborist\Objects\ObjectPropertyNode;
use Pharborist\Operators\AssignNode;
use Pharborist\Operators\BooleanNotNode;
use Pharborist\Parser;
use Pharborist\Types\StringNode;
use Pharborist\Variables\VariableNode;
use Psr\Log\LoggerInterface;

/**
 * @Rewriter(
 *  id = "_rewriter",
 *  deriver = "\Drupal\drupalmoduleupgrader\Plugin\DMU\Rewriter\GenericDeriver"
 * )
 */
class Generic extends PluginBase implements RewriterInterface {

  /**
   * @var \Drupal\drupalmoduleupgrader\Utility\Filter\NodeAssignmentFilter
   */
  protected $isAssigned;

  public function __construct(array $configuration, $plugin_id, $plugin_definition, TranslationInterface $translator, LoggerInterface $log) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $translator, $log);
    $this->isAssigned = new NodeAssignmentFilter();
  }

  /**
   * {@inheritdoc}
   */
  public function rewrite(ParameterNode $parameter) {
    // Don't even try to rewrite the function if the parameter is reassigned.
    if ($this->isReassigned($parameter)) {
      $error = $this->t('@function() cannot be parametrically rewritten because @parameter is reassigned.', [
        '@parameter' => $parameter->getName(),
        '@function' => $parameter->getFunction()->getName()->getText(),
      ]);
      throw new \LogicException($error);
    }

    foreach ($this->getExpressions($parameter)->not($this->isAssigned) as $expr) {
      $property = $this->getProperty($expr);
      if (empty($property)) {
        continue;
      }

      $getter = $this->rewriteAsGetter($expr, $property);
      if ($getter) {
        $empty = $expr->closest(Filter::isFunctionCall('empty', 'isset'));

        // If the original expression was wrapped by a call to isset() or
        // empty(), we need to replace it entirely.
        if ($getter instanceof CallNode && $empty instanceof CallNode) {
          // If the isset() or empty() call was negated, reverse that logic.
          $parent = $empty->parent();
          if ($parent instanceof BooleanNotNode) {
            $parent->replaceWith($getter);
          }
          else {
            $empty->replaceWith(BooleanNotNode::fromExpression($getter));
          }
        }
        else {
          $expr->replaceWith($getter);
        }
      }
    }

    foreach ($this->getExpressions($parameter)->filter($this->isAssigned) as $expr) {
      // If the property cannot be determined, don't even try to rewrite the
      // expression.
      $property = $this->getProperty($expr);
      if (empty($property)) {
        continue;
      }

      $assignment = $expr->closest(Filter::isInstanceOf('\Pharborist\Operators\AssignNode'));

      $setter = $this->rewriteAsSetter($expr, $property, $assignment);
      if ($setter) {
        $assignment->replaceWith($setter);
      }
    }

    // Set the type hint, if one is defined.
    if (isset($this->pluginDefinition['type_hint'])) {
      $parameter->setTypeHint($this->pluginDefinition['type_hint']);

      // If the type hint extends FieldableEntityInterface, rewrite any field
      // lookups (e.g. $node->body[LANGUAGE_NONE][0]['value']).
      if (in_array('Drupal\Core\Entity\FieldableEntityInterface', class_implements($this->pluginDefinition['type_hint']))) {
        $filter = new FieldValueFilter($parameter->getName());

        foreach ($parameter->getFunction()->find($filter) as $lookup) {
          $lookup->replaceWith(self::rewriteFieldLookup($lookup));
        }
      }
    }
  }

  /**
   * Finds every rewritable expression in the function body.
   *
   * @param \Pharborist\Functions\ParameterNode $parameter
   *  The parameter on which the rewrite is based.
   *
   * @return \Pharborist\NodeCollection
   */
  protected function getExpressions(ParameterNode $parameter) {
    $filter = Filter::isInstanceOf('\Pharborist\ArrayLookupNode', '\Pharborist\Objects\ObjectPropertyNode');
    $expressions = new NodeCollection();

    $parameter
      ->getFunction()
      ->find(Filter::isInstanceOf('\Pharborist\Variables\VariableNode'))
      ->filter(function(VariableNode $variable) use ($parameter) {
        return $variable->getName() == $parameter->getName();
      })
      ->each(function(VariableNode $variable) use ($filter, $expressions) {
        $root = $variable->furthest($filter);
        if ($root) {
          $expressions->add($root);
        }
      });

    return $expressions;
  }

  /**
   * Returns the property used by a rewritable expression, or NULL if the
   * property cannot be determined.
   *
   * @param \Pharborist\ExpressionNode $expr
   *  The rewritable expression.
   *
   * @return string|NULL
   */
  protected function getProperty(ExpressionNode $expr) {
    if ($expr instanceof ObjectPropertyNode) {
      return $expr->getPropertyName();
    }
    elseif ($expr instanceof ArrayLookupNode) {
      $key = $expr->getKey(0);

      if ($key instanceof StringNode) {
        return $key->toValue();
      }
    }
  }

  /**
   * Rewrites the given expression as a property getter. Returns NULL if the
   * expression cannot be rewritten.
   *
   * @param \Pharborist\ExpressionNode $expr
   *  The expression to rewrite.
   * @param string $property
   *  The property being used in the expression.
   *
   * @return \Pharborist\ExpressionNode|NULL
   */
  public function rewriteAsGetter(ExpressionNode $expr, $property) {
    if ($expr instanceof ObjectPropertyNode) {
      // Should be getRootObject() or getLookupRoot().
      // @see Pharborist issue #191
      $object = clone $expr->getObject();
    }
    elseif ($expr instanceof ArrayLookupNode) {
      $object = clone $expr->getRootArray();
    }

    if (isset($object) && isset($this->pluginDefinition['properties'][$property]['get'])) {
      return ObjectMethodCallNode::create($object, $this->pluginDefinition['properties'][$property]['get']);
    }
  }

  /**
   * Rewrites an assignment expression as a property setter. Returns NULL if
   * the expression cannot be rewritten.
   *
   * @param \Pharborist\ExpressionNode $expr
   *  The expression to rewrite.
   * @param string $property
   *  The property being used in the expression.
   * @param \Pharborist\Operators\AssignNode $assignment
   *  The entire assignment expression being rewritten.
   *
   * @return \Pharborist\ExpressionNode|NULL
   */
  public function rewriteAsSetter(ExpressionNode $expr, $property, AssignNode $assignment) {
    if ($expr instanceof ObjectPropertyNode) {
      // Should be getRootObject() or getLookupRoot().
      // @see Pharborist issue #191
      $object = clone $expr->getObject();
    }
    elseif ($expr instanceof ArrayLookupNode) {
      $object = clone $expr->getRootArray();
    }

    if (isset($object) && isset($this->pluginDefinition['properties'][$property]['set'])) {
      return ObjectMethodCallNode::create($object, $this->pluginDefinition['properties'][$property]['set'])
        ->appendArgument(clone $assignment->getRightOperand());
    }
  }

  /**
   * Returns if the parameter is fully reassigned anywhere in the function.
   *
   * @param \Pharborist\Functions\ParameterNode $parameter
   *  The parameter to check.
   *
   * @return boolean
   */
  protected function isReassigned(ParameterNode $parameter) {
    return (boolean) $parameter
      ->getFunction()
      ->find(Filter::isInstanceOf('\Pharborist\Variables\VariableNode'))
      ->filter(function(VariableNode $variable) use ($parameter) {
        return $variable->getName() == $parameter->getName();
      })
      ->filter($this->isAssigned)
      ->count();
  }

  /**
   * Rewrites a Drupal 7 field lookup like so:
   *
   * $node->body[LANGUAGE_NONE][0]['value'] --> $node->body[0]->value
   * $node->body['fr'][0]['value'] --> $node->getTranslation('fr')->body[0]->value
   *
   * @param \Pharborist\ArrayLookupNode $node
   *  The original field lookup.
   *
   * @return \Pharborist\ExpressionNode
   */
  public static function rewriteFieldLookup(ArrayLookupNode $node) {
    $keys = $node->getKeys();
    /** @var \Pharborist\Objects\ObjectPropertyNode $root */
    $root = $node->getRootArray();
    $expr = $root->getObject()->getText();

    if (self::isTranslation($keys[0])) {
      $expr .= '->getTranslation(' . $keys[0] . ')';
    }
    $expr .= '->' . $root->getPropertyName() . '[' . $keys[1] . ']';

    /** @var \Pharborist\Types\StringNode|\Pharborist\Node $column */
    foreach (array_slice($keys, 2) as $column) {
      $expr .= '->';
      $expr .= $column instanceof StringNode ? $column->toValue() : $column->getText();
    }

    return Parser::parseExpression($expr);
  }

  /**
   * Checks if a field lookup key is translated. This will be TRUE unless one
   * of the following conditions applies:
   *
   * - The key is the Drupal\Core\Language\Language::LANGCODE_NOT_SPECIFIED
   *   constant.
   * - The key is the LANGUAGE_NONE constant from Drupal 7.
   * - The key is the string 'und'.
   *
   * @param Node $key
   *  The key to check.
   *
   * @return boolean
   */
  public static function isTranslation(Node $key) {
    if ($key instanceof ClassConstantLookupNode) {
      $constant = $key->getClassName() . '::' . $key->getConstantName();
      return $constant != '\Drupal\Core\Language\Language::LANGCODE_NOT_SPECIFIED';
    }
    elseif ($key instanceof ConstantNode) {
      return $key->getConstantName() != 'LANGUAGE_NONE';
    }
    elseif ($key instanceof StringNode) {
      return $key->toValue() != 'und';
    }
    else {
      return TRUE;
    }
  }

}
