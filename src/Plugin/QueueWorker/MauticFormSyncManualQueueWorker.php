<?php

declare(strict_types = 1);

namespace Drupal\mautic_forms\Plugin\QueueWorker;

/**
 * Process queued forms for synchronization of Mautic form data.
 *
 * @QueueWorker(
 *   id = "mautic_forms_form_sync_manual_queue",
 *   title = @Translation("Manual queue worker for synchronizing Mautic forms."),
 * )
 */
class MauticFormSyncManualQueueWorker extends MauticFormSyncQueueWorker {

}
