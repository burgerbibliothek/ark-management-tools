<?php

namespace Burgerbibliothek\ArkManagementTools;
use Burgerbibliothek\ArkManagementTools\Anvl;

use function PHPSTORM_META\type;

class Erc extends Anvl
{

    /**
     * Instantiate ERC Record.
     * @param int $lineLength After how many words to wrap text.
     */
    function __construct($lineLength = 72)
    {
        parent::__construct($lineLength);
        $this->add('erc', ' ');
    }

    public function add(string $story, ?string $body){
        
        if(array_key_exists($story, $this->record)){
            $body .= '; '.$this->record[$story];
        }

        if(!$body){
            return;
        }

        parent::add($story, $body);

    }

    public function addMultiple(array $stories, array $values){

        foreach($stories as $i => $story){
            $this->add($story, $values[$i]);
        }

    }

    public static function parseKernelMetadata(string $metadata): ?array
    {

        $rows = explode(chr(0x0A), $metadata);

        if (!empty($rows[count($rows) - 1]) && !empty($rows[count($rows) - 2])) {
            return null;
        }

        $rows = array_slice($rows, 0, -2);

        if (trim($rows[0]) !== 'erc:') {
            return null;
        }

        foreach ($rows as $row) {
            $pair = explode(':', trim($row));
            $kernel[$pair[0]] = trim($pair[1]);
        }

        return $kernel;
    }
}
