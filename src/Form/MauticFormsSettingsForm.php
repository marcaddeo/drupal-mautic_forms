<?php

declare(strict_types = 1);

namespace Drupal\mautic_forms\Form;

use Psr\Log\LoggerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\mautic_forms\MauticApiFactory;
use Drupal\Core\Messenger\MessengerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * A form used for configuring the Mautic Forms module.
 */
class MauticFormsSettingsForm extends ConfigFormBase {

  /**
   * The configuration key for our settings.
   */
  protected const SETTINGS = 'mautic_forms.settings';

  /**
   * The name of our form.
   */
  protected const FORM_NAME = 'mautic_forms_settings_form';

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
   * @param Psr\Log\LoggerInterface $logger
   *   The mautic_forms logger channel.
   * @param Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   */
  public function __construct(
    MauticApiFactory $apiFactory,
    LoggerInterface $logger,
    MessengerInterface $messenger
  ) {
    $this->apiFactory = $apiFactory;
    $this->logger = $logger;
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new static(
      $container->get('mautic_forms.api_factory'),
      $container->get('logger.channel.mautic_forms'),
      $container->get('messenger')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(
    array $form,
    FormStateInterface $form_state
  ): array {
    $config = $this->config(static::SETTINGS);

    $form['mautic']['api'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('API Settings'),
    ];

    $form['mautic']['api']['mautic_api_username'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Mautic Username'),
      '#required' => TRUE,
      '#default_value' => $config->get('mautic.api.username'),
    ];

    $form['mautic']['api']['mautic_api_password'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Mautic Password'),
      '#required' => TRUE,
      '#default_value' => $config->get('mautic.api.password'),
    ];

    $form['mautic']['sync'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Form Synchronization Settings'),
    ];

    $form['mautic']['sync']['mautic_sync_queue'] = [
      '#type' => 'radios',
      '#options' => [
        'mautic_forms_form_sync_cron_queue' => $this->t('Process sync queue during cron runs'),
        'mautic_forms_form_sync_manual_queue' => $this->t('Manually run queue processing'),
      ],
      '#default_value' => $config->get('mautic.sync.queue') ?? 'mautic_forms_form_sync_cron_queue',
      '#description' => $this->t(
        "This setting determines how the Mautic form synchronization queue is processed. If you have many forms on your website, it's recommended to process the queue manually with <code>@command</code>",
        ['@command' => 'drush queue:run mautic_forms_form_sync_manual_queue']
      ),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(
    array &$form,
    FormStateInterface $form_state
  ): void {
    $config = $this->config(static::SETTINGS);
    $settings = [
      'mautic.api.username' => 'mautic_api_username',
      'mautic.api.password' => 'mautic_api_password',
      'mautic.sync.queue' => 'mautic_sync_queue',
    ];

    foreach ($settings as $setting => $element) {
      $config->set($setting, $form_state->getValue($element));
    }
    $config->save();

    parent::submitForm($form, $form_state);

    $api = $this->apiFactory->get('forms');
    $api->getList();

    if ($api->getResponseInfo()) {
      $this->messenger->addMessage(
        $this->t('Successfully authenticated with the Mautic API.')
      );
    }
    else {
      $this->messenger->addError(
        $this->t('There was an error authenticating with the Mautic Api. Ensure your API credentials are correct.')
      );
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return static::FORM_NAME;
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames(): array {
    return [static::SETTINGS];
  }

}
