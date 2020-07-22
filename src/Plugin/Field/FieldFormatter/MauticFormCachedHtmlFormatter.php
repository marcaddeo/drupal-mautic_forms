<?php

declare(strict_types = 1);

namespace Drupal\mautic_forms\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'mautic_form_cached_html' formatter.
 *
 * @FieldFormatter(
 *   id = "mautic_form_cached_html",
 *   label = @Translation("Mautic Form Cached Html"),
 *   field_types = {
 *     "mautic_form",
 *   },
 * )
 */
class MauticFormCachedHtmlFormatter extends FormatterBase implements ContainerFactoryPluginInterface {

  /**
   * The module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructor.
   *
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   Defines an interface for entity field definitions.
   * @param array $settings
   *   The formatter settings.
   * @param string $label
   *   The formatter label display setting.
   * @param string $view_mode
   *   The view mode.
   * @param array $third_party_settings
   *   Any third party settings.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   The module handler service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config factory service.
   */
  public function __construct(
    $plugin_id,
    $plugin_definition,
    FieldDefinitionInterface $field_definition,
    array $settings,
    $label,
    $view_mode,
    array $third_party_settings,
    ModuleHandlerInterface $moduleHandler,
    ConfigFactoryInterface $configFactory
  ) {
    parent::__construct(
      $plugin_id,
      $plugin_definition,
      $field_definition,
      $settings,
      $label,
      $view_mode,
      $third_party_settings
    );

    $this->moduleHandler = $moduleHandler;
    $this->configFactory = $configFactory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(
    ContainerInterface $container,
    array $configuration,
    $plugin_id,
    $plugin_definition
  ): self {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      $container->get('module_handler'),
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(
    FieldItemListInterface $items,
    $langcode
  ): array {
    $elements = [];
    foreach ($items as $delta => $item) {
      $cachedHtml = $item->cached_html;
      $this->moduleHandler->alter(
        'mautic_form_cached_html',
        $cachedHtml
      );

      $mauticHost = \parse_url(
        $this->configFactory->get('mautic.settings')->get('mautic_base_url'),
        \PHP_URL_HOST
      );

      $elements[$delta] = [
        '#type' => 'mautic_form_cached_html',
        '#id' => (int) $item->target_id,
        '#cached_html' => $cachedHtml,
        '#attached' => [
          'library' => [
            'mautic_forms/mautic-sdk',
          ],
          'drupalSettings' => [
            'mautic_forms' => [
              'mautic_host' => $mauticHost,
            ],
          ],
        ],
      ];
    }

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary(): array {
    return [$this->t('Displays the Mautic Form using the locally cached HTML. This formatter enables form label translation.')];
  }

}
