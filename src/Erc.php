<?php

namespace Burgerbibliothek\ArkManagementTools;
use Burgerbibliothek\ArkManagementTools\Anvl;

class Erc extends Anvl
{

    /**
     * ERC Record.
     * @param int $lineLength After how many words to wrap text.
     */
    function __construct($lineLength = 72)
    {
        parent::__construct($lineLength);
        $this->add('erc', ' ');
    }

    /**
     * Add story.
     * @param array $storyValues e.g. ['Gibbon, Edward', 'The Decline and Fall of the Roman Empire', 1781, 'http://www.ccel.org/g/gibbon/decline/']
     * @param array $type Story type e.g. 'about', 'neta', 'support', 'depositor'.
     * @param bool $append Append to story instead of overwriting.
     */
    public function addStory(array $storyValues, ?string $type = null, bool $append = true)
    {
        $labels = ['who', 'what', 'when', 'where', 'how'];
        $stories = array_slice($storyValues, 0, 5);

        if(isset($type)){
            $labels = array_map(fn($value) => $type.'-'.$value, $labels);
        }
 
        foreach($stories as $i => $story) {

            if($append && isset($this->record[$labels[$i]])){
                $story = $this->record[$labels[$i]].' | '.$story;
            }

            parent::add($labels[$i], $story);

        }

    }

    /**
     * Parse Kernel Metadata to array.
     * @param string $metadata
     */
    public static function parseKernelMetadata(string $metadata): ?array
    {
        
        $metadata = str_replace(chr(13).chr(10).chr(9), ' ', $metadata);

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
}
