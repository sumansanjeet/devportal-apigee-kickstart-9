<?php

namespace Drupal\Tests\entity_legal\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\WebDriverTestBase;
use Drupal\Tests\entity_legal\Traits\EntityLegalTestTrait;

/**
 * Tests the popup method.
 *
 * @group entity_legal
 */
class EntityLegalPopupMethodTest extends WebDriverTestBase {

  use EntityLegalTestTrait;

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['entity_legal'];

  /**
   * JQuery UI dialog method test.
   */
  public function testPopupMethod(): void {
    $document = $this->createDocument(TRUE, TRUE, [
      'existing_users' => [
        'require_method' => 'popup',
      ],
    ]);
    $this->createDocumentVersion($document, TRUE);

    $account = $this->createUserWithAcceptancePermissions($document);
    $this->drupalLogin($account);

    // Check for the presence of the legal document in the js settings array.
    $js_settings = $this->getDrupalSettings();
    $this->assertTrue(isset($js_settings['entityLegalPopup']));
    $this->assertSame($document->getPublishedVersion()->label(), $js_settings['entityLegalPopup'][0]['popupTitle']);

    $assert = $this->assertSession();
    $assert->waitForButton('Submit');
    $page = $this->getSession()->getPage();
    $page->checkField('I agree to the document');
    $page->pressButton('Submit');

    // Ensure the popup is no longer present.
    $assert->waitForElementRemoved('css', 'input[data-drupal-selector="edit-agree"]');

    // Create a new version.
    $this->createDocumentVersion($document, TRUE);

    // Visit the home page and ensure that the user must re-accept.
    $this->drupalGet('');
    $js_settings = $this->getDrupalSettings();
    $this->assertTrue(isset($js_settings['entityLegalPopup']));
    $this->assertSame($document->getPublishedVersion()->label(), $js_settings['entityLegalPopup'][0]['popupTitle']);
  }

}
