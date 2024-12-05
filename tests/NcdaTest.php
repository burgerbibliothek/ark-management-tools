<?php declare(strict_types=1);

use Burgerbibliothek\ArkManagementTools\Ncda;
use PHPUnit\Framework\TestCase;

class NcdaTest extends TestCase
{
    
    public $checkZone = '13030/xf93gt2';
    public $xdigits = '0123456789bcdfghjkmnpqrstvwxz000';
    /**
     * Test NOID CHECK DIGIT ALGORITHM.
     * https://metacpan.org/dist/Noid/view/noid#NOID-CHECK-DIGIT-ALGORITHM
     */
    public function test_noid_check_digit_algortihm_works(): void
    {
        
        $checkdigit = Ncda::calc($this->checkZone, $this->xdigits);
        $this->assertSame('q', $checkdigit, 'NCDA failed.');

        $this->assertTrue(Ncda::verify($this->checkZone.$checkdigit, $this->xdigits), 'Failed to verify that the NCDA is true.');

    }

    public function test_noid_check_digit_algortihm_length_exception(): void
    {
        $this->expectException(Exception::class);
        Ncda::calc($this->checkZone.'-._$@', $this->xdigits);
    }

    public function test_noid_check_digit_algortihm_well_formedness(): void
    {
        $this->expectException(Exception::class);
        Ncda::calc($this->checkZone.'-._$@', $this->xdigits);
    }
}
