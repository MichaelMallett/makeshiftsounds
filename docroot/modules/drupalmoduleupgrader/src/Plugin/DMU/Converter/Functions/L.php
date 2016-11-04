<?php

namespace Drupal\drupalmoduleupgrader\Plugin\DMU\Converter\Functions;

use Drupal\drupalmoduleupgrader\TargetInterface;
use Pharborist\Functions\FunctionCallNode;
use Pharborist\Objects\ClassMethodCallNode;
use Pharborist\Parser;
use Pharborist\Types\StringNode;

/**
 * @Converter(
 *  id = "l",
 *  description = @Translation("Rewrites calls to l()."),
 *  fixme = @Translation("l() expects a Url object, created from a route name or external URI."),
 *  dependencies = { "router.route_provider" }
 * )
 */
class L extends URL {

  /**
   * {@inheritdoc}
   */
  public function rewrite(FunctionCallNode $call, TargetInterface $target) {
    $arguments = $call->getArguments();
    if ($arguments[1] instanceof StringNode) {
      // Create a call to url() and let the parent class rewrite it like normal,
      // so we don't have to duplicate that code.
      $url = Parser::parseSnippet('url(' . $arguments[1] . ');')->firstChild();
      $url_rewritten = parent::rewrite($url, $target);
      if ($url_rewritten) {
        return ClassMethodCallNode::create('\Drupal', 'l')
          ->appendArgument($arguments[0])
          ->appendArgument($url_rewritten);
      }
    }
  }

}
