<?php
/**
 * @package Base
 * @subpackage Store
 * @copyright Clock Limited 2007
 * @version 3.0 - $Revision$ - $Date$
 */

/**
 * Include Data.php so that DataControl can be extended.
 * Include Order.php so that OrderControl can be extended.
 */
require_once("Atrox/3.0/Core/Data/Data.php");
require_once("Order.php");

/**
 * Control Object used to issue Refund
 *
 * @author Adam Forster (Clock Ltd) {@link mailto:adam.forster@clock.co.uk adam.forster@clock.co.uk }
 * @copyright Clock Limited 2007
 * @version 3.0 - $Revision$ - $Date$
 * @package Base
 * @subpackage Store
 */
class OrderNoteControl extends DataControl {
	var $table = "OrderNote";
	var $key = "Id";
	var $sequence = "OrderNote_Id_seq";
	var $defaultOrder = "DateCreated";

	var $status = array(
		ORD_NOTPROCESSED => "Not Processed",
		ORD_PROCESSED => "Processing",
		ORD_FAILED => "Failed",
		ORD_CANCELLED => "Cancelled",
		ORD_DISPATCHED => "Dispatched");

	function init() {

		$this->fieldMeta["Id"] = new FieldMeta(
			"Id", "", FM_TYPE_INTEGER, null, FM_STORE_NEVER, false);

		$this->fieldMeta["OrderId"] = new FieldMeta(
			"Order Id", "", FM_TYPE_RELATION, null, FM_STORE_ALWAYS, false);

		$this->fieldMeta["OrderId"]->setRelationControl(
			BaseFactory::getOrderControl());

		$this->fieldMeta["MemberId"] = new FieldMeta(
			"Member Id", "", FM_TYPE_RELATION, null, FM_STORE_ALWAYS, true);

		$this->fieldMeta["MemberId"]->setRelationControl(
			BaseFactory::getMemberControl());

		$this->fieldMeta["MemberId"]->setAutoData(
			CoreFactory::getCurrentMember());

		$this->fieldMeta["DateCreated"] = new FieldMeta(
			"DateCreated", "", FM_TYPE_DATE, null, FM_STORE_NEVER, false);

		$this->fieldMeta["DateCreated"]->setFormatter(
			CoreFactory::getDateTimeFieldFormatter());

		$this->fieldMeta["Status"] = new FieldMeta(
			"Status", "", FM_TYPE_INTEGER, null, FM_STORE_ALWAYS, false);

		$this->fieldMeta["Status"]->setFormatter(
			CoreFactory::getArrayRelationFormatter($this->status, ORD_NOTPROCESSED));

		$this->fieldMeta["Description"] = new FieldMeta(
			"Description", "", FM_TYPE_STRING, 5000, FM_STORE_ALWAYS, false);
	}

	function addNote(&$orderNote) {
		if ($orderNote->save()) {
			$order = $orderNote->getRelation("OrderId");
			$orderControl = $orderNote->getRelationControl("OrderId");
			$orderControl->setStatus($order, $orderNote->get("Status"));
			return true;
		} else {
			return false;
		}
	}

	function retrieveForOrder($order) {
		$filter = $this->getFilter();
		if ($filter == null) {
			$filter = CoreFactory::getFilter();
		}
		$filter->addOrder("DateCreated");
		$filter->addConditional($this->table, "OrderId", $order->get("Id"));
		$this->setFilter($filter);
		$this->retrieveAll();
	}
}