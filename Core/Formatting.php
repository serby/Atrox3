<?php
/**
 * @package Core
 * @subpackage Formatting
 */

/**
 * Includes
 */
require_once("Data/Data.php");
require_once("Internet/Html.php");

/**
 * Stack up a number of formatter so you can format a given value sequentially though them
 *
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Core
 * @subpackage Formatting
 */
class FormatterStack {
  var $formatters = array();

  function FormatterStack($formatter) {
  	$this->addFormatter($formatter);
  }

  function addFormatter($formatter) {
  	if ($formatter == null) {
  		return false;
  	}
  	if (is_array($formatter)) {
  		$this->formatters = array_merge($this->formatters, $formatter);
  	} else {
  		$this->formatters[] = $formatter;
  	}
  }

  function clear() {
  	$this->formatters = array();
  }

    function format($value) {
  	foreach($this->formatters as $formatter) {
  		$value = $formatter->format($value);
  	}
  	return $value;
  }
  }

/**
 * The base MultiPageFormatter interface that should be overridded by custom
 * formating classes
 *
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Core
 * @subpackage Formatting
 */
class MultiPageControlResultsFormatter {
	/**
	 * Displays a multi-page control using the Control object that has been
	 * passed in.
	 * @param $object The control object to take values from
	 * @retrun The multi page control
	 */
	function display(&$object) {
		if ($object instanceof DataControl) {
			$out = "Total: " . $object->getNumRows() .
				" - Page " . $object->getCurrentPage() .
				" of " . $object->getPageCount();
			return $out;
		} else {
			trigger_error("Invalid Control class. Expected Class DataControl
				or decendant",E_USER_ERROR);
		}
	}
}

/**
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Core
 * @subpackage Formatting
 */
class TaggedDataEncoder extends FieldFormatter {
	function format($value) {
		return implode("\n", array_unique(explode("\n", str_replace("\r", "", $value))));
	}
}

/**
 * @author Dom Udall (Clock Limited) {@link mailto:dom.udall@clock.co.uk dom.udall@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Core
 * @subpackage Formatting
 */
class TaggedDataFormatter extends FieldFormatter {

	function TaggedDataFormatter($wrapTag = "") {
		$this->wrapTag = $wrapTag;
	}

	function format($value) {
		$value = str_replace("\r", "", trim($value));
		return $this->wrapTag . str_replace("\n", $this->wrapTag . ", " . $this->wrapTag, $value) . $this->wrapTag;
	}
}

/**
 * @author Dom Udall (Clock Limited) {@link mailto:dom.udall@clock.co.uk dom.udall@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Core
 * @subpackage Formatting
 */
class TaggedDataArrayFormatter extends FieldFormatter {
	function format($value) {
		$value = str_replace("\r", "", trim($value));
		return explode("\n", $value);
	}
}

/**
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Core
 * @subpackage Formatting
 */
class MonthYearFieldFormatter extends FieldFormatter {
	var $nullValue;
	function MonthYearFieldFormatter($nullValue = "Not Set") {
		$this->nullValue = $nullValue;
	}
	function format($value) {
		$application = &CoreFactory::getApplication();
		return $application->formatMonthYear($value, $this->nullValue);
	}
}

/**
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Core
 * @subpackage Formatting
 */
class ArrayRelationFormatter extends FieldFormatter {
	var $array = null;
	var $default = null;

	function ArrayRelationFormatter(&$array, $default) {
		$this->array = $array;
		$this->default = $default;
	}
	function format($value) {

		if (isset($this->array[$value])) {
			return $this->array[$value];
		} else {
			return $this->array[$this->default];
		}
	}
}


/**
 * Converts the time in seconds to the nearest day, hour, minute, or second.
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Core
 * @subpackage Formatting
 */
class FuzzyTimeFormatter extends FieldFormatter {

 /**
	* Converts the time in seconds to the nearest day, hour, minute, or second.
	* @param Integer $value The seconds value to convert.
	* @returns String A formatted string containing the approximate time.
	*/
	function format($value) {
		if ($value > 86400) {
			$days = round($value / 86400);
			return "$days " . ($days > 1 ? "days" : "day");
		}
		if ($value > 3600) {
			$hours = round($value / 3600);
			return "$hours " . ($hours > 1 ? "hours" : "hour");
		}
		if ($value > 60) {
			$mins = round($value / 60);
			return "$mins " . ($mins > 1 ? " mins" : "min");
		}
		$value = round($value);
		return "$value " . ($value > 1 ? "sec" : "secs");
	}
}

/**
 * Converts the time in seconds in to the hour, minute, or seconds
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Core
 * @subpackage Formatting
 */
class SecondsFormatter extends FieldFormatter {

 /**
	* Converts the time in seconds to the nearest day, hour, minute, or second.
	* @param Integer $value The seconds value to convert.
	* @returns String A formatted string containing the approximate time.
	*/
	function format($value) {

		$hours = floor($value / 3600);
		$mins = floor($value / 60) % 60;
		$seconds = $value % 60;
		return ($hours > 0 ? "$hours:" : "") .
			str_pad($mins, 2, "0", STR_PAD_LEFT) . ":" .
			str_pad($seconds, 2, "0", STR_PAD_LEFT);
	}
}

/**
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Core
 * @subpackage Formatting
 */
class DateFieldFormatter extends FieldFormatter {
	var $nullValue;

