<?php

namespace Drupal\content_language_access\Form;

use Drupal\content_language_access\ContentLanguageAccessConfigManager;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Render\Element;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Administration form for content language access module.
 */
class ContentLanguageAccessAdminForm extends ConfigFormBase {

  /**
   * Content Language Access Manager Object.
   *
   * @var Drupal\content_language_access\ContentLanguageAccessConfigManager
   */
  private $contentLanguageAccessConfigManager;

  /**
   * Route provider Object.
   *
   * @var Drupal\Core\Config\ImmutableConfig
   */
  private $contentLanguageAccessConfigSettings;

  /**
   * Drupal\Core\Language\LanguageManagerInterface definition.
   *
   * @var Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * {@inheritDoc}
   */
  public function __construct(ConfigFactoryInterface $config_factory, LanguageManagerInterface $languageManager, ContentLanguageAccessConfigManager $content_language_access_config_manager) {
    $this->setConfigFactory($config_factory);
    $this->languageManager = $languageManager;
    $this->contentLanguageAccessConfigManager = $content_language_access_config_manager;
    $this->contentLanguageAccessConfigSettings = $this->config('content_language_access.settings');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('language_manager'),
      $container->get('content_language_access.config_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'content_language_access_admin_form';
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->contentLanguageAccessConfigSettings;

    foreach (Element::children($form['content_language_access']) as $group) {
      foreach (Element::children($form['content_language_access'][$group]) as $variable) {
        $config->set($variable, (bool) $form_state->getValue($variable));
      }
    }

    $config->set('access_bypass', (bool) $form_state->getValue('access_bypass'));
    $config->set('route_list', $this->contentLanguageAccessConfigManager->encodeArray(
      $form_state->getValue('route_list')
    ));

    $config->save();

    parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['content_language_access.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state = NULL) {
    $form = [];

    $config = $this->contentLanguageAccessConfigSettings;

    $form['content_language_access'] = [
      '#type' => 'details',
      '#title' => $this->t('Permissions'),
      '#open' => TRUE,
    ];

    $languages = $this->languageManager->getLanguages();
    foreach ($languages as $language) {
      if (!$language->isLocked()) {
        $form['content_language_access'][$language->getId()] = [
          '#type' => 'details',
          '#title' => $this->t('Drupal language: @language', [
            '@language' => $language->getName(),
          ]),
          '#open' => TRUE,
        ];
        foreach ($languages as $language_perm) {
          if (!$language_perm->isLocked()) {
            $form['content_language_access'][$language->getId()][$language->getId() . '_' . $language_perm->getId()] = [
              '#type' => 'checkbox',
              '#title' => $this->t('Content language: @language', [
                '@language' => $language_perm->getName(),
              ]),
              '#default_value' => (bool) $config->get($language->getId() . '_' . $language_perm->getId()),
            ];

            // Only shows the same language for better visualization.
            if ($language->getId() == $language_perm->getId()) {
              $form['content_language_access'][$language->getId()][$language->getId() . '_' . $language_perm->getId()]['#disabled'] = TRUE;
              $form['content_language_access'][$language->getId()][$language->getId() . '_' . $language_perm->getId()]['#value'] = TRUE;
            }
          }
        }
      }
    }

    $form['settings'] = [
      '#type' => 'details',
      '#title' => $this->t('Settings'),
      '#open' => FALSE,
    ];

    $form['settings']['access_bypass'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Bypass language access validation'),
      '#description' => $this->t('Enable this to bypass language validation for a list of routes.'),
      '#default_value' => $config->get('access_bypass'),
    ];

    $form['settings']['route_list'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Routes to be bypassed'),
      '#description' => $this->t('Specify routes name. Enter one information per line.'),
      '#default_value' => $this->contentLanguageAccessConfigManager->decodeArray($config->get('route_list')),
      '#size' => 20,
      '#required' => FALSE,
      '#states' => [
        'visible' => [
          ':input[name="access_bypass"]' => ['checked' => TRUE],
        ],
      ],
    ];

    return parent::buildForm($form, $form_state);
  }

}
