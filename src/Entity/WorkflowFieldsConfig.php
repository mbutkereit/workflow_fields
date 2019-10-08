<?php

namespace Drupal\workflow_fields\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines the Date Format configuration entity class.
 *
 * @ConfigEntityType(
 *   id = "workflow_fields_config",
 *   label = @Translation("Workflow Fields Config"),
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label"
 *   },
 *   admin_permission = "administer site configuration",
 *   list_cache_tags = { "rendered" },
 *   config_export = {
 *     "id",
 *     "label",
 *     "states",
 *   }
 * )
 */
class WorkflowFieldsConfig extends ConfigEntityBase {

  /**
   * The date format machine name.
   *
   * @var string
   */
  protected $id;

  /**
   * The human-readable name of the date format entity.
   *
   * @var string
   */
  protected $label;

  /**
   * The states.
   *
   * @var array
   */
  protected $states;

  /**
   * {@inheritdoc}
   */
  public function getCacheTagsToInvalidate() {
    return ['rendered'];
  }

  /**
   *
   */
  public function getConfig() {
    return $this->states;
  }

  /**
   * @param $field_name
   * @return mixed|null
   */
  public function getConfigField($field_name) {
    if (!empty($this->states[$field_name])) {
      return $this->states[$field_name];
    }
    return NULL;
  }

  /**
   *
   */
  public function getConfigFieldByState($field_name, $state) {
    $config_field = $this->getConfigField($field_name);
    if (!empty($config_field[$state])) {
      return $config_field[$state];
    }
    return NULL;
  }

  /**
   *
   */
  public function getConfigFieldByOp($field_name, $state, $op) {
    $config_field = $this->getConfigFieldByState($field_name, $state);
    if (!empty($config_field[$op])) {
      return $config_field[$op];
    }
    return NULL;
  }

}
