<?php

namespace Burgerbibliothek\ArkManagementTools;

use Burgerbibliothek\ArkManagementTools\Ncda;
use Burgerbibliothek\ArkManagementTools\Validator;
use Random\Randomizer;
use Exception;

class Ark
{
	/** 
	 * Remove duplicate characters from repetoire.
	 * @param string $xdigits String containing the character repetoire.
	 * @param bool $sort Sort the characters
	 * @param bool $arr Return the characters as array.
	 * @return string|array<string>
	 */
	public static function removeDuplicateChars(string $xdigits, bool $sort = true, bool $arr = false): string|array
	{
		$xdigits = array_unique(str_split($xdigits));
		
		if($sort){
			sort($xdigits);
		}

		if($arr === false){
			return implode($xdigits);
		}
		
		return $xdigits;
	}

	/**
	 * Generate an ARK of desired length with given NAAN and character repetoire.
	 * @param string $naan Name Assigning Authority Number.
	 * @param string $xdigits Charachter repetoire e.g. "0123456789bcdfghjkmnpqrstvwxz".
	 * @param int $length Desired length of ID.
	 * @param string $shoulder Prefix to blade (default: null).
	 * @param bool $ncda Executes Noid Check Digit Algorithm and appends result to blade (default: true).
	 * @param bool $slashAfterLabel Include / in label part e.g. ark:/ (default: false).
	 * @return string $id Generated ID.
	 */
	public static function generate(string $naan, string $xdigits, int $length, string $shoulder = null, bool $ncda = true, bool $slashAfterLabel = false): string
	{

		if ($length <= 0) {
			throw new Exception('ARKs must have a length of at least more than zero.');
		}

		if (Validator::followsNaanCharacterRepetoire($naan) === false) {
			throw new Exception('NAAN can only consist of the betanumeric characters: 0123456789bcdfghjkmnpqrstvwxz.');
		}

		if (Validator::followsArkCharacterRepetoire($xdigits) === false) {
			throw new Exception('ARKs may be built using letters, digits, or any of these seven characters: = ~ * + @ _ $');
		}

		$xdigits = self::removeDuplicateChars($xdigits);

		$id = $naan . '/';

		/** 
		 * Prepend Shoulder.
		 * Shoulder is prepended to assigned name if characters are valid.
		 */
		if ($shoulder && Validator::shoulderInXdigits($shoulder, $xdigits)) {
			$id .= $shoulder;
		}

		/** Generate random ID */
		$randomizer = new Randomizer();
		$id .= $randomizer->getBytesFromString($xdigits, $length);

		/** Append check digit. */
		if ($ncda) {
			$id .= Ncda::calc($id, $xdigits);
		}

		$label = $slashAfterLabel ? 'ark:/' : 'ark:';
		$id = $label . $id;

		return $id;
	}

