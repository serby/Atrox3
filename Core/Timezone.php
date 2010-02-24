<?php
/**
 * @package Core
 * @copyright Clock Limited 2010
 * @version 3.2
 */

/**
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Core
 */
class TimeZone {
	var $abbreviation = null;
	var $gmtOffset = null;
	var $name = null;
	var $daylightSaving = null;

	function TimeZone($abbreviation, $gmtOffset, $name, $daylightSaving=null) {
		$this->abbreviation = $abbreviation;
		$this->gmtOffset = $gmtOffset;
		$this->name = $name;
		$this->daylightSaving = $daylightSaving;

		// Run daylight savings converter.
		if (isset($daylightSaving)) {
			$daylightSaving->run($this);
		}
	}

	function getAbbreviation() {
		return $this->abbreviation;
	}

	function getGmtOffset() {
		return $this->gmtOffset;
	}

	function getName() {
		return $this->name;
	}
}

class TimeZoneControl {

	var $timeZoneData = null;

	function TimeZoneControl() {

		$this->timeZoneData["IdLW"] = new TimeZone("IdLW",-43200,
				"International Date Line West");

		$this->timeZoneData["NT"] = new TimeZone("NT",-39600,
				"Nome Time");

		$this->timeZoneData["AHST"] = new TimeZone("AHST",-36000,
				"Alaska-Hawaii Standard Time");

		$this->timeZoneData["YST"] = new TimeZone("YST",-32400,
				"Yukon Standard Time");

		$this->timeZoneData["PST"] = new TimeZone("PST",-28800,
				"Pacific Standard Time");

		$this->timeZoneData["IdLW"] = new TimeZone("IdLW",-25200,
				"Mountain Standard Time");

		$this->timeZoneData["MST"] = new TimeZone("MST",-43200,
				"International Date Line West");

		$this->timeZoneData["CST"] = new TimeZone("CST",-21600,
				"Central Standard Time");

		$this->timeZoneData["EST"] = new TimeZone("EST",-18000,
				"Eastern Standard Time");

		$this->timeZoneData["AST"] = new TimeZone("AST",-14400,
				"Atlantic Standard Time");

		$this->timeZoneData["AT"] = new TimeZone("AT",-7200,
				"Azores Time");

		$this->timeZoneData["WAT"] = new TimeZone("WAT",-3600,
				"West Africa Time");

		$this->timeZoneData["GMT"] = new TimeZone("GMT", 0,
				"Greenwich Mean Time");

		$this->timeZoneData["CET"] = new TimeZone("CET", 3600,
				"Central European Time");

		$this->timeZoneData["EET"] = new TimeZone("EET", 7200,
				"Eastern European Time and USSR Zone 1");

		$this->timeZoneData["BT"] = new TimeZone("BT", 10800,
				"Baghdad Time and USSR Zone 2");

		$this->timeZoneData["ZP4"] = new TimeZone("ZP4", 12600,
				"USSR Zone 3");

		$this->timeZoneData["ZP5"] = new TimeZone("ZP5", 18000,
				"USSR Zone 4");

		$this->timeZoneData["ZP6"] = new TimeZone("ZP6", 21600,
				"USSR Zone 5");

		$this->timeZoneData["ZP7"] = new TimeZone("ZP7", 25200,
				"USSR Zone 6");

		$this->timeZoneData["WAST"] = new TimeZone("WAST", 28800,
				"West Australian Standard Time and USSR Zone 7");

		$this->timeZoneData["JST"] = new TimeZone("JST", 32400,
				"Japan Standard Time and USSR Zone 8");

		$this->timeZoneData["ACT"] = new TimeZone("ACT", 34200,
				"Australian Central Time");

		$this->timeZoneData["EAST"] = new TimeZone("EAST", 36000,
				"East Australian Standard Time and USSR Zone 9");

		$this->timeZoneData["MG"] = new TimeZone("MG", 39600,
				"Magadan");

		$this->timeZoneData["IdLE"] = new TimeZone("IdLE", 43200,
				"International Date Line East");
	}

	function getArray() {
		$array = null;
		foreach ($this->timeZoneData as $k => $v) {
			$array[$k] = $v->name;
		}
		return $array;
	}

	function getTimeZone($code) {
		if (isset($this->timeZoneData[$code])) {
			return $this->timeZoneData[$code];
		} else {
			trigger_error("Bad Time Zone");
		}
	}
}