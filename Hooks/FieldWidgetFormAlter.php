<?php

namespace Drupal\workflow_fields\Hooks;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\paragraphs\Plugin\Field\FieldWidget\InlineParagraphsWidget;
use Drupal\paragraphs\Plugin\Field\FieldWidget\ParagraphsWidget;
use Drupal\node\Entity\Node;

class FieldWidgetFormAlter {

  /**
   * The hook hook_field_widget_form_alter().
   *
   * @param array $element
   *   The form element.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   * @param array $context
   *   The form context.
   */
  public function executeAction(array &$element, FormStateInterface $form_state, array $context) {
    $widget = NULL;
    $entity = $form_state->getFormObject();

    if (isset($context['widget']) &&
      $context['widget'] instanceof ParagraphsWidget &&
      $entity
    ) {
      $entity = $entity->getEntity();

      if ($entity instanceof Node && $entity->bundle() == 'detail') {
        $schemaWrapper = $this->factory->createInstance($entity);
        if ($schemaWrapper->hasSchemaInformation()) {
          $entity = $context['items']->getEntity();
          $field_name = $context['items']->getName();

          $id = $schemaWrapper->getEntityId($entity);

          if ($schemaWrapper->hasFieldOverride($field_name, $entity->bundle(), $id)) {
            if ($this->tourImporterIsMapped($context['items'], $context['delta'], $schemaWrapper) && $field_name != 'field_page_components') {
              unset($element['top']['links']['remove_button']);
              unset($element['top']['actions']['dropdown_actions']['remove_button']);
            }
            else {
              if ($this->tourImporterIsOnList($context['items'], $context['delta'], $schemaWrapper)) {
                unset($element['top']['links']['remove_button']);
                unset($element['top']['actions']['dropdown_actions']['remove_button']);
                // Protect all pre-populated paragraphs from being deleted.
                foreach ($element['subform'] as $field) {
                  if (array_key_exists('header_actions', $element)) {
                    unset($element['header_actions']);
                  }
                  if (array_key_exists('top', $element)) {
                    unset($element['top']['actions']['actions']);
                  }
                }
              }
            }
          }
        }
      }
    }
  }

  /**
   * A helper method for the removement of the remove button.
   *
   * @param mixed $items
   *   The items array.
   * @param mixed $delta
   *   The delta.
   * @param mixed $schemaWrapper
   *   The schema wrapper.
   *
   * @return mixed
   *   Return the value or false.
   */
  private function IsMapped($items, $delta, $schemaWrapper) {
    static $store = NULL;
    if ($store === NULL) {
      $values = $items->getValue();
      foreach ($values as $value) {
        $store[] = $schemaWrapper->hasFieldOverride('field_page_components', 'component_page', $value['target_id']);
      }
    }
    return isset($store[$delta]) ? $store[$delta] : FALSE;
  }

  /**
   * Checks if the paragraph ids are on the list.
   *
   * @param mixed $items
   *   An array of items.
   * @param int $delta
   *   The delta value.
   * @param \Drupal\data\Service\Schema\SchemaMetadataWrapper $schemaWrapper
   *   The schema wrapper.
   *
   * @return bool
   *   Returns TRUE if on list otherwise FALSE.
   */
  public function tourImporterIsOnList($items, $delta, SchemaMetadataWrapper $schemaWrapper) {
    $ids = $schemaWrapper->getAllEntitiesId();
    $items_value = $items->getValue();
    if (isset($items_value[$delta]['target_id'])) {
      $items_value = $items_value[$delta]['target_id'];
      if (!empty($items_value)) {
        if (isset($ids['paragraph'][$items_value])) {
          return TRUE;
        }
      }
    }

    return FALSE;
  }

}
