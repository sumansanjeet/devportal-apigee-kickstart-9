<?php

namespace Drupal\entity_legal\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Session\AccountInterface;
use Drupal\entity_legal\EntityLegalDocumentVersionInterface;

/**
 * Defines the entity legal document version entity.
 *
 * @ContentEntityType(
 *   id = "entity_legal_document_version",
 *   label = @Translation("Legal document version"),
 *   handlers = {
 *     "access" = "Drupal\Core\Entity\EntityAccessControlHandler",
 *     "storage" = "Drupal\Core\Entity\Sql\SqlContentEntityStorage",
 *     "view_builder" = "Drupal\entity_legal\EntityLegalDocumentVersionViewBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "form" = {
 *       "default" = "Drupal\entity_legal\Form\EntityLegalDocumentVersionForm",
 *       "edit" = "Drupal\entity_legal\Form\EntityLegalDocumentVersionForm",
 *       "delete" = "Drupal\entity_legal\Form\EntityLegalDocumentVersionDeleteForm",
 *     },
 *     "translation" = "Drupal\entity_legal\EntityLegalDocumentVersionTranslationHandler"
 *   },
 *   admin_permission = "administer entity legal",
 *   base_table = "entity_legal_document_version",
 *   data_table = "entity_legal_document_version_data",
 *   field_ui_base_route = "entity.entity_legal_document.edit_form",
 *   translatable = TRUE,
 *   entity_keys = {
 *     "id" = "name",
 *     "label" = "label",
 *     "langcode" = "langcode",
 *     "uuid" = "uuid",
 *     "bundle" = "document_name"
 *   },
 *   links = {
 *     "delete-form" = "/admin/structure/legal/document/{entity_legal_document_version}/delete",
 *     "canonical" = "/admin/structure/legal/document/{entity_legal_document_version}",
 *     "edit-form" = "/admin/structure/legal/document/{entity_legal_document_version}/edit",
 *   },
 *   bundle_entity_type = "entity_legal_document",
 *   field_ui_base_route = "entity.entity_legal_document.edit_form",
 *   render_cache = FALSE,
 * )
 */
class EntityLegalDocumentVersion extends ContentEntityBase implements EntityLegalDocumentVersionInterface {

  use EntityChangedTrait;

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t('The entity ID of this document.'))
      ->setReadOnly(TRUE)
      ->setSetting('max_length', 64)
      ->setSetting('unsigned', TRUE)
      ->setDefaultValueCallback('Drupal\entity_legal\Entity\EntityLegalDocumentVersion::getDefaultName');

    $fields['published'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Published'))
      ->setDescription(t('If this is the published version of the legal document.'))
      ->setRequired(TRUE)
      ->setDefaultValue(FALSE)
      ->setCardinality(1)
      ->setConstraints(['SingleLegalDocumentPublishedVersion' => []])
      ->setInitialValue(FALSE);

    $fields['langcode'] = BaseFieldDefinition::create('language')
      ->setLabel(t('Language'))
      ->setDescription(t('The document version language code.'))
      ->setTranslatable(TRUE);

    $fields['uuid'] = BaseFieldDefinition::create('uuid')
      ->setLabel(t('UUID'))
      ->setDescription(t('The entity UUID of this document'))
      ->setReadOnly(TRUE);

    $fields['document_name'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Form ID'))
      ->setDescription(t('The name of the document this version is bound to.'))
      ->setSetting('target_type', ENTITY_LEGAL_DOCUMENT_ENTITY_NAME)
      ->setRequired(TRUE);

    $fields['label'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Label'))
      ->setDescription(t('The title of the document.'))
      ->setTranslatable(TRUE)
      ->setRequired(TRUE);

    $fields['acceptance_label'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Acceptance label'))
      ->setTranslatable(TRUE)
      ->setRequired(TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The date the document was created.'))
      ->setTranslatable(TRUE)
      ->setRequired(TRUE);

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The date the document was changed.'))
      ->setTranslatable(TRUE)
      ->setRequired(TRUE);

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function delete() {
    if (!$this->isNew()) {
      // Delete all acceptances.
      $acceptances = $this->getAcceptances();
      foreach ($acceptances as $acceptance) {
        $acceptance->delete();
      }
    }

    parent::delete();
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public static function getDefaultName(EntityLegalDocumentVersionInterface $entity) {
    return $entity->bundle() . '_' . time();
  }

  /**
   * {@inheritdoc}
   */
  public function getFormattedDate($type = 'changed') {
    switch ($type) {
      case 'changed':
        return \Drupal::service('date.formatter')
          ->format($this->getChangedTime());

      case 'created':
        return \Drupal::service('date.formatter')
          ->format($this->getCreatedTime());
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getAcceptances(AccountInterface $account = NULL) {
    $query = \Drupal::entityQuery(ENTITY_LEGAL_DOCUMENT_ACCEPTANCE_ENTITY_NAME)
      ->condition('document_version_name', $this->id());

    if ($account) {
      $query->condition('uid', $account->id());
    }

    $results = $query->execute();
    if (!empty($results)) {
      return \Drupal::entityTypeManager()
        ->getStorage(ENTITY_LEGAL_DOCUMENT_ACCEPTANCE_ENTITY_NAME)
        ->loadMultiple($results);
    }

    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getDocument() {
    return \Drupal::entityTypeManager()
      ->getStorage(ENTITY_LEGAL_DOCUMENT_ENTITY_NAME)
      ->load($this->bundle());
  }

  /**
   * {@inheritdoc}
   */
  protected function urlRouteParameters($rel) {
    $uri_route_parameters = parent::urlRouteParameters($rel);

    if (in_array($rel, [
      'canonical',
      'edit-form',
      'entity_hierarchy_reorder',
      'token-devel',
      'drupal:content-translation-overview',
      'drupal:content-translation-add',
      'drupal:content-translation-edit',
      'drupal:content-translation-delete',
    ])) {
      $uri_route_parameters['entity_legal_document'] = $this->bundle();
    }

    return $uri_route_parameters;
  }

  /**
   * {@inheritdoc}
   */
  public function isPublished() {
    return (bool) $this->get('published')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function publish() {
    $this->set('published', TRUE);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function unpublish() {
    $this->set('published', FALSE);
    return $this;
  }

}
