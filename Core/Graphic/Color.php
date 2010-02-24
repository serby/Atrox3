<?php
/**
 * @package Core
 * @subpackage Graphic
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 */

/**
 * Color control
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Core
 * @subpackage Graphic
 */

class ColorControl {
	
	function getRgbFromHex($colorHex) {
		sscanf($colorHex, "%2x%2x%2x", $red, $green, $blue);
		return (object)array("red" => $red, "green" => $green, "blue" => $blue);
	}
	
	function isHexColor($hexColor) {
		if (!$hexColor) {
			return false;
		}
		if (!mb_ereg("#{0,1}[A-Fa-f0-9]{6}", $hexColor)) {
			return false;
		}
		$hexColor = mb_substr($hexColor, -6, 6);
		return $hexColor;
	} 
}