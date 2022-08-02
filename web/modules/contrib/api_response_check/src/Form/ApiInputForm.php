<?php

namespace Drupal\api_response_check\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Defines a form that configures api settings.
 */
class ApiInputForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'api_response_check.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'apiinput_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('api_response_check.settings');
    $form['api_inputs'] = [
      '#type' => 'textarea',
      '#title' => $this->t('API URL'),
      '#description' => $this->t('Enter the API URLs to validate in new line.'),
      '#default_value' => $config->get('api_inputs'),
      '#required' => TRUE,
    ];

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save Configuration'),
      '#button_type' => 'primary',
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    parent::submitForm($form, $form_state);

    $this->config('api_response_check.settings')
      ->set('api_inputs', $form_state->getValue('api_inputs'))
      ->save();
    $this->messenger()->addMessage("Click View Results to check API Response");
  }

}