	function DateFieldFormatter($nullValue = "Not Set") {
		$this->nullValue = $nullValue;
	}

	function format($value) {
		$application = &CoreFactory::getApplication();
		return $application->formatDate($value, $this->nullValue);
	}
}

/**
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Core
 * @subpackage Formatting
 */
class DateTimeFieldFormatter extends FieldFormatter {
	var $nullValue;
	function DateTimeFieldFormatter($nullValue = "Not Set") {
		$this->nullValue = $nullValue;
	}
	function format($value) {
		$application = &CoreFactory::getApplication();
		return $application->formatDateTime($value, $this->nullValue);
	}
}

/**
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Core
 * @subpackage Formatting
 */
class DateTimeFieldEncoder extends FieldFormatter {
	var $nullValue;
	function DateTimeFieldFormatter($nullValue = "Not Set") {
		$this->nullValue = $nullValue;
	}
	function format($value) {
		$application = &CoreFactory::getApplication();

		if (empty($value)) {
			return "";
		}
		if (is_array($value)) {
			$value = sprintf("%04d-%02d-%02d", $value["Year"], $value["Month"], $value["Day"]);
		}
		return $application->formatDBDateTime($value);
	}
}

/**
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Core
 * @subpackage Formatting
 */
class TimeFieldFormatter extends FieldFormatter {
	var $nullValue;
	function DateTimeFieldFormatter($nullValue = "Not Set") {
		$this->nullValue = $nullValue;
	}
	function format($value) {
		$application = &CoreFactory::getApplication();
		return $application->formatTime($value, $this->nullValue);
	}
}

 /**
* Outputs decimal time in a human readable form
* e.g. 5h 55m
*
* @author Adam Forster (Clock Ltd) {@link mailto:adam.forster@clock.co.uk adam.forster@clock.co.uk }
* @copyright Clock Limited 2007
* @version 3.0 - $Revision$ - $Date$
* @package Core
* @subpackage Formatting
*/
class DecimalTimeFieldFormatter extends FieldFormatter {
	var $nullValue;
	function DateTimeFieldFormatter($nullValue = "Not Set") {
		$this->nullValue = $nullValue;
	}
	function format($value) {
		$application = &CoreFactory::getApplication();
		return $application->formatDecimalTime($value, $this->nullValue);
	}
}

/**
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Core
 * @subpackage Formatting
 */
class DatabaseDateEncoder {
	function format($value) {
		$application = &CoreFactory::getApplication();
		return $application->customFormatDateTime($value, "%Y-%m-%d");
	}
}

/**
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Core
 * @subpackage Formatting
 */
class DatabaseDateTimeEncoder {
	function format($value) {
		$application = &CoreFactory::getApplication();
		return $application->customFormatDateTime($value, "%Y-%m-%d %H:%M:%S");
	}
}

/**
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Core
 * @subpackage Formatting
 */
class EmailFieldFormatter extends FieldFormatter {
	function format($value) {
		return "<a href=\"mailto:$value\">$value</a>";
	}
}

/**
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Core
 * @subpackage Formatting
 */
class UrlFieldFormatter extends FieldFormatter {
	function format($value) {
		return "<a href=\"$value\" target=\"_blank\">$value</a>";
	}
}

/**
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Core
 * @subpackage Formatting
 */
class LeftCropEncoder extends FieldFormatter {

	var $length = 0;

	function LeftCropEncoder($length) {
		$this->length = $length;
	}

	function format($value) {
		if ($this->length > 0) {
			$value = strip_tags($value);
			return mb_substr($value, 0, $this->length);
		} else {
			return $value;
		}
	}
}

/**
 * @author Adam Forster (Clock Ltd) {@link mailto:adam.forster@clock.co.uk adam.forster@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Core
 * @subpackage Formatting
 */
class LeftCropEllipsisEncoder extends FieldFormatter {

	var $length = 0;
	var $flash = false;

	function LeftCropEllipsisEncoder($length, $flash = false) {
		$this->length = $length;
		$this->flash = $flash;
	}

	function format($value) {
		$value = strip_tags($value);
		if (mb_strlen($value) > $this->length) {
			if ($this->flash) {
				return mb_substr($value, 0, $this->length -3) . "...";
			} else {
			return mb_substr($value, 0, $this->length -3) . "&hellip;";
			}
		} else {
			return mb_substr($value, 0, $this->length);
		}
	}
}

/**
 * @author Adam Forster (Clock Ltd) {@link mailto:adam.forster@clock.co.uk adam.forster@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Core
 * @subpackage Formatting
 */
class LeftCropBreakEllipsisEncoder extends FieldFormatter {

	var $length = 0;
	var $flash = false;

	function LeftCropBreakEllipsisEncoder($length, $flash = false) {
		$this->length = $length;
		$this->flash = $flash;
	}

	function format($value) {
		$value = strip_tags($value);
		if (mb_strlen($value) > $this->length) {
			if ($this->flash) {
				return mb_substr($value, 0, mb_strpos(mb_substr($value, 0, $this->length -3), " ")) . "...";
			} else {
				return mb_substr($value, 0, mb_strpos(mb_substr($value, 0, $this->length -3), " ")) . "&hellip;";
			}
		} else {
			return mb_substr($value, 0, $this->length);
		}
	}
}

/**
 * Left crops to the nearest last word with ellipsis
 * @author Elliot Coad (Clock Ltd) {@link mailto:elliot.coad@clock.co.uk elliot.coad@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Core
 * @subpackage Formatting
 */
class LeftCropWordEllipsisEncoder extends FieldFormatter {
	var $length = 0;
	var $flash = false;

