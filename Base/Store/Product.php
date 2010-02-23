<?php
/**
 * @package Base
 * @subpackage Store
 * @copyright Clock Limited 2007
 * @version 3.0 - $Revision$ - $Date$
 */

/**
 * Include Data.php so that DataControl can be extended.
 * Include DataEntity.php so that DataEntityControl can be extended.
 * Include Report so that ReportControl can be extended.
 */
require_once("Atrox/Core/Data/Data.php");
require_once("Atrox/Core/Data/DataEntity.php");
require_once("Atrox/Core/Data/Report.php");

 /**
  * Min/Max sizes for product images
  */
define("PCT_IMG_MINX", 100);
define("PCT_IMG_MINY", 100);
define("PCT_IMG_MAXX", 400);
define("PCT_IMG_MAXY", 400);

define("PCT_TYPE_MISC", 1);
define("PCT_TYPE_CLOTHING", 2);
define("PCT_TYPE_BOOK", 3);
define("PCT_TYPE_AUDIOBOOK", 4);
define("PCT_TYPE_CD", 5);
define("PCT_TYPE_MUSICDOWNLOAD", 6);
define("PCT_TYPE_DVD", 7);
define("PCT_TYPE_VIDEODOWNLOAD", 8);
define("PCT_TYPE_AUDIOSUB", 9);
define("PCT_TYPE_INFORMATION", 10);
define("PCT_TYPE_AUDIOSTREAM", 11);
define("PCT_TYPE_VIDEOSTREAM", 12);
define("PCT_TYPE_IMAGEDOWNLOAD", 13);
define("PCT_TYPE_AUDIODOWNLOADONLY", 14);
define("PCT_TYPE_SOFTWAREDOWNLOAD", 15);

/**
 *
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2007
 * @version 3.0 - $Revision$ - $Date$
 * @package Base
 * @subpackage Store
 */
class ProductControl extends DataControl {
	var $table = "Products";
	var $key = "Id";
	var $sequence = "Products_Id_seq";
	var $defaultOrder = "Name";
	var $searchFields = array("Name", "Description", "ProductCode");
	var $fullTextIndex = "TextIndex";

	var $types = array(
		PCT_TYPE_CLOTHING => "Clothing",
		PCT_TYPE_MISC => "Misc",
		PCT_TYPE_BOOK => "Book",
		PCT_TYPE_AUDIOBOOK => "Audio",
		PCT_TYPE_CD => "CD",
		PCT_TYPE_MUSICDOWNLOAD => "Music Download",
		PCT_TYPE_DVD => "DVD",
		PCT_TYPE_VIDEODOWNLOAD => "Video Download",
		PCT_TYPE_AUDIOSUB => "Audio Subscription",
		PCT_TYPE_INFORMATION => "Information",
		PCT_TYPE_AUDIOSTREAM => "Audio Stream",
		PCT_TYPE_VIDEOSTREAM => "Video Stream",
		PCT_TYPE_IMAGEDOWNLOAD => "Image Download",
		PCT_TYPE_AUDIODOWNLOADONLY => "Audio Download Only",
		PCT_TYPE_SOFTWAREDOWNLOAD => "Software Download"
	);

	var $stockTypes = array(
		PCT_TYPE_CLOTHING => "Clothing",
		PCT_TYPE_MISC => "Misc",
		PCT_TYPE_BOOK => "Book",
		PCT_TYPE_AUDIOBOOK => "Audio Track",
		PCT_TYPE_CD => "CD",
		PCT_TYPE_MUSICDOWNLOAD => "Music Track",
		PCT_TYPE_DVD => "DVD",
		PCT_TYPE_VIDEODOWNLOAD => "Video Clip",
		PCT_TYPE_AUDIOSUB => "Audio Subscription",
		PCT_TYPE_INFORMATION => "Information",
		PCT_TYPE_AUDIOSTREAM => "Audio Stream",
		PCT_TYPE_VIDEOSTREAM => "Video Stream",
		PCT_TYPE_IMAGEDOWNLOAD => "Image Download",
		PCT_TYPE_AUDIODOWNLOADONLY => "Audio Track",
		PCT_TYPE_SOFTWAREDOWNLOAD => "Software"
	);

	var $searchFields = array("Id", "Name", "BarCode", "Isbn");

