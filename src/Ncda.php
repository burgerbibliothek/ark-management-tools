<?php
namespace Burgerbibliothek\ArkManagementTools;

use Burgerbibliothek\ArkManagementTools\Ark;
use Exception;

class Ncda extends Ark
{

	/**
	 * NOID Check digit algorithm.
	 * Reference: https://metacpan.org/dist/Noid/view/noid#NOID-CHECK-DIGIT-ALGORITHM.
	 * @param string $id ID for which a checkdigit should be calculated.
	 * @param string $xdigits Character repetoire used for ID generation.
	 * @return string Returns check digit.
	 */
	static public function calc(string $id, string $xdigits): string
	{

		if (strlen($id) >= strlen($xdigits)) {
			throw new Exception('Length of id can\'t exceed the number of xdigits in order for the NCDA to work.');
		}

		/** Check if $id contains only characters that are in $xdigits */
		if (preg_match_all('/[\/' . $xdigits . ']/', $id) !== strlen($id)) {
			throw new Exception('$id is not well formed.');
		}

		/** Make sure $xdigits contains unique values only */
		$xdigits = array_unique(str_split($xdigits));

		/** Sort character repetoire */
		sort($xdigits);

		/** Calculate the checkdigit */
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
