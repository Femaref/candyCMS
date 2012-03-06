<?php

/**
 * PHP unit tests
 *
 * @link http://github.com/marcoraddatz/candyCMS
 * @author Marco Raddatz <http://marcoraddatz.com>
 * @license MIT
 * @since 2.0
 *
 */

require_once PATH_STANDARD . '/app/controllers/Index.controller.php';

use \CandyCMS\Controller\Index as Index;

class UnitTestOfIndexController extends CandyUnitTest {

	function setUp() {
		$this->oObject = new Index($this->aRequest, $this->aSession);
	}

	function tearDown() {
		parent::tearDown();
	}

	function testGetConfigFiles() {
		$this->assertTrue(file_exists(PATH_STANDARD . '/config/Candy.inc.php'), 'Candy.inc.php exists.');
		$this->assertTrue(file_exists(PATH_STANDARD . '/config/Plugins.inc.php'), 'Plugins.inc.php exists.');
		$this->assertTrue(file_exists(PATH_STANDARD . '/config/Mailchimp.inc.php'), 'Plugins.inc.php exists.');
		$this->assertTrue($this->oObject->getConfigFiles(array('Candy', 'Plugins', 'Mailchimp')));
	}

	function testGetPlugins() {
		$this->assertTrue($this->oObject->getPlugins(ALLOW_PLUGINS));
	}

	function testGetLanguage() {
		$this->assertIsA($this->oObject->getLanguage(), 'string');
	}

	function testSetTemplate() {
		$this->assertIsA($this->oObject->setTemplate(), 'string');
	}

	function testUploadDirIsWritable() {
		$this->assertTrue(parent::createFile('upload'));
		$this->assertTrue(unlink(PATH_STANDARD . '/upload/test.log'));
	}

	function testCacheDirIsWritable() {
		$this->assertTrue(parent::createFile('cache'));
		$this->assertTrue(unlink(PATH_STANDARD . '/cache/test.log'));
	}

	function testCompileDirIsWritable() {
		$this->assertTrue(parent::createFile('compile'));
		$this->assertTrue(unlink(PATH_STANDARD . '/compile/test.log'));
	}

	function testBackupDirIsWritable() {
		$this->assertTrue(parent::createFile('backup'));
		$this->assertTrue(unlink(PATH_STANDARD . '/backup/test.log'));
	}

	function testLogsDirIsWritable() {
		$this->assertTrue(parent::createFile('logs'));
		$this->assertTrue(unlink(PATH_STANDARD . '/logs/test.log'));
	}
}

class WebTestOfIndexController extends CandyWebTest {

	function setUp() {
		$this->oObject = new Index($this->aRequest, $this->aSession);
	}

	function tearDown() {
		parent::tearDown();
	}

	function testShowIndex() {
		$this->assertTrue($this->get(WEBSITE_URL));
		$this->assertResponse(200);
		$this->assertText('Login'); # This should be on every page.
	}

	function testShowNonExistingPage() {
		$this->assertTrue($this->get(WEBSITE_URL . '/' . md5(RANDOM_HASH)));
		$this->assertResponse(404);
	}

	function testShowSampleAddon() {
		$this->assertTrue($this->get(WEBSITE_URL . '/sample'));
		$this->assertResponse(200);
		$this->assertText('Sample');
		$this->assertNoText('Error');
	}
}