	function init() {

		$this->fieldMeta["Id"] = new FieldMeta(
			"Id", "", FM_TYPE_INTEGER, null, FM_STORE_NEVER, false);

		$this->fieldMeta["Id"]->setFormatter(CoreFactory::getConCatFormatter(SITE_CODE . "-"));

		$this->fieldMeta["Type"] = new FieldMeta(
			"Type", PCT_TYPE_MISC, FM_TYPE_INTEGER, null, FM_STORE_ALWAYS, false);

		$this->fieldMeta["Type"]->setFormatter(CoreFactory::getArrayRelationFormatter($this->types, PCT_TYPE_MISC));

		$this->fieldMeta["Name"] = new FieldMeta(
			"Name", "", FM_TYPE_STRING, 50, FM_STORE_ALWAYS, false, FM_OPTIONS_UNIQUE);

		$this->fieldMeta["Summary"] = new FieldMeta(
			"Summary", "", FM_TYPE_STRING, 255, FM_STORE_ALWAYS, false);
		$this->fieldMeta["Summary"]->setFormatter(CoreFactory::getBodyTextFormatter());

		$this->fieldMeta["Description"] = new FieldMeta(
			"Description", "", FM_TYPE_STRING, 4000, FM_STORE_ALWAYS, false);
		$this->fieldMeta["Description"]->setFormatter(CoreFactory::getBodyTextFormatter());

		$this->fieldMeta["ImageId"] = new FieldMeta(
			"Image", "", FM_TYPE_BINARY, null, FM_STORE_ALWAYS, true);

		$this->fieldMeta["ImageId"]->setValidation(CoreFactory::getImageValidation(
			PCT_IMG_MINX,
			PCT_IMG_MINY,
			PCT_IMG_MAXX,
			PCT_IMG_MAXY));
			
		$this->fieldMeta["Image2Id"] = new FieldMeta(
			"Image 2", "", FM_TYPE_BINARY, null, FM_STORE_ALWAYS, true);

		$this->fieldMeta["Image2Id"]->setValidation(CoreFactory::getImageValidation(
			PCT_IMG_MINX,
			PCT_IMG_MINY,
			PCT_IMG_MAXX,
			PCT_IMG_MAXY));
			
		$this->fieldMeta["Image3Id"] = new FieldMeta(
			"Image 3", "", FM_TYPE_BINARY, null, FM_STORE_ALWAYS, true);

		$this->fieldMeta["Image3Id"]->setValidation(CoreFactory::getImageValidation(
			PCT_IMG_MINX,
			PCT_IMG_MINY,
			PCT_IMG_MAXX,
			PCT_IMG_MAXY));
			
		$this->fieldMeta["VideoId"] = new FieldMeta(
			"Video", "", FM_TYPE_BINARY, null, FM_STORE_ALWAYS, true);

		$this->fieldMeta["VideoId"]->setValidation(CoreFactory::getImageValidation(
			PCT_IMG_MINX,
			PCT_IMG_MINY,
			PCT_IMG_MAXX,
			PCT_IMG_MAXY));

		$this->fieldMeta["DateCreated"] = new FieldMeta(
			"DateCreated", "", FM_TYPE_DATE, null, FM_STORE_NEVER, false);

		$this->fieldMeta["DateCreated"]->setFormatter(CoreFactory::getDateTimeFieldFormatter());

		$this->fieldMeta["Available"] = new FieldMeta(
			"Available", "", FM_TYPE_BOOLEAN, null, FM_STORE_ALWAYS, false);
			
		$this->fieldMeta["Available"]->setFormatter(CoreFactory::getBooleanFormatter());
		
		$this->manyToMany["ProductCategory"] = CoreFactory::getManyToMany($this->table,
			"ProductsToProductCategory", "ProductId",
			"ProductCategoryId", BaseFactory::getProductCategoryControl());

		$this->manyToMany["RelatedProducts"] = CoreFactory::getManyToMany($this->table,
			"ProductsToRelatedProducts", "ProductId",
			"RelatedProductId", BaseFactory::getProductControl());

		$this->fieldMeta["SupplierId"] = new FieldMeta(
			"Supplier Id", "", FM_TYPE_RELATION, null, FM_STORE_ALWAYS, false);

		$this->fieldMeta["SupplierId"]->setRelationControl(BaseFactory::getSupplierControl());

		$this->fieldMeta["Isbn"] = new FieldMeta(
			"Isbn", "", FM_TYPE_STRING, 10, FM_STORE_ALWAYS, true);

		$this->fieldMeta["Isbn"]->setValidation(CoreFactory::getIsbnValidation());

		$this->fieldMeta["BarCode"] = new FieldMeta(
			"Bar Code", "", FM_TYPE_STRING, 50, FM_STORE_ALWAYS, true);

		$this->fieldMeta["OrderingInformation"] = new FieldMeta(
			"Ordering Information", "", FM_TYPE_STRING, 5000, FM_STORE_ALWAYS, true);

		$this->fieldMeta["FulfillmentDetails"] = new FieldMeta(
			"Fulfillment Details", "", FM_TYPE_STRING, 5000, FM_STORE_ALWAYS, true);

		$this->fieldMeta["Creator"] = new FieldMeta(
			"Creator", "", FM_TYPE_STRING, 50, FM_STORE_ALWAYS, true);

		$this->fieldMeta["Publisher"] = new FieldMeta(
			"Publisher", "", FM_TYPE_STRING, 50, FM_STORE_ALWAYS, true);

		$this->fieldMeta["AverageReview"] = new FieldMeta(
			"Average Review", 0, FM_TYPE_FLOAT, null, FM_STORE_NEVER, true);

		$this->fieldMeta["OurReview"] = new FieldMeta(
			"Our Review", 0, FM_TYPE_INTEGER, 1, FM_STORE_ALWAYS, true);

		$this->fieldMeta["WholeProductOnly"] = new FieldMeta(
			"Whole Product Only", "", FM_TYPE_BOOLEAN, null, FM_STORE_ALWAYS, false);
		
		$this->fieldMeta["WholeProductOnly"]->setFormatter(CoreFactory::getBooleanFormatter());				
		
		$this->fieldMeta["WholeProductPrice"] = new FieldMeta(
			"Whole Product Price", "", FM_TYPE_CURRENCY, null, FM_STORE_ALWAYS, true);
			
		$this->fieldMeta["WholeProductPrice"] = new FieldMeta(
			"Whole Product Price", "", FM_TYPE_CURRENCY, null, FM_STORE_ALWAYS, true);				
	}

