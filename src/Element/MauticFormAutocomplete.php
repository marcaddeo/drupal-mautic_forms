<?php

declare(strict_types = 1);

namespace Drupal\mautic_forms\Element;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element\Textfield;

/**
 * Provides a Mautic Form autocomplete.
 *
 * phpcs:disable Drupal.Commenting.DocComment.TagsNotGrouped
 *
 * Usage example:
 * @code
 * $form['mautic_form'] = array(
 *   '#type' => 'mautic_form_autocomplete',
 *   '#title' => $this->t('Select a Mautic Form'),
 *   '#default_value' => $node->field_mautic_form->value,
 *   '#required' => TRUE,
 * );
 * @endcode
 *
 * Extracting the form id:
 * @code
 * use Drupal\mautic_forms\Element\MauticFormAutocomplete;
 *
 * ...
 *
 * $form_id = MauticFormAutocomplete::extractFormIdFromAutocompleteInput(
 *   $form_state->get('mautic_form')
 * );
 * @encode
 *
 * @see Drupal\Core\Render\Element\Textfield
 *
 * @FormElement("mautic_form_autocomplete")
 *
 * phpcs:enable Drupal.Commenting.DocComment.TagsNotGrouped
 */
class MauticFormAutocomplete extends Textfield {

  /**
   * {@inheritdoc}
   */
  public function getInfo(): array {
    $info = parent::getInfo();
    $class = get_class($this);

    array_unshift($info['#process'], [
      $class,
      'processMauticFormAutocomplete',
    ]);

    return $info;
  }

  /**
   * Add the Mautic form autocomplete route name to the form.
   *
   * @param array $element
   *   The form element to process.
   * @param Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param array $complete_form
   *   The complete form structure.
   *
   * @return array
   *   The form element.
   */
  public static function processMauticFormAutocomplete(
    array &$element,
    FormStateInterface $form_state,
    array &$complete_form
  ): array {
    $element['#autocomplete_route_name'] = 'mautic_forms.autocomplete';

    return $element;
  }

  /**
   * Extracts the Mautic form ID from the autocompletion result.
   *
   * @param string $input
   *   The input coming from the autocompletion result.
   *
   * @return int|null
   *   An Mautic form ID or NULL if the input does not contain one.
   */
  public static function extractFormIdFromAutocompleteInput(
    string $input
  ): ?int {
    if (!preg_match('~.+ \((?P<id>\d+)\)$~', $input, $matches)) {
      return NULL;
    }

    return (int) $matches['id'];
  }

}
