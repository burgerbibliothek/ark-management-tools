<?php
namespace Burgerbibliothek\ArkManagementTools;

class Validator{

    /**
     * String follows ARK character repetoire.
     * Check if the string contains only characters that are valid for forming ARKs: 
     * https://www.ietf.org/archive/id/draft-kunze-ark-39.html#name-character-repertoires
     * @param string $subject The input string.
     * @param bool $reservedChars Check conformance, including the reserved character: % - . / (default: true)
     * @return bool
     */
    public static function followsArkCharacterRepetoire(string $subject, bool $reservedChars = true): bool
    {
        if($reservedChars){
            return preg_match('/[^0-9A-z=~*+@_$%-.\/]/', $subject) > 0 ? false : true;
        }
        return preg_match('/[^0-9A-z=~*+@_$]/', $subject) > 0 ? false : true;
    }

    /**
     * String follows NAAN character repetoire.
     * Check if the string contains only characters that are valid for forming NAANs.
     * Check: https://www.ietf.org/archive/id/draft-kunze-ark-39.html#name-the-name-assigning-authorit.
     * @param string $subject The input string.
     * @return bool
     */
    public static function followsNaanCharacterRepetoire(string $subject): bool
    {
        return preg_match('/[^0-9bcdfghjkmnpqrstvwxz]/', $subject) > 0 ? false : true;
    }

    /**
     * Is a valid NAAN.
     * String contains a valid NAAN ().
     * @param $subject The input string.
     * @return bool
     */
    public static function isValidNaan(string $subject): bool
    {   
        return preg_match('/^[0-9bcdfghjkmnpqrstvwxz]{5,14}$/', $subject) === 1 ? true : false;
    }

    /**
     * Is a valid base NAAN.
     * String contains pattern in the form of ark:[/]NAAN/{Base Name}.
     * @param $subject The input string.
     * @return bool
     */
    public static function isValidBaseCompactName(string $subject): bool
    {
        return preg_match('/^ark:\/?[0-9bcdfghjkmnpqrstvwxz]{5,14}\/[0-9A-z=~*+@_$]+$/', $subject) === 1 ? true : false;
    }

    /**
     * Validate Shoulder.
     * Check if shoulder contains only character which are also in the character repetoire.
     * @param string $shoulder Shoulder
     * @param string $xdigits character repetoire.
     * @return bool
     */
    public static function shoulderInXdigits(string $shoulder, string $xdigits): bool
    {
        
        $shoulder = str_split($shoulder);
        $xdigits = str_split($xdigits);

        foreach($shoulder as &$s)
        {
            if(!in_array($s, $xdigits)){
                return false;
            }
        }

        return true;
    }
    
}