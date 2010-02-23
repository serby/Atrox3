<?php
/**
 * @package Base
 * @subpackage Store
 * @copyright Clock Limited 2007
 * @version 3.0 - $Revision$ - $Date$
 */

/**
 * Include Data.php so that DataControl can be extended.
 */
require_once("Atrox/3.0/Core/Data/Data.php");

/**
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2007
 * @version 3.0 - $Revision$ - $Date$
 * @package Base
 * @subpackage Store
 */
class AllowedCountryControl extends DataControl {
	
	var $table = "AllowedCountry";
	var $key = "Id";
	var $sequence = "AllowedCountry_Id_seq";
	var $defaultOrder = "CountryName";
	var $searchFields = array("CountryId", "ProductId", "CountryName");
	
	function init() {							
		$this->fieldMeta["Id"] = new FieldMeta(
			"Id", "", FM_TYPE_INTEGER, null, FM_STORE_NEVER, false);

		$this->fieldMeta["CountryId"] = new FieldMeta(
			"Country Id", "", FM_TYPE_RELATION, null, FM_STORE_ALWAYS, false);

		$this->fieldMeta["CountryId"]->setRelationControl(BaseFactory::getCountryControl());
		
		$this->fieldMeta["CountryName"] = new FieldMeta(
			"Country Name", "", FM_TYPE_STRING, 100, FM_STORE_ALWAYS, false);
		
		$this->fieldMeta["ProductId"] = new FieldMeta(
			"Product Id", "", FM_TYPE_RELATION, null, FM_STORE_ALWAYS, false);

		$this->fieldMeta["ProductId"]->setRelationControl(BaseFactory::getProductControl());
	}
	
	function retrieveForProduct($product) {
		$filter = $this->getFilter();
		$filter->addConditional($this->table, "ProductId", $product->get("Id"));
		$this->setFilter($filter);
		return $this->retrieveAll();
	}
	
	function addCountry(&$product, $countryIds) {
		$countryControl = BaseFactory::getCountryControl();

		if (!is_array($countryIds)) {
			$countryIds = array($countryIds);
		}
		foreach($countryIds as $countryId) {
			if (!$country = $countryControl->item($countryId)) {
				return false;
			}
			$allowedCountry = $this->makeNew();
			$allowedCountry->set("ProductId", $product->get("Id"));
			$allowedCountry->set("CountryId", $country->get("Id"));
			$allowedCountry->set("CountryName", $country->get("Name"));
			$allowedCountry->save();
		}
	}

	function removeCountry(&$toRemoveAllowedCountryIds) {

		if (!is_array($toRemoveAllowedCountryIds)) {
			$toRemoveAllowedCountryIds = array($toRemoveAllowedCountryIds);
		}

		foreach($toRemoveAllowedCountryIds as $allowedCountryId) {
			$this->delete($allowedCountryId);
		}
	}
	
	function isAllowed($stockItem) {
		$application = &CoreFactory::getApplication();
		$countryControl = BaseFactory::getCountryControl();
		$ipToCountryControl = CoreFactory::getIpToCountry();
		$userCountryCode = "";
		
		//This is put in, as it fails for everyone who access fred.wd4 etc
		//Tom 23/10/06
		if ($_SERVER["SERVER_TYPE"] == "Development") {
			$userCountryCode = $ipToCountryControl->getCountryFromIp("195.173.111.4");
		} else {
			$userCountryCode = $ipToCountryControl->getCountryFromClient();
		}
		if ($userCountryCode["CountryId"] != "") {
			$userCountry = $countryControl->itemByField($userCountryCode["CountryId"], "Code");
			$product = $stockItem->getRelation("ProductId");
			$this->retrieveForProduct($product);
		} else {
			$application->errorControl->addError($userCountryCode["Error"]);
			return false;
		}		
		$allowedCountries = $this->getResultsAsFieldArray("CountryName","CountryId");
		
		
		if (in_array($userCountry->get("Name"), $allowedCountries)) {
			return true;
		} else if (count($allowedCountries) < 1) {
			return true;
		} else {
			$application->errorControl->addError("Sorry '" . $stockItem->getFormatted("Description") . "' is not available from your country (" . $userCountry->get("Name") . ")");
			return false;
		}
	}
}