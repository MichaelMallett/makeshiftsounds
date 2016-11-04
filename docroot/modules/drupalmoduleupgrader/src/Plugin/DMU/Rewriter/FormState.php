<?php

namespace Drupal\drupalmoduleupgrader\Plugin\DMU\Rewriter;

use Pharborist\ExpressionNode;
use Pharborist\Filter;
use Pharborist\Functions\ParameterNode;
use Pharborist\Objects\ObjectMethodCallNode;
use Pharborist\Operators\AssignNode;
use Pharborist\Token;
use Pharborist\Types\ArrayNode;

/**
 * @Rewriter(
 *  id = "form_state",
 *  type_hint = "\Drupal\Core\Form\FormStateInterface",
 *  properties = {
 *    "always_process" = {
 *      "get" = "getAlwaysProcess",
 *      "set" = "setAlwaysProcess"
 *    },
 *    "build_info" = {
 *      "get" = "getBuildInfo",
 *      "set" = "setBuildInfo"
 *    },
 *    "buttons" = {
 *      "get" = "getButtons",
 *      "set" = "setButtons"
 *    },
 *    "cache" = {
 *      "get" = "isCached",
 *      "set" = "setCached"
 *    },
 *    "complete_form" = {
 *      "get" = "getCompleteForm"
 *    },
 *    "executed" = {
 *      "get" = "isExecuted",
 *      "set" = "setExecuted"
 *    },
 *    "groups" = {
 *      "get" = "getGroups",
 *      "set" = "setGroups"
 *    },
 *    "has_file_element" = {
 *      "get" = "hasFileElement",
 *      "set" = "setHasFileElement"
 *    },
 *    "input" = {
 *      "get" = "getUserInput",
 *      "set" = "setUserInput"
 *    },
 *    "limit_validation_errors" = {
 *      "get" = "getLimitValidationErrors",
 *      "set" = "setLimitValidationErrors"
 *    },
 *    "must_validate" = {
 *      "get" = "isValidationEnforced",
 *      "set" = "setValidationEnforced"
 *    },
 *    "process_input" = {
 *      "get" = "isProcessingInput",
 *      "set" = "setProcessInput"
 *    },
 *    "programmed" = {
 *      "get" = "isProgrammed",
 *      "set" = "setProgrammed"
 *    },
 *    "programmed_bypass_access_check" = {
 *      "get" = "isBypassingProgrammedAccessChecks",
 *      "set" = "setProgrammedBypassAccessCheck"
 *    },
 *    "rebuild" = {
 *      "get" = "isRebuilding",
 *      "set" = "setRebuild"
 *    },
 *    "response" = {
 *      "get" = "getResponse",
 *      "set" = "setResponse"
 *    },
 *    "storage" = {
 *      "get" = "getStorage",
 *      "set" = "setStorage"
 *    },
 *    "submit_handlers" = {
 *      "get" = "getSubmitHandlers",
 *      "set" = "setSubmitHandlers"
 *    },
 *    "submitted" = {
 *      "get" = "isSubmitted",
 *      "set" = "getSubmitted"
 *    },
 *    "temporary" = {
 *      "get" = "getTemporary",
 *      "set" = "setTemporary"
 *    },
 *    "triggering_element" = {
 *      "get" = "getTriggeringElement",
 *      "set" = "setTriggeringElement"
 *    },
 *    "validate_handlers" = {
 *      "get" = "getValidateHandlers",
 *      "set" = "setValidateHandlers"
 *    },
 *    "validation_complete" = {
 *      "get" = "isValidationComplete",
 *      "set" = "setValidationComplete"
 *    }
 *  }
 * )
 */
class FormState extends Generic {

  /**
   * {@inheritdoc}
   */
  public function rewrite(ParameterNode $parameter) {
    parent::rewrite($parameter);

    $function = $parameter->getFunction();
    $form_state = Token::variable('$' . $parameter->getName());

    $set_errors = $function->find(Filter::isFunctionCall('form_set_error', 'form_error'));
    /** @var \Pharborist\Functions\FunctionCallNode $set_error */
    foreach ($set_errors as $set_error) {
      $arguments = $set_error->getArguments();
      $method = $set_error->getName()->getText() == 'form_set_error' ? 'setErrorByName' : 'setError';

      $rewrite = ObjectMethodCallNode::create(clone $form_state, $method)
        ->appendArgument(clone $arguments[0])
        ->appendArgument(clone $arguments[1]);

      $set_error->replaceWith($rewrite);
    }

    // form_clear_error() --> $form_state->clearErrors().
    $clear_errors = $function->find(Filter::isFunctionCall('form_clear_error'));
    foreach ($clear_errors as $clear_error) {
      $clear_error->replaceWith(ObjectMethodCallNode::create(clone $form_state, 'clearErrors'));
    }

    // form_get_errors() --> $form_state->getErrors()
    $get_errors = $function->find(Filter::isFunctionCall('form_get_errors'));
    foreach ($get_errors as $get_error) {
      $get_error->replaceWith(ObjectMethodCallNode::create(clone $form_state, 'getErrors'));
    }

    // form_get_error() --> $form_state->getError()
    $get_errors = $function->find(Filter::isFunctionCall('form_get_error'));
    /** @var \Pharborist\Functions\FunctionCallNode $get_error */
    foreach ($get_errors as $get_error) {
      $rewrite = ObjectMethodCallNode::create(clone $form_state, 'getError')
        ->appendArgument($get_error->getArguments()->get(0));
      $get_error->replaceWith($rewrite);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function rewriteAsGetter(ExpressionNode $expr, $property) {
    /** @var \Pharborist\ArrayLookupNode $expr */
    $object = clone $expr->getRootArray();
    $keys = $expr->getKeys();

    // $foo = $form_state['values'] --> $foo = $form_state->getValues()
    // $foo = $form_state['values']['baz'] --> $form_state->getValue(['baz'])
    if ($property == 'values') {
      if (sizeof($keys) == 1) {
        return ObjectMethodCallNode::create($object, 'getValues');
      }
      else {
        array_shift($keys);
        return ObjectMethodCallNode::create($object, 'getValue')->appendArgument(ArrayNode::create($keys));
      }
    }
    elseif (isset($this->pluginDefinition['properties'][$property]['get'])) {
      return parent::rewriteAsGetter($expr, $property);
    }
    // $foo = $form_state['arbitrary_key'] --> $foo = $form_state->get(['arbitrary_key'])
    else {
      return ObjectMethodCallNode::create($object, 'get')
        ->appendArgument(ArrayNode::create($keys));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function rewriteAsSetter(ExpressionNode $expr, $property, AssignNode $assignment) {
    /** @var \Pharborist\ArrayLookupNode $expr */
    $object = clone $expr->getRootArray();
    $keys = $expr->getKeys();
    $value = clone $assignment->getRightOperand();

    // $form_state['values']['baz'] = 'foo' --> $form_state->setValue(['baz'], 'foo')
    if ($property == 'values') {
      array_shift($keys);
      return ObjectMethodCallNode::create($object, 'setValue')
        ->appendArgument(ArrayNode::create($keys))
        ->appendArgument($value);
    }
    elseif (isset($this->pluginDefinition['properties'][$property]['set'])) {
      return parent::rewriteAsSetter($expr, $property, $assignment);
    }
    // $form_state['arbitrary_key'] = 'baz' --> $form_state->set(['arbitrary_key'], 'baz')
    else {
      return ObjectMethodCallNode::create($object, 'set')
        ->appendArgument(ArrayNode::create($keys))
        ->appendArgument($value);
    }
  }

}
