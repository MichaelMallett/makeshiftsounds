<?php

use Drupal\drupalmoduleupgrader\Report;
use Drupal\drupalmoduleupgrader\Target;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Implements hook_drush_command().
 */
function drupalmoduleupgrader_drush_command() {
  $items = [];

  $items['dmu-list'] = [
    'description' => 'Lists available plugins.',
    'arguments' => [
      'plugin_type' => 'The plugin type to query. Can be one of: indexer, analyzer, converter, cleaner.',
    ],
    'required-arguments' => TRUE,
    'bootstrap' => DRUSH_BOOTSTRAP_DRUPAL_ROOT,
  ];

  $items['dmu-index'] = [
    'description' => 'Indexes a target module.',
    'arguments' => [
      'module' => 'The name of a Drupal 7 module.',
    ],
    'required-arguments' => TRUE,
    'examples' => [
      'drush dmu-index pants' => 'Indexes the pants module.',
    ],
    'bootstrap' => DRUSH_BOOTSTRAP_DRUPAL_ROOT,
  ];

  $items['dmu-analyze'] = [
    'description' => "Analyzes a Drupal 7 module and reports the changes needed to port it to Drupal 8.",
    'arguments' => [
      'module' => 'The machine name of a Drupal 7 module.',
    ],
    'required-arguments' => TRUE,
    'options' => [
      'only' => [
        'description' => 'A comma-separated list of analyzers to run, excluding all others.',
        'example-value' => 'HookMenu,VariableAPI,BlockInfo',
      ],
      'skip' => [
        'description' => 'A comma-separated list of analyzers to skip.',
        'example-value' => 'HookInit,HookExit',
      ],
      'path' => [
        'description' => 'Optional path to the target module.',
        'example-value' => 'drupal/modules/foobaz',
      ],
      'output' => [
        'description' => 'Optional path to output the report.',
        'example-value' => 'path/to/module/analyze.html',
      ],
    ],
    'examples' => [
      'drush dmu-analyze pants' => 'Analyze what needs to be changed in order to port the pants module.',
    ],
    'bootstrap' => DRUSH_BOOTSTRAP_DRUPAL_ROOT,
  ];

  $items['dmu-upgrade'] = [
    'description' => "Upgrades a Drupal 7 module to Drupal 8.",
    'arguments' => [
      'module' => 'The machine name of a Drupal 7 module.',
    ],
    'required-arguments' => TRUE,
    'options' => [
      'backup' => [
        'description' => 'If set, creates a backup copy of the module before conversion.',
      ],
      'only' => [
        'description' => 'A comma-separated list of converters to run, excluding all others.',
        'example-value' => 'HookMenu,VariableAPI,BlockInfo',
      ],
      'skip' => [
        'description' => 'A comma-separated list of converters to skip.',
        'example-value' => 'HookInit,HookExit',
      ],
      'path' => [
        'description' => 'Optional path to the target module. Will be determined automatically if omitted.',
        'example-value' => 'drupal/modules/foobaz',
      ],
    ],
    'examples' => [
      'drush dmu-upgrade pants' => 'Upgrade whatever can be automatically upgraded in the pants module.',
    ],
    'bootstrap' => DRUSH_BOOTSTRAP_DRUPAL_ROOT,
  ];

  return $items;
}

/**
 * Returns a list of plugin IDs of a given type, filtered by the --only
 * and --skip options.
 *
 * @param string $plugin_type
 *  The plugin type. Can be one of indexer, analyzer, converter, cleaner.
 *
 * @return string[]
 */
function _dmu_plugin_list($plugin_type) {
  // Instantiate the plugin manager and get all available plugin IDs.
  $manager = \Drupal::service('plugin.manager.drupalmoduleupgrader.' . $plugin_type);
  $plugin_IDs = array_keys($manager->getDefinitions());

  // Filter by the --only and --skip options, if set.
  if ($only = drush_get_option('only', FALSE)) {
    $plugin_IDs = array_intersect($plugin_IDs, explode(',', $only));
  }
  elseif ($skip = drush_get_option('skip', FALSE)) {
    $plugin_IDs = array_diff($plugin_IDs, explode(',', $skip));
  }

  return $plugin_IDs;
}

/**
 * Checks for autoload.php, and includes it if it exists or sets an error
 * if it doesn't.
 */
function _dmu_ensure_autoload() {
  $locations = [
    __DIR__ . '/vendor/autoload.php',
    './vendor/autoload.php',
  ];
  foreach ($locations as $location) {
    if (file_exists($location)) {
      require_once $location;
      return;
    }
  }

  drush_set_error('no_autoload', 'autoload.php not found! Did you remember to run composer install from the drupalmoduleupgrader directory?');
}

/**
 * Determines the path to a module.
 *
 * @param string $module
 *  The module's machine name.
 *
 * @return string|NULL
 */
function _dmu_get_directory($module) {
  if ($path = drush_get_option('path', NULL)) {
    return $path;
  }
  else {
    $search_directories = [
      DRUPAL_ROOT . '/modules/' . $module,
      __DIR__ . '/'. $module,
    ];

    $directories = array_filter($search_directories, 'is_dir');
    if ($directories) {
      return reset($directories);
    }
  }
}

