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

    public function test_ark_split_into_components(): void
    {
        
        /** components with resolverService */
        $ark_w_nma = 'https://example.tld/ark:/99999/a1b2c3d4e5f6g/suffix?info';
        $ark_w_nma_components = [
            'resolverService' => 'https://example.tld',
            'naan' => '99999',
            'baseName' => 'a1b2c3d4e5f6g',
            'baseCompactName' => 'ark:99999/a1b2c3d4e5f6g',
            'checkZone' => '99999/a1b2c3d4e5f6g',
            'suffixes' => 'suffix?info'
        ];

        $ark_w_nma = Ark::splitIntoComponents($ark_w_nma);
        $this->assertEquals($ark_w_nma, $ark_w_nma_components);

        /** components without resolverService */
        $ark = 'ark:/99999/a1b2c3d4e5f6g/suffix?info';
        $ark_components = [
            'resolverService' => '',
            'naan' => '99999',
            'baseName' => 'a1b2c3d4e5f6g',
            'baseCompactName' => 'ark:99999/a1b2c3d4e5f6g',
            'checkZone' => '99999/a1b2c3d4e5f6g',
            'suffixes' => 'suffix?info'
        ];

        $ark = Ark::splitIntoComponents($ark);
        $this->assertEquals($ark_components, $ark);

        /** components without resolverService */
        $scrambled_url = 'ftp:/exampleark:/99999/a1b2c3d4e5f6g/suffix?info';
        $scrambled_url_components = [
            'resolverService' => '',
            'naan' => '99999',
            'baseName' => 'a1b2c3d4e5f6g',
            'baseCompactName' => 'ark:99999/a1b2c3d4e5f6g',
            'checkZone' => '99999/a1b2c3d4e5f6g',
            'suffixes' => 'suffix?info'
        ];

        $scrambled_url = Ark::splitIntoComponents($scrambled_url);
        $this->assertEquals($scrambled_url_components, $scrambled_url);

        /** no components */
        $invalid_ark = 'ftp:/examplea/rk:/99999/a1bark2c3dark:4e5f6g/suffix?info';
        $invalid_ark_components = [
            'resolverService' => '',
            'naan' => '',
            'baseName' => '',
            'baseCompactName' => '',
            'checkZone' => '',
            'suffixes' => ''
        ];

        $invalid_ark = Ark::splitIntoComponents($invalid_ark);
        $this->assertEquals($invalid_ark_components, $invalid_ark);

    }

}
