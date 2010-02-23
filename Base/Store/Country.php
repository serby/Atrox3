<?php
/**
 * @package Base
 * @subpackage Store
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 */

/**
 * Include Data.php so that DataControl can be extended.
 */
require_once("Atrox/Core/Data/Data.php");
require_once("nusoap/1.94.custom/nusoap.php");

define("CTRY_IMG_MINX", 30);
define("CTRY_IMG_MINY", 20);
define("CTRY_IMG_MAXX", 120);
define("CTRY_IMG_MAXY", 80);

/**
 *
 * @author Adam Forster (Clock Ltd) {@link mailto:adam.forster@clock.co.uk adam.forster@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Base
 * @subpackage Store
 */
class CountryControl extends DataControl {
	var $table = "Country";
	var $key = "Id";
	var $sequence = "Country_Id_seq";
	var $defaultOrder = "Name";
	var $searchFields = array("Name");

	function init() {
		$this->fieldMeta["Id"] = new FieldMeta(
			"Id", "", FM_TYPE_INTEGER, null, FM_STORE_NEVER, false);

		$this->fieldMeta["Name"] = new FieldMeta(
			"Name", "", FM_TYPE_STRING, 50, FM_STORE_ALWAYS, false, FM_OPTIONS_UNIQUE);

		$this->fieldMeta["Currency"] = new FieldMeta(
			"Currency", "", FM_TYPE_STRING_UPPER, 5, FM_STORE_ALWAYS, false);

		$this->fieldMeta["Rate"] = new FieldMeta(
			"Rate", 0, FM_TYPE_FLOAT, null, FM_STORE_ALWAYS, false);

		$this->fieldMeta["Code"] = new FieldMeta(
			"Code", "", FM_TYPE_STRING_UPPER, 5, FM_STORE_ALWAYS, false, FM_OPTIONS_UNIQUE);

		$this->fieldMeta["Symbol"] = new FieldMeta(
			"Symbol", "", FM_TYPE_STRING, 20, FM_STORE_ALWAYS, false);

		$this->fieldMeta["VatExempt"] = new FieldMeta(
			"Vat Exempt", "", FM_TYPE_BOOLEAN, null, FM_STORE_ALWAYS, false);

		$this->fieldMeta["VatExempt"]->setFormatter(CoreFactory::getBooleanFormatter());

		$this->fieldMeta["RateUpdated"] = new FieldMeta(
			"Rate Updated", "", FM_TYPE_DATE, null, FM_STORE_NEVER, false);

		$this->fieldMeta["RateUpdated"]->setFormatter(CoreFactory::getDateTimeFieldFormatter());

		$this->fieldMeta["ImageId"] = new FieldMeta(
			"Image", "", FM_TYPE_BINARY, null, FM_STORE_ALWAYS, true);

		$this->fieldMeta["ImageId"]->setValidation(CoreFactory::getImageValidation(
				CTRY_IMG_MINX,
				CTRY_IMG_MINY,
				CTRY_IMG_MAXX,
				CTRY_IMG_MAXY));
				
		$this->fieldMeta["FreeShippingThreshold"] = new FieldMeta(
			"Free Shipping Threshold", null, FM_TYPE_CURRENCY, null, FM_STORE_ALWAYS, true);
			
		$this->fieldMeta["Zone"] = new FieldMeta(
			"Zone", "", FM_TYPE_INTEGER, null, FM_STORE_ALWAYS, false);
	}

	function updateExchangeRates() {
		$client = new nusoapclient("http://webservices.lb.lt/ExchangeRates/ExchangeRates.asmx", false);

		$baseRate = $client->call("getCurrentExchangeRate",
			array("Currency" => "GBP"),
			"http://webservices.lb.lt/ExchangeRates",
			"http://webservices.lb.lt/ExchangeRates/getCurrentExchangeRate",
			false, null, "rpc", "literal");

		if (($baseRate != -1) && ($baseRate != null)) {
			$this->reset();
			$rates[] = "";

			while ($country = $this->getNext()) {
				$currency = $country->get("Currency");
				if (!array_key_exists($currency, $rates)) {
					$rate = $client->call("getCurrentExchangeRate",
						array("Currency" => $currency),
						"http://webservices.lb.lt/ExchangeRates",
						"http://webservices.lb.lt/ExchangeRates/getCurrentExchangeRate",
						false, null, "rpc", "literal");

					if (($rate != -1) && ($rate != 0)) {
						$rates[$currency] = $baseRate / $rate;
					} else {
						$rates[$currency] = null;
					}
				}

				if ($rates[$currency] != null) {
					$this->updateField($country, "Rate", $rates[$currency]);
					$this->updateField($country, "RateUpdated", $this->databaseControl->getCurrentDateTime());
					$results[$country->get("Name")] = "Updated successfully";
				} else {
					$results[$country->get("Name")] = "Updated failed (invalid currency code)";
				}
			}
		} else {
			$results = "Service not available";
		}
		return $results;
	}
}