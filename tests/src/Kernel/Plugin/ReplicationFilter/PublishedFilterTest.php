<?php

namespace Drupal\Tests\replication\Kernel;

use Drupal\Core\Entity\EntityInterface;
use Drupal\KernelTests\KernelTestBase;
use Drupal\block_content\Entity\BlockContent;
use Drupal\block_content\Entity\BlockContentType;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use Drupal\replication\Plugin\ReplicationFilter\PublishedFilter;

/**
 * Tests that the published filter parses parameters correctly.
 *
 * @group replication
 */
class PublishedFilterTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected $strictConfigSchema = FALSE;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'block',
    'block_content',
    'key_value',
    'multiversion',
    'node',
    'replication',
    'serialization',
    'system',
    'user',
  ];

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $node_type = NodeType::create([
      'type' => 'test',
      'label' => 'Test',
    ]);
    $node_type->save();

    $bundle_type = BlockContentType::create([
      'id' => 'test',
      'label' => 'Test',
    ]);
    $bundle_type->save();
  }

  /**
   * Test filtering published entities.
   *
   * @dataProvider filterTestProvider
   *
   * @param bool $include_unpublisheable_entities
   *   The plugin configuration value for including unpublisheable entities.
   * @param string $entity_class
   *   The fully qualified class name (FQCN) of the entity to create for testing the filter.
   * @param array $entity_values
   *   The values to pass to $class::create().
   * @param bool $expected
   *   The expected return value from the filter function.
   */
  public function testFilter($include_unpublisheable_entities, $entity_class, $entity_values, $expected) {
    /** @var \Drupal\replication\Plugin\ReplicationFilterManagerInterface $filter_manager */
    $filter_manager = $this->container->get('plugin.manager.replication_filter');
    $configuration = [
      'include_unpublisheable_entities' => $include_unpublisheable_entities,
    ];
    $filter = $filter_manager->createInstance('published', $configuration);
    $entity = call_user_func($entity_class . '::create', $entity_values);

    $value = $filter->filter($entity);

    $this->assertEquals($expected, $value);
  }

  /**
   * Provide test cases for the "entity_type_id" and "bundle" parameters.
   *
   * Note: the only node bundle is 'test' and the only block content bundle is
   * 'test'.
   *
   * @return array
   */
  public function filterTestProvider() {
    $published_node = [
      'type' => 'test',
      'status' => TRUE,
    ];

    $unpublished_node = [
      'type' => 'test',
      'status' => FALSE,
    ];

    $block = [
      'type' => 'test',
    ];

    return [
      [FALSE, Node::class, $published_node, TRUE],
      [FALSE, Node::class, $unpublished_node, FALSE],
      [TRUE, BlockContent::class, $block, TRUE],
      [FALSE, BlockContent::class, $block, FALSE],
    ];
  }

}
