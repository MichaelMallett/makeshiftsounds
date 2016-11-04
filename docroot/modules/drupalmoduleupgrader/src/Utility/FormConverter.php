<?php

namespace Drupal\drupalmoduleupgrader\Utility;

use Drupal\drupalmoduleupgrader\RewriterInterface;
use Drupal\drupalmoduleupgrader\TargetInterface;
use Pharborist\Filter;
use Pharborist\Functions\FunctionCallNode;
use Pharborist\Functions\FunctionDeclarationNode;
use Pharborist\Functions\ParameterNode;
use Pharborist\Objects\ClassMethodNode;
use Pharborist\Objects\ClassNode;
use Pharborist\Parser;
use Pharborist\Token;
use Pharborist\TokenNode;

/**
 * Converts a form from a set of callback functions to a class implementing
 * \Drupal\Core\Form\FormInterface.
 */
class FormConverter {

  use StringTransformTrait;

  /**
   * @var \Drupal\drupalmoduleupgrader\TargetInterface
   */
  protected $target;

  /**
   * @var string
   */
  protected $formID;

  /**
   * @var \Pharborist\Functions\FunctionDeclarationNode
   */
  protected $builder;

  /**
   * @var \Pharborist\Functions\FunctionDeclarationNode
   */
  protected $validator;

  /**
   * @var \Pharborist\Functions\FunctionDeclarationNode
   */
  protected $submitHandler;

  /**
   * @var boolean
   */
  protected $isConfig;

  /**
   * @var \Drupal\drupalmoduleupgrader\RewriterInterface
   */
  protected $formStateRewriter;

  /**
   * @var \Pharborist\Objects\ClassNode
   */
  protected $controller;

  public function __construct(TargetInterface $target, $form_id, RewriterInterface $rewriter) {
    $indexer = $target->getIndexer('function');

    $this->target = $target;
    $this->formID = $form_id;

    $this->builder = $indexer->get($form_id);

    $validator = $form_id . '_validate';
    if ($indexer->has($validator)) {
      $this->validator = $indexer->get($validator);
    }
    $submit_handler = $form_id . '_submit';
    if ($indexer->has($submit_handler)) {
      $this->submitHandler = $indexer->get($submit_handler);
    }

    $this->isConfig = $this->builder->has(Filter::isFunctionCall('system_settings_form'));
    $this->formStateRewriter = $rewriter;
  }

  /**
   * @return \Pharborist\Objects\ClassNode
   */
  public function render() {
    if (empty($this->controller)) {
      $render = [
        '#theme' => 'dmu_form',
        '#module' => $this->target->id(),
        '#form_id' => $this->formID,
        '#class' => $this->toTitleCase($this->formID),
        '#config' => $this->isConfig,
      ];
      $source = \Drupal::service('renderer')->renderPlain($render);
      $this->controller = Parser::parseSource($source)
        ->find(Filter::isClass($render['#class']))->get(0);
    }
    return $this->controller;
  }

  /**
   * @return \Pharborist\Objects\ClassNode
   */
  public function build() {
    $controller = $this->render();

    $builder = $this->addMethod($this->builder, $controller, 'buildForm');
    if ($this->isConfig) {
      $builder
        ->find(Filter::isFunctionCall('system_settings_form'))
        ->each(function(FunctionCallNode $call) {
          $call
            ->setName('parent::buildForm')
            ->appendArgument(Token::variable('$form_state'));
        });
    }

    if ($this->validator) {
      $this
        ->addMethod($this->validator, $controller, 'validateForm')
        ->getParameterAtIndex(0)
        ->setReference(TRUE)
        ->setTypeHint('array');
    }
    if ($this->submitHandler) {
      $this
        ->addMethod($this->submitHandler, $controller, ($this->isConfig ? '_submitForm' : 'submitForm'))
        ->getParameterAtIndex(0)
        ->setReference(TRUE)
        ->setTypeHint('array');
    }

    return $controller;
  }

  /**
   * @return \Pharborist\Objects\ClassMethodNode
   */
  protected function addMethod(FunctionDeclarationNode $function, ClassNode $class, $alias = NULL) {
    $method = ClassMethodNode::fromFunction($function);
    if ($alias) {
      $method->setName($alias);
    }
    $class->appendMethod($method);

    // Add the parameters required for FormInterface conformance.
    $parameters = $method->getParameters()->toArray();
    if (empty($parameters)) {
      $parameters[0] = ParameterNode::create('$form');
      $method->appendParameter($parameters[0]);
    }
    if (sizeof($parameters) == 1) {
      $parameters[1] = ParameterNode::create('$form_state');
      $method->appendParameter($parameters[1]);
    }

    // The $form parameter must have the array type hint.
    $parameters[0]->setTypeHint('array');

    // The form state is never passed by reference.
    $parameters[1]->setReference(FALSE);

    // Additional parameters MUST have a default value of NULL in order to conform
    // to FormInterface.
    for ($i = 2; $i < sizeof($parameters); $i++) {
      $parameters[$i]->setValue(new TokenNode(T_STRING, 'NULL'));
    }

    $this->formStateRewriter->rewrite($parameters[1]);

    return $method;
  }

}
