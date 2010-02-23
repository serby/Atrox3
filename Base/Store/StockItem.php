<?php
/**
 * @package Base
 * @subpackage Store
 * @copyright Clock Limited 2007
 * @version 3.0 - $Revision$ - $Date$
 */

/**
 * Include Data.php so that DataControl can be extended.
 * Include Report.php so that ReportControl can be extended.
 */
require_once("Atrox/3.0/Core/Data/Data.php");
require_once("Atrox/3.0/Core/Data/Report.php");

define("STO_LOWSTOCK", 5);
define("STOCK_LOW_EMAIL", SITE_ADDRESS . "/includes/emailtemplate/lowstock.php");

define("STOCK_TYPE_NORMAL", 0);
define("STOCK_TYPE_WHOLEPRODUCTDISCOUNT", 1);

/**
 * Subscription Lengths
 */
define("STOCK_SUBLN_1MONTH", 1);
define("STOCK_SUBLN_3MONTHS", 3);
define("STOCK_SUBLN_6MONTHS", 6);
define("STOCK_SUBLN_12MONTHS", 12);

define("STOCK_AUTOPREVIEW_USER", 0);
define("STOCK_AUTOPREVIEW_FIRST", 1);
define("STOCK_AUTOPREVIEW_RANDOM", 2);
define("STOCK_AUTOPREVIEW_CUSTOM", 3);

/**
 *
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2007
 * @version 3.0 - $Revision$ - $Date$
 * @package Base
 * @subpackage Store
 */
class StockItemControl extends DataControl {
	var $table = "StockItems";
	var $key = "Id";
	var $sequence = "StockItems_Id_seq";
	var $defaultOrder = "Description";

	var $types = array(
		STOCK_TYPE_NORMAL => "Normal",
		STOCK_TYPE_WHOLEPRODUCTDISCOUNT => "Whole Product Discount");
		
	var $subLengths = array(
		STOCK_SUBLN_1MONTH => "1 Month",
		STOCK_SUBLN_3MONTHS => "3 Months",
		STOCK_SUBLN_6MONTHS => "6 Months",
		STOCK_SUBLN_12MONTHS => "12 Months"
	);

	function init() {

		$this->fieldMeta["Id"] = new FieldMeta(
			"Id", "", FM_TYPE_INTEGER, null, FM_STORE_NEVER, false);

		$this->fieldMeta["Description"] = new FieldMeta(
			"Description", "", FM_TYPE_STRING, 50, FM_STORE_ALWAYS, false);

		$this->fieldMeta["SizingInformation"] = new FieldMeta(
			"Sizing Information", "", FM_TYPE_STRING, 1000, FM_STORE_ALWAYS, true);

		$this->fieldMeta["VatExempt"] = new FieldMeta(
			"Vat Exempt", "", FM_TYPE_BOOLEAN, 1, FM_STORE_ALWAYS, false);

		$this->fieldMeta["VatExempt"]->setFormatter(CoreFactory::getBooleanFormatter());

		$this->fieldMeta["ProductId"] = new FieldMeta(
			"Product Id", "", FM_TYPE_RELATION, null, FM_STORE_ALWAYS, false);

		$this->fieldMeta["ProductId"]->setRelationControl(BaseFactory::getProductControl());

		$this->fieldMeta["ProductionCost"] = new FieldMeta(
			"Production Cost", 0, FM_TYPE_CURRENCY, null, FM_STORE_ALWAYS, false);

		$this->fieldMeta["Price"] = new FieldMeta(
			"Price", 0, FM_TYPE_CURRENCY, null, FM_STORE_ALWAYS, false);

		$this->fieldMeta["Weight"] = new FieldMeta(
			"Weight", 0, FM_TYPE_INTEGER, null, FM_STORE_ALWAYS, false);

		$this->fieldMeta["Quantity"] = new FieldMeta(
			"Quantity", null, FM_TYPE_INTEGER, null, FM_STORE_ALWAYS, true);

		$this->fieldMeta["DateCreated"] = new FieldMeta(
			"DateCreated", "", FM_TYPE_DATE, null, FM_STORE_NEVER, false);

		$this->fieldMeta["DateCreated"]->setFormatter(CoreFactory::getDateTimeFieldFormatter());

		$this->fieldMeta["StockExpected"] = new FieldMeta(
			"Stock Expected", "", FM_TYPE_DATE, null, FM_STORE_ALWAYS, true);

		$this->fieldMeta["StockExpected"]->setFormatter(CoreFactory::getDateFieldFormatter());
		$this->fieldMeta["StockExpected"]->setEncoder(CoreFactory::getArrayDateEncoder());

		$this->fieldMeta["PreviewId"] = new FieldMeta(
			"Preview", "", FM_TYPE_BINARY, null, FM_STORE_ALWAYS, true);
		
		$this->fieldMeta["DownloadId"] = new FieldMeta(
			"Download", "", FM_TYPE_BINARY, null, FM_STORE_ALWAYS, true);

		$this->fieldMeta["Length"] = new FieldMeta(
			"Length", "", FM_TYPE_INTEGER, null, FM_STORE_ALWAYS, true);
		
		$this->fieldMeta["Length"]->setFormatter(CoreFactory::getSecondsFormatter());
		
		$this->fieldMeta["Type"] = new FieldMeta(
			"Type", STOCK_TYPE_NORMAL, FM_TYPE_INTEGER, null, FM_STORE_ALWAYS, false);

		$this->fieldMeta["Type"]->setFormatter(CoreFactory::getArrayRelationFormatter($this->types, STOCK_TYPE_NORMAL));
		
		$this->fieldMeta["SubscriptionLength"] = new FieldMeta(
			"Subscription Length", STOCK_TYPE_NORMAL, FM_TYPE_INTEGER, null, FM_STORE_ALWAYS, true);

		$this->fieldMeta["SubscriptionLength"]->setFormatter(CoreFactory::getArrayRelationFormatter($this->subLengths, STOCK_SUBLN_3MONTHS));	
		
		$this->fieldMeta["Available"] = new FieldMeta(
			"Available", "", FM_TYPE_BOOLEAN, null, FM_STORE_ALWAYS, false);
			
		$this->fieldMeta["Available"]->setFormatter(CoreFactory::getBooleanFormatter());					
			
		$this->fieldMeta["PackagingId"] = new FieldMeta(
			"Packaging Id", "", FM_TYPE_RELATION, null, FM_STORE_ALWAYS, true);

		$this->fieldMeta["PackagingId"]->setRelationControl(BaseFactory::getPackagingControl());	
	}
	
