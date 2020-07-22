<?php

declare(strict_types = 1);

namespace Drupal\mautic_forms;

use Drupal\Core\Queue\QueueFactory;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Provides a service to keep Mautic form data synchronized with Mautic.
 */
class MauticFormSynchronizer {

  /**
   * The configuration key for our settings.
   */
  protected const SETTINGS = 'mautic_forms.settings';

  /**
   * The queue factory service.
   *
   * @var \Drupal\Core\Queue\QueueFactory
   */
  protected $queueFactory;

  /**
   * The config factory service.
   *
   * @var Drupal\Core\Config\ConfigFactoryInterface
   */
  private $configFactory;

  /**
   * Constructor.
   *
   * @param \Drupal\mautic_forms\MauticApiFactory $apiFactory
   *   The mautic api factory service.
   * @param \Drupal\Core\Queue\QueueFactory $queueFactory
   *   The queue factory service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config factory service.
   */
  public function __construct(
    MauticApiFactory $apiFactory,
    QueueFactory $queueFactory,
    ConfigFactoryInterface $configFactory
  ) {
    $this->apiFactory = $apiFactory;
    $this->queueFactory = $queueFactory;
    $this->configFactory = $configFactory;
  }

  /**
   * Synchronize locally cached Mautic form data with current Mautic data.
   *
   * This will find all mautic_form fields and ensure their `value` and
   * `cached_html` fields are up-to-date.
   */
  public function synchronizeForms(): void {
    $config = $this->configFactory->get(static::SETTINGS);
    $queue = $this->queueFactory->get($config->get('mautic.sync.queue'));
    if ($queue->numberOfItems() !== 0) {
      return;
    }

    $api = $this->apiFactory->get('forms');
    $forms = $api->makeRequest('forms', [
      'published' => TRUE,
      'limit' => '9999999',
    ])['forms'] ?? [];

    foreach ($forms as $form) {
      $queue->createItem($form);
    }
  }

}
