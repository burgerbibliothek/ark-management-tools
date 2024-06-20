<?php declare(strict_types=1);

use Burgerbibliothek\ArkManagementTools\Ark;
use PHPUnit\Framework\TestCase;

class ArkTest extends TestCase
{
    /**
     * Test ARK generator
     */
    public function test_ark_generator(): void
    {
        
        $ark = Ark::generate('12345', '0123456789abcdefghijklmnopqrstuvwxyz=~*+@_$', 5, 'x1', true);
        
        /** Length of ARK */
        $this->assertEquals(14, strlen($ark), 'ARK has not the expected length.');
        /** NAAN is prepended */
        $this->assertSame('12345/', substr($ark, 0, 6), 'Something is wrong with the NAAN.');
        /** Shoulder is prepended */
        $this->assertSame('x1', substr($ark, 6, 2), 'Something is wrong with the shoulder.');

        
        $ark_only_assigned_name = Ark::generate('12345', '0123456789abcdefghijklmnopqrstuvwxyz=~*+@_$', 5, null, false);
        /** Lenght of ARK */
        $this->assertEquals(11, strlen($ark_only_assigned_name), 'ARK has not the expected length.');
        
    }

}
