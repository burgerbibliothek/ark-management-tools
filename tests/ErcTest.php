<?php

declare(strict_types=1);

use Burgerbibliothek\ArkManagementTools\Erc;
use PHPUnit\Framework\TestCase;

class ErcTest extends TestCase
{
    
    /**
     * Test Validation of ERC records.
     */
    public function test_erc_record_validation(): void
    {
        $valid_data = __DIR__ . '/ERCsamples-valid.txt';
        $valid_content = preg_split('/\n{2}|(\n\r){2}/', file_get_contents($valid_data));
        array_pop($valid_content);

        foreach ($valid_content as $record) {
            $this->assertTrue(Erc::isValidRecord($record."\n\n"));
        }

        $invalid_data = __DIR__ . '/ERCsamples-invalid.txt';
        $invalid_content = preg_split('/\n{2}|(\n\r){2}/', file_get_contents($invalid_data));
        array_pop($invalid_content);

        foreach ($invalid_content as $record) {
            $this->assertFalse(Erc::isValidRecord($record."\n\n"));
        }
        
    }

    /**
     * Test isValidKernelElementLabel
     */
    public function test_valid_kernel_element_label(): void
    {
        
        $valid = ['# Comment', 'wer(h1)', 'test-1', 'test_2'];

        foreach ($valid as $v) {
            $this->assertTrue(Erc::isValidKernelElementLabel($v));
        }

        $invalid = ['12345', 'test.1', 'test 2'];

        foreach ($invalid as $v) {
            $this->assertFalse(Erc::isValidKernelElementLabel($v));
        }
        
    }

}
