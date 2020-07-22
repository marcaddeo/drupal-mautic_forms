<?php

declare(strict_types = 1);

namespace Drupal\mautic_forms\Element;

use Drupal\Core\Render\Element\RenderElement;

/**
 * Provides a render element for Mautic Form cached html embeds.
 *
 * Properties:
 * - #id: The id of the form in Mautic.
 * - #cached_html: The Mautic Form cached html content.
 *
 * Usage example:
 * @code
 * $build['gated_content_form'] = [
 *   '#type' => 'mautic_form_cached_html',
 *   '#id' => 1234,
 *   '#cached_html' => '<form ...',
 * ];
 * @endcode
 *
 * @RenderElement("mautic_form_cached_html")
 */
class MauticFormCachedHtml extends RenderElement {

  /**
   * {@inheritdoc}
   */
  public function getInfo(): array {
    return [
      '#theme' => 'mautic_form_cached_html',
      '#attributes' => [],
      '#id' => NULL,
      '#cached_html' => NULL,
    ];
  }

}
