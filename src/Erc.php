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
     * @param array<string> $labelList Optionally pass a list of allowed labels.
     * @return bool Returns TRUE if record is valid.
     */
    public static function isValidRecord(string $record, array $labelList = null): bool
    {   
        $record = ltrim($record);

        if(substr($record, 0, 4) === 'erc:'){
        
            /** Remove indentations. */
            $record = preg_replace('/\r\n\t|\n\t/', ' ', $record);

            /** Create array from record. */
            $record = preg_split('/\r\n|\n/', $record);

            /** The length of a valid record is at least 3 and the last two elements are void */
            $recordLength = count($record);
            if($recordLength >= 3 && $record[$recordLength - 1] == '' && $record[$recordLength - 2] == ''){

                $record = array_slice($record, 1, -2);

                /** Check if labels are valid */
                foreach ($record as $r) {

                    if(str_contains($r, ':')){   
                        $labelValue = preg_split('/:/', $r, 2);
                        if(!self::isValidKernelElementLabel($labelValue[0])){
                            return false;
                        }

                        if($labelList && !in_array($labelValue[0], $labelList)){
                            return false;                           
                        }

                    }else if($r != ''){

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
     * https://www.dublincore.org/groups/kernel/spec/#95--element-value-encoding.
     * @param string $value String which should be encoded.
     * @return string
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
     * @return string
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
     * @return void
     */
    public function addElement(string $label, string $value): void
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
     * @return void
     */
    public function addComment(string $comment): void
    {
        $this->addElement('#', $comment);
    }

    /**
     * Add story to record.
     * @param array<string> $storyValues e.g. ['Gibbon, Edward', 'The Decline and Fall of the Roman Empire', 1781, 'http://www.ccel.org/g/gibbon/decline/']
     * @param string $label Story type e.g. 'about', 'neta', 'support', 'depositor'.
     * @param bool $append Append to story instead of overwriting.
     */
    public function addStory(array $storyValues, ?string $label = null, bool $append = true): void
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
     * Parse ERC record to array.
     * @param string $erc ERC record.
     * @param array<string> $labelList Optionally pass a list of allowed labels.
     * @return null|array<string> In case of a valid erc record, the parsed metadata is returned.
     */
    public static function parseRecord(string $erc, array &$labelList = null): ?array
    {
        
        // Check if record is valid
        if(!self::isValidRecord($erc, $labelList)){
            return null;
        }    

        /** Remove linebreaks in values */
        $erc = str_replace(chr(13) . chr(10) . chr(9), ' ', $erc);

        /** Split into elements */
        $rows = array_slice(preg_split('/\r\n|\n/', $erc), 0, -2);

        $record = [];

        foreach ($rows as $row) {
            if(!empty($row)){
                $element = explode(':', trim($row), 2);
                $record[$element[0]] = trim($element[1]);
            }
        }
        
        return $record;
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

        $record = array_map(fn($value) => self::decodeElementValue($value), self::parseRecord($record));
        
        $anvl = new Anvl;
        $anvl->record = $record;

        return $anvl->record();
        
    }

    /**
     * Retrieve record.
     * @param bool $decode Decode values.
     * @param bool $comments Hide comments.
     * @return string
     */
    public function record(bool $decode = true, bool $comments = false): string
    {
        if ($decode) {
            $this->record = array_map(fn($value) => self::decodeElementValue($value), $this->record);
        }

        return parent::record($comments);
    }
}
