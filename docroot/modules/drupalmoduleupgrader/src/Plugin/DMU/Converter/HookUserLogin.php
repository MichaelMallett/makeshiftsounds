<?php

namespace Drupal\drupalmoduleupgrader\Plugin\DMU\Converter;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\drupalmoduleupgrader\ConverterBase;
use Drupal\drupalmoduleupgrader\TargetInterface;
use Pharborist\DocCommentNode;
use Pharborist\Types\ArrayNode;
use Psr\Log\LoggerInterface;

/**
 * @Converter(
 *  id = "hook_user_login",
 *  description = @Translation("Alters signatures of hook_user_login() implementations."),
 *  hook = "hook_user_login",
 *  dependencies = { "plugin.manager.drupalmoduleupgrader.rewriter" }
 * )
 */
class HookUserLogin extends ConverterBase {

  /**
   * @var \Drupal\Component\Plugin\PluginManagerInterface
   */
  protected $rewriters;

  public function __construct(array $configuration, $plugin_id, $plugin_definition, TranslationInterface $translator, LoggerInterface $log, PluginManagerInterface $rewriters) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $translator, $log);
    $this->rewriters = $rewriters;
  }

  /**
   * {@inheritdoc}
   */
  public function convert(TargetInterface $target) {
    /** @var \Pharborist\Functions\FunctionDeclarationNode $function */
    $function = $target->getIndexer('function')->get('hook_user_login');
    // The $edit parameter is defunct in Drupal 8, but we'll leave it in
    // there as an empty array to prevent errors, and move it to the back
    // of the line.
    /** @var \Pharborist\Functions\ParameterNode $edit */
    $edit = $function->getParameterList()->shift()->setReference(FALSE)->setValue(ArrayNode::create([]));
    $function->appendParameter($edit);

    // Slap a FIXME on the hook implementation, informing the developer that
    // $edit and $category are dead.
    $comment = $function->getDocComment();
    $comment_text = $comment ? $comment->getCommentText() : '';
    if ($comment_text) {
      $comment_text .= "\n\n";
    }
    $comment_text .= <<<'END'
@FIXME
The $edit parameter is gone in Drupal 8. It has been left here in order to
prevent 'undefined variable' errors, but it will never actually be passed to
this hook. You'll need to modify this function and remove every reference to it.
END;
    $function->setDocComment(DocCommentNode::create($comment_text));

    $rewriter = $this->rewriters->createInstance('_rewriter:user');
    $this->rewriteFunction($rewriter, $function->getParameterAtIndex(0), $target);
    $target->save($function);
  }

}
