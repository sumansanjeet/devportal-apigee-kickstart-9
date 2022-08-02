<?php

namespace Drupal\api_response_check\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Returns responses for apiresponse module routes.
 */
class ApiResponseController extends ControllerBase {

  /**
   * The database service.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;
  /**
   * The form builder.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database'),
      $container->get('date.formatter'),
      $container->get('form_builder')
    );
  }

  /**
   * Constructs a ApiResponseController object.
   *
   * @param \Drupal\Core\Database\Connection $database
   *   A database connection.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   The date formatter service.
   * @param \Drupal\Core\Form\FormBuilderInterface $form_builder
   *   The form builder.
   */
  public function __construct(Connection $database, DateFormatterInterface $date_formatter, FormBuilderInterface $form_builder) {
    $this->database = $database;
    $this->dateFormatter = $date_formatter;
    $this->formBuilder = $form_builder;
  }

  /**
   * Displays a listing of API Response results.
   */
  public function results() {

    $build['api_response_form'] = $this->formBuilder->getForm('Drupal\api_response_check\Form\ApiResponseForm');
    // Define Header items for the table format output.
    $header = [
      [
        'data' => $this->t('Date'),
        'field' => 'w.wid',
        'sort' => 'desc',
        'class' => [RESPONSIVE_PRIORITY_LOW],
      ],
      $this->t('API URLs'),
      [
        'data' => $this->t('Status'),
        'field' => 'w.status',
        'class' => [RESPONSIVE_PRIORITY_MEDIUM],
      ],
    ];
    $query = $this->database->select('api_response_check', 'w')
      ->extend('\Drupal\Core\Database\Query\PagerSelectExtender')
      ->extend('\Drupal\Core\Database\Query\TableSortExtender');
    $query->fields('w', [
      'wid',
      'status',
      'timestamp',
      'api_url',
    ]);
    $result = $query
      ->limit(50)
      ->orderByHeader($header)
      ->execute();
    $rows = [];
    foreach ($result as $log) {
      $rows[] = [
        'data' => [
          $this->dateFormatter->format($log->timestamp, 'short'),
          $log->api_url,
          $log->status,
        ],
      ];
    }
    // Render data in table format.
    $build['api_response_table'] = [
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $rows,
      '#empty' => $this->t('No log messages available.'),
    ];
    $build['api_response_pager'] = ['#type' => 'pager'];

    return $build;
  }

}
