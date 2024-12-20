<?php

namespace Burgerbibliothek\ArkManagementTools;

class Anvl{

    /**
     * @param array<string> $record
     */
    public array $record;
    protected int $lineLength;
    
    /**
     * A Name Value Object.
     * Create and interact with ANVL records.
     * http://www.cdlib.org/inside/diglib/ark/anvlspec.pdf,
     * https://datatracker.ietf.org/doc/draft-kunze-anvl/
     */
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
    public function add(string $label, ?string $value = ''): void
    {         
        // TODO add check if label only contains valid characters
        $this->record[$label] = trim($value);
    }

    /**
     * ANVL record.
     * Parse anvl record.
     * @param bool $comments Set to true to output comments.
     * @return string
     */
    public function record(bool $comments = false): string
    {

        $record = '';

        foreach($this->record as $name => $value){

            if(!$comments && $name === '#'){
                continue; 
            }
            
            $separator = $name === '#' ? chr(32) : chr(58).chr(32);
            
            if($this->lineLength){
                $value =  wordwrap($value, $this->lineLength, chr(13).chr(10).chr(9));
            }
            
            
            $record .= $name.$separator.$value.chr(13).chr(10);
        
        }

        $record .= chr(13).chr(10);

        return $record;
    }


}