	function LeftCropWordEllipsisEncoder($length, $flash = false) {
		$this->length = $length;
		$this->flash = $flash;
	}

	function format($value) {
		$value = trim(strip_tags($value));

		$string = mb_substr($value, 0, $this->length);
		$key1 = strripos($string, " ");
		$key2 = strripos($string, "&nbsp;");

		if ($key1 >= $key2) {
			$position = $key1;
		} else {
			$position = $key2;
		}

		if (mb_strlen($value) > $this->length) {
			if ($this->flash) {
				$string = mb_substr($value, 0, $this->length);
				return substr($string, 0, $position) . "...";
			} else {
				$string = mb_substr($value, 0, $this->length);
				return substr($string, 0, $position) . "&hellip;";
			}
		} else {
			return mb_substr($value, 0, $this->length);
		}
	}
}

/**
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Core
 * @subpackage Formatting
 */

class PadFormatter extends FieldFormatter {
	var $amount;
	var $padding;
	var $padType;
	var $prefix;

	function PadFormatter($amount, $padding, $padType = null, $prefix = null) {
		$this->amount = $amount;
		$this->padding = $padding;
		$this->padType = $padType;
		$this->prefix = $prefix;
	}

	function format($value) {
		return $this->prefix . str_pad($value, $this->amount, $this->padding, $this->padType);
	}
}

/**
 * Prefixes/Postfixes the value with a given string
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Core
 * @subpackage Formatting
 */
class ConCatFormatter extends FieldFormatter {
	var $prefix;
	var $postfix;

	function ConCatFormatter($prefix = null, $postfix = null) {
		$this->prefix = $prefix;
		$this->postfix = $postfix;
	}

	function format($value) {
		return $this->prefix . $value . $this->postfix;
	}
}

/**
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Core
 * @subpackage Formatting
 */
class UpperCaseFormatter extends FieldFormatter {
	function format($value) {
		return mb_strtoupper($value);
	}
}

/**
 * Replaces whitespace with a dash
 *
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Core
 * @subpackage Data
 */
class DasherizeFormatter extends FieldFormatter {
	function format($value) {
		return preg_replace("/\s+/", "-", trim($value));
	}
}

/**
 * Urlencode text however ignores any slashes
 *
 * @author Tom Smith {@link mailto:thomas.smith@clock.co.uk thomas.smith@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Core
 */
class UrlSlashEncoder extends FieldFormatter {
	function format($value) {
		return str_replace("%2F", "/", urlencode($value));
	}
}



/**
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Core
 * @subpackage Formatting
 */
class BodyTextFormatter extends FieldFormatter {
	function format($value) {
		//TODO: Should it be this line to catch accented characters eg e with double dots above
		//return html_entity_decode(HtmlControl::formatTaggedText(htmlentities($value, ENT_QUOTES, "UTF-8")));
		$htmlControl = CoreFactory::getHtmlControl();
		return $htmlControl->formatTaggedText(htmlentities($value, ENT_QUOTES, "UTF-8"));
	}
}

class ImageResizeAndCropEncoder extends FieldFormatter {

	function ImageResizeAndCropEncoder($width, $height) {
		$this->width = $width;
		$this->height = $height;
	}

	function format($value) {
		$imageControl = CoreFactory::getImageControl();
		if ($value) {
			$imageControl->resizeAndCrop($value, $value, $this->width, $this->height);
		}
		return $value;
	}
}

class ImageResizeEncoder extends FieldFormatter {

	function ImageResizeEncoder($width, $height) {
		$this->width = $width;
		$this->height = $height;
	}

	function format($value) {
		$imageControl = CoreFactory::getImageControl();
		if ($value) {
			$imageControl->resizeAndCrop($value, $value, $this->width, $this->height, false, true);
		}
		return $value;
	}
}

/**
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Core
 * @subpackage Formatting
 */
class HtmlEncoder extends FieldFormatter {
	function format($value) {
		return $value;
	}
}

/**
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Core
 * @subpackage Formatting
 */
class JavascriptStringEncoder extends FieldFormatter {
	function format($value) {
		return addslashes(preg_replace('/[\r|\n]+/', " ", $value));
	}
}

/**
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Core
 * @subpackage Formatting
 */
class SafeHtmlEncoder extends FieldFormatter {
	function format($value) {
		$htmlControl = CoreFactory::getHtmlControl();
		return $htmlControl->sanitiseHtml(stripslashes(trim($value)));
	}
}

/**
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Core
 * @subpackage Formatting
 */
class BooleanFormatter extends FieldFormatter {
	function format($value) {
		if (empty($value)) {
			return;
		}
		return ($value == "t") ? "Yes":"No";
	}
}

/**
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Core
 * @subpackage Formatting
 */
class PercentageFormatter extends FieldFormatter {
	function format($value) {
		/**
		 * SERBY: please advise, when checked by rob - found to be incorrect
		 * replaced was found to be return
		 */
		if ($value == 0) {
			return "0%";
		} else {
			return number_format($value * 100, 2) . "%";
			//return rtrim(trim(number_format($value * 100, 2), "0"), ".") . "%";
		}
	}
}

/**
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Core
 * @subpackage Formatting
 */
class RealFormatter extends FieldFormatter {

	var $precision = 2;

