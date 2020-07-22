<?php

declare(strict_types = 1);

namespace Drupal\mautic_forms;

use Mautic\Api\Api;
use Mautic\MauticApi;
use Mautic\Auth\ApiAuth;
use Psr\Log\LoggerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Provides a factory for the Mautic API.
 */
class MauticApiFactory {

  /**
   * The configuration key for our settings.
   */
  protected const SETTINGS = 'mautic_forms.settings';

  /**
   * The error message used when logging an authentication error.
   */
  protected const ERROR_AUTHENTICATING = 'There was an error authenticating with the Mautic API when trying to construct an API endpoint object.';

  /**
   * The config factory service.
   *
   * @var Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The mautic_forms logger channel.
   *
   * @var Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * Constructor.
   *
   * @param Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config factory service.
   * @param Psr\Log\LoggerInterface $logger
   *   The mautic_forms logger channel.
   */
  public function __construct(
    ConfigFactoryInterface $configFactory,
    LoggerInterface $logger
  ) {
    $this->configFactory = $configFactory;
    $this->logger = $logger;
  }

  /**
   * Get an API endpoint object to interact with.
   *
   * @param string $name
   *   The name of the API endpoint to build.
   *
   * @return Mautic\MauticApi|null
   *   An API endpoint object, or NULL if there was an error.
   */
  public function get(string $name): ?Api {
    $config = $this->configFactory->get(static::SETTINGS);
    try {
      $auth = ApiAuth::initiate([
        'userName' => $config->get('mautic.api.username'),
        'password' => $config->get('mautic.api.password'),
      ], 'BasicAuth');
    }
    catch (\Exception $e) {
      $this->logger->error(static::ERROR_AUTHENTICATING);

      return NULL;
    }

    if (!$auth) {
      return NULL;
    }

    $mauticConfig = $this->configFactory->get('mautic.settings');
    $baseUrl = $mauticConfig->get('mautic_base_url');
    $baseUrl = parse_url($baseUrl);
    $baseUrl = $baseUrl['scheme'] . '://' . $baseUrl['host'];

    return MauticApi::getContext($name, $auth, $baseUrl);
  }

}