/**
 * Checks possible locations of a target module, and ensures that at least
 * one exists. If none do, sets an error.
 *
 * @param string $module
 *  The target module's machine name.
 */
function _dmu_ensure_directory($module) {
  $directory = _dmu_get_directory($module);

  if (empty($directory)) {
    if ($path = drush_get_option('path', NULL)) {
      drush_set_error('invalid_dir', 'Invalid path: ' . $path);
    }
    else {
      drush_set_error('no_directory', "Cannot determine base directory of module $module. Try passing --path=modules/foobar");
    }
  }
}

/**
 * Validates any of the DMU commands.
 */
function _dmu_validate_command($module) {
  _dmu_ensure_autoload();
  _dmu_ensure_directory($module);
}

function _dmu_build_target($module) {
  $target = new Target(_dmu_get_directory($module), \Drupal::getContainer());

  drush_print(\Drupal::translation()->translate('Indexing...'), 0, NULL, FALSE);
  $target->buildIndex();
  drush_print(\Drupal::translation()->translate('done.'));

  return $target;
}

/**
 * ----- dmu-list -----
 */

/**
 * Lists all the available module-wide plugins.
 */
function drush_drupalmoduleupgrader_dmu_list($plugin_type) {
  $manager = \Drupal::service('plugin.manager.drupalmoduleupgrader.' . $plugin_type);

  $list = [];
  foreach ($manager->getDefinitions() as $id => $definition) {
    $list[$id] = $definition['description'];
  }
  drush_print_table(drush_key_value_to_array_table($list));
}

/**
 * ----- dmu-index -----
 */

function drush_drupalmoduleupgrader_dmu_index_validate($module) {
  _dmu_validate_command($module);
}

/**
 * ----- dmu-analyze -----
 */

function drush_drupalmoduleupgrader_dmu_analyze_validate($module) {
  _dmu_validate_command($module);
}

/**
 * Analyzes what needs changing in a module to port it to Drupal 8.
 *
 * @param string $module
 *  The machine name of the module to analyze.
 */
function drush_drupalmoduleupgrader_dmu_analyze($module) {
  $target = _dmu_build_target($module);

  $total_issues = 0;
  $report = new Report();

  $analyzers = \Drupal::service('plugin.manager.drupalmoduleupgrader.analyzer');
  foreach (_dmu_plugin_list('analyzer') as $id) {
    drush_log(\Drupal::translation()->translate('Executing plugin: @plugin_id', ['@plugin_id' => $id]), 'notice');
    $issues = $analyzers->createInstance($id)->analyze($target);

    if ($issues) {
      if (! is_array($issues)) {
        $issues = array($issues);
      }
      foreach ($issues as $issue) {
        $report->addIssue($issue);
      }
      $total_issues += sizeof($issues);
    }
  }

  if ($total_issues) {
    $render = [
      '#theme' => 'dmu_report',
      '#report' => $report,
      '#group_by' => 'category',
    ];

    $destination = drush_get_option('output', $target->getPath('upgrade-info.html'));
    $output = \Drupal::service('renderer')->renderRoot($render);
    file_put_contents($destination, $output);
    drush_log(\Drupal::translation()->translate('Generated a report at @path', ['@path' => $destination]), 'success');
  }
  else {
    drush_log(\Drupal::translation()->translate('Wow...no issues found! You get a cookie :)', 'success'));
  }
}

/**
 * ----- dmu-upgrade -----
 */

function drush_drupalmoduleupgrader_dmu_upgrade_validate($module) {
  _dmu_validate_command($module);
}

/**
 * Tries to automatically convert a Drupal 7 module to Drupal 8.
 *
 * @param string $module
 *  The module to upgrade.
 */
function drush_drupalmoduleupgrader_dmu_upgrade($module) {
  $target = _dmu_build_target($module);

  if (drush_get_option('backup', FALSE)) {
    $fs = new Filesystem();
    $backup_at = $target->getBasePath() . '.bak';
    $fs->mirror($target->getBasePath(), $backup_at);
    drush_log(\Drupal::translation()->translate('Created backup at @path', [ '@path' => $backup_at ]), 'success');
  }

  $converters = \Drupal::service('plugin.manager.drupalmoduleupgrader.converter');
  foreach (_dmu_plugin_list('converter') as $id) {
    /** @var \Drupal\drupalmoduleupgrader\ConverterInterface $converter */
    $converter = $converters->createInstance($id);

    if ($converter->isExecutable($target)) {
      drush_log(\Drupal::translation()->translate('Executing plugin: @plugin_id', ['@plugin_id' => $id]), 'notice');
      try {
        $converter->convert($target);
      }
      catch (Exception $e) {
        drush_log($e->getMessage(), 'error');
        // Being a notice, the stack trace will only appear in verbose mode.
        drush_log($e->getTraceAsString(), 'notice');
      }
    }
  }
}
