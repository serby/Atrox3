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
 *
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2007
 * @version 3.0 - $Revision$ - $Date$
 * @package Base
 * @subpackage Store
 */
class OrderItemControl extends DataControl {
	var $table = "OrderItem";
	var $key = "Id";
	var $sequence = "OrderItem_Id_seq";
	var $defaultOrder = "Id";

	function init() {

		$this->fieldMeta["Id"] = new FieldMeta(
			"Id", "", FM_TYPE_INTEGER, null, FM_STORE_NEVER, false);

		$this->fieldMeta["OrderId"] = new FieldMeta(
			"Order Id", "", FM_TYPE_RELATION, null, FM_STORE_ALWAYS, false);

		$this->fieldMeta["OrderId"]->setRelationControl(BaseFactory::getOrderControl());

		$this->fieldMeta["StockItemId"] = new FieldMeta(
			"Stock Item Id", "", FM_TYPE_RELATION, null, FM_STORE_ALWAYS, false);

		$this->fieldMeta["StockItemId"]->setRelationControl(BaseFactory::getStockItemControl());

		$this->fieldMeta["Quantity"] = new FieldMeta(
			"Quantity", "", FM_TYPE_INTEGER, null, FM_STORE_ALWAYS, false);

		$this->fieldMeta["NetPrice"] = new FieldMeta(
			"Net Price", "", FM_TYPE_CURRENCY, null, FM_STORE_ALWAYS, false);

		$this->fieldMeta["Vat"] = new FieldMeta(
			"Vat", "", FM_TYPE_CURRENCY, null, FM_STORE_ALWAYS, false);

		$this->fieldMeta["GrossPrice"] = new FieldMeta(
			"Gross Price", "", FM_TYPE_CURRENCY, null, FM_STORE_ALWAYS, false);

		$this->fieldMeta["ItemGrossPrice"] = new FieldMeta(
			"Item Gross Price", "", FM_TYPE_CURRENCY, null, FM_STORE_ALWAYS, false);

		$this->fieldMeta["DispatchCost"] = new FieldMeta(
			"Dispatch Cost", "", FM_TYPE_CURRENCY, null, FM_STORE_ALWAYS, false);

		$this->fieldMeta["ProductionCost"] = new FieldMeta(
			"Production Cost", "", FM_TYPE_CURRENCY, null, FM_STORE_ALWAYS, false);

		$this->fieldMeta["SupplierProfitShare"] = new FieldMeta(
			"Supplier Profit Share", "", FM_TYPE_FLOAT, null, FM_STORE_ALWAYS, false);

		$this->fieldMeta["ShareBeforeCosts"] = new FieldMeta(
			"Share Before Costs", "f", FM_TYPE_BOOLEAN, null, FM_STORE_ALWAYS, false);

		$this->fieldMeta["SupplierId"] = new FieldMeta(
			"Supplier Id", "", FM_TYPE_RELATION, null, FM_STORE_ALWAYS, true);

		$this->fieldMeta["SupplierId"]->setRelationControl(BaseFactory::getSupplierControl());			
	}

	function retrieveForOrder($order) {
		$filter = CoreFactory::getFilter();
		if ($filter == null) {
			$filter = CoreFactory::getFilter();
		}
		$filter->addConditional($this->table, "OrderId", $order->get("Id"));
		$this->setFilter($filter);
		$this->retrieveAll();
	}
	
	function getMemberPurchases($memberId) {					
		$filter = CoreFactory::getFilter();
		$orderControl = BaseFactory::getOrderControl();
		$filter->addJoin($this->table, "OrderId", $orderControl->table, "Id");
		$filter->addConditional($orderControl->table, "MemberId", $memberId);						
		$this->setFilter($filter);
		return $this->retrieveAll();			
	}
}