	function format($value) {

		if ($value === null || $value === "") {
			return null;
		}

		if (is_numeric($value)) {
			if ($this->precision <= 0) {
				return number_format($value, 0);
			} else {
				return rtrim(rtrim(number_format($value, $this->precision), "0"), ".");
			}
		} else {
			return $value;
		}
	}

	function setDecimalPrecision($value) {
		$this->precision = $value;
	}
}

/**
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Core
 * @subpackage Formatting
 */
class RealWithTrailingZerosFormatter extends FieldFormatter {

	var $precision = 2;

	function &format($value) {
		if ($value === null || $value === "") {
			return null;
		}
		return number_format($value, $this->precision);
	}

	function setDecimalPrecision($value) {
		$this->precision = $value;
	}
}

/**
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Core
 * @subpackage Formatting
 */
class IntegerFormatter extends FieldFormatter {
	function format($value) {
		if ($value === null || $value === "") {
			return null;
		}
		if (is_int($value)) {
			return number_format($value);
		} else {
			return $value;
		}
	}
}

/**
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @package Core
 * @subpackage Formatting
 */
class RealEncoder extends FieldFormatter {
	function format($value) {
		if ($value === null || $value === "") {
			return null;
		}
		$new = preg_replace(array("/[^(\.|\-|\d)]/"), "", $value);
		$new = mb_substr($new, 0, 1) . preg_replace(array("/-/"), "", mb_substr($new,1));

		if (($new !== "") && (is_numeric($new))) {
			return (float)$new;
		} else {
			return $value;
		}

	}
}

/**
 * Ensures that input produces a valid JSON string 
 * @package Core
 * @subpackage Formatting
 */
class JsonEncoder extends FieldFormatter {
	function format($value) {
		return json_encode($value);
	}
}

/**
 * Ensures that a JSON string can be converted back to PHP
 * @package Core
 * @subpackage Formatting
 */
class JsonDecoder extends FieldFormatter {
	function format($value) {
		if (json_decode($value)) {
			return json_decode($value);
		} else {
			trigger_error("Malformed JSON string being decoded: {$value}",E_USER_ERROR);
		}
	}
}

/**
 * Ensures that input is a valid Integer
 * @package Core
 * @subpackage Formatting
 */
class IntegerEncoder extends FieldFormatter {
	function format($value) {
		if ($value === null || $value === "") {
			return null;
		}
		$new = preg_replace(array("/[^(\.|\-|\d)]/"), "", $value);
		$new = mb_substr($new, 0, 1) . preg_replace(array("/-/"), "", mb_substr($new, 1));
		if (is_numeric($new) && ($new == intval($new))) {
			return (int)$new;
		} else {
			return $value;
		}
	}
}

/**
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @package Core
 * @subpackage Formatting
 */
class CurrencyEncoder extends FieldFormatter {
	function format($value) {
		if ($value === null || $value === "") {
			return null;
		}
		$value = preg_replace(array("/[^(\.|\-|\d)]/"), "", $value);
		$value = mb_substr($value,0,1) . preg_replace(array("/-/"), "", mb_substr($value,1));
		$application = &CoreFactory::getApplication();
		return $application->encodeCurrency((float)$value);
	}
}

/**
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @package Core
 * @subpackage Formatting
 */
class PercentageEncoder extends FieldFormatter {
	function format($value) {
		$value = preg_replace(array("/[^(\.|\-|\d)]/"), "", $value);
		return $value / 100;
	}
}

/**
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @package Core
 * @subpackage Formatting
 */
class CurrencyFormatter extends FieldFormatter {
	function format($value) {
		$application = &CoreFactory::getApplication();
		return $application->formatCurrency($value);
	}
}

/**
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Core
 * @subpackage Formatting
 */
class PlainCurrencyFormatter extends FieldFormatter {
	function format($value) {
		$application = &CoreFactory::getApplication();
		return html_entity_decode($application->formatCurrency($value));
	}
}

/**
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Core
 * @subpackage Formatting
 */
class CurrencyNoUnitsFormatter extends FieldFormatter {
	function format($value) {
		$application = &CoreFactory::getApplication();
		return $application->formatCurrency($value, 0);
	}
}

/**
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Core
 * @subpackage Formatting
 */
class CardNumberFormatter extends FieldFormatter {
	function format($value) {
		return str_pad(mb_substr($value,-4),mb_strlen($value), "*",STR_PAD_LEFT);
	}
}

/**
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Core
 * @subpackage Formatting
 */
class ArrayDateEncoder extends FieldFormatter {

