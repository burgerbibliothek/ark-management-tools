<?php

namespace Burgerbibliothek\ArkManagementTools;

use Burgerbibliothek\ArkManagementTools\Ncda;
use Burgerbibliothek\ArkManagementTools\Validator;
use Random\Randomizer;
use Exception;

class Ark
{

	/**
	 * ARK generator.
	 * Generate an ARK of desired length with given NAAN and character repetoire.
	 * @param string $naan Name Assigning Authority Number.
	 * @param string $xdigits Charachter repetoire e.g. "0123456789bcdfghjkmnpqrstvwxz".
	 * @param int $length Desired length of ID.
	 * @param string $shoulder Prefix to blade.
	 * @param bool $ncda Executes Noid Check Digit Algorithm and appends result to blade.
	 */
	public static function generate(string $naan, string $xdigits, int $length, string $shoulder = null, bool $ncda = true)
	{

		if($length <= 0){
			throw new Exception('ARKs must have at least a length greater than zero.');
		}
		
		if(Validator::validNaan($naan) === false){
			throw new Exception('NAAN can only consist of the betanumeric characters: 0123456789bcdfghjkmnpqrstvwxz.');
		}

		if(Validator::validArkCharacterRepetoire($xdigits) === false){
			throw new Exception('ARKs may be built using letters, digits, or any of these seven characters: = ~ * + @ _ $');
		}

		$id = $naan . '/';

		/** 
		 * Prepend Shoulder.
		 * Shoulder is prepended to assigned name if characters are valid.
		 */
		if($shoulder && Validator::shoulderInXdigits($shoulder, $xdigits)){
			$id .= $shoulder;
		}

		/** Generate random ID */
		$randomizer = new Randomizer();
		$id .= $randomizer->getBytesFromString($xdigits, $length);
		
		/**
		 * Append check digit.
		 * Length of id can't exceed the number of xdigits in order for the NCDA to work
		 * https://metacpan.org/dist/Noid/view/noid#NOID-CHECK-DIGIT-ALGORITHM
		 */
		if($ncda && $length <= strlen($xdigits)) {
			$id .= Ncda::calc($id, $xdigits);
		}

		return $id;
	}
}
