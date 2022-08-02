<?php

namespace Drupal\Tests\entity_legal\Functional;

use Drupal\entity_legal\Entity\EntityLegalDocument;
use Drupal\entity_legal\Entity\EntityLegalDocumentVersion;

/**
 * Tests admin functionality for the legal document version entity.
 *
 * @group entity_legal
 */
class EntityLegalDocumentVersionTest extends EntityLegalTestBase {

  /**
   * Test the overview page contains a list of entities.
   */
  public function testAdminOverviewUi(): void {
    // Create a document.
    $document = $this->createDocument();

    // Create 3 documents versions.
    $versions = [];
    for ($i = 0; $i < 3; $i++) {
      $version = $this->createDocumentVersion($document);
      $versions[] = $version;
    }
    $this->drupalLogin($this->adminUser);
    $this->drupalGet('admin/structure/legal/manage/' . $document->id());

    $assert = $this->assertSession();

    /** @var \Drupal\entity_legal\EntityLegalDocumentVersionInterface $version */
    foreach ($versions as $version) {
      $assert->responseContains($version->label());
      $assert->linkByHrefExists('/admin/structure/legal/document/' . $version->id() . '/edit');
    }
  }

  /**
   * Test the functionality of the create form.
   */
  public function testCreateForm(): void {
    $document = $this->createDocument();

    $test_label = $this->randomMachineName();
    $document_text = $this->randomMachineName();
    $acceptance_label = $this->randomMachineName();

    $this->drupalLogin($this->adminUser);
    $this->drupalGet('admin/structure/legal/manage/' . $document->id() . '/add');
    $this->submitForm([
      'label' => $test_label,
      'entity_legal_document_text[0][value]' => $document_text,
      'acceptance_label' => $acceptance_label,
    ], 'Save');

    $document = EntityLegalDocument::load($document->id());

    $versions = $document->getAllVersions();
    /** @var \Drupal\entity_legal\EntityLegalDocumentVersionInterface $created_version */
    $created_version = reset($versions);

    $this->assertTrue(!empty($created_version), 'Document version was successfully created');

    $this->drupalGet('admin/structure/legal/manage/' . $document->id());

    $assert = $this->assertSession();

    $assert->pageTextContains($test_label);
    if ($created_version) {
      $this->assertSame($test_label, $created_version->label());
      $this->assertSame($acceptance_label, $created_version->get('acceptance_label')->value);
      $this->assertSame($document_text, $created_version->get('entity_legal_document_text')[0]->value);
      $this->assertSame($document->id(), $created_version->bundle());
      $this->assertSame($document->getPublishedVersion()->id(), $created_version->id());
    }
  }

  /**
   * Test the functionality of the edit form.
   */
  public function testEditForm(): void {
    $assert = $this->assertSession();
    $document = $this->createDocument();
    $version = $this->createDocumentVersion($document);

    $this->drupalLogin($this->adminUser);
    $this->drupalGet('admin/structure/legal/document/' . $version->id() . '/edit');

    // Test field default values.
    $assert->fieldValueEquals('label', $version->label());
    $assert->fieldValueEquals('entity_legal_document_text[0][value]', $version->get('entity_legal_document_text')[0]->value);
    $assert->fieldValueEquals('acceptance_label', $version->get('acceptance_label')->value);

    // Test that changing values saves correctly.
    $new_label = $this->randomMachineName();
    $new_text = $this->randomMachineName();
    $new_acceptance_label = $this->randomMachineName();

    $this->submitForm([
      'label' => $new_label,
      'entity_legal_document_text[0][value]' => $new_text,
      'acceptance_label' => $new_acceptance_label,
    ], 'Save');

    $version = EntityLegalDocumentVersion::load($version->id());
    $this->assertSame($new_label, $version->label());
    $this->assertSame($new_text, $version->get('entity_legal_document_text')[0]->value);
    $this->assertSame($new_acceptance_label, $version->get('acceptance_label')->value);
  }

}
