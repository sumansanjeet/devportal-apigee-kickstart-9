<?php

namespace Drupal\Tests\entity_legal\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\entity_legal\Traits\EntityLegalTestTrait;

/**
 * Common Simpletest class for all legal tests.
 */
abstract class EntityLegalTestBase extends BrowserTestBase {

  use EntityLegalTestTrait;

  /**
   * The administrative user to use for tests.
   *
   * @var \Drupal\user\Entity\User
   */
  protected $adminUser;

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['block', 'entity_legal', 'field_ui', 'token'];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->adminUser = $this->drupalCreateUser([
      'administer entity legal',
      'administer permissions',
      'administer user form display',
      'administer users',
    ]);

    // Ensure relevant blocks present if profile isn't 'standard'.
    if ($this->profile !== 'standard') {
      $this->drupalPlaceBlock('local_actions_block');
      $this->drupalPlaceBlock('page_title_block');
    }
  }

  /**
   * {@inheritdoc}
   *
   * Ensures generated names are lower case.
   */
  protected function randomMachineName($length = 8) {
    return strtolower(parent::randomMachineName($length));
  }

}
