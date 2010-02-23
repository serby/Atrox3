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
 * @author Adam Forster (Clock Ltd) {@link mailto:adam.forster@clock.co.uk adam.forster@clock.co.uk }
 * @copyright Clock Limited 2007
 * @version 3.0 - $Revision$ - $Date$
 * @package Base
 * @subpackage Store
 */
class SubscriptionItemControl extends DataControl {
	var $table = "SubscriptionItems";
	var $key = "Id";
	var $sequence = "SubscriptionItems_Id_seq";
	var $defaultOrder = "Id";

	function init() {

		$this->fieldMeta["Id"] = new FieldMeta(
			"Id", "", FM_TYPE_INTEGER, null, FM_STORE_NEVER, false);

		$this->fieldMeta["Description"] = new FieldMeta(
			"Description", "", FM_TYPE_STRING, 50, FM_STORE_ALWAYS, false);

		$this->fieldMeta["ProductId"] = new FieldMeta(
			"Product Id", "", FM_TYPE_RELATION, null, FM_STORE_ALWAYS, false);

		$this->fieldMeta["ProductId"]->setRelationControl(BaseFactory::getProductControl());

		$this->fieldMeta["DownloadId"] = new FieldMeta(
			"Download", "", FM_TYPE_BINARY, null, FM_STORE_ALWAYS, false);

		$this->fieldMeta["Length"] = new FieldMeta(
			"Length", "", FM_TYPE_INTEGER, null, FM_STORE_ALWAYS, true);

		$this->fieldMeta["Length"]->setFormatter(CoreFactory::getSecondsFormatter());

		$this->fieldMeta["DateCreated"] = new FieldMeta(
			"DateCreated", "", FM_TYPE_DATE, null, FM_STORE_NEVER, false);

		$this->fieldMeta["DateCreated"]->setFormatter(CoreFactory::getDateTimeFieldFormatter());					
	}
	
	function retrieveForProduct(&$product) {
		$filter = CoreFactory::getFilter();
		$filter->addConditional($this->table, "ProductId", $product->get("Id"));
		$this->setFilter($filter);
		$this->retrieveAll();
	}
	
	function updateLength($subscriptionItem) {
		$binary = $subscriptionItem->getRelation("DownloadId");
		$binaryControl = CoreFactory::getBinaryControl();
		$filename = $binaryControl->getBinaryFullPath($binary);
		$id3Control = CoreFactory::getId3Control($filename, true);
		$id3Control->study();
		$this->updateField($subscriptionItem, "Length", $id3Control->lengths);	
	}
	
	function distributeItem($subscriptionItem) {
		$subscriptionControl = BaseFactory::getSubscriptionControl();
		$libraryItemControl = BaseFactory::getLibraryItemControl();
		
		$this->updateLength($subscriptionItem);
		$product = $subscriptionItem->getRelation("ProductId");
		
		$subscriptionControl->retrieveActiveForProduct($product);
		while ($subscription = $subscriptionControl->getNext()) {
			$member = $subscription->getRelation("MemberId");
			$libraryItemControl->addStockItem($member, $order = null, $subscriptionItem, LB_DOWNLOADS, true);
		}
	}
	
	function afterInsert($subscriptionItem) {
		$this->distributeItem($subscriptionItem);
	}
}