<?php
namespace Burgerbibliothek\ArkManagementTools;
use Burgerbibliothek\ArkManagementTools\Ncda;

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
		
		$id = $naan . '/';

		/**
		 * Length of id can't exceed the number of xdigits in order for the NCDA to work
		 * https://metacpan.org/dist/Noid/view/noid#NOID-CHECK-DIGIT-ALGORITHM
		 */
		if ($length > strlen($xdigits)) {
			return false;
		}

		/** Prepend shoulder to blade */
		if ($shoulder) {
			$id .= $shoulder;
		}

		/** Generate random ID */
		$randomizer = new \Random\Randomizer();
		$id .= $randomizer->getBytesFromString($xdigits, $length);

		/** Append check digit */
		if ($ncda === true) {
			$id .= Ncda::calc($id, $xdigits);
		}

		return $id;
	}


}

