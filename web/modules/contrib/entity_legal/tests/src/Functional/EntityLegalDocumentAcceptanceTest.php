<?php

namespace Drupal\Tests\entity_legal\Functional;

use Drupal\entity_legal\Entity\EntityLegalDocument;

/**
 * Tests acceptance functionality for the legal document entity.
 *
 * @group entity_legal
 */
class EntityLegalDocumentAcceptanceTest extends EntityLegalTestBase {

  /**
   * Test that user has the ability to agree to legal documents.
   */
  public function testSubmissionForm(): void {
    $document = $this->createDocument(TRUE, TRUE);
    $version = $this->createDocumentVersion($document, TRUE);

    $acceptance_user = $this->drupalCreateUser([
      $document->getPermissionView(),
      $document->getPermissionExistingUser(),
    ]);

    $this->drupalLogin($acceptance_user);

    $document_url = $document->toUrl();

    $assert = $this->assertSession();
    $this->drupalGet($document_url);
    $assert->fieldValueEquals('agree', NULL);
    $assert->buttonExists('Submit');
    $assert->pageTextContains($document->label());
    $assert->pageTextContains($version->get('entity_legal_document_text')[0]->value);

    $this->drupalGet($document_url);
    $this->submitForm(['agree' => 1], 'Submit');

    $this->drupalGet($document_url);
    // @todo Assert checkbox is disabled and acceptance date displayed.
    // $this->assertNoFieldByName('agree', NULL, 'Agree checkbox not found');
    $assert->buttonNotExists('Submit');

    $document = EntityLegalDocument::load($document->id());
    $this->assertTrue($document->userHasAgreed($acceptance_user));

    $new_version = $this->createDocumentVersion($document, TRUE);

    $document = EntityLegalDocument::load($document->id());
    $this->assertFalse($document->userHasAgreed($acceptance_user));

    $this->drupalGet($document_url);
    $assert->fieldValueEquals('agree', NULL);
    $assert->buttonExists('Submit');
    $assert->pageTextContains($document->label());
    $assert->pageTextContains($new_version->get('entity_legal_document_text')[0]->value);

    $this->submitForm([
      'agree' => 1,
    ], 'Submit');

    $this->drupalGet($document_url);
    // @todo Assert checkbox is disabled and acceptance date displayed.
    // $this->assertNoFieldByName('agree', NULL, 'Agree checkbox not found');
    $assert->buttonNotExists('Submit');

    $document = EntityLegalDocument::load($document->id());
    $this->assertTrue($document->userHasAgreed($acceptance_user));

    $acceptance_storage = \Drupal::entityTypeManager()->getStorage(ENTITY_LEGAL_DOCUMENT_ACCEPTANCE_ENTITY_NAME);

    // Check that removing a document version deletes all related acceptances.
    $version->delete();
    $aid_count = $acceptance_storage->getQuery()
      ->condition('uid', $acceptance_user->id())
      ->execute();
    $this->assertCount(1, $aid_count);

    // Check that removing a user deletes all its acceptance records.
    $acceptance_user->delete();
    $aid_count = $acceptance_storage->getQuery()
      ->condition('uid', $acceptance_user->id())
      ->execute();
    $this->assertEmpty($aid_count);
  }

}
