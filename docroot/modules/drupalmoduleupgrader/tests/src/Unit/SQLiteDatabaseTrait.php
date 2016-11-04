<?php

namespace Drupal\Tests\drupalmoduleupgrader\Unit;

use Drupal\Core\Database\Driver\sqlite\Connection;

/**
 * A trait for tests that need a database.
 */
trait SQLiteDatabaseTrait {

  /**
   * @var \Drupal\Core\Database\Connection
   */
  protected $db;

  protected function initDB() {
    if (empty($this->db)) {
      // In-memory databases will cease to exist as soon as the connection
      // is closed, which is...convenient as hell!
      $db = new \PDO('sqlite::memory:');
      // Throw exceptions when things go awry.
      $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
      $this->db = new Connection($db, []);
    }
  }

}
