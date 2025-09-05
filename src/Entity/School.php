<?php

namespace Drupal\ascend_school\Entity;

use Drupal\Core\Entity\EditorialContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityPublishedTrait;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\user\EntityOwnerTrait;

/**
 * Provides the School entity.
 *
 * @ContentEntityType(
 *   id = "school",
 *   label = @Translation("School"),
 *   label_collection = @Translation("Schools"),
 *   label_singular = @Translation("school"),
 *   label_plural = @Translation("schools"),
 *   label_count = @PluralTranslation(
 *     singular = "@count school",
 *     plural = "@count schools",
 *   ),
 *   base_table = "school",
 *   revision_table = "school_revision",
 *   show_revision_ui = TRUE,
 *   collection_permission = "access school overview",
 *   handlers = {
 *     "access" = "Drupal\entity\EntityAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\entity_admin_handlers\SingleBundleEntity\SingleBundleEntityHtmlRouteProvider",
 *       "revision" = \Drupal\Core\Entity\Routing\RevisionHtmlRouteProvider::class,
 *     },
 *     "form" = {
 *       "default" = "Drupal\ascend_school\Form\SchoolForm",
 *       "edit" = "Drupal\ascend_school\Form\SchoolForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm",
 *       "revision-delete" = \Drupal\Core\Entity\Form\RevisionDeleteForm::class,
 *       "revision-revert" = \Drupal\Core\Entity\Form\RevisionRevertForm::class,
 *     },
 *     "list_builder" = "Drupal\ascend_school\Entity\Handler\SchoolListBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "permission_provider" = "Drupal\entity\EntityPermissionProvider",
 *   },
 *   admin_permission = "administer school entities",
 *   entity_keys = {
 *     "id" = "school_id",
 *     "label" = "title",
 *     "uuid" = "uuid",
 *     "revision" = "revision_id",
 *     "owner" = "uid",
 *     "uid" = "uid",
 *     "published" = "status",
 *   },
 *   revision_metadata_keys = {
 *     "revision_user" = "revision_uid",
 *     "revision_created" = "revision_timestamp",
 *     "revision_log_message" = "revision_log"
 *   },
 *   field_ui_base_route = "entity.school.field_ui_base",
 *   links = {
 *     "add-form" = "/school/add",
 *     "canonical" = "/school/{school}",
 *     "collection" = "/admin/content/school",
 *     "delete-form" = "/school/{school}/delete",
 *     "edit-form" = "/school/{school}/edit",
 *     "field-ui-base" = "/admin/structure/school",
 *     "version-history" = "/admin/structure/school/{school}/revisions",
 *     "revision" = "/admin/structure/school/{school}/revisions/{school_revision}/view",
 *     "revision-revert-form" = "/admin/structure/school/{school}/revisions/{school_revision}/revert",
 *     "revision-delete-form" = "/admin/structure/school/{school}/revisions/{school_revision}/delete",
 *   },
 * )
 */
class School extends EditorialContentEntityBase implements SchoolInterface {

  use EntityChangedTrait;
  use EntityOwnerTrait;
  use EntityPublishedTrait;

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);
    $fields += static::ownerBaseFieldDefinitions($entity_type);

    $fields['title'] = BaseFieldDefinition::create('string')
      ->setLabel(t("School name"))
      ->setRequired(TRUE)
      ->setRevisionable(TRUE)
      ->setSetting('max_length', 100)
      ->setDisplayOptions('form', ['type' => 'string_textfield', 'weight' => -5])
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayConfigurable('form', TRUE);

    $fields['uid']
      ->setLabel(t('Authored by'))
      ->setDescription(t('The username of the content author.'))
      ->setRevisionable(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'author',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 5,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'placeholder' => '',
        ],
      ])
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayConfigurable('form', TRUE);

    $fields['status']
      ->setLabel(t("Published"))
      ->setDefaultValue(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'settings' => [
          'display_label' => TRUE,
        ],
        'weight' => 120,
      ])
      ->setDisplayConfigurable('form', TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t("Authored on"))
      ->setDescription(t("The date & time that the school was created."))
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'timestamp',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'datetime_timestamp',
        'weight' => 10,
      ])
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayConfigurable('form', TRUE);

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t("Changed"))
      ->setDescription(t("The time that the school was last edited."))
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayConfigurable('form', TRUE);

    return $fields;
  }

  public function postSave(EntityStorageInterface $storage, $update = TRUE) {
    parent::postSave($storage, $update);

    // Update auditor's working school after save.
    $this->updateAuditorWorkingSchool();
  }

  /**
   * Update the associated auditor's working school if not set.
   */
  protected function updateAuditorWorkingSchool() {
    $auditor = $this->get('auditor');

    if ($auditor->isEmpty()) {
      return;
    }

    $auditor_uid = $auditor->target_id;

    // Load auditor profile
    $auditor_profiles = \Drupal::entityTypeManager()
      ->getStorage('auditor')
      ->loadByProperties(['user_id' => $auditor_uid]);

    if (empty($auditor_profiles)) {
      return;
    }

    /** @var auditor_profile \Drupal\profile\Entity\Profile */
    $auditor_profile = reset($auditor_profiles);
    $working_school = $auditor_profile->get('auditor_p_working_school');

    // Only set if not already set.
    if ($working_school->isEmpty()) {
      $auditor_profile->set('auditor_p_working_school', $this->id());
      $auditor_profile->save();

      // Log the action.
      \Drupal::logger('ascend_school')->info('Set @school_title (@school_id) as working school for auditor @auditor_id', [
        '@school_title' => $this->get('title'),
        '@school_id' => $this->id(),
        // '@auditor_id' => $auditor_profile->id(),
        '@auditor_id' => $auditor_profile->id(),
      ]);
    }
  }

}
