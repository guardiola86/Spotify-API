<?php

namespace Drupal\spotify_api\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * TODO: class docs.
 */
class AdminSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'spotify_api_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    $config = $this->config('spotify_api.settings');

    $form['number_artists'] = [
      '#type' => 'select',
      '#title' => t('Number of artists'),
      '#description' => t('Enter the number of artists you want to display in the block.'),
      '#required' => TRUE,
      '#default_value' => $config->get('number_artists'),
      '#options' => range(0, 20)
    ];
    unset($form['number_artists']['#options'][0]);
    $form['client_id'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Client ID'),
      '#default_value' => $config->get('client_id'),
      '#size' => 60,
      '#maxlength' => 128,
      '#required' => TRUE,
    );
    $form['client_secret'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Client Secret'),
      '#default_value' => $config->get('client_secret'),
      '#size' => 60,
      '#maxlength' => 128,
      '#required' => TRUE,
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    $config = $this->config('spotify_api.settings');

    if ($form_state->hasValue('number_artists')) {
      $config->set('number_artists', $form_state->getValue('number_artists'));
    }

    if ($form_state->hasValue('client_id')) {
      $config->set('client_id', $form_state->getValue('client_id'));
    }

    if ($form_state->hasValue('client_secret')) {
      $config->set('client_secret', $form_state->getValue('client_secret'));
    }

    $config->save();

    // Clear cache, so the block shows updated results.
    drupal_flush_all_caches();
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['spotify_api.settings'];
  }

}
