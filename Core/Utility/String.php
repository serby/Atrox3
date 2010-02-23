<?php
/**
 * @copyright Clock Limited 2007
 * @version 3.0 - $Revision$ - $Date$
 * @package Core
 * @subpackage Utility
 */

/**
 * Extra functions for working with strings
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2007
 * @version 3.0 - $Revision$ - $Date$
 * @package Core
 * @subpackage Utility
 */
class String {

	//TODO: Round up useful functions to add to this class

	/**
	 * Takes a camelized string and adds spaces before each uppercase character.
	 *
	 * ie.
	 *
	 * echo String::decamelize("ClockWebProject);
	 *
	 * Would display
	 *
	 * > Clock Web Project
	 *
	 * @param string $value String value to be decamelized
	 * @return string Decamelized version of $value
	 */
	function decamelize($value) {
		$output = $value[0];
		$spaceCount = 0;
		for ($i = 1; $i < mb_strlen($value); $i++) {
			if ((!is_numeric($value[$i])) && ($value[$i] == mb_strtoupper($value[$i]))) {
				$output .= " ";
				$spaceCount++;
			}
			$output .= $value[$i];
		}
		if ($spaceCount == mb_strlen($value) - 1) {
			$output = $value;
		}
		return $output;
	}
	
	/**
	 * Searches a string for matches to replace, insensitive to case.
	 *
	 * ie.
	 *
	 * echo String::caseInsensitiveReplace("web", "Website", "Clock Web Project."));
	 *
	 * Would display
	 *
	 * > Clock Website Project.
	 * 
	 * @param string $search String value to be located
	 * @param string $replace String value to replace with
	 * @param string $string String value to be searched
	 * @return string Case Insensitive replacement version of $string
	 */
	function caseInsensitiveReplace($search, $replace, $string) {
		return str_ireplace($search, $replace, $string);
	}
	
	/**
	 * Searches a string for matches to add preFix and postFix around searched item.
	 *
	 * ie.
	 *
	 * echo String::caseInsensitiveWrap("Clock", "Clock Website Project.", "<strong>", "</strong>");
	 *
	 * Would display
	 *
	 * > <strong>Clock</strong> Website Project.
	 * 
	 * @param string $search String value to be located
	 * @param string $string String value to be searched
	 * @param string $preFix String value tag to placed before $search
	 * @param string $postFix String value tag to placed after $search
	 * @return string $string with wrapped $search
	 */
	function caseInsensitiveWrap($search, $string, $preFix, $postFix) {
		return preg_replace("/(" . preg_quote($search, "/") . ")/i", "{$preFix}\\1{$postFix}", $string);
	}
	/**
	 * Removes redundant seperators (more than one together)
	 *
	 * ie.
	 *
	 * echo String::removeRedundantSeperators("Clock,, Website", ",");
	 *
	 * Would display
	 *
	 * > Clock, Website
	 * 
	 * @param string $string String value to be parsed
	 * @param string $seperator String value of seperator
	 * @return string $string with removed redundant seperators
	 */
	function removeRedundantSeperators($string, $seperator = ", ") {
		$doubleSeperator = $seperator . $seperator;
		$string = str_replace($doubleSeperator, $seperator, $string);
		return $string;
	}
}