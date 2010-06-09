<?php
require_once "ResourceCollector.php";
/**
 * Collects text based resources minifies it.
 *
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision: 1480 $ - $Date: 2010-05-19 14:51:14 +0100 (Wed, 19 May 2010) $
 * @package Core
 * @subpackage Internet
 */
class CssCollector extends ResourceCollector {

	public function getFileExtention() {
		return "css";
	}


	public function makeHtml($filename, $other) {
		return <<<TEXT
<link rel="stylesheet" type="text/css" href="{$filename}" media="{$other}" />
TEXT;
	}

	/**
	 * Removes
	 *
	 * Based on cssmin by Joe Scylla
	 *
	 * @param string $source
	 * @return string
	 */
	protected function minify($source) {
		$v = trim($source);
		$v = str_replace("\r\n", "\n", $v);
	  $search = array("/\/\*[\d\D]*?\*\/|\t+/", "/\s+/", "/\}\s+/");
	  $replace = array(null, " ", "}\n");
		$v = preg_replace($search, $replace, $v);
		$search = array("/\\;\s/", "/\s+\{\\s+/", "/\\:\s+\\#/", "/,\s+/i", "/\\:\s+\\\'/i", "/\\:\s+([0-9]+|[A-F]+)/i");
	  $replace = array(";", "{", ":#", ",", ":\'", ":$1");
	  $v = preg_replace($search, $replace, $v);
	  $v = str_replace("\n", null, $v);
	  return $v;
	}
}