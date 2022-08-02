<?php

namespace Drupal\Tests\entity_legal\Functional;

use Drupal\entity_legal\Entity\EntityLegalDocument;

/**
 * Tests admin functionality for the legal document entity.
 *
 * @group entity_legal
 */
class EntityLegalDocumentTest extends EntityLegalTestBase {

  /**
   * Test the overview page contains a list of entities.
   */
  public function testAdminOverviewUi(): void {
    // Create 3 legal documents.
    $documents = [];
    for ($i = 0; $i < 3; $i++) {
      $documents[] = $this->createDocument();
    }
    $this->drupalLogin($this->adminUser);
    $this->drupalGet('admin/structure/legal');

    $assert = $this->assertSession();

    /** @var \Drupal\entity_legal\Entity\EntityLegalDocument $document */
    foreach ($documents as $document) {
      $assert->responseContains($document->label());
      $assert->linkByHrefExists('/admin/structure/legal/manage/' . $document->id());
    }

    $assert->linkByHrefExists('/admin/structure/legal/add');
  }

  /**
   * Test the functionality of the create form.
   */
  public function testCreateForm(): void {
    $test_label = $this->randomMachineName();
    $test_id = $this->randomMachineName();

    $this->drupalLogin($this->adminUser);
    $this->drupalGet('admin/structure/legal/add');
    $this->submitForm([
      'label' => $test_label,
      'id' => $test_id,
      'settings[new_users][require]' => 1,
      'settings[new_users][require_method]' => 'form_inline',
      'settings[existing_users][require]' => 1,
      'settings[existing_users][require_method]' => 'redirect',
    ], 'Save');

    /** @var \Drupal\entity_legal\EntityLegalDocumentInterface $created_document */
    $created_document = EntityLegalDocument::load($test_id);

    $this->assertNotNull($created_document);

    if ($created_document) {
      $this->assertSame($test_label, $created_document->label());
      $this->assertSame($test_id, $created_document->id());
      $this->assertTrue($created_document->get('require_signup'));
      $this->assertTrue($created_document->get('require_existing'));
      $this->assertSame('form_inline', $created_document->get('settings')['new_users']['require_method']);
      $this->assertSame('redirect', $created_document->get('settings')['existing_users']['require_method']);
    }
  }

  /**
   * Test the functionality of the edit form.
   */
  public function testEditForm(): void {
    $document = $this->createDocument(TRUE, TRUE, [
      'new_users' => [
        'require_method' => 'form_inline',
      ],
      'existing_users' => [
        'require_method' => 'redirect',
      ],
    ]);

    $this->drupalLogin($this->adminUser);
    $this->drupalGet('admin/structure/legal/manage/' . $document->id());

    $assert = $this->assertSession();

    // Test field default values.
    $assert->fieldValueEquals('label', $document->label());
    $assert->fieldValueEquals('settings[new_users][require]', 1);
    $assert->fieldValueEquals('settings[new_users][require_method]', 'form_inline');
    $assert->fieldValueEquals('settings[existing_users][require]', 1);
    $assert->fieldValueEquals('settings[existing_users][require_method]', 'redirect');

    // Test that changing values saves correctly.
    $new_label = $this->randomMachineName();
    $this->submitForm([
      'label' => $new_label,
      'settings[new_users][require]' => FALSE,
      'settings[new_users][require_method]' => 'form_link',
      'settings[existing_users][require]' => FALSE,
      'settings[existing_users][require_method]' => 'popup',
    ], 'Save');

    $document = EntityLegalDocument::load($document->id());

    $this->assertSame($new_label, $document->label());
    $this->assertFalse($document->get('require_signup'));
    $this->assertFalse($document->get('require_existing'));
    $this->assertSame('form_link', $document->get('settings')['new_users']['require_method']);
    $this->assertSame('popup', $document->get('settings')['existing_users']['require_method']);
  }

  /**
   * Test the functionality of the delete form.
   */
  public function testDeleteForm(): void {
    $document = $this->createDocument();

    $document_name = $document->id();

    // Log in and check for existence of the created document.
    $this->drupalLogin($this->adminUser);
    $this->drupalGet('admin/structure/legal');
    $assert = $this->assertSession();

    $assert->responseContains($document_name);

    // Delete the document.
    $this->drupalGet('admin/structure/legal/manage/' . $document_name . '/delete');
    $this->submitForm([], 'Delete');

    // Ensure document no longer exists on the overview page.
    $assert->addressEquals('admin/structure/legal');
    $assert->pageTextNotContains($document_name);
  }

}
