<?php

declare(strict_types = 1);

namespace Drupal\mautic_forms\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\mautic_forms\MauticApiFactory;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\Core\Logger\LoggerChannelTrait;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Plugin implementation of the 'mautic_form' field type.
 *
 * @FieldType(
 *   id = "mautic_form",
 *   label = @Translation("Mautic Form Embed"),
 *   module = "mautic_forms",
 *   description = @Translation("A field used to embed a Mautic form"),
 *   default_widget = "mautic_form_autocomplete",
 *   default_formatter = "mautic_form_script_embed",
 * )
 */
class MauticFormItem extends FieldItemBase {

  use MessengerTrait;
  use LoggerChannelTrait;
  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function isEmpty(): bool {
    return !$this->get('target_id')->getValue();
  }

  /**
   * {@inheritdoc}
   */
  public function preSave(): void {
    $api = $this->getMauticApiFactory()->get('forms');
    $response = $api->makeRequest(sprintf(
      'forms/%d',
      $this->get('target_id')->getCastedValue()
    ));

    if (isset($response['error'])) {
      $this->messenger()->addError(
        $this->t('There was an API error retrieving the Mautic Form (#@id) on @entity (@langcode)', [
          '@id' => $this->get('target_id')->getCastedValue(),
          '@entity' => $this->getEntity()->label(),
          '@langcode' => $this->getEntity()->language()->getId(),
        ])
      );

      $this->getLogger('mautic_forms')
        ->error(sprintf(
          '[%d] %s',
          $response['error']['code'],
          $response['error']['message']
        ));

      return;
    }

    $id = $response['form']['id'];
    $name = $response['form']['name'];
    $cachedHtml = $response['form']['cachedHtml'];

    $this->set('value', "$name ($id)");
    $this->set('cached_html', $cachedHtml);
  }

  /**
   * Get the mautic api factory service.
   *
   * @return \Drupal\mautic_forms\MauticApiFactory
   *   The mautic api factory service.
   */
  public function getMauticApiFactory(): MauticApiFactory {
    if (!isset($this->apiFactory)) {
      $this->setMauticApiFactory(\Drupal::service('mautic_forms.api_factory'));
    }

    return $this->apiFactory;
  }

  /**
   * Setter for the Mautic Api Factory service.
   *
   * Formatters can't use dependency injection, so this is to ease testing.
   *
   * @param \Drupal\mautic_forms\MauticApiFactory $apiFactory
   *   The mautic api factory service.
   */
  public function setMauticApiFactory(MauticApiFactory $apiFactory): void {
    $this->apiFactory = $apiFactory;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(
    FieldStorageDefinitionInterface $field_definition
  ): array {
    return [
      'columns' => [
        'target_id' => [
          'type' => 'int',
          'unsigned' => TRUE,
          'not null' => TRUE,
        ],
        'value' => [
          'type' => 'text',
          'not null' => TRUE,
        ],
        'cached_html' => [
          'type' => 'text',
          'size' => 'big',
          'not null' => TRUE,
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(
    FieldStorageDefinitionInterface $field_definition
  ): array {
    $properties['target_id'] = DataDefinition::create('integer')
      ->setLabel(t('Mautic form id'));
    $properties['value'] = DataDefinition::create('string')
      ->setLabel(t('Mautic form name'));
    $properties['cached_html'] = DataDefinition::create('string')
      ->setLabel(t('Mautic form cached html'));

    return $properties;
  }

}
