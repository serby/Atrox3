<?php
	require_once("Atrox/Core/Data/IProcessor.php");
/**
 * Minifies Cascading Style Sheets.
 *
 * @author Dom Udall <dom.udall@clock.co.uk>
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision: 1481 $ - $Date: 2010-05-19 19:09:03 +0100 (Wed, 19 May 2010) $
 * @package Core
 * @subpackage Data
 */
class CssMinifier implements IProcessor {
	public function process($source) {
		$value = trim($source);
		$value = str_replace("\r\n", "\n", $value);
		$search = array("/\/\*[\d\D]*?\*\/|\t+/", "/\s+/", "/\}\s+/");
		$replace = array(null, " ", "}\n");
		$value = preg_replace($search, $replace, $value);
		$search = array("/\\;\s/", "/\s+\{\\s+/", "/\\:\s+\\#/", "/,\s+/i", "/\\:\s+\\\'/i", "/\\:\s+([0-9]+|[A-F]+)/i");
		$replace = array(";", "{", ":#", ",", ":\'", ":$1");
		$value = preg_replace($search, $replace, $value);
		$value = str_replace("\n", null, $value);

		return $value;
	}
}