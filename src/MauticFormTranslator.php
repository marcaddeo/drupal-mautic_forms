<?php

declare(strict_types = 1);

namespace Drupal\mautic_forms;

use Wa72\HtmlPageDom\HtmlPageCrawler;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\StringTranslation\TranslationInterface;

/**
 * Provides a helper class to translate the Mautic form.
 */
class MauticFormTranslator {

  /**
   * The string translation service.
   *
   * @var \Drupal\Core\StringTranslation\TranslationInterface
   */
  protected $stringTranslation;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\StringTranslation\TranslationInterface $stringTranslation
   *   The string translation service.
   */
  public function __construct(TranslationInterface $stringTranslation) {
    $this->stringTranslation = $stringTranslation;
  }

  /**
   * Translate the cached Mautic Form html contents.
   *
   * @param string &$cachedHtml
   *   A reference to the cached html contents.
   */
  public function translateCachedHtml(string &$cachedHtml): void {
    $dom = HtmlPageCrawler::create($cachedHtml);
    $stringTranslation = $this->stringTranslation;

    // Translate simple text nodes.
    $dom->filter('label, option, button, .mauticform-errormsg')
      ->each(function (HtmlPageCrawler $node) use ($stringTranslation): void {
        // phpcs:disable Drupal.Semantics.FunctionT.NotLiteralString
        $translated = new TranslatableMarkup(
          $node->text(),
          [],
          [],
          $stringTranslation
        );
        // phpcs:enable

        $node->setText($translated);
      });

    $cachedHtml = (string) $dom;
  }

}
