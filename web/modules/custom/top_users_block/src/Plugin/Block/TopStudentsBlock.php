<?php

namespace Drupal\top_users_block\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Database\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a top students block.
 *
 * @Block(
 *   id = "top_users_block_top_students",
 *   admin_label = @Translation("Top Students"),
 *   category = @Translation("Custom"),
 * )
 */
final class TopStudentsBlock extends BlockBase {

  /**
   * Connects to the database server.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $conn;

  /**
   * Contructs an object of the class.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    protected Connection $connection,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->conn = $connection;

  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container, 
  array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('database'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build(): array {
    $query = $this->connection->select('users_field_data', 'u')
      ->fields('u', ['uid', 'name']);
    $query->innerJoin('user__roles', 'ur', 'ur.entity_id = u.uid');
    $query->fields('ur', ['entity_id', 'roles_target_id'])
      ->condition('ur.roles_target_id = student')->range(0, 5);
    $result = $query->execute()->fetchAll();
    $students = [];
    foreach ($result as $record) {
      array_push($students, $record->name);
    }
    return [
      '#theme' => 'block_theme',
      '#content' => $students,
    ];
  }

}
