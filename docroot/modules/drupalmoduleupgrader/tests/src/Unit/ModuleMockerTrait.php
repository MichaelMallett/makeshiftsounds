<?php

namespace Drupal\Tests\drupalmoduleupgrader\Unit;

use org\bovigo\vfs\vfsStream;

/**
 * A trait for tests that need a mock module to work on.
 */
trait ModuleMockerTrait {

  protected function mockModule($id) {
    // Create a virtual (in-memory) directory for the module, and touch
    // a few empty files. Tests should fill in the code of the module
    // according to their own needs.
    $dir = vfsStream::setup($id);
    vfsStream::newFile($id . '.module')->at($dir);
    vfsStream::newFile($id . '.info')->at($dir);
    vfsStream::newFile($id . '.install')->at($dir);
    vfsStream::newFile($id . '.test')->at($dir);
    vfsStream::newDirectory('src')->at($dir);

    $config_dir = vfsStream::newDirectory('config')->at($dir);
    vfsStream::newDirectory('install')->at($config_dir);
    vfsStream::newDirectory('optional')->at($config_dir);
    vfsStream::newDirectory('schema')->at($config_dir);

    return $dir;
  }

}