	function format($value) {
		if (!is_array($value)) {
			return $value;
		}
		if ($value == null) {
			return null;
		}
		if ($value["Year"] == "") {
			return null;
		}
		if (!isset($value["Day"])) {
			$value["Day"] = 1;
		}
		if (!isset($value["Month"])) {
			$value["Month"] = 1;
		}
		if (!isset($value["Year"])) {
			$value["Year"] = 1990;
		}


		$value["Month"] = str_pad($value["Month"], 2, "0", STR_PAD_LEFT);
		$value["Day"] = str_pad($value["Day"], 2, "0", STR_PAD_LEFT);

		return "$value[Year]-$value[Month]-$value[Day]";
	}
}

/**
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Core
 * @subpackage Formatting
 */
class ArrayDateTimeEncoder extends FieldFormatter {
	function format($value) {
		if (!is_array($value)) {
			return $value;
		}
		if (!isset($value["Day"])) {
			$value["Day"] = "01";
		}

		if (($value == null) || (!is_array($value))) {
			return null;
		}
		if ($value["Year"] . $value["Month"] . $value["Day"] .
				$value["Hour"] . $value["Minute"] == null) {
			return null;
		}
		$application = &CoreFactory::getApplication();


		//TODO: Add TimeZone to date $value[GmtOffset]";
		//TODO: Daylight saving on timezones

		$value["Month"] = str_pad($value["Month"], 2, "0", STR_PAD_LEFT);
		$value["Day"] = str_pad($value["Day"], 2, "0", STR_PAD_LEFT);
		$value["Hour"] = str_pad($value["Hour"], 2, "0", STR_PAD_LEFT);
		$value["Minute"] = str_pad($value["Minute"], 2, "0", STR_PAD_LEFT);

		return $application->formatDBDateTime("$value[Year]-$value[Month]-$value[Day] $value[Hour]:$value[Minute]:00");

	}
}

/**
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Core
 * @subpackage Formatting
 */
class ArrayMonthYearEncoder extends FieldFormatter {
	function format($value) {
		if (!is_array($value)) {
			return str_replace(" ", "/", $value);
		}

		if ($value == null) {
			return null;
		}
		if ($value["Year"] . $value["Month"] == null) {
			return null;
		}
		return "$value[Month]" . "/" . "$value[Year]";
	}
}

/**
 * @author Elliot Coad {@link mailto:elliot.coad@clock.co.uk elliot.coad@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Core
 * @subpackage Formatting
 */
class UrlEncoder extends FieldFormatter {
	function format($value) {
		return urlencode($value);
	}
}

/**
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Core
 * @subpackage Formatting
 */
class UrlDecoder extends FieldFormatter {
	function format($value) {
		return urldecode($value);
	}
}

/**
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Core
 * @subpackage Formatting
 */
class UrlFieldEncoder extends FieldFormatter {
	function format($value) {
		if (($value == "") || (preg_match("'^(.*):\/\/'", $value))) {
			return trim($value, " /");
		} else {
			return "http://" . trim($value, " /");
		}
	}
}

/**
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Core
 * @subpackage Formatting
 */
class StringFormatter extends FieldFormatter {
	function format($value) {
		return htmlspecialchars($value);
	}
}

/**
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Core
 * @subpackage Formatting
 */
class StringEncoder extends FieldFormatter {
	function format($value) {
		return stripslashes(trim($value));
	}
}

/**
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Core
 * @subpackage Formatting
 */
class StringUpperEncoder extends StringEncoder {
	function format($value) {
		return mb_strtoupper(parent::format($value));
	}
}

/**
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Core
 * @subpackage Formatting
 */
class StringLowerEncoder extends StringEncoder {
	function format($value) {
		return mb_strtolower(parent::format($value));
	}
}

/**
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Core
 * @subpackage Formatting
 */
class PasswordEncoder extends StringEncoder {
	function format($value) {
		if ($value != null) {
			return sha1(parent::format($value));
		} else {
			return null;
		}
	}
}

/**
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Core
 * @subpackage Formatting
 */
class BooleanEncoder extends FieldFormatter {
	function format($value) {
		return ($value=="t"?"t":"f");
	}
}

/**
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Core
 * @subpackage Formatting

class HtmlMultiPageControlResultsFormatter
	extends MultiPageControlResultsFormatter {

	function display(&$object, $queryStringControl, $showTotal = true) {

		$nr = $object->getNumRows();
		$cp = $object->getCurrentPage();
		$pc = $object->getPageCount();
		$pl = $object->getPageLength();
		if ($nr < 1) {
			return false;
		}
		// Don't both showing the multipage control if there are less than 8 items.
		if (($pl <= 8) && ($nr < $pl)) {
			if (($showTotal) && ($nr > 10)) {
				return "<p>Total: <strong title=\"The Total number of records found\">" . number_format($nr, 0) . "</strong></p>";
			} else {
				return "";
			}
		}

		$queryString = $queryStringControl->getValues("CurrentPage", null);

		$out = "<p>Total: <strong title=\"The Total number of records found\">" . number_format($nr, 0) . "</strong> - Page <strong title=\"The current page of results\">$cp</strong> of
				<a href=\"?$queryString&amp;CurrentPage=" . $pc . "\" title=\"The total number of result pages. Clicking here will take you to the last page\">$pc</a> - ";

		$start = max($cp - 3, 1);
		$end = min($start + 6, $pc);
		$start = max($end - 6, 1);
		$out .= "<a class=\"button\" href=\"?$queryString&amp;CurrentPage=1\" title=\"Goto the first page of results\">|&lt;</a>";
		$out .= "<a class=\"button\" href=\"?$queryString&amp;CurrentPage=" . ($cp - 1) . "\" title=\"Goto the previous page of results\">&lt;</a>";
		for ($i = $start; $i <= $end; $i++) {
			$out .= "<a href=\"?$queryString&amp;CurrentPage=$i\" title=\"Goto page $i of the results\">$i</a>,&nbsp;";
		}
		$out = mb_substr($out, 0, -7) . "&nbsp;";
		$out .= "<a class=\"button\" href=\"?$queryString&amp;CurrentPage=" . ($cp + 1) . "\" title=\"Goto the next page of results\">&gt;</a>";
		$out .= "<a class=\"button\" href=\"?$queryString&amp;CurrentPage=-1\" title=\"Goto the last page of results\">&gt;|</a>";
		$out .= "<select name=\"PageLength\" class=\"page_length\" onchange=\"submit();\" title=\"How many results to display per page\">";
		$out .= "<option value=\"\">All</option>";
		$out .= HtmlControl::createRppListValues($pl);
		$out .= "</select>";
		$out .= "<input type=\"submit\" name=\"Go\" value=\"Go\" class=\"button\" title=\"Some old browsers will not automatically change the Results Per Page. In these cases you can click this button to submit a change to the Results per page\" />\n";
		$out .= $queryStringControl->getHiddenControls(array("PageLength")) . "</p>";
		return $out;
	}
}
*/

class PreviousNextHtmlMultiPageControlResultsFormatter
	extends MultiPageControlResultsFormatter {

	function display(&$object, $queryStringControl, $extraClass = "", $hidePageNumbers = false) {

		$nr = $object->getNumRows();
		$cp = $object->getCurrentPage();
		$pc = $object->getPageCount();
		$pl = $object->getPageLength();

		if ($nr < 1) {
			return false;
		}

		if (($pl <= $object->application->registry->get("ListSize")) && ($nr < $pl)) {
			return false;
		}

		if ($pc < 2) {
			return false;
		}

		$queryString = $queryStringControl->getValues("CurrentPage", null);

		$out = "";

		$start = max($cp - 2, 1);
		$end = min($start + 4, $pc);
		$start = max($end - 4, 1);

		$out .= "<a href=\"?$queryString&amp;CurrentPage=" . (($cp - 1) ? ($cp - 1) : 1) . "\" title=\"Goto the previous page of results\" class=\"previous\"><strong><< Previous</strong></a>";
		
		if (!$hidePageNumbers) {
			$out .= "<div class=\"numeration\">";
			for ($i = $start; $i <= $end; $i++) {
				if ($i == $cp) {
					$out .= "<span class=\"current\">$i</span>";
				} else {
					$out .= "<a href=\"?$queryString&amp;CurrentPage=$i\" title=\"Goto page $i of the results\"><span>$i</span></a>";
				}
			}
			$out .= "</div>";
		}
		$out .= "<a href=\"?$queryString&amp;CurrentPage=" . ((($cp + 1) > $pc) ? $pc : ($cp + 1)) . "\" title=\"Goto the next page of results\" class=\"next\"><strong>Next >></strong></a>";
		return "<div class=\"multipage-controller $extraClass\">$out</div>";
	}
}

class SimpleHtmlMultiPageControlResultsFormatter
	extends MultiPageControlResultsFormatter {

	function display(&$object, $queryStringControl, $showTotal = true, $resultsName = "Pages", $className = "multipagecontrol") {

		$nr = $object->getNumRows();
		$cp = $object->getCurrentPage();
		$pc = $object->getPageCount();
		$pl = $object->getPageLength();

		if ($nr < 1) {
			return false;
		}

		if (($pl <= $object->application->registry->get("ListSize")) && ($nr < $pl)) {
			return false;
		}

		$queryString = $queryStringControl->getValues("CurrentPage", null);

		$out = "<p>" . $resultsName . "&nbsp;";

		$start = max($cp - 2, 1);
		$end = min($start + 4, $pc);
		$start = max($end - 4, 1);

		if ($cp > 1) {
			$out .= "<a href=\"?$queryString&amp;CurrentPage=1\" title=\"Goto the first page of results\">&#9668;&#9668;</a>";
			$out .= "<a href=\"?$queryString&amp;CurrentPage=" . ($cp - 1) . "\" title=\"Goto the previous page of results\">&#9668;</a>";
		}
		for ($i = $start; $i <= $end; $i++) {
			$class = "";
			if ($i == $cp) {
				$class = " class=\"currentpage\"";
			}
			$out .= "<a" . $class . " href=\"?$queryString&amp;CurrentPage=$i\" title=\"Goto page $i of the results\">$i</a><span>&nbsp;|&nbsp;</span>";
		}
		$out = mb_substr($out, 0, -26) . "<span>&nbsp;</span>";
		if ($cp < $pc) {
			$out .= "<a href=\"?$queryString&amp;CurrentPage=" . ($cp + 1) . "\" title=\"Goto the next page of results\">&#9658;</a><span>&nbsp;</span>";
			$out .= "<a href=\"?$queryString&amp;CurrentPage=-1\" title=\"Goto the last page of results\">&#9658;&#9658;</a>";
		}
		$out .= $queryStringControl->getHiddenControls(array("PageLength")) . "</p>";
		return "<div class=\"$className\">$out</div>";
	}
}

class ForwardBackwardsControlResultsFormatter
	extends MultiPageControlResultsFormatter {

	function display(&$object, $queryStringControl, $count = 2, $prefix = "") {

		$nr = $object->getNumRows();
		$cp = $object->getCurrentPage();
		$pc = $object->getPageCount();
		$pl = $object->getPageLength();

		if ($nr < 1) {
			return false;
		}

		$queryString = $queryStringControl->getValues("{$prefix}CurrentPage", null);


		$lowerHalf = floor($count / 2);
		$upperHalf = ceil($count / 2);

		$start = max($cp - $lowerHalf, 1);
		$end = min($start + $count, $pc);
		$start = max($end - $count, 1);

		$out = "<p>";
		$out .= "<a href=\"?$queryString&amp;{$prefix}CurrentPage=1\" title=\"Goto the first page\">|&#9668;</a>";
		$out .= "<a href=\"?$queryString&amp;{$prefix}CurrentPage=" . ($cp - 1) . "\" title=\"Goto the previous page of results\">&#9668;</a>";

		for ($i = $start; $i <= $end; $i++) {
			$class = "";
			if ($i == $cp) {
				$class = " class=\"currentpage\"";
			}
			$out .= "<a" . $class . " href=\"?$queryString&amp;{$prefix}CurrentPage=$i\" title=\"Goto page $i of the results\">$i</a><span>&nbsp;,&nbsp;</span>";
		}




		$out = mb_substr($out, 0, -26) . "<span>&nbsp;</span>";
		//$out .= "<span>&nbsp;</span>";
		$out .= "<a href=\"?$queryString&amp;{$prefix}CurrentPage=" . ($cp + 1) . "\" title=\"Goto the next page of results\">&#9658;</a><span>&nbsp;</span>";
		$out .= "<a href=\"?$queryString&amp;{$prefix}CurrentPage=$pc\" title=\"Goto the last page\">&#9658;|</a>";

		return "<div class=\"multipagecontrol\">$out</div>";
	}
}

class HtmlMultiPageControlResultsFormatter
	extends MultiPageControlResultsFormatter {

	function display(&$object, $queryStringControl, $showTotal = true, $controllerClass = "multipagecontrol", $totalArticlesClass = "total_articles", $currentPageClass = "currentpage", $selectClass = "page_length", $buttonClass = "button", $seperatorClass = "") {

		$nr = $object->getNumRows();
		$cp = $object->getCurrentPage();
		$pc = $object->getPageCount();
		$pl = $object->getPageLength();

		if ($nr < 1) {
			return false;
		}
		// Don't both showing the multipage control if there are less than 8 items.
		if (($pl <= $object->application->registry->get("ListSize")) && ($nr < $pl)) {
			if (($showTotal) && ($nr > 10)) {
				return "<p class=\"" . $buttonClass . "\">There are a total of <strong title=\"The Total number of records found\">" . number_format($nr, 0) . "</strong> items. </p>";
			} else {
				return "";
			}
		}

		$queryString = $queryStringControl->getValues("CurrentPage", null);

		$out = "<p><span class=\"" . $totalArticlesClass . "\">There are a total of <strong title=\"The Total number of records found\">" . number_format($nr, 0) . "</strong> items. </span>";

		$start = max($cp - 3, 1);
		$end = min($start + 6, $pc);
		$start = max($end - 6, 1);

		$out .= "<a href=\"?$queryString&amp;CurrentPage=1\" title=\"Goto the first page of results\">|&#9668; First</a>";
		$out .= "<a href=\"?$queryString&amp;CurrentPage=" . ($cp - 1) . "\" title=\"Goto the previous page of results\">&#9668; Prev</a>";

		for ($i = $start; $i <= $end; $i++) {
			$class = "";
			if ($i == $cp) {
				$class = " class=\"" . $currentPageClass . "\"";
			}
			$out .= "<a" . $class . " href=\"?$queryString&amp;CurrentPage=$i\" title=\"Goto page $i of the results\">$i</a>";
			if ($seperatorClass != "") {
				$out .= "<span class=\"" . $seperatorClass . "\">&nbsp;,&nbsp;</span>";
			} else {
				$out .= "<span>&nbsp;,&nbsp;</span>";
			}
		}
		if ($seperatorClass != "") {
			$separatorLength = 0 - strlen($seperatorClass) - 35;
		} else {
			$separatorLength = -26;
		}

		$out = mb_substr($out, 0, $separatorLength);
		//$out .= "<span>&nbsp;</span>";
		$out .= "<a href=\"?$queryString&amp;CurrentPage=" . ($cp + 1) . "\" title=\"Goto the next page of results\">Next &#9658;</a>";
		$out .= "<a href=\"?$queryString&amp;CurrentPage=-1\" title=\"Goto the last page of results\">Last &#9658;|</a>";
		$out .= "<select name=\"PageLength\" class=\"" . $selectClass . "\" onchange=\"submit();\" title=\"How many results to display per page\">";
		$out .= "<option value=\"\">All</option>";
		$htmlControl = CoreFactory::getHtmlControl();
		$out .= $htmlControl->createRppListValues($pl);
		$out .= "</select>";
		$out .= $queryStringControl->getHiddenControls(array("PageLength")) . "</p>";
		return "<div class=\"" . $controllerClass . "\">" . $out . "</div>";
	}
}

/**
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Core
 * @subpackage Formatting
 */
class HtmlSelectOptions {