	function getTypeArray() {
		return $this->types;
	}

	function getStockType($product) {
		return $this->stockTypes[$product->get("Type")];
	}

	function retrieveLatest($type = null, $limit = 5, $excludeIdArray = null) {
		$filter = CoreFactory::getFilter();
		$filter->addOrder("DateCreated", true);
		$filter->addConditional("Products", "Available", "t");
		if ($type !== null) {
			$filter->addConditional("Products", "Type", $type);
		}
		if (is_array($excludeIdArray)) {			
			$filter->addConditional($this->table, "Id", $excludeIdArray, "NOT IN");				
		}
		$filter->addLimit($limit);
		$this->setFilter($filter);
		return $this->retrieveAll();
	}

	function retrieveForProductCategory($productCategoryId) {
		$filter = $this->getFilter();
		if ($filter == null) {
			$filter = CoreFactory::getFilter();
		}
		$filter->addJoin($this->table, "Id", "ProductsToProductCategory", "ProductId");
		$filter->addConditional("ProductsToProductCategory", "ProductCategoryId", $productCategoryId);
		$filter->addConditional($this->table, "Available", true);
		$this->setFilter($filter);
		return $this->retrieveAll();
	}

	function retrieveForProductCategoryBranch($productCategoryId) {
		$filter = $this->getFilter();
		if ($filter == null) {
			$filter = CoreFactory::getFilter();
		}
		$productCategoryControl = BaseFactory::getProductCategoryControl();
		$filter->addJoin($this->table, "Id",
			"ProductsToProductCategory", "ProductId");
		$conditions = array();
		$conditions[] = $filter->makeConditional("ProductsToProductCategory", "ProductCategoryId", $productCategoryId);

		$productCategoryControl = BaseFactory::getProductCategoryControl();
		$p = array();

		array_push($p, $productCategoryControl->item($productCategoryId));
		while (count($p)) {
			$productCategory = array_pop($p);
			$productCategoryControl->retrieveForParent($productCategory);
			$productCategoryControl->reset();
			while ($productCategory = $productCategoryControl->getNext()) {
				array_push($p, $productCategory);
				$conditions[] = $filter->makeConditional("ProductsToProductCategory",
					"ProductCategoryId", $productCategory->get("Id"), "=", "OR");
			}
		}
		$filter->addConditionalGroup($conditions);
		$filter->setDistinct(true);
		$this->setFilter($filter);
		
		$distinct = $this->filter->getDistinctSql();
		$joins = $this->filter->getJoinSql();
		$conditions = $this->filter->getConditionSql();
		$order = $this->filter->getOrderSql();
		$limit = $this->filter->getLimitSql();
		$sql = "SELECT $distinct " .
			"\"Products\".\"Id\", " .
			"\"Products\".\"Name\", " .
			"\"Products\".\"Summary\", " .				
			"\"Products\".\"DateCreated\"," .
			"\"Products\".\"Available\"," .
			"\"Products\".\"ImageId\"" .
			"FROM $this->fullTable $joins $conditions $order $limit";
		$this->results = $this->databaseControl->query($sql);
		$this->numRows = $this->databaseControl->numRows($this->results);				
	}

