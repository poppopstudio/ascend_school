<?php

namespace Drupal\ascend_school\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides the default form handler for the School entity.
 */
class SchoolForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    /** @var \Drupal\ascend_school\Entity\School $school */
    $school = $this->entity;

    if ($this->operation == 'edit') {
      $form['#title'] = $this->t('<em>Edit @type</em> @title', [
        '@type' => 'school',
        '@title' => $school->label(),
      ]);
    }

    // Emulates entity info behaviour similar to nodes (guess where it's from).
    $form['meta'] = [
      '#type' => 'details',
      '#group' => 'advanced',
      '#weight' => -100,
      '#title' => $this->t('Status'),
      '#attributes' => ['class' => ['entity-meta__header']],
      '#tree' => TRUE,
      '#access' => $this->currentUser()->hasPermission('update any school'),
    ];
    $form['meta']['published'] = [
      '#type' => 'item',
      '#markup' => $school->isPublished() ? $this->t('Published') : $this->t('Not published'),
      // This line seems redundant but the above line doesn't work anyway? Only shows published for either.
      '#access' => !$school->isNew(),
      '#wrapper_attributes' => ['class' => ['entity-meta__title']],
    ];
    $form['meta']['changed'] = [
      '#type' => 'item',
      '#title' => $this->t('Last saved'),
      // '#markup' => !$school->isNew() ? $this->dateFormatter->format($school->getChangedTime(), 'short') : $this->t('Not saved yet'),
      '#markup' => !$school->isNew() ? \Drupal::service('date.formatter')->format($school->getChangedTime(), 'short') : $this->t('Not saved yet'),
      '#wrapper_attributes' => ['class' => ['entity-meta__last-saved']],
    ];
    $form['meta']['author'] = [
      '#type' => 'item',
      '#title' => $this->t('Author'),
      '#markup' => $school->getOwner()->getAccountName(),
      '#wrapper_attributes' => ['class' => ['entity-meta__author']],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $saved = parent::save($form, $form_state);
    $form_state->setRedirectUrl($this->entity->toUrl('canonical'));

    return $saved;
  }

}
