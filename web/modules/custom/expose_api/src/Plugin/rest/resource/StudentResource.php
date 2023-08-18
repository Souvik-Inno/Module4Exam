<?php

namespace Drupal\expose_api\Plugin\rest\resource;

use Drupal\Core\Database\Connection;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a Resource for getting student data.
 *
 * @RestResource(
 *   id = "student_resource",
 *   label = @Translation("Student Resource"),
 *   uri_paths = {
 *     "canonical" = "/expose_api/student_resource"
 *   }
 * )
 */
class StudentResource extends ResourceBase {

  /**
   * Connects to the database server.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $conn;

  /**
   * Constructs an object of the class.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    array $serializer_formats,
    LoggerInterface $logger,
    Connection $connection,
    ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->conn = $connection;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
    $configuration,
    $plugin_id,
    $plugin_definition,
    $container->getParameter('serializer.formats'),
    $container->get('logger.factory')->get('rest'),
    $container->get('database')
    );
  }

  /**
   * Responds to entity GET requests.
   *
   * @return \Drupal\rest\ResourceResponse
   *   Response to send student data.
   */
  public function get() {
    $query = $this->conn->select('users_field_data', 'u')
      ->fields('u', ['uid', 'name']);
    $query->innerJoin('user__roles', 'ur', 'u.uid = ur.entity_id');
    $query->fields('ur', ['entity_id', 'roles_target_id'])
      ->condition('ur.roles_target_id', 'student');
    $query->innerJoin('user__field_passing_year', 'upy', 'u.uid = upy.entity_id');
    $query->fields('upy', ['entity_id', 'field_passing_year_value']);
    $result = $query->execute()->fetchAll();
    $student_data = [];
    foreach ($result as $record) {
      $data = [];
      $data['name'] = $record->name;
      $data['pass_year'] = $record->field_passing_year_value;
      array_push($student_data, $data);
    }
    $response = ['data' => $student_data];
    return new ResourceResponse($response);
  }

}
