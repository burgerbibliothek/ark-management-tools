<?php
namespace Burgerbibliothek\ArkManagementTools;

class Validator{

    /**
     * String follows ARK character repetoire.
     * Check if the string contains only characters that are valid for forming ARKs
     * https://www.ietf.org/archive/id/draft-kunze-ark-39.html#name-character-repertoires.
     * @param string $subject The input string.
     * @param string $reservedChars Check conformance, including the reserved character: % - . /
     */
    public static function followsArkCharacterRepetoire(string $subject, bool $reservedChars = false){
        if($reservedChars){
            return preg_match('/[^A-Za-z0-9=~*+@_$%-.\/]/', $subject) > 0 ? false : true;
        }
        return preg_match('/[^A-Za-z0-9=~*+@_$]/', $subject) > 0 ? false : true;
    }

    /**
     * String follows NAAN character repetoire.
     * Check if the string contains only characters that are valid for forming NAANs
     * https://www.ietf.org/archive/id/draft-kunze-ark-39.html#name-the-name-assigning-authorit.
     * @param string $subject The input string.
    */
    public static function followsNaanCharacterRepetoire(string $subject){
        return preg_match('/[^0-9bcdfghjkmnpqrstvwxz]/', $subject) > 0 ? false : true;
    }

    /**
     * Contains Base Compact Name.
     * String contains pattern in the form of ark:[/]NAAN.
     * @param $subject The input string.
     */
    public static function isValidBaseCompactName(string $subject){
        return preg_match('/(ark:)\/?[0-9bcdfghjkmnpqrstvwxz]{5}/', $subject) > 0 ? true : false;
    }

    /**
     * Validate Shoulder.
     * Check if shoulder contains only character which are also in the character repetoire.
     * @param string $shoulder Shoulder
     * @param string $xdigits character repetoire.
    */
    public static function shoulderInXdigits(string $shoulder, string $xdigits)
    {
        $xdigits = str_split($xdigits);
        $shoulder = str_split($shoulder);

        foreach($shoulder as &$s)
        {
            if(!in_array($s, $xdigits)){
                return false;
            }
        }

        return true;
    }
    
}