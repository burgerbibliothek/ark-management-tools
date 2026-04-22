<?php

declare(strict_types=1);

use Burgerbibliothek\ArkManagementTools\Validator;
use PHPUnit\Framework\TestCase;

class ValidatorTest extends TestCase
{
	/**
	 * Test Validator
	 */
	public function test_validator(): void
	{

		$xdigits = '0123456789abcdefghijklmnopqrstuvwxyz=~*+@_$%-.\/';
		$label = 'ark:';
		$naan = '12345';
		$shoulder = 'x0';
		$baseName = '783hrh89$u';
		$checkZone = $naan . '/' . $shoulder . $baseName;
		$baseCompactName = $label . $naan . '/' . $shoulder . $baseName;

		$this->assertTrue(Validator::followsNaanCharacterRepetoire($naan), 'Validation of character repetoire failed.');
		$this->assertFalse(Validator::followsNaanCharacterRepetoire($naan . '$'), 'Validation of character repetoire failed.');

		$this->assertTrue(Validator::followsArkCharacterRepetoire($xdigits), 'Validation of character repetoire failed.');
		$this->assertFalse(Validator::followsArkCharacterRepetoire($xdigits, false), 'Validation of character repetoire failed.');
		$this->assertFalse(Validator::followsArkCharacterRepetoire($xdigits . 'äöü'), 'Validation of character repetoire failed.');

		$this->assertTrue(Validator::isValidBaseCompactName($baseCompactName), 'Failed to assert that $baseCompactName is valid.');
		$this->assertFalse(Validator::isValidBaseCompactName($baseCompactName . '/'), 'Failed to assert that $baseCompactName is invalid.');

		$this->assertTrue(Validator::isValidCheckZone($checkZone), 'Failed to assert that $checkZone is valid.');
		$this->assertFalse(Validator::isValidCheckZone($checkZone . '9999/abcdef'), 'Failed to assert that $checkZone is invalid.');
		$this->assertFalse(Validator::isValidCheckZone($checkZone . 'ark:99999/abcdef'), 'Failed to assert that $checkZone is invalid.');

	}
}