	function create(&$object, $fieldName, $default = "", $id = "Id", $maxLength = null, $usedId = true, $colorField = null) {
		$out = "";
		$leftCropEncoder = CoreFactory::getLeftCropEncoder($maxLength);
		while ($data = $object->getNext()) {
			$idValue = $data->get($id);

			$colorStyle = "";
			$color = "";

			if ($colorField) {
				if (is_array($colorField)) {
					$keys = array_keys($colorField);
					$color = $data->getRelationValue($colorField[$keys[0]], $keys[0]);
				} else {
					$color = $data->getFormatted($colorField);
				}
				if (!$color) {
					$color = "FFFFFF";
				}
				$colorStyle = " style=\"margin: 2px; padding-left: 5px; border-left: 15px solid {$color}; \"";
			}

			if ($usedId) {
				$out .= "\t<option" .(($default==$idValue)?" selected=\"selected\"":""). " value=\"$idValue\"{$colorStyle}>";
			} else {
				$out .= "\t<option" .(($default==$idValue)?" selected=\"selected\"":""). "{$colorStyle}>";
			}

			$out .= $leftCropEncoder->format($data->getFormatted($fieldName)) . "</option>\n";
		}
		$object->reset();
		return $out;
	}

	function createMultiField(&$object, $fieldNames, $seperator = " ", $default = "", $id = "Id", $maxLength = 0) {
		$out = "";
		while ($data = $object->getNext()) {
			$idValue = $data->get($id);
			$out .= "\t<option" .(($default==$idValue)?" selected=\"selected\"":""). " value=\"$idValue\">";

			$listValue = "";
			foreach($fieldNames as $key => $fieldName) {
				if (is_numeric($key)) {
					$listValue .= $data->getFormatted($fieldName) . $seperator;
				} else {
					$listValue .= $data->getRelationValue($key, $fieldName) . $seperator;
				}
			}

			$listValue = mb_substr($listValue, 0, -mb_strlen($seperator));

			if ($maxLength > 0) {
				$out .= mb_substr($listValue, 0, $maxLength) . "</option>\n";
			} else {
				$out .= $listValue . "</option>\n";
			}
		}
		$object->reset();
		return $out;
	}
			/**
		 * Creates drop-down options, formatted as per sprintf().
		 * @param DataEntity $object The control object to take values from
		 * @param Array $fieldName An array of the field names to be used
		 * @param String $format Format stringl as per sprintf()
		 * @param Integer $default The Id to be selected by default
		 * @param String $id The name of the Id field
		 * @param Integer $maxLength The maximum length of an option
		 * @return The multi page control
		 */
		 //Replaced 2.2 with 2.1 version to work with intranet - commented out by tom, new function below
//		function createFormattedMultiField(&$object, $fieldNames, $format, $default = "", $id = "Id", $maxLength = 0) {
//			$out = "";
//			while ($data = $object->getNext()) {
//				$idValue = $data->get($id);
//				$out .= "\t<option" .(($default==$idValue)?" selected=\"selected\"":""). " value=\"$idValue\">";
//
//				$listData = array();
//				foreach($fieldNames as $key => $fieldName) {
//					if (is_numeric($key)) {
//						$listData[] = $data->getFormatted($fieldName);
//					} else {
//						$listData[] = $data->getRelationValue($key, $fieldName);
//					}
//				}
//
//				$listValue = vsprintf($format, $listData);
//				if ($maxLength > 0) {
//					$out .= mb_substr($listValue, 0, $maxLength) . "</option>\n";
//				} else {
//					$out .= $listValue . "</option>\n";
//				}
//			}
//			$object->reset();
//			return $out;
//		}
	function createFormattedMultiField(&$object, $fieldNames, $format, $default = "", $id = "Id", $maxLength = 0, $colorField = null) {
		$out = "";

		while ($data = $object->getNext()) {

			$idValue = $data->get($id);

			$colorStyle = "";
			$color = "";

			if ($colorField) {
				if (is_array($colorField)) {
					$keys = array_keys($colorField);
					$color = $data->getRelationValue($colorField[$keys[0]], $keys[0]);
				} else {
					$color = $data->getFormatted($colorField);
				}
				if (!$color) {
					$color = "#FFFFFF";
				}
				$colorStyle = " style=\"margin: 2px; padding-left: 5px; border-left: 15px solid {$color}; \"";
			}

			$out .= "\t<option" . (($default==$idValue)?" selected=\"selected\"":"") . " value=\"$idValue\"{$colorStyle}>";

			$listData = array();
			foreach($fieldNames as $key => $fieldName) {
				if (is_numeric($key)) {
					$listData[] = $data->getFormatted($fieldName);
				} else {
					$listData[] = $data->getRelationValue($fieldName, $key);
				}
			}


			$listValue = vsprintf($format, $listData);
			if ($maxLength > 0) {
				$out .= mb_substr($listValue, 0, $maxLength) . "</option>\n";
			} else {
				$out .= $listValue . "</option>\n";
			}
		}
		$object->reset();
		return $out;
	}
}

/**
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Core
 * @subpackage Formatting
 */
class HtmlShowBinary {
	function display(&$dataEntity) {
		if (($dataEntity instanceof DataEntity)) {
			return $dataEntity->get("Filename");
		} else {
			return "";
		}
	}
}

/**
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Core
 * @subpackage Formatting
 */
class DocumentFormatter extends FieldFormatter {
	function format($value) {
		$parser = CoreFactory::getDocumentMarkupParser();
		return $parser->parseDocument($value);
	}
}


/**
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Core
 * @subpackage Formatting
 */
class SimpleDocumentMarkupFormatter extends FieldFormatter {
	function format($value) {
		$parser = CoreFactory::getSimpleDocumentMarkupParser();
		return $parser->parseDocument($value);
	}
}

/**
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Core
 * @subpackage Formatting
 */
class SimpleDocumentMarkupWithTableOfContentsFormatter extends FieldFormatter {
	function format($value) {
		$parser = CoreFactory::getSimpleDocumentMarkupWithTableOfContentsParser();
		return $parser->parseDocument($value);
	}
}