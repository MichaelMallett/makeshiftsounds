<?php

namespace Drupal\drupalmoduleupgrader;

use Drupal\Component\Serialization\Yaml;
use Drupal\drupalmoduleupgrader\Utility\Filter\ContainsLogicFilter;
use Drupal\drupalmoduleupgrader\Utility\Filter\FunctionCallArgumentFilter;
use Pharborist\DocCommentNode;
use Pharborist\Filter;
use Pharborist\Functions\FunctionCallNode;
use Pharborist\Functions\FunctionDeclarationNode;
use Pharborist\Functions\ParameterNode;
use Pharborist\LineCommentBlockNode;
use Pharborist\Objects\ClassNode;
use Pharborist\Parser;
use Pharborist\Variables\VariableNode;
use Pharborist\WhitespaceNode;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Base class for converters.
 */
abstract class ConverterBase extends PluginBase implements ConverterInterface {

  // Used by buildFixMe() to determine the comment style of the generated
  // FIXME notice.
  const LINE_COMMENT = '//';
  const DOC_COMMENT = '/**/';

  /**
   * {@inheritdoc}
   */
  public function isExecutable(TargetInterface $target) {
    // If the plugin applies to particular hook(s), only return TRUE if the
    // target module implements any of the hooks. Otherwise, return TRUE
    // unconditionally.
    if (isset($this->pluginDefinition['hook'])) {
      return (boolean) array_filter((array) $this->pluginDefinition['hook'], [ $target->getIndexer('function'), 'has' ]);
    }
    else {
      return TRUE;
    }
  }

  /**
   * Executes the target module's implementation of the specified hook, and
   * returns the result.
   *
   * @return mixed
   *
   * @throws \LogicException if the target module doesn't implement the
   * specified hook, or if the implementation contains logic.
   *
   * @deprecated
   */
  protected function executeHook(TargetInterface $target, $hook) {
    $indexer = $target->getIndexer('function');

    if ($indexer->has($hook)) {
      // Configure the ContainsLogicFilter so that certain "safe" functions
      // will pass it.
      $has_logic = new ContainsLogicFilter();
      $has_logic->whitelist('t');
      $has_logic->whitelist('drupal_get_path');

      $function = $indexer->get($hook);
      if ($function->is($has_logic)) {
        throw new \LogicException('{target}_{hook} cannot be executed because it contains logic.');
      }
      else {
        $function_name = $function->getName()->getText();
        if (! function_exists($function_name)) {
          eval($function->getText());
        }
        return call_user_func($function_name);
      }
    }
    else {
      throw new \LogicException('{target} does not implement hook_{hook}.');
    }
  }

  /**
   * Creates an empty implementation of a hook.
   *
   * @param TargetInterface $target
   *  The target module.
   * @param string $hook
   *  The hook to implement, without the hook_ prefix.
   *
   * @return \Pharborist\Functions\FunctionDeclarationNode
   *  The hook implementation, appended to the main module file.
   */
  protected function implement(TargetInterface $target, $hook) {
    $function = FunctionDeclarationNode::create($target->id() . '_' . $hook);
    $function->setDocComment(DocCommentNode::create('Implements hook_' . $hook . '().'));

    $module_file = $target->getPath('.module');
    $target->open($module_file)->append($function);

    WhitespaceNode::create("\n")->insertBefore($function);
    WhitespaceNode::create("\n")->insertAfter($function);

    return $function;
  }

  /**
   * Writes a file to the target module's directory.
   *
   * @param TargetInterface $target
   *  The target module.
   * @param string $path
   *  The path of the file to write, relative to the module root.
   * @param string $data
   *  The file contents.
   *
   * @return string
   *  The path of the file, including the target's base path.
   */
  public function write(TargetInterface $target, $path, $data) {
    static $fs;
    if (empty($fs)) {
      $fs = new Filesystem();
    }

    $destination_path = $target->getPath($path);
    $fs->dumpFile($destination_path, (string) $data);

    return $destination_path;
  }

  /**
   * Writes a class to the target module's PSR-4 root.
   *
   * @param TargetInterface $target
   *  The target module.
   * @param ClassNode $class
   *  The class to write. The path will be determined from the class'
   *  fully qualified name.
   *
   * @return string
   *  The generated path to the class.
   */
  public function writeClass(TargetInterface $target, ClassNode $class) {
    $class_path = ltrim($class->getName()->getAbsolutePath(), '\\');
    $path = str_replace([ 'Drupal\\' . $target->id(), '\\', ], [ 'src', '/' ], $class_path) . '.php';

    return $this->write($target, $path, $class->parents()->get(0));
  }

