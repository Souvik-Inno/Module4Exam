<?php

namespace Drupal\expose_api\Controller;

use Drupal\Core\Controller\ControllerBase;
use GuzzleHttp\Client;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Displays students data from custom API.
 */
class APIController extends ControllerBase {

  /**
   * Performs a GET request.
   *
   * @var \GuzzleHttp\Client
   */
  public $client;

  /**
   * Creates object of the class.
   *
   * @param \GuzzleHttp\Client $client
   *   To set the client.
   */
  public function __construct(Client $client) {
    $this->client = $client;
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('http_client'),
    );
  }

  /**
   * Shows the data fetched in a custom template.
   *
   * @return array
   *   Renderable array to show fetched data.
   */
  public function view() {
    $request = $this->client->get('http://module4exam.com/expose_api/student_resource');
    $response = json_decode($request->getBody());
    $build = [
      '#theme' => 'student',
      '#content' => $response->data,
    ];
    return $build;
  }

}
