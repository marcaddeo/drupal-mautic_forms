<?php

declare(strict_types = 1);

namespace Drupal\mautic_forms\Plugin\Field\FieldWidget;

use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\mautic_forms\Element\MauticFormAutocomplete;

/**
 * Plugin implementation of the 'mautic_form_autocomplete' widget.
 *
 * @FieldWidget(
 *   id = "mautic_form_autocomplete",
 *   module = "mautic_forms",
 *   label = @Translation("Autocomplete for Matuic forms"),
 *   field_types = {
 *     "mautic_form",
 *   },
 * )
 */
class MauticFormAutocompleteWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(
    FieldItemListInterface $items,
    $delta,
    array $element,
    array &$form,
    FormStateInterface $formstate
  ): array {
    $element += [
      '#type' => 'mautic_form_autocomplete',
      '#default_value' => $items[$delta]->value ?? NULL,
    ];
    return ['value' => $element];
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(
    array $values,
    array $form,
    FormStateInterface $form_state
  ) {
    foreach ($values as $delta => $value) {
      $form_id = MauticFormAutocomplete::extractFormIdFromAutocompleteInput(
        $value['value']
      );

      $values[$delta]['target_id'] = $form_id;
    }

    return $values;
  }

}
