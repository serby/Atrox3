<?php
/**
 * @package Base
 * @subpackage Store
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 */

/**
 * Include Data.php so that DataControl can be extended.
 * Include DeliveryMethod.php so that DeleiveryMethod can be extended.
 */
require_once("Atrox/Core/Data/Data.php");

require_once("DeliveryMethod.php");	

/**
 *
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Base
 * @subpackage Store
 */
class PostageRateControl extends DataControl {
	var $table = "PostageRate";
	var $key = "Id";
	var $sequence = "PostageRate_Id_seq";
	var $defaultOrder = "Id";
	var $searchFields = array("Weight", "Zone");
	
	function init() {
		$this->fieldMeta["Id"] = new FieldMeta(
			"Id", "", FM_TYPE_INTEGER, null, FM_STORE_NEVER, false);

		$this->fieldMeta["Weight"] = new FieldMeta(
			"Weight", "", FM_TYPE_INTEGER, 8, FM_STORE_ALWAYS, false);

		$this->fieldMeta["DeliveryMethodId"] = new FieldMeta(
			"Delivery Method", "", FM_TYPE_RELATION, null, FM_STORE_ALWAYS, false);

		$this->fieldMeta["DeliveryMethodId"]->setRelationControl(
			BaseFactory::getDeliveryMethodControl());				
			
		$this->fieldMeta["NetPrice"] = new FieldMeta(
			"NetPrice", "", FM_TYPE_CURRENCY, 8, FM_STORE_ALWAYS, false);

		$this->fieldMeta["EstimatedTime"] = new FieldMeta(
			"EstimatedTime", "", FM_TYPE_STRING, 1000, FM_STORE_ALWAYS, false);
	}
	
	function getPostageRate($weight, $zone = DM_ZN_UK) {
		$filter = CoreFactory::getFilter();
		$filter->addJoin($this->table, "DeliveryMethodId", "DeliveryMethod", "Id");
		$filter->addConditional("DeliveryMethod", "Zone", $zone);
		$filter->addConditional($this->table, "Weight", $weight, ">");
		$filter->addOrder("NetPrice");
		$this->setFilter($filter);			
		if (!$data = $this->getNext()) {
			$data = $this->makeNew();
			$data->set("Weight", $weight);
			$deliveryMethodControl = BaseFactory::getDeliveryMethodControl();
			$cost = 0;
			if ($deliveryMethod = $deliveryMethodControl->getBestDeliveryMethod($cost, $weight, $zone)) {
				$data->set("DeliveryMethodId", $deliveryMethod->get("Id"));
				$data->setWithoutFormatting("NetPrice", $deliveryMethod->get("BaseCharge") + $deliveryMethodControl->getExtraChargeForDelivery($deliveryMethod, $weight));
			} else {
				$data->setWithoutFormatting("NetPrice", 30);
			}
		}
		return $data;			
	}

	function getPostageCost($weight, $zone = DM_ZN_UK) {
		if ($data = $this->getPostageRate($weight, $zone)) {
			return $data->get("NetPrice");
		}
	}
}