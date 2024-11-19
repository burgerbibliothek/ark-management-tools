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
	 * @param string $shoulder Prefix to blade.
	 * @param bool $ncda Executes Noid Check Digit Algorithm and appends result to blade.
	 * @return string $id Generated ID.
	 */
	public static function generate(string $naan, string $xdigits, int $length, string $shoulder = null, bool $ncda = true): string
	{

		if ($length <= 0) {
			throw new Exception('ARKs must have at least a length greater than zero.');
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

		/**
		 * Append check digit.
		 * Length of id can't exceed the number of xdigits in order for the NCDA to work
		 * https://metacpan.org/dist/Noid/view/noid#NOID-CHECK-DIGIT-ALGORITHM
		 */
		if ($ncda && $length <= strlen($xdigits)) {
			$id .= Ncda::calc($id, $xdigits);
		}

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
		
		if(substr($ark, 0, 3) == 'ark'){

			$ark = explode('/', $ark);
			
			if($ark[0] == 'ark:'){
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

		return $components;

	}

	/**
	 * Normalization for ARK.
	 * https://www.ietf.org/archive/id/draft-kunze-ark-39.html#name-normalization-and-lexical-e.
	 * @param $ark ARK or URI containing an ARK.
	 * @return string|null The normalized ARK or null if provided ARK is not valid. 
	 */
	public static function normalize(string $ark): ?string
	{

		$ark = trim($ark);

		if (filter_var($ark, FILTER_VALIDATE_URL)) {
			/** If $ark is a URL, extract the path component */
			$ark = ltrim(parse_url($ark, PHP_URL_PATH), "/");
		}

		/** $ark beginns with ark:[/]NAAN */
		if (preg_match('/(?:^ark:\/?[0-9A-z]{5})/i', $ark, $baseNameComponent)) {

			/** Any URI query string is removed (everything from the first literal '?' to the end of the string) */
			$normalized_ark = explode('?', $ark)[0];

			/** The first case-insensitive match on "ark:/" or "ark:" is converted to "ark:" 
			 * (replacing any uppercase letters and removing any terminal '/').
			 */
			$normalized_ark = preg_replace('/(?:^ark:\/?)/i', 'ark:', $normalized_ark);

			/** Any uppercase letters in the NAAN are converted to lowercase */
			$normalized_ark = preg_replace_callback('/(?:ark:[0-9A-z]{5})/', fn ($matches) => strtolower($matches[0]), $normalized_ark, 1);

			/** the two characters following every occurrence of '%' are converted to uppercase */
			$normalized_ark = preg_replace_callback('/(?:%..)/', fn ($matches) => strtoupper($matches[0]), $normalized_ark);

			/** All hyphens are removed */
			$normalized_ark = preg_replace('/[\x{0020}|\x{00a0}|\x{002d}|\x{00ad}|\x{2000}-\x{2015}]/u', '', $normalized_ark);

			/** Structural characters (slash and period) are normalized: 
			 *  initial and final occurrences are removed, and two structural characters in a row (e.g., // or ./) 
			 *  are replaced by the first character, iterating until each occurrence has at least one 
			 * 	non-structural character on either side.
			 */
			$normalized_ark = rtrim($normalized_ark, '/');
			$rgxp = '/(?:[\.\/]{2})/';
			while (preg_match($rgxp, $normalized_ark) > 0) {
				$normalized_ark = preg_replace_callback($rgxp, fn ($matches) => substr($matches[0], 0, 1), $normalized_ark);
			}

			return $normalized_ark;
		};

		return null;
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
