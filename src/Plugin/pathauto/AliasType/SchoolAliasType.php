<?php

namespace Drupal\ascend_school\Plugin\pathauto\AliasType;

use Drupal\pathauto\Plugin\pathauto\AliasType\EntityAliasTypeBase;

/**
 * Provides an alias type for School entities.
 *
 * @AliasType(
 *   id = "school",
 *   label = @Translation("School"),
 *   types = {"school"},
 *   provider = "ascend_school",
 *   context_definitions = {
 *     "school" = @ContextDefinition("entity:school", label = @Translation("School"))
 *   }
 * )
 */
class SchoolAliasType extends EntityAliasTypeBase {

  /**
   * {@inheritdoc}
   */
  protected function getEntityTypeId() {
    return 'school';
  }
}
