<?php

namespace Drupal\Tests\entity_legal\Functional;

/**
 * Tests methods of encouraging users to accept legal documents.
 *
 * @group entity_legal
 */
class EntityLegalMethodsTest extends EntityLegalTestBase {

  /**
   * Drupal message method test.
   */
  public function testMessageMethod(): void {
    $document = $this->createDocument(TRUE, TRUE, [
      'existing_users' => [
        'require_method' => 'message',
      ],
    ]);
    $this->createDocumentVersion($document, TRUE);

    $acceptance_message = "Please accept the {$document->getPublishedVersion()->label()}";

    $document_url = $document->toUrl();
    $document_path = $document_url->toString();

    $account = $this->createUserWithAcceptancePermissions($document);
    $this->drupalLogin($account);

    $assert = $this->assertSession();

    $assert->pageTextContains($acceptance_message);
    $assert->linkByHrefExists($document_path);

    $this->clickLink($document->getPublishedVersion()->label());
    $assert->fieldValueEquals('agree', NULL);

    $this->submitForm(['agree' => TRUE], 'Submit');

    // @todo Assert checkbox is disabled and acceptance date displayed.
    $assert->pageTextNotContains($acceptance_message);

    $this->createDocumentVersion($document, TRUE);

    $this->drupalGet('');

    $acceptance_message_2 = "Please accept the {$document->getPublishedVersion()->label()}";

    $assert->pageTextContains($acceptance_message_2);
    $assert->linkByHrefExists($document_path);
  }

  /**
   * User signup form with link method test.
   */
  public function testSignupFormLinkMethod(): void {
    $assert = $this->assertSession();
    $document = $this->createDocument(TRUE, TRUE, [
      'new_users' => [
        'require_method' => 'form_link',
      ],
    ]);
    $this->createDocumentVersion($document, TRUE);

    $this->drupalGet('user/register');
    $assert->fieldValueEquals('legal_' . $document->id(), NULL);

    $document_url = $document->toUrl();
    $document_path = $document_url->toString();

    $assert->linkByHrefExists($document_path);

    // Ensure the field extra field is available for re-ordering.
    $this->drupalLogin($this->adminUser);
    $this->drupalGet('admin/config/people/accounts/form-display');
    $assert->responseContains('legal_' . $document->id());
  }

  /**
   * User signup form with inline method test.
   */
  public function testProfileFormInlineMethod(): void {
    $assert = $this->assertSession();
    $document = $this->createDocument(TRUE, TRUE, [
      'new_users' => [
        'require_method' => 'form_inline',
      ],
    ]);
    $this->createDocumentVersion($document, TRUE);

    $this->drupalGet('user/register');
    $assert->fieldValueEquals('legal_' . $document->id(), NULL);

    // Ensure the field extra field is available for re-ordering.
    $this->drupalLogin($this->adminUser);
    $this->drupalGet('admin/config/people/accounts/form-display');
    $assert->responseContains('legal_' . $document->id());
  }

}
