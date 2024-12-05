<?php declare(strict_types=1);

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
    	$baseCompactName = $label.$naan.'/'.$shoulder.$baseName;

    	/** Test conformance for NAAN character repetoire. */
    	$this->assertTrue(Validator::followsNaanCharacterRepetoire($naan), 'Validation of character repetoire failed.');

    	$this->assertFalse(Validator::followsNaanCharacterRepetoire($naan.'$'), 'Validation of character repetoire failed.');

    	/** Test for conformance w/ ARK character repetoire. */
    	$this->assertTrue(Validator::followsArkCharacterRepetoire($xdigits), 'Validation of character repetoire failed.');      
        
        $this->assertFalse(Validator::followsArkCharacterRepetoire($xdigits, false), 'Validation of character repetoire failed.');

        $this->assertFalse(Validator::followsArkCharacterRepetoire($xdigits.'äöü'), 'Validation of character repetoire failed.');

        /** Test validity of BaseCompactName. */
    	$this->assertTrue(Validator::isValidBaseCompactName($baseCompactName), 'Failed to assert that $baseCompactName is valid.');

    	$this->assertFalse(Validator::isValidBaseCompactName($baseCompactName.'/'), 'Failed to assert that $baseCompactName is invalid.');

    	/** Test validity of BaseCompactName. */
    	$this->assertTrue(Validator::shoulderInXdigits($shoulder, $xdigits), 'Failed to assert $shoulder is in $xdigits.');

    	$this->assertFalse(Validator::shoulderInXdigits($shoulder.'ö', $xdigits), 'Failed to assert $shoulder is not in $xdigits.');
    	
    }
}
