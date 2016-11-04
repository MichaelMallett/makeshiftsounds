<?php

/**
 * @file
 * Contains \Drupal\drupalmoduleupgrader\Plugin\DMU\Fixer\Notify.
 */

namespace Drupal\drupalmoduleupgrader\Plugin\DMU\Fixer;

use Drupal\drupalmoduleupgrader\FixerBase;
use Pharborist\DocCommentNode;
use Pharborist\LineCommentBlockNode;
use Pharborist\NodeInterface;

/**
 * @Fixer(
 *  id = "notify"
 * )
 */
class Notify extends FixerBase {

  use NodeCollectorTrait;

  /**
   * {@inheritdoc}
   */
  public function execute() {
    foreach ($this->getObjects() as $node) {
      $comment = $this->getComment($node);
      if ($comment) {
        $comment .= "\n\n";
      }
      $this->setComment($node, $comment . $this->configuration['note']);
    }

    $this->target->save();
  }

  protected function getComment(NodeInterface $node) {
    if ($this->supportsDocComments($node)) {
      /** @var \Pharborist\DocCommentTrait $node */
      $comment = $node->getDocComment() ?: DocCommentNode::create('');
      return $comment->getCommentText();
    }
    else {
      return '';
    }
  }

  protected function setComment(NodeInterface $node, $comment_text) {
    if ($this->supportsDocComments($node)) {
      /** @var \Pharborist\DocCommentTrait $node */
      $node->setDocComment(DocCommentNode::create($comment_text));
    }
    else {
      LineCommentBlockNode::create($comment_text)->insertBefore($node->getStatement());
    }
  }

  /**
   * Returns if a node supports doc comments by importing DocCommentTrait
   * anywhere in its lineage.
   *
   * @param \Pharborist\NodeInterface $node
   *
   * @return boolean
   */
  protected function supportsDocComments(NodeInterface $node) {
    return $this->usesTrait('Pharborist\DocCommentTrait', $node);
  }

}