	function hasDependancies($stockItem) {
		$orderItemControl = BaseFactory::getOrderItemControl();
		$filter = CoreFactory::getFilter();
		$filter->addConditional($orderItemControl->table, "StockItemId",  $stockItem->get("Id"));
		$orderItemControl->setFilter($filter);
		$orderItemControl->retrieveAll();
		if ($orderItemControl->getNumRows() > 0) {
			$this->errorControl->addError($stockItem->get("Description") . 
				". This has already been purchased so can't be deleted.");
		}
		return $this->errorControl->hasErrors();
	}
	
	function getTypeArray() {
		return $this->types;
	}
	
	function getSubscriptionLengthArray() {
		return $this->subLengths;
	}
	
	function getNewestForProduct($product) {
		$this->reset();
		$this->retrieveForProduct($product, STOCK_TYPE_NORMAL, true, "DateCreated", true);
		return $this->getNext();
	}		

	function retrieveForProduct(&$product, $type = STOCK_TYPE_NORMAL, $availableOnly = true, $order = "Description", $desc = "") {
		$filter = CoreFactory::getFilter();			
		if ($desc == "") {
			$desc = false;
		} else {
			$desc = true;
		}		
		$filter->addOrder($order, $desc);
		$filter->addConditional($this->table, "ProductId", $product->get("Id"));
		if ($type !== null) {
			$filter->addConditional($this->table, "Type", $type);
		}			
		if ($availableOnly) {		
			$filter->addConditional($this->table, "Available", "t");
		}					
		$this->setFilter($filter);
		return $this->retrieveAll();
	}

	function sumForProduct(&$product, $type = STOCK_TYPE_NORMAL, $availableOnly = true) {
		
		$filter = CoreFactory::getFilter();
	
		$filter->addConditional($this->table, "ProductId", $product->get("Id"));
		if ($type !== null) {
			$filter->addConditional($this->table, "Type", $type);
		}
		if ($availableOnly) {		
			$filter->addConditional($this->table, "Available", "t");
		}		
		$this->setFilter($filter);
		return $this->sumField("Price");
	}

	function totalStockForProduct(&$product) {
		$orginalfilter = $this->getFilter();
		$this->reset();
		$filter = CoreFactory::getFilter();
		$filter->addConditional($this->table, "ProductId", $product->get("Id"));
		$this->setFilter($filter);
		$sum = $this->sumField("Quantity");
		$this->setFilter($orginalfilter);
		return $sum;
	}

	function checkStockItemQuantity($stockItem) {
		$quantity = $stockItem->get("Quantity");			
		if ($quantity === "") {
			return false;
		}					
		return $quantity - 1 <= STO_LOWSTOCK;
	}
	