	/**
	 * Split ARK into components.
	 * Splits an ARK into the Components Resolver Service, NAAN, Base Name, Base Compact Name, Check Zone and Suffixes.
	 * @param string $ark ARK (also with resolver service).
	 * @return array<string,string>|array<string,mixed>
 	 */
	public static function splitIntoComponents(string $ark): array
	{
		
		$components = [
			'resolverService' => '',
			'naan' => '',
			'baseName' => '',
			'baseCompactName' => '',
			'checkZone' => '',
			'suffixes' => '',
		];

		if (filter_var($ark, FILTER_VALIDATE_URL)) {

			$url = parse_url($ark);

			$componentKeys = [
				'scheme' => '://',
				'user' => '',
				'pass' => '@',
				'host' => '',
				'port' => ':',
			];

			foreach ($componentKeys as $key => $comp) {

				if (isset($url[$key])) {

					switch ($key) {
						case 'port':
							$components['resolverService'] .= $comp . $url[$key];
							break;
						case 'pass':
							$components['resolverService'] .= ':'. $url[$key] . $comp;
							break;						
						default:
							$components['resolverService'] .= $url[$key] . $comp;
							break;
					}
				}
			}

			$ark = substr($ark, strlen($components['resolverService']) + 1, strlen($ark));

		}
		
		if(substr($ark, 0, 3) === 'ark'){

			$ark = explode('/', $ark);
			
			if($ark[0] === 'ark:'){
				$components['naan'] = $ark[1];
				array_splice($ark, 0, 2);		
			}else{
				$components['naan'] = explode(':', $ark[0])[1];
				array_splice($ark, 0, 1);
			}

			if(isset($ark[0])){
				$components['baseName'] = $ark[0];
				$components['baseCompactName'] = 'ark:'.$components['naan'].'/'.$components['baseName'];
				$components['checkZone'] = $components['naan'].'/'.$components['baseName'];
				unset($ark[0]);
			}

			if(count($ark) > 0){
				$components['suffixes'] = implode('/', $ark);
			}
			
		}

		if(Validator::isValidBaseCompactName($components['baseCompactName']) === false){
			throw new Exception('$ark seems to be invalid.');
		}

		return $components;

	}

	/**
	 * Normalization for ARK.
	 * https://www.ietf.org/archive/id/draft-kunze-ark-39.html#name-normalization-and-lexical-e.
	 * @param $ark ARK or URI containing an ARK.
	 * @return string
	 */
	public static function normalize(string $ark): string
	{
		/** Remove whitespace on beginning and end of string */
		$ark = trim($ark);

		/** The NMA part (eg, everything from an initial "https://" up to the first occurrence of "/ark:"), if present is removed. */
		$ark = preg_replace('/.*?(?=ark:)/i', '', $ark, limit: 1);

		/** Any URI query string is removed (everything from the first literal '?' to the end of the string). */
		$ark = explode('?', $ark)[0];

		/** All hyphens are removed */
		$ark = preg_replace('/[\x{0020}|\x{00a0}|\x{002d}|\x{00ad}|\x{2000}-\x{2015}]/u', '', $ark);

		/** The first case-insensitive match on "ark:/" or "ark:" is converted to "ark:" (replacing any uppercase letters and removing any terminal '/'). */
		$ark = preg_replace('/(?:^ark:\/?)/i', 'ark:', $ark);

		/** Any uppercase letters in the NAAN are converted to lowercase */
		$ark = preg_replace_callback('/(?:ark:[0-9A-z]{5})/', fn ($matches) => strtolower($matches[0]), $ark, 1);

		/** the two characters following every occurrence of '%' are converted to uppercase */
		$ark = preg_replace_callback('/(?:%..)/', fn ($matches) => strtoupper($matches[0]), $ark);

		/** Structural characters (slash and period) are normalized: 
		 *  initial and final occurrences are removed, and two structural characters in a row (e.g., // or ./) 
		 *  are replaced by the first character, iterating until each occurrence has at least one 
		 * 	non-structural character on either side.
		 */
		$ark = rtrim($ark, '/');
		$rgxp = '/(?:[\.\/]{2})/';
		while (preg_match($rgxp, $ark) > 0) {
			$ark = preg_replace_callback($rgxp, fn ($matches) => substr($matches[0], 0, 1), $ark);
		}

		return $ark;
	}

	/**
	 * Compare multiple ARKs for lexical equivalence.
	 * Check if all ARKs in a list are lexically equivalent. 
	 * https://www.ietf.org/archive/id/draft-kunze-ark-39.html#name-normalization-and-lexical-e.
	 * @param array<string> $arks List containing arks e.g. ['ark:/ABC456/xyz?info', 'ARK:ABC456/xyz??'].
	 * @return bool
 	 */
	public static function areLexicalEquivalent(array $arks): bool
	{
		$check = [];
		foreach ($arks as $ark) {
			$check[] = self::normalize($ark);
		}

		return count(array_unique($check)) == count($arks) ? false : true;
	}
}