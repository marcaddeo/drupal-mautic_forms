<?php

declare(strict_types = 1);

namespace Drupal\mautic_forms\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the 'mautic_form_script_embed' formatter.
 *
 * @FieldFormatter(
 *   id = "mautic_form_script_embed",
 *   label = @Translation("Mautic Form Script Embed"),
 *   field_types = {
 *     "mautic_form",
 *   },
 * )
 */
class MauticFormScriptEmbedFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(
    FieldItemListInterface $items,
    $langcode
  ): array {
    $elements = [];
    foreach ($items as $delta => $item) {
      $elements[$delta] = [
        '#type' => 'mautic_form_script_embed',
        '#id' => (int) $item->target_id,
      ];
    }

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary(): array {
    return [$this->t('Displays the Mautic Form using the JavaScript embed method')];
  }

}
