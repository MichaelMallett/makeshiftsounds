<?php

namespace Drupal\drupalmoduleupgrader\Plugin\DMU\Converter;

use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\drupalmoduleupgrader\ConverterBase;
use Drupal\drupalmoduleupgrader\RewriterInterface;
use Drupal\drupalmoduleupgrader\TargetInterface;
use Pharborist\DocCommentNode;
use Pharborist\Types\NullNode;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @Converter(
 *  id = "user_hooks",
 *  description = @Translation("Alters implementations of hook_user_insert(), hook_user_presave(), and hook_user_update()."),
 *  hook = {
 *    "hook_user_insert",
 *    "hook_user_presave",
 *    "hook_user_update"
 *  }
 * )
 */
class UserHooks extends ConverterBase {

  /**
   * @var \Drupal\drupalmoduleupgrader\RewriterInterface
   */
  protected $rewriter;

  public function __construct(array $configuration, $plugin_id, $plugin_definition, TranslationInterface $translator, LoggerInterface $log, RewriterInterface $rewriter) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $translator, $log);
    $this->rewriter = $rewriter;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('string_translation'),
      $container->get('logger.factory')->get('drupalmoduleupgrader'),
      $container->get('plugin.manager.drupalmoduleupgrader.rewriter')->createInstance('_rewriter:user')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function convert(TargetInterface $target) {
    $indexer = $target->getIndexer('function');

    $hooks = array_filter($this->pluginDefinition['hook'], [$indexer, 'has']);
    foreach ($hooks as $hook) {
      /** @var \Pharborist\Functions\FunctionDeclarationNode $function */
      $function = $indexer->get($hook);
      // The $edit parameter is defunct in Drupal 8, but we'll leave it in
      // there as an empty array to prevent errors, and move it to the back
      // of the line.
      /** @var \Pharborist\Functions\ParameterNode $edit */
      $edit = $function->getParameterList()->shift()->setReference(FALSE)->setValue(NullNode::create());
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
The $edit and $category parameters are gone in Drupal 8. They have been left
here in order to prevent 'undefined variable' errors, but they will never
actually be passed to this hook. You'll need to modify this function and
remove every reference to them.
END;
      $function->setDocComment(DocCommentNode::create($comment_text));

      $this->rewriteFunction($this->rewriter, $function->getParameterAtIndex(0), $target);
      $target->save($function);
    }
  }

}