	function afterInsert(&$dataEntity) {
		$cacheControl = &CoreFactory::getCacheControl();
		$cacheControl->deleteWebPageCache("LatestProducts");
		$cacheControl->deleteWebPageCache("TopProducts");
	}

	function afterUpdate(&$dataEntity) {
		$this->afterInsert($dataEntity);
	}

	function afterDelete(&$dataEntity) {
		$this->afterInsert($dataEntity);
	}
	
	function removeProductsByIds($ids) {
		$productIds = explode(",", $ids);			
		foreach ($productIds as $productId) {
			if ($product = $this->item($productId)) {
				$this->removeProduct($product);
			}
		}
	}
	
	function removeProduct($product) {
		$stockItemControl = BaseFactory::getStockItemControl();
		$stockItemControl->retrieveForProduct(&$product, null);
		while ($stockItem = $stockItemControl->getNext()) {
			$stockItemControl->delete($stockItem->get("Id"));
		}
		$this->delete($product->get("Id"));
	}

	function getDataEntity() {
		return new ProductDataEntity($this);
	}
	
	function searchForAvailableProducts($value, $freeText = false) {
		$this->initTable();
		if ($freeText) {
			$this->filter->addConditional($this->table, "Available", "t");
			if (($value == null) || (!isset($this->fullTextIndex))) {
				return false;
			}
			if (!$this->filter->addFreeTextCondition($this->table, $this->fullTextIndex, $value)) {					
				return false;
			}
		} else {
			if (($value == null) || (!isset($this->searchFields)) || (!is_array($this->searchFields))) {
				return false;
			}
			$value .= "%";
			$conditions = array();
			foreach ($this->searchFields as $fieldName) {
				$conditions[] = $this->filter->makeConditional($this->table,
				$fieldName, $value, "ILIKE", "OR");								
			}
			$this->filter->addConditionalGroup($conditions);	
			$this->filter->addConditional($this->table, "Available", "t");			
		}
		return true;
	}
	
	function getStartingFromPrice($product) {
		$stockItemControl = BaseFactory::getStockItemControl();
		if ($product->get("WholeProductOnly") == "f") {
			$filter = $stockItemControl->getFilter();
			$filter->addConditional($stockItemControl->table, "ProductId", $product->get("Id"));		
			$filter->addConditional($stockItemControl->table, "Available", "t");	
			$filter->addOrder("Price");
			$filter->addLimit(1);
			$stockItemControl->setFilter($filter);	
			if ($stockItem = $stockItemControl->getNext()) {
				return $stockItem->get("Price");
			} else {
				return false;
			}
		} else {
			return $stockItemControl->sumForProduct($product, null);
		}
	}		
	
	function retrieveRelatedProducts($product, $availableOnly = true) {
		$relatedProductControl = $product->getManyToManyControl("RelatedProducts");
		$filter = $relatedProductControl->getFilter();
		$filter->addConditional("Products", "Available", "t");
		$filter->addLimit(5);
		$relatedProductControl->setFilter($filter);	
		return $relatedProductControl;
	}
}

class TopProductReportControl extends ReportControl {

	function TopProductReportControl() {
		$this->fieldMeta["Id"] = new  FieldMeta(
			"Id", "", FM_TYPE_INTEGER, null, FM_STORE_REPORT, false);
		$this->init();
	}

