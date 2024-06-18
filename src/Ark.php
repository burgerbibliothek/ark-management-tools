<?php
namespace Burgerbib\AmsCore;
use Burgerbib\AmsCore\Ncda;

class Ark
{

	/**
	 * ARK generator.
	 * Generate an ARK of desired length with given NAAN and Character Repetoire.
	 * @param string $naan Name Assigning Authority Name.
	 * @param string $xdigits charachter repetoire e.g. "0123456789bcdfghjkmnpqrstvwxz".
	 * @param int $length Desired length of ID.
	 * @param string $shoulder prefix to blade.
	 * @param bool $ncda Execute Noid Check Digit Algorithm is appended to the blade.
	 */
	public static function generate(string $naan, string $xdigits, int $length, string $shoulder = null, bool $ncda = true)
	{

		$id = $naan . '/';

		/**
		 * Length of id can't exceed the number of xdigits in order for the NCDA to work.
		 * https://metacpan.org/dist/Noid/view/noid#NOID-CHECK-DIGIT-ALGORITHM
		 */
		if ($length > strlen($xdigits)) {
			return false;
		}

		/** Prepend Characters */
		if ($shoulder) {
			$id .= $shoulder;
		}

		/** Generate random ID based on minter settings */
		$randomizer = new \Random\Randomizer();
		$id = $randomizer->getBytesFromString($xdigits, $length);

		/** Append check digit */
		if ($ncda === true) {
			$id = $id . Ncda::calc($id, $xdigits);
		}

		return $id;
	}
}

