<?php

declare(strict_types=1);

use Burgerbibliothek\ArkManagementTools\Erc;
use PHPUnit\Framework\TestCase;

class ErcTest extends TestCase
{
     
    /**
     * Test records creation
     */
    public function test_erc_record_creation(): void
    {
        $erc = new Erc;
        $erc->add('who', 'Burgerbibliothek Bern');
        $erc->add('what', 'Test');
        $erc->add('when', '2026-08-05');
        $erc->addComment('This is a test.');

        $expectedResult = "erc: \r\nwho: Burgerbibliothek Bern\r\nwhat: Test\r\nwhen: 2026-08-05\r\n\r\n";
        $expectedResultComments = "erc: \r\nwho: Burgerbibliothek Bern\r\nwhat: Test\r\nwhen: 2026-08-05\r\n# This is a test.\r\n\r\n";
        
        $this->assertEquals($erc->record(), $expectedResult, 'Created record has not the expected output.');
        $this->assertEquals($erc->record(comments: true), $expectedResultComments, 'Created record has not the expected output.');
        
    }

    /**
     * Test Validation of ERC records.
     */
    public function test_erc_record_validation(): void
    {
        $valid_data = __DIR__ . '/ERCsamples-valid.txt';
        $valid_content = preg_split('/(\r\n){2}/', file_get_contents($valid_data));
        array_pop($valid_content);

        foreach ($valid_content as $record) {
            $this->assertTrue(Erc::isValidRecord($record."\n\n"));
        }

        $invalid_data = __DIR__ . '/ERCsamples-invalid.txt';
        $invalid_content = preg_split('/\r\n{2}/', file_get_contents($invalid_data));
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

        foreach ($invalid as $iv) {
            $this->assertFalse(Erc::isValidKernelElementLabel($iv));
        }
        
    }

    /**
     * Test mergeRecords
     */
    public function test_merge_records(): void
    {

        $r1 = "erc: \r\nwho: PhpUnit\r\nwhat: Test\r\n\r\n";
        $r2 = "erc: \r\nwho: PhpUnit\r\nwhat: Test\r\nwhen: 2026-01-01\r\n\r\n";
        $keepMerge = "erc: \r\nwho: PhpUnit%sc%spPhpUnit\r\nwhat: Test%sc%spTest\r\nwhen: 2026-01-01\r\n\r\n";
        $overwriteMerge = "erc: \r\nwho: PhpUnit\r\nwhat: Test\r\nwhen: 2026-01-01\r\n\r\n";
        
        $this->assertEquals(Erc::mergeRecords($r1, $r2, 'keep'), $keepMerge, 'Output of records merge (strategy "keep") has not expected output.');
        $this->assertEquals(Erc::mergeRecords($r1, $r2, 'overwrite'), $overwriteMerge, 'Output of records merge (strategy "keep") has not expected output.');
        
    }

}