	function generate($productType = null, $limit = 5, $excludeId = false) {
		$sql = "SELECT DISTINCT \"Products\".\"Id\", COUNT(\"StockItemId\")
			FROM \"OrderItem\" 
			LEFT JOIN \"StockItems\" ON \"StockItems\".\"Id\" = \"OrderItem\".\"StockItemId\"
			LEFT JOIN \"Order\" ON \"Order\".\"Id\" = \"OrderItem\".\"OrderId\"
			LEFT JOIN \"Products\" ON \"Products\".\"Id\" = \"StockItems\".\"ProductId\"
			WHERE \"Order\".\"DateCreated\" > now() - INTERVAL '20 days' AND \"Products\".\"Available\" = 't'";
			
		if ($productType !== null) {
			$sql .= "AND \"Products\".\"Type\" = '$productType' ";
		}
		
		if ($excludeId !== false) {
			$sql .= "AND \"Products\".\"Id\" != '" . $excludeId . "' ";
		}
		
		$sql .= "GROUP BY \"Products\".\"Id\"
			ORDER BY COUNT(\"StockItemId\") DESC ";

		if ($limit !== null) {
			$sql .= "LIMIT $limit;";
		}
		$this->runQuery($sql);
	}
	
	function generateTopProductsList($productType = null, $limit = 5, $excludeId = false) {
		$dataConnection = &CoreFactory::getDatabaseControl();
		
		$sql = "SELECT \"Products\".\"Id\"
			FROM \"OrderItem\"
				LEFT JOIN \"StockItems\" ON \"StockItems\".\"Id\" = \"OrderItem\".\"StockItemId\"
				LEFT JOIN \"Order\" ON \"Order\".\"Id\" = \"OrderItem\".\"OrderId\"
				LEFT JOIN \"Products\" ON \"Products\".\"Id\" = \"StockItems\".\"ProductId\"
			WHERE \"Order\".\"DateCreated\" > now() - INTERVAL '20 days' 
			AND \"Products\".\"Available\" = 't'";
			
		if ($productType !== null) {
			$sql .= "AND \"Products\".\"Type\" = '{$productType}'";
		}
		if ($excludeId !== false) {
			$sql .= "AND \"Products\".\"Id\" != '" . $excludeId . "' ";
		}	
		
		$sql .= "GROUP BY \"Order\".\"Id\", \"Products\".\"Id\"";
		
		$result = $dataConnection->query($sql);
		
		while ($row = $dataConnection->fetchRow($result)) {				
			if (!isset($products[$row["Id"]])) {
				$products[$row["Id"]] = 1;
			} else {
				$products[$row["Id"]] = $products[$row["Id"]] + 1;
			}	
		}
			
		arsort($products, SORT_NUMERIC);

		if ($limit !== null) {
			$products = array_chunk($products, $limit, true);
		}
		
		return $products[0];
	}		
}

class ProductDataEntity extends DataEntity {
	/*		
	function ProductDataEntity(&$control) {
		$this->control = $control;
	}*/

	function hasDrm() {
		$stockItemControl = BaseFactory::getStockItemControl();
		$stockItemControl->retrieveForProduct($this, STOCK_TYPE_NORMAL, true);
		while ($stockItem = $stockItemControl->getNext()) {
			if ($stockItem->get("DRMKeyId") != null) {
				return true;
			}
		}
		return false;		
	}

	function isDownload() {
		switch ($this->data["Type"]) {
			case PCT_TYPE_MISC:
			case PCT_TYPE_CLOTHING:
			case PCT_TYPE_CD:
			case PCT_TYPE_DVD:		
				return false;
		}
		return true;
	}
			
	function isImage() {
		switch ($this->data["Type"]) {
			case PCT_TYPE_IMAGEDOWNLOAD:
				return true;
		}
		return false;
	}
	
	function isSubscription() {
		switch ($this->data["Type"]) {
			case PCT_TYPE_AUDIOSUB:
				return true;
		}
		return false;
	}
			
	function isVideo() {
		switch ($this->data["Type"]) {
			case PCT_TYPE_VIDEODOWNLOAD:
			case PCT_TYPE_VIDEOSTREAM:
				return true;
		}
		return false;
	}

	function isAudio() {
		switch ($this->data["Type"]) {
			case PCT_TYPE_AUDIOBOOK:
			case PCT_TYPE_MUSICDOWNLOAD:
			case PCT_TYPE_AUDIOSTREAM:
			case PCT_TYPE_AUDIODOWNLOADONLY:
				return true;
		}
		return false;
	}
	
	function isStreamable() {
		switch ($this->data["Type"]) {
			case PCT_TYPE_AUDIOBOOK:
			case PCT_TYPE_VIDEOSTREAM:
			case PCT_TYPE_VIDEODOWNLOAD:
			case PCT_TYPE_MUSICDOWNLOAD:
			case PCT_TYPE_AUDIOSTREAM:
				return true;
		}
		return false;
	}
	
	function isOnlyStreamable() {
		switch ($this->data["Type"]) {
			case PCT_TYPE_VIDEOSTREAM:
			case PCT_TYPE_AUDIOSTREAM:
				return true;
		}
		return false;
	}		
}