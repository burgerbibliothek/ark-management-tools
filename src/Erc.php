<?php

namespace Burgerbibliothek\ArkManagementTools;

use Burgerbibliothek\ArkManagementTools\Anvl;
use Exception\InvalidArgumentException;

/**
 * Electronic Resource Citation (ERC).
 * Methods for creating and interacting with ERC records.
 * @link https://www.dublincore.org/groups/kernel/spec/
 */
class Erc extends Anvl
{

    const ENCODINGSCHEME = [
        "chr" => [' ', '!', '"', '#', '$', '&', '\'', '(', ')', '*', '+', ',', '.', '/', ':', ';', '<', '=', '>', '?', '@', '[', '\\', ']', '|'],
        "code" => ['%sp', '%ex', '%dq', '%ns', '%do', '%am', '%sq', '%op', '%cp', '%as', '%pl', '%co', '%pd', '%sl', '%cn', '%sc', '%lt', '%eq', '%gt', '%qu', '%at', '%ox', '%ls', '%cx', '%vb']
    ];

    /**
     * ERC Record.
     * @param int $lineLength The number of characters at which the element-bodies will be wrapped (default: 72).
     */
    function __construct(int $lineLength = 72)
    {
        parent::__construct($lineLength);
        $this->add('erc', '');
    }

    /**
     * Kernel Element Label Validation.
     * Kernel element labels are strings beginning with a letter that may contain any combination 
     * of letters, numbers, hyphens, and underscores ("_"). An element label may also be accompanied 
     * by its coded synonym e. g. wer(h1).
     * @param string $label
     * @link https://www.dublincore.org/groups/kernel/spec/#7--kernel-label-structure
     */
    public static function isValidKernelElementLabel(string $label): bool
    {
        preg_match('/^(?:#.*|([A-Za-z][\w\-]*)+(\(h\d{1,2}\))?)$/', $label, $matches);
        return $matches[0] === $label ? true : false;
    }

    /**
     * ERC record Validation.
     * Checks if string is a valid ERC record.
     * @param string $record String to check.
     * @param ?array<string> $labelList Optionally pass a list of allowed labels.
     */
    public static function isValidRecord(string $record, ?array $labelList = null): bool
    {
        $record = ltrim($record);

        if (substr($record, 0, 4) === 'erc:') {

            /** Remove indentations. */
            $record = preg_replace('/\r\n\t/', ' ', $record);

            /** Create array from record. */
            $record = preg_split('/\r\n/', $record);

            /** The length of a valid record is at least 3 and the last two elements are void */
            $recordLength = count($record);
            if ($recordLength >= 3 && $record[$recordLength - 1] == '' && $record[$recordLength - 2] == '') {

                $record = array_slice($record, 1, -2);

                /** Check if labels are valid */
                foreach ($record as $r) {

                    if (str_contains($r, ':')) {
                        $labelValue = preg_split('/:/', $r, 2);
                        if (!self::isValidKernelElementLabel($labelValue[0])) {
                            return false;
                        }

                        if ($labelList && !in_array($labelValue[0], $labelList)) {
                            return false;
                        }
                    } else if ($r != '') {

                        return false;
                    }
                }
                return true;
            }
        }
        return false;
    }

    /**
     * Encode element value.
     * @param string $value String which should be encoded.
     * @link https://www.dublincore.org/groups/kernel/spec/#95--element-value-encoding
     */
    public static function encodeElementValue(string $value): string
    {
        $value = str_replace('%', '%pe', $value);
        return str_replace(self::ENCODINGSCHEME['chr'], self::ENCODINGSCHEME['code'], $value);
    }

    /**
     * Decode element value.
     * @param string $value String which should be decoded.
     * @link https://www.dublincore.org/groups/kernel/spec/#95--element-value-encoding.
     */
    public static function decodeElementValue(string $value): string
    {
        $value = str_replace('%pe', '%', $value);
        return str_replace(self::ENCODINGSCHEME['code'], self::ENCODINGSCHEME['chr'], $value);
    }

    /**
     * Add Kernel element.
     * Add a Kernel element to the record.
     * @param string $elementName String beginning with a letter that may contain any combination of letters, numbers, hyphens, and underscores. An element label may also be accompanied by its coded synonym e. g. wer(h1)
     * @param string $elementBody Value of the element will be encoded.
     */
    #[\Override]
    public function add(string $elementName, string $elementBody): void
    {
        if (self::isValidKernelElementLabel($elementName)) {

            if (key_exists($elementName, $this->record)) {
                $elementBody .= '; ' . $this->record[$elementName];
            }

            $elementBody = self::encodeElementValue(trim($elementBody));

            parent::add($elementName, $elementBody);
        }
    }

    /**
     * Decode record.
     * Get record in decoded form.
     * @param string $record String containing a valid ERC record.
     */
    public static function decodeRecord(string $record): string
    {
        if (!self::isValidRecord($record)) {
            throw new \InvalidArgumentException('ERC record is not valid.');
        }

        $record = array_map(fn($value) => self::decodeElementValue($value), self::parseRecord($record));

        $anvl = new Anvl;
        $anvl->record = $record;

        return $anvl->record();
    }

    /**
     * Retrieve record.
     * @param bool $decode Decode values.
     * @param bool $comments Hide comments.
     */
    #[\Override]
    public function record(bool $comments = false, bool $decode = true): string
    {
        if ($decode) {
            $this->record = array_map(fn($value) => self::decodeElementValue($value), $this->record);
        }

        return parent::record($comments);
    }

    /**
     * Merge two Records.
     * @param string $primaryRecord
     * @param string $secondaryRecord
     * @param ?string $strategy Strategy how element-bodies should be merged. Possible strategies "keep", "overwrite", "substitute".
     * @param bool $decode If the returned record should be decoded (default: false).
     */
    public static function mergeRecords(string $primaryRecord, string $secondaryRecord, ?string $strategy = null, bool $decode = false): ?string
    {
        $pR = new Erc;
        $sR = new Erc;
        $pR->load($primaryRecord);
        $sR->load($secondaryRecord);
        unset($sR->record['erc']);

        /** All existing values are kept, new values get appended */
        if($strategy === null || $strategy === "keep"){
            foreach($sR->record as $elName => $elBody){
                $pR->add($elName, self::decodeElementValue($elBody));
            }

            return $pR->record(decode: $decode);
        }

        /** Existing values may get overwritten */
        if($strategy === 'overwrite'){
            $pR->record = array_merge($pR->record, $sR->record);
            return $pR->record(decode: $decode);
        }

        /** Secondary record substitutes primary record */
        if($strategy === 'substitute'){
            return $sR->record(decode: $decode);
        }

        throw new \InvalidArgumentException('Strategy "'.$strategy.'" does not exist.');

    }

    /**
     * Load ERC record.
     * @param string $record
     */
    #[\Override]
    public function load(string $record): void
    {
        $this->record = [];
        parent::load($record);
    }
}
