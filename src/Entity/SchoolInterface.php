<?php

namespace Drupal\ascend_school\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityPublishedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Interface for School entities.
 */
interface SchoolInterface extends ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface, EntityPublishedInterface {
  
}
