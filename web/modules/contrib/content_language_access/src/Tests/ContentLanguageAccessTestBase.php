<?php

namespace Drupal\content_language_access\Tests;

use Drupal\Core\Language\Language;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Tests\BrowserTestBase;

/**
 * Test the features of content_language_access module.
 *
 * @group content_language_access
 */
abstract class ContentLanguageAccessTestBase extends BrowserTestBase {

  use StringTranslationTrait;

  /**
   * Drupal installation profile to use.
   *
   * @var string
   */
  protected $profile = 'standard';

  /**
   * Modules to install.
   *
   * @var array
   */
  protected static $modules = ['content_language_access'];

  /**
   * A simple user with 'access content' permission.
   *
   * @var \Drupal\user\Entity\User
   */
  protected $adminUser;

  /**
   * A simple user with 'access content' permission.
   *
   * @var \Drupal\user\Entity\User
   */
  protected $visitor;

  /**
   * Content type created for tests.
   *
   * @var \Drupal\node\Entity\NodeType
   */
  protected $contentType;

  /**
   * Contents created.
   *
   * @var \Drupal\node\NodeInterface[]
   */
  protected $nodes;

  /**
   * Implements setUp().
   */
  public function setUp(): void {
    parent::setUp();

    $this->adminUser = $this->drupalCreateUser([
      'administer languages',
      'administer site configuration',
      'access administration pages',
      'administer content types',
      'administer nodes',
      'administer users',
    ]);
    $this->visitor = $this->drupalCreateUser(['access content']);

    $this->configureLanguages();

    $this->createContentType();
    $this->createContents();
  }

  /**
   * Creates the languages for the test execution.
   */
  protected function configureLanguages() {
    $this->drupalLogin($this->adminUser);

    $this->addLanguage('aaa');
    $this->addLanguage('bbb');

    \Drupal::languageManager()->reset();
  }

  /**
   * Creates a random content type for test execution.
   */
  protected function createContentType(array $values = []) {
    $this->contentType = $this->drupalCreateContentType();
    // Set the content type to use multilingual support.
    $this->drupalGet("admin/structure/types/manage/{$this->contentType->id()}");
    $this->assertSession()->pageTextContains($this->t('Language settings'));
    $edit = [
      'language_configuration[language_alterable]' => TRUE,
    ];
    $this->drupalGet("admin/structure/types/manage/{$this->contentType->id()}");
    $this->submitForm($edit, 'Save content type');
    $this->assertSession()->responseContains($this->t('The content type %type has been updated.', ['%type' => $this->contentType->label()]));
  }

  /**
   * Creates a content for each language for the tests.
   */
  protected function createContents() {
    $this->drupalLogin($this->adminUser);

    $languages = $this->getLanguageList();

    foreach ($languages as $language_key => $language) {
      $settings = [
        'title' => 'Test ' . $language->getName(),
        'langcode' => $language_key,
        'type' => $this->contentType->id(),
      ];

      $this->nodes[$language_key] = $this->drupalCreateNode($settings);
    }
  }

  /**
   * Returns the list of languages available.
   *
   * @param bool $with_neutral_language
   *   Optional, specifies if the function needs to return also the neutral
   *   language.
   *
   * @return \Drupal\Core\Language\LanguageInterface[]
   *   With all the languages available (plus the neutral language)
   */
  protected function getLanguageList($with_neutral_language = TRUE) {
    $languages = \Drupal::languageManager()->getLanguages();

    if ($with_neutral_language) {
      $languages[Language::LANGCODE_NOT_SPECIFIED] = new Language([
        'id' => Language::LANGCODE_NOT_SPECIFIED,
        'name' => 'Language Neutral',
      ]);
    }

    return $languages;
  }

  /**
   * Enables the specified language if it has not been already.
   *
   * @param string $language_code
   *   The language code to enable.
   */
  protected function addLanguage($language_code) {
    // Check to make sure that language has not already been installed.
    $this->drupalGet('admin/config/regional/language');

    if (strpos($this->getTextContent(), 'edit-languages-' . $language_code) === FALSE) {
      // Doesn't have language installed so add it.
      $edit = [
        'predefined_langcode' => 'custom',
        'langcode' => $language_code,
        'label' => $language_code,
        'direction' => LanguageInterface::DIRECTION_LTR,
      ];
      $this->drupalGet('admin/config/regional/language/add');
      $this->submitForm($edit, 'Add custom language');
    }
  }

  /**
   * Tests each content in each language.
   */
  protected function baseTestContentLanguageAccess() {
    $this->drupalLogin($this->visitor);

    $languages = $this->getLanguageList(FALSE);

    foreach ($this->nodes as $node) {
      foreach ($languages as $language) {
        // English is the default language and does not have prefix.
        if ($language->getId() != \Drupal::languageManager()
          ->getDefaultLanguage()
          ->getId()
        ) {
          $prefix = $language->getId() . '/';
        }
        else {
          $prefix = '';
        }

        $this->drupalGet($prefix . 'node/' . $node->id());

        $node_language = $node->language()->getId();

        if ($node_language == Language::LANGCODE_NOT_SPECIFIED ||
          $node_language == Language::LANGCODE_NOT_APPLICABLE ||
          $node_language == $language->getId()
        ) {
          $this->assertSession()->statusCodeEquals(200);
        }
        else {
          $this->assertSession()->statusCodeEquals(403);
        }
      }
    }
  }

}
