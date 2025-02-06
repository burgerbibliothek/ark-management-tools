<?php declare(strict_types=1);

use Burgerbibliothek\ArkManagementTools\Ark;
use Burgerbibliothek\ArkManagementTools\Ncda;
use Burgerbibliothek\ArkManagementTools\Validator;
use PHPUnit\Framework\TestCase;

class ArkTest extends TestCase
{
    /**
     * Test ARK generator
     */
    public function test_ark_generator(): void
    {

        /** Define NAAN and character repetoire */
        $naan = '12345';
        $xdigits = '0123456789abcdefghijklmnopqrstuvwxyz=~*+@_$';
        $shoulder = 'x1';

        /** Follows the NAAN character repetoire */
        $this->assertTrue(Validator::followsNaanCharacterRepetoire($naan), 'Illegal characters detected.');

        /** Follows the ARK character repetoire */
        $this->assertTrue(Validator::followsArkCharacterRepetoire($xdigits), 'Illegal characters detected.');

        /** Shoulder in xdigits */
        $this->assertTrue(Validator::shoulderInXdigits($shoulder, $xdigits), 'Illegal characters detected.');

        /** Generate ARK */
        $ark = Ark::generate($naan, $xdigits, 5, shoulder: $shoulder, ncda: true);
        $components = Ark::splitIntoComponents($ark);
        
        /** Length of ARK */
        $this->assertEquals(18, strlen($ark), 'ARK has not the expected length.');

        /** Check the character repetoire */
        $this->assertTrue(Validator::isValidBaseCompactName($components['baseCompactName']), 'Illegal characters detected.');

        /** Shoulder is prepended */
        $this->assertSame('x1', substr($components['baseName'], 0, 2), 'Something is wrong with the shoulder.');

        /** NCDA */
        $this->assertTrue(Ncda::verify($components['checkZone'], $xdigits), 'NCDA failed.');

        /** Define NAAN and character repetoire */
        $naan = '99999';
        $xdigits = '0123456789';

        /** Follows the NAAN character repetoire */
        $this->assertTrue(Validator::followsNaanCharacterRepetoire($naan), 'Illegal characters detected.');

        /** Follows the ARK character repetoire */
        $this->assertTrue(Validator::followsArkCharacterRepetoire($xdigits, true), 'Illegal characters detected.');

        /** Generate ARK with / in label */
        $ark = Ark::generate($naan, $xdigits, 10, ncda: false, slashAfterLabel: true);
        $components = Ark::splitIntoComponents($ark);

        /** Length of ARK */
        $this->assertEquals(21, strlen($ark), 'ARK has not the expected length.');

        /** Check the character repetoire */
        $this->assertTrue(Validator::isValidBaseCompactName($components['baseCompactName']), 'Illegal characters detected.');

        /** Check normalization **/
        $ark = Ark::normalize(' Ar K:/ABC123/abc-def-—123-567%c3%b6/xyz/?query=www    ');
        $this->assertEquals($ark, 'ark:abc123/abcdef123567%C3%B6/xyz', 'Normalization did not work.');


    }

}
