<?php

namespace Drupal\Tests\entity_legal\Kernel;

use Drupal\entity_legal\Controller\EntityLegalController;
use Drupal\entity_legal\Entity\EntityLegalDocument;
use Drupal\entity_legal\Entity\EntityLegalDocumentVersion;
use Drupal\KernelTests\KernelTestBase;

/**
 * Tests the 'entity_legal_document_version' entity type.
 *
 * @group entity_legal
 */
class DocumentDeleteTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'entity_legal',
    'field',
    'system',
    'text',
    'user',
  ];

  /**
   * Tests the canonical route title callback.
   */
  public function testDeleteDocumentVersion() {
    $class_resolver = $this->container->get('class_resolver');

    $this->installConfig(['entity_legal']);
    $this->installEntitySchema(ENTITY_LEGAL_DOCUMENT_VERSION_ENTITY_NAME);
    $this->installEntitySchema(ENTITY_LEGAL_DOCUMENT_ACCEPTANCE_ENTITY_NAME);

    /** @var \Drupal\entity_legal\Controller\EntityLegalController $controller */
    $controller = $class_resolver->getInstanceFromDefinition(EntityLegalController::class);

    $document = EntityLegalDocument::create([
      'id' => 'new_notice',
      'label' => 'New notice',
    ]);
    $document->save();

    $version = EntityLegalDocumentVersion::create([
      'document_name' => $document->id(),
      'label' => 'v1.0.0',
    ]);
    $version->validate();

    $version_2 = EntityLegalDocumentVersion::create([
      'document_name' => $document->id(),
      'label' => 'v2.0.0',
    ]);
    $version_2->validate();

    $document->setPublishedVersion($version);
    $settings = $document->get('settings');

    // Check that the default title pattern [entity_legal_document:label].
    $title = $controller->documentPageTitle($document);
    $this->assertEquals('New notice', $title);

    // Check a custom pattern.
    $settings['title_pattern'] = '[entity_legal_document:label] (version [entity_legal_document:published-version:label])';
    $document->set('settings', $settings)->save();
    $title = $controller->documentPageTitle($document);
    $this->assertEquals('New notice (version v1.0.0)', $title);

    // Delete version 1.
    $doc_v1 = EntityLegalDocumentVersion::load($version->id());
    $doc_v1->delete();

    $document->setPublishedVersion($version_2);
    $title = $controller->documentPageTitle($document);
    $this->assertEquals('New notice (version v2.0.0)', $title);
  }

}
