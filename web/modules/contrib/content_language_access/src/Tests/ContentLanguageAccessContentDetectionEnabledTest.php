<?php

namespace Drupal\content_language_access\Tests;

use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Test the features with content language detection enabled.
 *
 * @group content_language_access
 */
class ContentLanguageAccessContentDetectionEnabledTest extends ContentLanguageAccessTestBase {

  use StringTranslationTrait;

  /**
   * Modules to install.
   *
   * @var array
   */
  protected static $modules = ['content_language_access', 'content_translation'];

  /**
   * Implements setUp().
   */
  public function setUp(): void {
    parent::setUp();

    // Enable content language detection.
    $edit = [
      // Disable interface language detection.
      'language_interface[enabled][language-url]' => FALSE,
      // Content language detection: only using URL.
      'language_content[configurable]' => TRUE,
      'language_content[enabled][language-url]' => TRUE,
      'language_content[enabled][language-interface]' => FALSE,
    ];
    $this->drupalGet('admin/config/regional/language/detection');
    $this->submitForm($edit, $this->t('Save settings'));
  }

  /**
   * Tests each content in each language.
   */
  public function testContentLanguageAccess() {
    parent::baseTestContentLanguageAccess();
  }

}
