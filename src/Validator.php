<?php
namespace Burgerbib\AmsCore;

class Validator{

    /**
     * Validate character repetoire.
     * Check if string contains only characters which are valid for forming ARKs
     * https://www.ietf.org/archive/id/draft-kunze-ark-39.html#name-character-repertoires.
     * @param string $repetoire Character repetoire
     */
    public static function validArkCharacterRepetoire($repetoire){
        return preg_match('/[^A-Za-z0-9=~*+@_$]/', $repetoire) > 0 ? false : true;
    }

    /**
     * Validate NAAN.
     * Check if string contains only characters which are valid for forming NAANs
     * https://www.ietf.org/archive/id/draft-kunze-ark-39.html#name-the-name-assigning-authorit.
     * @param string $naan NAAN
    */
    public static function validNaan($naan){
        return preg_match('/[^0-9bcdfghjkmnpqrstvwxz]/', $naan) > 0 ? false : true;
    }
    
}