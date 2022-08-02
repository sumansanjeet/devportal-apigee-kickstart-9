<?php

namespace Drupal\entity_legal\Form;

use Drupal\Core\Entity\ContentEntityConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Url;

/**
 * Provides a form for deleting a entity_legal_document_version entity.
 *
 * @ingroup entity_legal
 */
class EntityLegalDocumentVersionDeleteForm extends ContentEntityConfirmFormBase {

  /**
   * {@inheritdoc}
   */
  public function getQuestion(): TranslatableMarkup {
    return $this->t('Are you sure you want to delete the legal document version %name?', ['%name' => $this->entity->label()]);
  }

  /**
   * {@inheritdoc}
   *
   * If the delete command is canceled, return to the legal document versions.
   */
  public function getCancelUrl(): Url {
    return new Url('entity.entity_legal_document.edit_form', [
      'entity_legal_document' => $this->entity->document_name->target_id,
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText(): TranslatableMarkup {
    return $this->t('Delete');
  }

  /**
   * {@inheritdoc}
   *
   * Delete the entity and log the event. logger() replaces the watchdog.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $entity = $this->getEntity();
    $entity->delete();

    $this->messenger()->addStatus($this->t('%title document version has been deleted from @type.',
      [
        '@type' => $this->entity->bundle(),
        '%title' => $this->entity->label(),
      ]));

    $form_state->setRedirect('entity.entity_legal_document.edit_form', [
      'entity_legal_document' => $this->entity->document_name->target_id,
    ]);
  }

}
