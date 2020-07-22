<?php

declare(strict_types = 1);

namespace Drupal\mautic_forms\Plugin\QueueWorker;

/**
 * Process queued forms for synchronization of Mautic form data.
 *
 * @QueueWorker(
 *   id = "mautic_forms_form_sync_cron_queue",
 *   title = @Translation("Cron queue worker for synchronizing Mautic forms."),
 *   cron = {
 *     "time" = 30,
 *     "weight" = 100,
 *   },
 * )
 */
class MauticFormSyncCronQueueWorker extends MauticFormSyncQueueWorker {

}
