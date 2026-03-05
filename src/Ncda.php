<?php
namespace Burgerbibliothek\ArkManagementTools;

use Burgerbibliothek\ArkManagementTools\Ark;
use Burgerbibliothek\ArkManagementTools\Validator;
use Exception;
use ValueError;

/**
 * NOID Check digit algorithm (NCDA).
 */
class Ncda extends Ark
{

	/**
	 * Calculate Check Digit.
	 * Calculates a check digit using the NOID Check Digit Algorithm.
	 * @link https://metacpan.org/dist/Noid/view/noid#NOID-CHECK-DIGIT-ALGORITHM.
	 * @param string $id ID for which a checkdigit should be calculated.
	 * @param string $xdigits Character repetoire used for ID generation.
	 * @return string Returns check digit.
	 */
	static public function calc(string $id, string $xdigits): string
	{
		
		/** Check if $id conforms to ARK Check Zone */
		if(Validator::isValidCheckZone($id) === false){
			throw new Exception("\$id is not well formed (^[0-9bcdfghjkmnpqrstvwxz]{5,14}\/[0-9A-z=~*+@_$]+$): $id");
		}

		/** Make sure $xdigits contains unique values only */
		$xdigits = self::removeDuplicateChars($xdigits, true);

		if (strlen(str_replace('/','',$id)) >= strlen($xdigits)) {
			throw new Exception('Length of $id must be less than the number of xdigits in order for the NCDA to work.');
		}

		/** Check if $id contains only characters that are in $xdigits */
		if (preg_match_all('/[\/' . $xdigits . ']/', $id) !== strlen($id)) {
			throw new Exception('$id is not well formed (characters which are not part of $xdigits found in $id).');
		}

		/** Calculate the checkdigit */
		$xdigits = str_split($xdigits);
		$xdigitValues = array_flip($xdigits);
		$xdigitValues['/'] = 0;
		$chars = str_split($id);
		$sum = 0;

		foreach ($chars as $index => $value) {
			$sum += $xdigitValues[$value] * ($index + 1);
		}

		$checkDigit = $sum % count($xdigits);

		return $xdigits[$checkDigit];
	}

	/**
	 * Verify ID.
	 * Verify a given ID against the Noid Check Digit Algorithm.
	 * @param string $id ID whichs should be verified.
	 * @param string $xdigits Character repetoire used for ID generation.
	 * @return bool
 	 */
	static public function verify(string $id, string $xdigits): bool
	{

		$id = str_split($id, strlen($id) - 1);
		$checkId = Ncda::calc($id[0], $xdigits);

		if ($id[1] === $checkId) {
			return true;
		}

		return false;
	}
}
