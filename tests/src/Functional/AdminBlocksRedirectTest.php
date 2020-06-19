<?php

namespace Drupal\Tests\dropsolid_rocketship_profile\Functional;

/**
 * Test the redirect of the Admin block overview page.
 *
 * @group dropsolid_blocks
 * @group dropsolid
 */
class AdminBlocksRedirectTest extends DropsolidBrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'dropsolid_blocks',
  ];

  /**
   * Tests for a the redirect of the blocks overview page.
   */
  public function testBlocksRedirect() {
    $this->drupalLoginAsWebAdmin();
    $this->drupalGet('/admin/structure/block/block-content');
    $this->assertSession()->addressEquals('/admin/content/blocks');
  }

}
