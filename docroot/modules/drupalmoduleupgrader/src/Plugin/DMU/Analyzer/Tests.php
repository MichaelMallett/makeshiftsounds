<?php

namespace Drupal\drupalmoduleupgrader\Plugin\DMU\Analyzer;

use Drupal\drupalmoduleupgrader\AnalyzerBase;
use Drupal\drupalmoduleupgrader\TargetInterface;

/**
 * @Analyzer(
 *  id = "tests",
 *  description = @Translation("Checks for test classes that need to be rejiggered for Drupal 8."),
 *  documentation = {
 *    {
 *      "url" = "https://www.drupal.org/node/1543796",
 *      "title" = @Translation("Namespacing of automated tests has changed")
 *    },
 *    {
 *      "url" = "https://www.drupal.org/node/2301125",
 *      "title" = @Translation("<code>getInfo()</code> in test classes replaced by doc comments")
 *    },
 *    {
 *      "url" = "https://www.drupal.org/node/1710766",
 *      "title" = @Translation("Test classes should define a <code>$modules</code> property declaring dependencies")
 *    },
 *    {
 *      "url" = "https://www.drupal.org/node/1911318",
 *      "title" = @Translation("SimpleTest tests now use empty &quot;testing&quot; profile by default")
 *    },
 *    {
 *      "url" = "https://www.drupal.org/node/1829160",
 *      "title" = @Translation("New <code>KernelTestBase</code> class for API-level integration tests")
 *    },
 *    {
 *      "url" = "https://www.drupal.org/node/2012184",
 *      "title" = @Translation("PHPUnit added to Drupal core")
 *    }
 *  },
 *  tags = {
 *    "category" = { "misc", "system" }
 *  },
 *  message = @Translation("Automated web tests must be in a PSR-4 namespace, and unit tests must be converted to PHPUnit.")
 * )
 */
class Tests extends AnalyzerBase {

  /**
   * {@inheritdoc}
   */
  public function analyze(TargetInterface $target) {
    $issues = [];
    $total = 0;
    $total += $target->getIndexer('class')->getQuery()->condition('parent', 'DrupalWebTestCase')->countQuery()->execute();
    $total += $target->getIndexer('class')->getQuery()->condition('parent', 'DrupalUnitTestCase')->countQuery()->execute();
    $total += $target->getIndexer('class')->getQuery()->condition('parent', 'DrupalTestBase')->countQuery()->execute();

    if ($total) {
      $issues[] = $this->buildIssue($target);
    }

    return $issues;
  }

}
