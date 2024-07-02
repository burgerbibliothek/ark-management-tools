<?php

namespace Burgerbibliothek\ArkManagementTools;

class Anvl{

    public array $record;
    protected int $lineLength;
    
    /**
     * A Name Value Object.
     * Create and interact with ANVL records.
     * https://datatracker.ietf.org/doc/draft-kunze-anvl/
     */
    function __construct($lineLength = 72)
    {
        $this->lineLength = $lineLength;
        $this->record = [];
    }

    /**
     * Add data element.
     * Add a new data element to the ANVL record.
     * @param string $name 1*<any CHAR, excluding control-chars and ":"> 
     * @param string $body text
     */
    public function add(string $name, string $body)
    {         
        $this->record[$name] = trim($body);
    }

    /**
     * ANVL record.
     */
    public function record(){
        
        $record = '';

        foreach($this->record as $name => $value){
            
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