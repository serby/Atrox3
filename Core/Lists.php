<?php
/**
 * @package Core
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 */

/**
 * Defines
 */
  // Constants for getTextualDateRange
define("DTR_TODAY", 1);
define("DTR_WEEK", 2);
define("DTR_MONTH", 3);
define("DTR_YEAR", 4);
define("DTR_RANGE", 5);
define("DTR_ALL", 6);

define("GEN_UNSPECIFIED", -1);
define("GEN_MALE", 1);
define("GEN_FEMALE", 2);

/**
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Core
 */
 class Lists {

 	function getTextualDateRange() {
		return array(DTR_TODAY => "Today", DTR_WEEK => "This Week", DTR_MONTH => "This Month",
			DTR_YEAR => "This Year", DTR_RANGE => "Range", DTR_ALL => "All");
	}

 	function getCardTypes() {
		return array("Visa", "Mastercard", "Maestro", "Solo", "Delta");
	}

	function getTitles() {
		return array("Mr", "Mrs", "Ms", "Miss", "Dr", "Rev", "Prof", "Sir", "Lord");
	}

	function getGenders($withKeys = false, $allowUnspecified = true) {
		$temp = array(GEN_MALE => "Male", GEN_FEMALE => "Female");
		$allowUnspecified && ($temp[GEN_UNSPECIFIED] = "Unspecified");
		$withKeys || ($temp = array_values($temp));
		return $temp;
	}

	function getBooleans() {
		return array("t"=>"Yes", "f"=>"No");
	}
}