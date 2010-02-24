<?php
/**
 * @package Core
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 */

/**
 * Includes nusoap.php
 */
require_once("nusoap/1.94/nusoap.php");

define("POSTCODE_SERVICE", "http://clockwebservices.clock.co.uk/postcodes/postcodes.php");
define("IPTOCOUNTRY_SERVICE", "http://clockwebservices.fred.wd4.clock.co.uk/ip-to-country/ip-to-country.php");
define("GOOGLE_MAPS_HTTP_REQUEST_ADDRESS", "http://maps.google.com/maps/geo?");

/**
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Core
 */
class PostcodeLocator {

	var $soapClient = null;

	var $params = array("Postcode" => "");

	function PostcodeLocator() {
		$this->soapClient = new SoapClient(POSTCODE_SERVICE);
	}

	function search($postcode) {
		$this->params["Postcode"] = $postcode;

		$searchResults = $this->soapClient->call(
		"getCoordinates",
		$this->params);

		return $searchResults;
	}
}


/**
 * @author Tom Smith (Clock Ltd) {@link mailto:thomas.smith@clock.co.uk thomas.smith@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Core
 * @subpackage Geo
 */
class GoogleMapsAddressFinder {
	var $apiKey = null;
	var $output = "xml";
	var $response = null;
	var $formattedResponse = null;
	var $url = null;

	function GoogleMapsAddressFinder() {
		$application = CoreFactory::getApplication();
		$this->apiKey = $application->registry->get("Google/Maps/ApiKey");
	}

	function setOutputFormat($format) {
		$this->output = $format;
	}

	function getUrl() {
		return $this->url;
	}
	
	function makeRequest($location) {
		$location = urlencode($location);
		$this->url = GOOGLE_MAPS_HTTP_REQUEST_ADDRESS . "q={$location},UK&output={$this->output}&key={$this->apiKey}";
		$this->response = @file_get_contents($this->url);
	}
	
	function getFormattedResponse($location) {
		$this->makeRequest($location);
		$this->formattedResponse = "";
		switch($this->output) {
			case "xml":
				$xmlParser = CoreFactory::getXmlParser();
				$this->formattedResponse = $xmlParser->toArray($this->response);
				break;
		}
		return $this->formattedResponse;
	}
	
	function getField($field) {
		if (!$this->output == "xml") {
			return false;
		}
		switch($field) {
			case "LatLong":
				if ($this->getField("Status") == 200) {
					return explode(",", $this->formattedResponse["kml"][0]["response"][0]["placemark"][0]["point"][0]["coordinates"][0]["Data"]);
				}
				break;
			case "Address":
				if ($this->getField("Status") == 200) {
					return $this->formattedResponse["kml"][0]["response"][0]["placemark"][0]["address"][0]["Data"];
				}
				break;
			case "Status":
				if (isset($this->formattedResponse["kml"])) {
					return $this->formattedResponse["kml"][0]["response"][0]["status"][0]["code"][0]["Data"];
				} else {
					return false;
				}
				break;
		}
		return false;
	}
}

/**
 * @author Michael Cronnelly (Clock Ltd) {@link mailto:mike@clock.co.uk mike@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Core
 * @subpackage Geo
 */
class IpToCountry {

	var $soapClient = null;

	var $params = array("IP" => "");

	function IpToCountry() {
		$this->soapClient = new SoapClient(IPTOCOUNTRY_SERVICE);
	}

	function getCountryFromIp($ip) {
		$this->params["IP"] = $ip;

		$searchResults = $this->soapClient->call(
		"getCountryFromIp",
		$this->params);

		return $searchResults;
	}

	function getCountryFromClient() {
		$clientIp = $_SERVER["REMOTE_ADDR"];
		return $this->getCountryFromIp($clientIp);
	}
}