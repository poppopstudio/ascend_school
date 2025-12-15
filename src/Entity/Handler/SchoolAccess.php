<?php

namespace Drupal\ascend_school\Entity\Handler;

use Drupal\entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Provides the access handler for the School entity.
 */
class SchoolAccess extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {

    // Handle revision operations.
    if (in_array($operation, ['view revision', 'view all revisions'])) {
      return AccessResult::allowedIfHasPermission($account, 'view school revisions')
        ->cachePerPermissions();
    }

    if (in_array($operation, ['revert', 'revert revision'])) {
      return AccessResult::allowedIfHasPermission($account, 'revert school revisions')
        ->cachePerPermissions();
    }

    if ($operation === 'delete revision') {
      return AccessResult::allowedIfHasPermission($account, 'delete school revisions')
        ->cachePerPermissions();
    }

    // For all other operations, use parent EntityAccessControlHandler logic
    return parent::checkAccess($entity, $operation, $account);
  }
}