	function inStock($stockItem) {
		$quantity = $stockItem->get("Quantity");		
		if ($quantity == null) {
			return true;
		}					
		return $quantity > 0;
	}		
	
	function handleType($stockItem) {
		$product = $stockItem->getRelation("ProductId");
		if ($product->isAudio() && ($stockItem->get("Type") == STOCK_TYPE_NORMAL)) {
			$binary = $stockItem->getRelation("DownloadId");
			$binaryControl = CoreFactory::getBinaryControl();
			$filename = $binaryControl->getBinaryFullPath($binary);
			$id3Control = CoreFactory::getId3Control($filename, true);
			$id3Control->study();
			$this->updateField($stockItem, "Length", $id3Control->lengths);		
		}
	}

	function createPreview(&$stockItem, $action) {
		$this->initControl();
		$product = $stockItem->getRelation("ProductId");
		if ((!$product->isAudio()) || ($stockItem->get("DownloadId") == "")) {
			return false;
		}

		$tmpname = tempnam("/tmp", SITE_CODE . "_");
		$mp3Control = CoreFactory::getMp3Control();
		$binary = $stockItem->getRelation("DownloadId");
		$binaryControl = CoreFactory::getBinaryControl();
		$filename = $binaryControl->getBinaryFullPath($binary);
		switch ($action) {
			case STOCK_AUTOPREVIEW_USER:
				return false;
			case STOCK_AUTOPREVIEW_FIRST:
				$mp3Control->split($filename, $tmpname, false, 1, 30);
				break;
			case STOCK_AUTOPREVIEW_RANDOM:
				$mp3Control->split($filename, $tmpname, true, 1, 30);
				break;					
		}

		if (defined("SITE_SONICLOGO")) {
			$tmpname2 = tempnam("/tmp", SITE_CODE . "_");
			$mp3Control->join($tmpname, SITE_SONICLOGO, $tmpname2);
			$tmpname = $tmpname2;
		}
		$fileInfo["name"] = "preview_" . $binary->get("Filename");
		$fileInfo["size"] = filesize($tmpname);
		$fileInfo["error"] = 0;
		$fileInfo["type"] = $binary->get("MimeType");
		$fileInfo["tmp_name"] = $tmpname;
		$stockItem->setBinary("PreviewId", $stockItem->get("PreviewId"), $fileInfo, true);
		$stockItem->setBinary("PreviewId", $stockItem->get("PreviewId"), $fileInfo, false);
		$stockItem->save();
		return true;
	}

	function retrievePurchasesForMember(&$member) {
		$orderItemControl = BaseFactory::getOrderItemControl();
		$orderControl = BaseFactory::getOrderControl();
		$filter = CoreFactory::getFilter();
		$filter->addConditional($orderControl->table, "MemberId", $member->get("Id"));
		$filter->addJoin($this->table, "Id", $orderItemControl->table, "StockItemId");
		$filter->addJoin($orderItemControl->table, "OrderId", $orderControl->table, "Id");
		$this->setFilter($filter);
		return $this->retrieveAll();
	}
	
	function sortForAudioSub() {
		$filter = $this->getFilter();
		if ($filter == null) {
			$filter = CoreFactory::getFilter();
		}
		$filter->clearOrder();
		$filter->addOrder("SubscriptionLength");
		$this->setFilter($filter);
		$this->retrieveAll();
	}	
			
	function isSubscription($data) {
		if ($data > 0) {
			return true;
		}
		return false;
	}
}

class StockItemReportControl extends ReportControl {

	function StockItemReportControl() {
		$this->fieldMeta["Id"] = new  FieldMeta(
			"Id", "", FM_TYPE_INTEGER, null, FM_STORE_REPORT, false);
		$this->init();
	}

	function generate($productType = null, $limit = 5) {
		$sql = "SELECT \"StockItemId\" AS \"Id\", COUNT(\"StockItemId\")
			FROM \"OrderItem\" LEFT JOIN \"StockItems\" ON \"StockItems\".\"Id\" = \"OrderItem\".\"StockItemId\"
			LEFT JOIN \"Products\" ON \"Products\".\"Id\" = \"StockItems\".\"ProductId\"
			WHERE \"Products\".\"Available\" = 't' ";
		if ($productType !== null) {
			$sql .= "AND \"Products\".\"Type\" = '$productType' ";
		}	
		$sql .= "GROUP BY \"StockItemId\"
			ORDER BY COUNT(\"StockItemId\") DESC ";				
		if ($limit !== null) {
			$sql .= "LIMIT $limit;";
		}
		$this->runQuery($sql);
	}
}