<?php

namespace Drupal\api_response_check\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Connection;
use GuzzleHttp\ClientInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the latest API response from the database.
 *
 * @internal
 */
class ApiResponseForm extends ConfigFormBase {

  /**
   * The database service.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;
  /**
   * The HTTP client to fetch the feed data with.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $httpClient;

  /**
   * Class constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\Database\Connection $database
   *   A database connection.
   * @param \GuzzleHttp\ClientInterface $http_client
   *   The Guzzle HTTP client.
   */
  public function __construct(ConfigFactoryInterface $config_factory, Connection $database, ClientInterface $http_client) {
    parent::__construct($config_factory);
    $this->database = $database;
    $this->httpClient = $http_client;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('database'),
      $container->get('http_client')
    );
  }

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
    return 'api_response_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['#markup'] = $this->t('Click the below button to get the latest API Response results');

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Get Instant Response'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Get API URLs from the configuration.
    if ($config_data = $this->config('api_response_check.settings')->get('api_inputs')) {
      $urls = explode(PHP_EOL, $config_data);
      // Log the information into Database.
      foreach ($urls as $uri) {
        $api_url = trim($uri);
        $response = $this->httpClient->get($api_url, ['http_errors' => FALSE, 'headers' => ['Accept' => 'text/plain']]);
        if ($response->getStatusCode() == 200) {
          $this->database->insert('api_response_check')
            ->fields([
              'status' => 'Success',
              'api_url' => $api_url,
              'timestamp' => time(),
              'severity' => 1,
            ])
            ->execute();
        }
        else {
          $this->database->insert('api_response_check')
            ->fields([
              'status' => 'Failure',
              'api_url' => $api_url,
              'timestamp' => time(),
              'severity' => 0,
            ])
            ->execute();
        }
      }
    }
  }

}
