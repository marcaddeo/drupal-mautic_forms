<?php

declare(strict_types = 1);

namespace Drupal\mautic_forms\Element;

use Drupal\Core\Render\Element\RenderElement;

/**
 * Provides a render element for Mautic Form script embeds.
 *
 * Properties:
 * - #id: The id of the form in Mautic.
 *
 * Usage example:
 * @code
 * $build['gated_content_form'] = [
 *   '#type' => 'mautic_form_script_embed',
 *   '#id' => 1234,
 * ];
 * @endcode
 *
 * @RenderElement("mautic_form_script_embed")
 */
class MauticFormScriptEmbed extends RenderElement {

  /**
   * {@inheritdoc}
   */
  public function getInfo(): array {
    return [
      '#theme' => 'mautic_form_script_embed',
      '#attributes' => [],
      '#id' => NULL,
    ];
  }

}
