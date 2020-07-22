<?php

declare(strict_types = 1);

namespace Drupal\mautic_forms\Plugin\QueueWorker;

use Psr\Log\LoggerInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Process queued forms for synchronization of Mautic form data.
 */
abstract class MauticFormSyncQueueWorker extends QueueWorkerBase implements ContainerFactoryPluginInterface {

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The entity field manager service.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * The acquia_agenda logger service.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * Constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $pluginId
   *   The plugin_id for the plugin instance.
   * @param array $pluginDefinition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager service.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entityFieldManager
   *   The entity field manager service.
   * @param \Psr\Log\LoggerInterface $logger
   *   The acquia_agenda logger service.
   */
  public function __construct(
    array $configuration,
    string $pluginId,
    array $pluginDefinition,
    EntityTypeManagerInterface $entityTypeManager,
    EntityFieldManagerInterface $entityFieldManager,
    LoggerInterface $logger
  ) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);

    $this->entityTypeManager = $entityTypeManager;
    $this->entityFieldManager = $entityFieldManager;
    $this->logger = $logger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(
    ContainerInterface $container,
    array $configuration,
    $pluginId,
    $pluginDefinition
  ): self {
    return new static(
      $configuration,
      $pluginId,
      $pluginDefinition,
      $container->get('entity_type.manager'),
      $container->get('entity_field.manager'),
      $container->get('logger.channel.mautic_forms')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function processItem($form): void {
    $fieldMap = $this->entityFieldManager
      ->getFieldMapByFieldType('mautic_form');
    foreach ($fieldMap as $entityTypeId => $fields) {
      $storage = $this->entityTypeManager->getStorage($entityTypeId);

      foreach ($fields as $field => $info) {
        $entityIds = $storage->getQuery()
          ->condition('type', array_keys($info['bundles']), 'IN')
          ->condition("${field}.target_id", $form['id'])
          ->execute();
        // Chunk the entities so we can load 5 at a time. Otherwise we easily
        // run out of memory.
        $entityIds = array_chunk($entityIds, 5, TRUE);

        foreach ($entityIds as $ids) {
          $entities = $storage->loadMultiple($ids);

          foreach ($entities as $entity) {
            foreach ($entity->getTranslationLanguages() as $langcode => $_) {
              $entity = $entity->getTranslation($langcode);
              $mauticForm = $entity->get($field);

              $remoteValue = "${form['name']} (${form['id']})";
              if (
                $mauticForm->value === $remoteValue
                && $mauticForm->cached_html === $form['cachedHtml']
              ) {
                continue;
              }

              $this->logger->info('Updating Muatic Form#@formId on @bundle#@entityId[@langcode]', [
                '@formId' => $form['id'],
                '@bundle' => $entity->bundle(),
                '@entityId' => $entity->id(),
                '@langcode' => $langcode,
              ]);

              $mauticForm->value = $remoteValue;
              $mauticForm->cached_html = $form['cachedHtml'];
              $entity->save();
            }
          }
        }
      }
    }
  }

}
