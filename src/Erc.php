<?php

namespace Burgerbibliothek\ArkManagementTools;
use Burgerbibliothek\ArkManagementTools\Anvl;

class Erc extends Anvl
{

    /**
     * Instantiate ERC Record.
     * @param arr $elements ['Gibbon, Edward','The Decline and Fall of the Roman Empire', '1781', 'http://www.ccel.org/g/gibbon/decline/']
     * @param int $lineLength After how many words to wrap text.
     */
    function __construct($lineLength = 72)
    {
        parent::__construct($lineLength);
        $this->add('erc', '');
    }

    /**
     * Existing stories are overwritten.
     */
    public function story(array $values, $story = null)
    {

        $elements = ['who', 'what', 'when', 'where'];

        if ($story) {
            $elements = array_map(fn ($h) => $story . '-' . $h, $elements);
        }

        foreach ($elements as $index => $h) {
            $this->add($h, $values[$index]);
        }
    }

    

    /**
     * Add new element
     */
    public function add(string $name, string $body){

        if(array_key_exists($name, $this->record)){
            $body = $this->record[$name].'; '.$body;
        }

        parent::add($name, $body);
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

        if (!array_key_exists('who', $kernel) || !array_key_exists('what', $kernel) || !array_key_exists('when', $kernel) || !array_key_exists('where', $kernel)) {
            return null;
        }

        return $kernel;
    }
}
