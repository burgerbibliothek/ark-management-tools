<?php

namespace Burgerbibliothek\ArkManagementTools;

use ErrorException as GlobalErrorException;
use Exception\ErrorException;

/**
 * Name-Value Language (ANVL).
 * Methods for creating and interacting with ANVL records.
 * @link http://www.cdlib.org/inside/diglib/ark/anvlspec.pdf
 * @link https://datatracker.ietf.org/doc/draft-kunze-anvl/
 */
class Anvl{

    /**
     * @param array<string> $record Contains the loaded record.$
     * @param int $lineLenght Max count of chars per line in element-body
     */
    public array $record;
    protected int $lineLength;
    
    function __construct(int $lineLength = 72)
    {
        $this->record = [];
        $this->lineLength = $lineLength;
    }

    /**
     * Add element.
     * Add a new data element to the ANVL record.
     * @param string $label 1*<any CHAR, excluding control-chars and ":"> 
     * @param string $value text
     * @return void
     */
    public function add(string $elementName, string $elementBody): void
    {         
        /** CRLF are not allowed in element-body */
        $elementBody = preg_replace('/\r\n/', '', $elementBody);
        $this->record[$elementName] = trim($elementBody);
    }

    /**
     * Output ANVL record.
     * @param bool $comments Set to false to prevent output of comments.
     * @return string
     */
    public function record(bool $comments = true): string
    {

        $record = '';

        foreach($this->record as $elementName => $elementBody){

            if($comments === false && $elementName === '#'){
                continue;
            }
            
            $separator = $elementName === '#' ? chr(32) : chr(58).chr(32);
            
            if($this->lineLength){
                $elementBody =  wordwrap($elementBody, $this->lineLength, chr(13).chr(10).chr(9));
            }
            
            $record .= $elementName.$separator.$elementBody.chr(13).chr(10);
        
        }

        $record .= chr(13).chr(10);

        return $record;
    }

    /**
     * Load ANVL record.
     * @param string $record
     */
    public function load(string $record): void
    {
        /** Remove indentations from linewraps */
        $record = preg_replace('/\r\n\t/', ' ', $record);
        $record = explode("\r\n", $record);
        
        foreach($record as $element){
            
            $elementNameDelimiter = strpos($element, ':');
            
            if($elementNameDelimiter !== false){
                $elementName = substr($element, 0, $elementNameDelimiter);
                $elementBody = substr($element, $elementNameDelimiter + 1);
                $this->add($elementName, $elementBody);
            } else {
                // Add comments
                if(str_starts_with($element, '#')){
                    $this->add('#', substr($element, 1));
                }
            }
        }
    }
}