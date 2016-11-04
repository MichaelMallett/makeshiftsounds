<?php

namespace Drupal\drupalmoduleupgrader\Plugin\DMU\Converter\Functions;

use Drupal\drupalmoduleupgrader\TargetInterface;
use Pharborist\Functions\FunctionCallNode;
use Pharborist\Types\StringNode;

/**
 * Parent class of the VariableGet, VariableSet, and VariableDel plugins.
 */
abstract class VariableAPI extends FunctionCallModifier {

  /**
   * Helper for subclasses' rewrite() methods. This checks if the call can
   * be rewritten at all and leaves a FIXME if it can't. If the variable's
   * key is not a string starting with MODULE_, the call will not be
   * considered rewritable.
   *
   * @return boolean
   */
  protected function tryRewrite(FunctionCallNode $call, TargetInterface $target) {
    $statement = $call->getStatement();
    $arguments = $call->getArguments();

    if ($arguments[0] instanceof StringNode) {
      $key = $arguments[0]->toValue();

      if (strPos($key, $target->id() . '_') === 0) {
        return TRUE;
      }
      else {
        $comment = <<<END
This looks like another module's variable. You'll need to rewrite this call
to ensure that it uses the correct configuration object.
END;
        $this->buildFixMe($comment)->prependTo($statement);
        return FALSE;
      }
    }
    else {
      $comment = <<<END
The correct configuration object could not be determined. You'll need to
rewrite this call manually.
END;
      $this->buildFixMe($comment)->prependTo($statement);
      return FALSE;
    }
  }

}