  /**
   * Writes out arbitrary data in YAML format.
   *
   * @param TargetInterface $target
   *  The target module.
   * @param string $group
   *  The name of the YAML file. It will be prefixed with the module's machine
   *  name and suffixed with .yml. For example, a group value of 'routing'
   *  will write MODULE.routing.yml.
   * @param array $data
   *  The data to write.
   *
   * @todo This should be writeYAML, not writeInfo.
   */
  protected function writeInfo(TargetInterface $target, $group, array $data) {
    $destination = $target->getPath('.' . $group . '.yml');
    file_put_contents($destination, Yaml::encode($data));
  }

  /**
   * Writes a service definition to the target module's services.yml file.
   *
   * @param TargetInterface $target
   *  The target module.
   * @param string $service_id
   *  The service ID. If an existing one with the same ID already exists,
   *  it will be overwritten.
   * @param array $service_definition
   */
  protected function writeService(TargetInterface $target, $service_id, array $service_definition) {
    $services = $target->getServices();
    $services->set($service_id, $service_definition);
    $this->writeInfo($target, 'services', [ 'services' => $services->toArray() ]);
  }

  /**
   * Parses a generated class into a syntax tree.
   *
   * @param string|array $class
   *  The class to parse, either as a string of PHP code or a renderable array.
   *
   * @return \Pharborist\Objects\ClassNode
   */
  protected function parse($class) {
    if (is_array($class)) {
      $class = \Drupal::service('renderer')->renderPlain($class);
    }
    return Parser::parseSnippet($class)->find(Filter::isInstanceOf('Pharborist\Objects\ClassNode'))[0];
  }

  /**
   * Builds a FIXME notice using either the text in the plugin definition,
   * or passed-in text.
   *
   * @param string|NULL $text
   *  The FIXME notice's text, with variable placeholders and no translation.
   * @param array $variables
   *  Optional variables to use in translation. If empty, the FIXME will not
   *  be translated.
   * @param string|NULL $style
   *  The comment style. Returns a LineCommentBlockNode if this is set to
   *  self::LINE_COMMENT, a DocCommentNode if self::DOC_COMMENT, or the FIXME
   *  as a string if set to anything else.
   *
   * @return mixed
   */
  protected function buildFixMe($text = NULL, array $variables = [], $style = self::LINE_COMMENT) {
    $fixMe = "@FIXME\n" . ($text ?: $this->pluginDefinition['fixme']);

    if (isset($this->pluginDefinition['documentation'])) {
      $fixMe .= "\n";
      foreach ($this->pluginDefinition['documentation'] as $doc) {
        $fixMe .= "\n@see ";
        $fixMe .= (isset($doc['url']) ? $doc['url'] : (string) $doc);
      }
    }

    if ($variables) {
      $fixMe = $this->t($fixMe, $variables);
    }

    switch ($style) {
      case self::LINE_COMMENT:
        return LineCommentBlockNode::create($fixMe);

      case self::DOC_COMMENT:
        return DocCommentNode::create($fixMe);

      default:
        return $fixMe;
    }
  }

  /**
   * Parametrically rewrites a function.
   *
   * @param \Drupal\drupalmoduleupgrader\RewriterInterface $rewriter
   *  A fully configured parametric rewriter.
   * @param \Pharborist\Functions\ParameterNode $parameter
   *  The parameter upon which to base the rewrite.
   * @param TargetInterface $target
   *  The target module.
   * @param boolean $recursive
   *  If TRUE, rewriting will recurse into called functions which are passed
   *  the rewritten parameter as an argument.
   */
  protected function rewriteFunction(RewriterInterface $rewriter, ParameterNode $parameter, TargetInterface $target, $recursive = TRUE) {
    $rewriter->rewrite($parameter);
    $target->save($parameter);

    // Find function calls within the rewritten function which are called
    // with the rewritten parameter.
    $indexer = $target->getIndexer('function');
    $next = $parameter
      ->getFunction()
      ->find(new FunctionCallArgumentFilter($parameter->getName()))
      ->filter(function(FunctionCallNode $call) use ($indexer) {
        return $indexer->has($call->getName()->getText());
      });

    /** @var \Pharborist\Functions\FunctionCallNode $call */
    foreach ($next as $call) {
      /** @var \Pharborist\Functions\FunctionDeclarationNode $function */
      $function = $indexer->get($call->getName()->getText());

      foreach ($call->getArguments() as $index => $argument) {
        if ($argument instanceof VariableNode && $argument->getName() == $parameter->getName()) {
          $this->rewriteFunction($rewriter, $function->getParameterAtIndex($index), $target, $recursive);
          break;
        }
      }
    }
  }

}
