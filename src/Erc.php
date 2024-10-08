<?php

namespace Burgerbibliothek\ArkManagementTools;

use Burgerbibliothek\ArkManagementTools\Anvl;
use Exception\InvalidArgumentException;

class Erc extends Anvl
{

    const ENCODINGSCHEME = [
        "chr" => [' ', '!', '"', '#', '$', '&', '\'', '(', ')', '*', '+', ',', '.', '/', ':', ';', '<', '=', '>', '?', '@', '[', '\\', ']', '|'],
        "code" => ['%sp', '%ex', '%dq', '%ns', '%do', '%am', '%sq', '%op', '%cp', '%as', '%pl', '%co', '%pd', '%sl', '%cn', '%sc', '%lt', '%eq', '%gt', '%qu', '%at', '%ox', '%ls', '%cx', '%vb']
    ];

    /**
     * ERC Record.
     * @param int $lineLength After how many words to wrap text.
     */
    function __construct($lineLength = 72)
    {
        parent::__construct($lineLength);
        $this->add('erc');
    }

    /**
     * Check if string is valid kernel element label.
     * Kernel element labels are strings beginning with a letter that may contain any combination 
     * of letters, numbers, hyphens, and underscores ("_"). An element label may also be accompanied 
     * by its coded synonym e. g. wer(h1). 
     * @param string $label
     * @return bool
     */
    public static function isValidKernelElementLabel(string $label): bool
    {
        preg_match('/^(#.+)|([A-z]{1}[\w\-]*)+(\(h\d{1,2}\))?$/', $label, $matches);
        return $matches[0] === $label ? true : false;
    }

    /**
     * Validate ERC record.
     * Checks if string conforms to ERC.
     * @param string $record String to check.
     */
    public static function isValidRecord(string $record): bool
    {

        /** Remove indentations. */
        $record = preg_replace('/\r\n\t|\n\t/', ' ', $record);
        /** Create array from record. */
        $record = preg_split('/\r\n|\n/', $record);
        /** Get last two elements record */
        $record = array_chunk($record, count($record) - 2);

        /** Starts with erc: and ends with two newlines  */
        if (trim($record[0][0]) === 'erc:' && empty($end[1][0]) && empty($end[1][1])) {
           
            /** Element consist of a label, a colon, and an optional value. */
            foreach ($record[0] as $r) {
                $labelValue = preg_split('/:/', $r, 2);
                if(!self::isValidKernelElementLabel($labelValue[0])){
                    return false;
                }
            }
            return true;
        }
        return false;
    }

    /**
     * Encode element value.
     * https://www.dublincore.org/groups/kernel/spec/#95--element-value-encoding.
     * @param string $value String which should be encoded.
     */
    public static function encodeElementValue(string $value): string
    {
        $value = str_replace('%', '%pe', $value);
        return str_replace(self::ENCODINGSCHEME['chr'], self::ENCODINGSCHEME['code'], $value);
    }

    /**
     * Decode element value.
     * https://www.dublincore.org/groups/kernel/spec/#95--element-value-encoding.
     * @param string $value String which should be decoded.
     */
    public static function decodeElementValue(string $value): string
    {
        $value = str_replace('%pe', '%', $value);
        return str_replace(self::ENCODINGSCHEME['code'], self::ENCODINGSCHEME['chr'], $value);
    }

    /**
     * Add Kernel element.
     * Adds a Kernel element to the record.
     * @param string $label String beginning with a letter that may contain any combination of letters, numbers, hyphens, and underscores. An element label may also be accompanied by its coded synonym e. g. wer(h1)
     * @param string $value Value of the element will be encoded.
     */
    public function addElement(string $label, string $value)
    {
        if (self::isValidKernelElementLabel($label)) {

            $value = self::encodeElementValue($value);

            if (key_exists($label, $this->record)) {
                $value .= '; ' . $this->record[$label];
            }

            parent::add($label, $value);
        }
    }

    /**
     * Add Comment
     * 
     */
    public function addComment(string $comment)
    {
        $this->addElement('#', $comment);
    }

    /**
     * Add story to record.
     * @param array $storyValues e.g. ['Gibbon, Edward', 'The Decline and Fall of the Roman Empire', 1781, 'http://www.ccel.org/g/gibbon/decline/']
     * @param array $type Story type e.g. 'about', 'neta', 'support', 'depositor'.
     * @param bool $append Append to story instead of overwriting.
     */
    public function addStory(array $storyValues, ?string $label = null, bool $append = true)
    {

        $labels = ['who', 'what', 'when', 'where'];
        $stories = array_slice($storyValues, 0, 4);
        
        if(isset($label)){
            $labels = array_map(fn($value) => $label.'-'.$value, $labels);
        }
 
        foreach($stories as $i => $story) {
            self::addElement($labels[$i], $story);
        }
        
    }

    /**
     * Parse Kernel Metadata to array.
     * @param string $metadata
     */
    public static function parseKernelMetadata(string $metadata): ?array
    {
        $metadata = str_replace(chr(13) . chr(10) . chr(9), ' ', $metadata);

        $rows = explode(chr(0x0A), $metadata);

        if (!empty($rows[count($rows) - 1]) && !empty($rows[count($rows) - 2])) {
            return null;
        }

        $rows = array_slice($rows, 0, -2);

        if (trim($rows[0]) !== 'erc:') {
            return null;
        }

        foreach ($rows as $row) {
            $pair = explode(':', trim($row), 2);
            $kernel[$pair[0]] = trim($pair[1]);
        }

        return $kernel;
    }

    /**
     * Decode record.
     * Gets 
     * @param string $record String containing a valid ERC record.
     * @return string
     */
    public static function decodeRecord(string $record): string
    {
        if(!self::isValidRecord($record)){
            throw new \InvalidArgumentException('ERC record is not valid.');
        }

        $record = array_map(fn($value) => self::decodeElementValue($value), self::parseKernelMetadata($record));
        
        $anvl = new Anvl;
        $anvl->record = $record;

        return $anvl->record();
        
    }

    /**
     * Retrieve record.
     * @param bool $decode Decode values.
     * @param bool $comments Hide comments. 
     */
    public function record(bool $decode = true, bool $comments = false)
    {
        if ($decode) {
            $this->record = array_map(fn($value) => self::decodeElementValue($value), $this->record);
        }
        return parent::record($comments);
    }
}
