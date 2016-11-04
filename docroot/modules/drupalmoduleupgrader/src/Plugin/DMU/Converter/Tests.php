<?php

namespace Drupal\drupalmoduleupgrader\Plugin\DMU\Converter;

use Drupal\drupalmoduleupgrader\ConverterBase;
use Drupal\drupalmoduleupgrader\TargetInterface;
use Drupal\drupalmoduleupgrader\Utility\Filter\ContainsLogicFilter;
use Pharborist\DocCommentNode;
use Pharborist\Filter;
use Pharborist\Objects\ClassMemberNode;
use Pharborist\Objects\ClassMethodCallNode;
use Pharborist\Objects\ClassNode;
use Pharborist\Parser;
use Pharborist\RootNode;
use Pharborist\Types\StringNode;
use Pharborist\WhitespaceNode;

/**
 * @Converter(
 *  id = "tests",
 *  description = @Translation("Modifies test classes.")
 * )
 */
class Tests extends ConverterBase {

  private $target;

  /**
   * {@inheritdoc}
   */
  public function isExecutable(TargetInterface $target) {
    foreach (['DrupalTestCase', 'DrupalWebTestCase'] as $parent_class) {
      if ($target->getIndexer('class')->getQuery()->condition('parent', $parent_class)->countQuery()->execute()) {
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function convert(TargetInterface $target) {
    $this->target = $target;

    $mapping = [
      'DrupalWebTestCase' => 'convertWeb',
      'AJAXTestCase' => 'convertAjax',
    ];
    foreach ($mapping as $parent_class => $convert_method) {
      $test_files = $target->getIndexer('class')->getQuery(['file'])->condition('parent', $parent_class)->execute()->fetchCol();
      foreach ($test_files as $test_file) {
        /** @var \Pharborist\Objects\Classnode[] $tests */
        $tests = $target->open($test_file)->find(Filter::isInstanceOf('\Pharborist\Objects\SingleInheritanceNode'))->toArray();
        foreach ($tests as $test) {
          if ((string) $test->getExtends() === $parent_class) {
            $this->$convert_method($test);
          }
        }
      }
    }
  }

  /**
   * Converts a single web test.
   *
   * @param \Pharborist\Objects\ClassNode $test
   */
  public function convertWeb(ClassNode $test) {
    $test->setExtends('\Drupal\simpletest\WebTestBase');
    $this->convertInfo($test);
    $this->setModules($test);
    $this->setProfile($test);
    $this->move($test);
  }

  /**
   * Converts the test's getInfo() method to an annotation.
   *
   * @param \Pharborist\Objects\ClassNode $test
   */
  private function convertInfo(ClassNode $test) {
    $info = $this->extractInfo($test);

    if ($info) {
      $comment = '';
      $comment .= $info['description'] . "\n\n";
      $comment .= '@group ' . $this->target->id();

      if (isset($info['dependencies'])) {
        $comment .= "\n";
        foreach ($info['dependencies'] as $module) {
          $comment .= '@requires module . ' . $module . "\n";
        }
      }

      $test->setDocComment(DocCommentNode::create($comment));
    }
    else {
      $this->log->error('Could not get info for test {class}.', [
        'class' => $test->getName(),
      ]);
    }
  }

  /**
   * Extracts the return value of the test's getInfo() method, if there's no
   * logic in the method.
   *
   * @param \Pharborist\Objects\ClassNode $test
   *
   * @return array|NULL
   */
  private function extractInfo(ClassNode $test) {
    if ($test->hasMethod('getInfo')) {
      $info = $test->getMethod('getInfo');

      if (! $info->is(new ContainsLogicFilter())) {
        return eval($info->getBody()->getText());
      }
    }
  }

  /**
   * Sets the test's $modules property.
   *
   * @param \Pharborist\Objects\ClassNode $test
   */
  private function setModules(ClassNode $test) {
    $modules = $this->extractModules($test);
    if ($modules) {
      // @todo Use ClassNode::createProperty() when #124 lands in Pharborist
      $property = Parser::parseSnippet('class Foo { public static $modules = ["' . implode('", "', $modules) . '"]; }')
        ->getBody()
        ->firstChild()
        ->remove();
      $test->appendProperty($property);
    }
  }

  /**
   * Extracts every module required by a web test by scanning its calls
   * to parent::setUp().
   *
   * @param \Pharborist\Objects\ClassNode $test
   *
   * @return string[]
   *  Array of modules set up by this module.
   */
  private function extractModules(ClassNode $test) {
    $modules = [];

    $test
      ->find(Filter::isClassMethodCall('parent', 'setUp'))
      ->filter(function(ClassMethodCallNode $call) {
        return (sizeof($call->getArguments()) > 0);
      })
      ->each(function(ClassMethodCallNode $call) use (&$modules) {
        foreach ($call->getArguments() as $argument) {
          if ($argument instanceof StringNode) {
            $modules[] = $argument->toValue();
          }
        }

        $call->clearArguments();
      });

    return array_unique($modules);
  }

  /**
   * Sets the test's $profile property.
   *
   * @param \Pharborist\Objects\ClassNode $test
   */
  private function setProfile(ClassNode $test) {
    if (! $test->hasProperty('profile')) {
      $test->appendProperty(ClassMemberNode::create('profile', StringNode::create("'standard'"), 'protected'));
    }
  }

  public function move(ClassNode $test) {
    $ns = 'Drupal\\' . $this->target->id() . '\\Tests';
    RootNode::create($ns)->getNamespace($ns)->append($test->remove());
    WhitespaceNode::create("\n\n")->insertBefore($test);

    $this->writeClass($this->target, $test);
  }

  /**
   * Converts a single Ajax test.
   *
   * @param \Pharborist\Objects\ClassNode $test
   */
  public function convertAjax(ClassNode $test) {
    $test->setExtends('\Drupal\system\Tests\Ajax\AjaxTestBase');
    $this->setModules($test);
    $this->move($test);
  }

}
