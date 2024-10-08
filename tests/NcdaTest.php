<?php declare(strict_types=1);

use Burgerbibliothek\ArkManagementTools\Ncda;
use PHPUnit\Framework\TestCase;

class NcdaTest extends TestCase
{
    /**
     * Test NOID CHECK DIGIT ALGORITHM.
     * https://metacpan.org/dist/Noid/view/noid#NOID-CHECK-DIGIT-ALGORITHM
     */
    public function test_noid_check_digit_algortihm_works(): void
    {
        
        $id = '13030/xf93gt2';
        $repetoire = 'rp4444kj0f91zbgntw3qsdcm822227v6h5x';
        $illegalRepetoire = '123abc@#_-';

        $checkdigit = Ncda::calc($id, $repetoire);
        $this->assertSame('q', $checkdigit);

        $verifyNoid = Ncda::verify($id.$checkdigit, $repetoire);
        $this->assertTrue($verifyNoid);

        $checkIllegealRepetoire = Ncda::calc($id, $illegalRepetoire);
        $this->assertFalse($checkIllegealRepetoire);
        
    }
}
