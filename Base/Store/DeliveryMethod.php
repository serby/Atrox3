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


define("DM_ZN_UK", 1);	
define("DM_ZN_EUROPE", 2);	
define("DM_ZN_WORLDZONE1", 3);	
define("DM_ZN_WORLDZONE2", 4);	

define("DM_IMG_MINX", 100);
define("DM_IMG_MINY", 100);
define("DM_IMG_MAXX", 400);
define("DM_IMG_MAXY", 400);	
	
/**
 *
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Base
 * @subpackage Store
 */
class DeliveryMethodControl extends DataControl {
	var $table = "DeliveryMethod";
	var $key = "Id";
	var $sequence = "DeliveryMethod_Id_seq";
	var $defaultOrder = "Id";
	var $searchFields = array("Name");
	var $priority = array(
		"1" => "1", "2" => "2", "3" => "3", "4" => "4", "5" => "5",
		"6" => "6", "7" => "7", "8" => "8", "9" => "9", "10" => "10");

	var $zones = array(
		DM_ZN_UK => "UK",
		DM_ZN_EUROPE => "Europe",
		DM_ZN_WORLDZONE1 => "World Zone 1",
		DM_ZN_WORLDZONE2 => "World Zone 2");

	function init() {
		$this->fieldMeta["Id"] = new FieldMeta(
			"Id", "", FM_TYPE_INTEGER, null, FM_STORE_NEVER, false);

		$this->fieldMeta["Name"] = new FieldMeta(
			"Name", "", FM_TYPE_STRING, 100, FM_STORE_ALWAYS, false);

		$this->fieldMeta["Description"] = new FieldMeta(
			"Description", "", FM_TYPE_STRING, null, FM_STORE_ALWAYS, false);

		$this->fieldMeta["VatExempt"] = new FieldMeta(
			"Vat Exempt", "", FM_TYPE_BOOLEAN, 1, FM_STORE_ALWAYS, false);

		$this->fieldMeta["VatExempt"]->setFormatter(CoreFactory::getBooleanFormatter());

		$this->fieldMeta["Priority"] = new FieldMeta(
			"Priority", 1, FM_TYPE_INTEGER, null, FM_STORE_ALWAYS, false);			

		$this->fieldMeta["MinWeight"] = new FieldMeta(
			"Min Weight", 0, FM_TYPE_INTEGER, 16, FM_STORE_ALWAYS, false);
			
		$this->fieldMeta["MaxWeight"] = new FieldMeta(
			"Max Weight", 0, FM_TYPE_INTEGER, 16, FM_STORE_ALWAYS, false);				
			
		$this->fieldMeta["BaseCharge"] = new FieldMeta(
			"Base Charge", 0, FM_TYPE_CURRENCY, 16, FM_STORE_ALWAYS, false);
		
		$this->fieldMeta["ExtraCharge"] = new FieldMeta(
			"Extra Charge", 0, FM_TYPE_CURRENCY, 16, FM_STORE_ALWAYS, false);								

		$this->fieldMeta["ExtraChargeEvery"] = new FieldMeta(
			"Extra Charge Every", 0, FM_TYPE_INTEGER, 16, FM_STORE_ALWAYS, false);		
			
		$this->fieldMeta["Zone"] = new FieldMeta(
			"Zone", "", FM_TYPE_INTEGER, null, FM_STORE_ALWAYS, false);
			
		$this->fieldMeta["Zone"]->setFormatter(
			CoreFactory::getArrayRelationFormatter($this->zones, DM_ZN_UK));					

		$this->fieldMeta["ImageId"] = new FieldMeta(
			"Logo", "", FM_TYPE_BINARY, null, FM_STORE_ALWAYS, true);

		$this->fieldMeta["ImageId"]->setValidation(CoreFactory::getImageValidation(
			DM_IMG_MINX,
			DM_IMG_MINY,
			DM_IMG_MAXX,
			DM_IMG_MAXY));
								
	}
	
	function getZoneArray(){
		return $this->zones;
	}
	
	function getBestDeliveryMethod(&$bestDeliveryCost, $weight, $zone = DM_ZN_UK) {
		$filter = CoreFactory::getFilter();
		$filter->addConditional($this->table, "Zone", $zone);
		$filter->addConditional($this->table, "MinWeight", $weight, "<=");
		$filter->addConditional($this->table, "MaxWeight", $weight, ">");
		$filter->addOrder("Priority");
		$this->setFilter($filter);
		$bestDeliveryCost = false;
		$bestDeliveryMethod = false;
		while ($deliveryMethod = $this->getNext()) {
			$extraCharge = $this->getExtraChargeForDelivery($deliveryMethod, $weight);
			if ((!$bestDeliveryCost) || ($bestDeliveryCost > $deliveryMethod->get("BaseCharge") + $extraCharge)) {
				$bestDeliveryCost = $deliveryMethod->get("BaseCharge") + $extraCharge;
				$bestDeliveryMethod = $deliveryMethod;
			}
		}
		return $bestDeliveryMethod;
	}	

	function getPriorityArray(){
		return $this->priority;
	}
	
	function getExtraChargeForDelivery($deliveryMethod, $weight) {
		$over = $weight - $deliveryMethod->get("MinWeight");
		$extraCharge = ceil($over / $deliveryMethod->get("ExtraChargeEvery")) * $deliveryMethod->get("ExtraCharge");
		return $extraCharge;
	}
}