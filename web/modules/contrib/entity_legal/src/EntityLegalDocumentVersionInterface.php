<?php

namespace Drupal\entity_legal;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Provides an interface defining a entity legal document version entity.
 */
interface EntityLegalDocumentVersionInterface extends ContentEntityInterface, EntityChangedInterface {

  /**
   * Get the acceptances for this entity legal document version.
   *
   * @param \Drupal\Core\Session\AccountInterface|null $account
   *   The Drupal user account to check for, or get all acceptances if NULL.
   *
   * @return array
   *   The acceptance entities keyed by acceptance id.
   */
  public function getAcceptances(AccountInterface $account = NULL);

  /**
   * Gets the legal document version creation timestamp.
   *
   * @return int
   *   Creation timestamp of the legal document version.
   */
  public function getCreatedTime();

  /**
   * Get the default document version name value.
   *
   * @param EntityLegalDocumentVersionInterface $entity
   *   The Entity legal document version entity.
   *
   * @return string
   *   The default document version name.
   */
  public static function getDefaultName(EntityLegalDocumentVersionInterface $entity);

  /**
   * Get attached document entity.
   *
   * @return EntityLegalDocumentInterface
   *   The attached document entity.
   */
  public function getDocument();

  /**
   * Get the date for a given entity property.
   *
   * @param string $type
   *   The type of date to retrieve, updated or created.
   *
   * @return string
   *   The formatted date.
   */
  public function getFormattedDate($type = 'changed');

  /**
   * Checks if this version is the legal document published version.
   *
   * @return bool
   *   If this the legal document published version.
   */
  public function isPublished();

  /**
   * Publish this version.
   *
   * @return $this
   */
  public function publish();

  /**
   * Unpublish this version.
   *
   * @return $this
   */
  public function unpublish();

}
