<?php

declare(strict_types = 1);

namespace Drupal\mautic_forms\Controller;

use Psr\Log\LoggerInterface;
use Drupal\Component\Utility\Xss;
use Drupal\mautic_forms\MauticApiFactory;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Messenger\MessengerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;

/**
 * Provides a controller for mautic forms autocomplete requests.
 */
class MauticFormsController implements ContainerInjectionInterface {

  use StringTranslationTrait;

  /**
   * The error message logged when we could not retrieve forms from the API.
   */
  protected const ERROR_RETRIEVING_FORMS = 'There was an error retrieving forms from the Mautic API.';

  /**
   * The Mautic API factory.
   *
   * @var Drupal\mautic_forms\MauticApiFactory
   */
  protected $apiFactory;

  /**
   * The mautic_forms logger channel.
   *
   * @var Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * The meesenger service.
   *
   * @var Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * Constructor.
   *
   * @param Drupal\mautic_forms\MauticApiFactory $apiFactory
   *   The Mautic API factory.
   * @param Drupal\Core\StringTranslation\TranslationInterface $stringTranslation
   *   The string translation service.
   * @param Psr\Log\LoggerInterface $logger
   *   The mautic_forms logger channel.
   * @param Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   */
  public function __construct(
    MauticApiFactory $apiFactory,
    TranslationInterface $stringTranslation,
    LoggerInterface $logger,
    MessengerInterface $messenger
  ) {
    $this->apiFactory = $apiFactory;
    $this->stringTranslation = $stringTranslation;
    $this->logger = $logger;
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new static(
      $container->get('mautic_forms.api_factory'),
      $container->get('string_translation'),
      $container->get('logger.channel.mautic_forms'),
      $container->get('messenger')
    );
  }

  /**
   * Autocomplete handler for the Mautic Form field widget.
   *
   * @param Symfony\Component\HttpFoundation\Request $request
   *   The current request.
   *
   * @return Symfony\Component\HttpFoundation\JsonResponse
   *   The autocomplete results in JSON format.
   */
  public function autocomplete(Request $request): JsonResponse {
    $query = $request->query->get('q');

    if (empty($query)) {
      return new JsonResponse([]);
    }

    $query = Xss::filter($query);
    // The API does not handle spaces for some reason.. This replaces them with
    // the wildcard character, which gives us a more "fuzzy" search.
    $query = str_replace(' ', '%', $query);
    $forms = [];

    try {
      if ($api = $this->apiFactory->get('forms')) {
        $forms = $api->makeRequest('forms', [
          'search' => "%{$query}%",
          'published' => TRUE,
          'minimal' => TRUE,
        ])['forms'] ?? [];
      }
    }
    catch (\Exception $e) {
      $this->logger->error(static::ERROR_RETRIEVING_FORMS);

      return new JsonResponse([]);
    }

    $results = [];
    foreach ($forms as $form) {
      $results[] = [
        'value' => "${form['name']} (${form['id']})",
        'label' => $form['name'],
      ];
    }

    return new JsonResponse($results);
  }

}
