<?php

/**
 * @file
 * Contains \Drupal\responsive_iframe_filter\Plugin\Filter\FilterResponsiveIframe
 */

namespace Drupal\responsive_iframe_filter\Plugin\Filter;

use Drupal\filter\Annotation\Filter;
use Drupal\filter\FilterProcessResult;
use Drupal\filter\Plugin\FilterBase;
use Drupal\Component\Utility\Xss;
use Drupal\Core\Template\Attribute;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a filter that wraps <iframe> tags with a <figure> tag.
 *
 * @Filter(
 *   id = "filter_responsive_iframe",
 *   title = @Translation("Responsive iFrame filter"),
 *   type = Drupal\filter\Plugin\FilterInterface::TYPE_TRANSFORM_REVERSIBLE,
 *   settings = {
 *     "wrapper_element" = "figure",
 *     "wrapper_classes" = "media-wrapper"
 *   }
 * )
 */
class FilterResponsiveIframe extends FilterBase {

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form['wrapper_element'] = [
      '#type' => 'textfield',
      '#required' => TRUE,
      '#title' => $this->t('Wrapper element'),
      '#default_value' => $this->settings['wrapper_element'],
      '#description' => $this->t('The element to wrap the responsive iframe (e.g. figure)'),
    ];
    $form['wrapper_classes'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Wrapper class(es)'),
      '#default_value' => $this->settings['wrapper_classes'],
      '#description' => $this->t("Any wrapper class(es) separated by spaces (e.g. media-wrapper)"),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function process($text, $langcode) {
    $result = new FilterProcessResult($text);
    $text = preg_replace_callback('@<iframe([^>]*)>(.+?)</iframe>@s', [$this, 'processIframeCallback'], $text);

    $result->setProcessedText($text);
    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function tips($long = FALSE, $context = []) {
    return $this->t('Wraps a %iframe tags with a %figure tag.', [
      '%iframe' => '<iframe>',
      '%figure' => '<' . $this->getWrapperElement() . '>',
    ]);
  }

  /**
   * Callback to replace content of the <iframe> elements.
   *
   * @param array $matches
   *   An array of matches passed by preg_replace_callback().
   *
   * @return string
   *   A formatted string.
   */
  private function processIframeCallback(array $matches) {
    $attributes = $matches[1];
    $text = $matches[2];
    $text = '<' . $this->getWrapperElement() . $this->getWrapperAttributes() . '><iframe' . $attributes . '>' . $text . '</iframe></' . $this->getWrapperElement() . '>';

    return $text;
  }

  /**
   * Get the wrapper element.
   *
   * @return string
   *   The wrapper element tag.
   */
  private function getWrapperElement() {
    return Xss::filter($this->settings['wrapper_element']);
  }

  /**
   * Get the wrapper class(es).
   *
   * @return Attribute
   *   The wrapper element classes.
   */
  private function getWrapperAttributes() {
    return new Attribute([
      'class' => [$this->settings['wrapper_classes']],
    ]);
  }

}
