<?php
namespace Burgerbib\AmsCore;

class Ncda
{

	/**
	 * NOID Check digit algorithm.
	 * Reference: https://metacpan.org/dist/Noid/view/noid#NOID-CHECK-DIGIT-ALGORITHM.
	 * @param string $id ID for which a checkdigit should be calculated.
	 * @param string $xdigits Character repetoire used for ID generation.
	 */
	static public function calc(string $id, string $xdigits)
	{

		// Check if $id only contains characters which are in $xdigits
		if (preg_match_all('/[\/' . $xdigits . ']/', $id) !== strlen($id)) {
			return false;
		}

		// Make sure xdigits only contains unique values
		$xdigits = array_unique(str_split($xdigits));

		// Sort array
		sort($xdigits);

		// Calculate the checkdigit
		$xdigitValues = array_flip($xdigits);
		$chars = str_split($id);
		$sum = 0;

		foreach ($chars as $index => $value) {
			if (isset($xdigitValues[$value])) {
				$sum += $xdigitValues[$value] * ($index + 1);
			}
		}

		$checkDigit = $sum % count($xdigits);

		return $xdigits[$checkDigit];
	}

	/**
	 * Verify ID.
	 * Verify if an ID conforms to the Noid Check Digit Algorithm (NCDA).
	 * @param string $id ID whichs should be verified.
	 * @param string $xdigits Character repetoire used for ID generation.
	 */
	static public function verify(string $id, string $xdigits)
	{

		$id = str_split($id, strlen($id) - 1);
		$checkId = Ncda::calc($id[0], $xdigits);

		if ($id[1] === $checkId) {
			return true;
		}

		return false;
	}
}
