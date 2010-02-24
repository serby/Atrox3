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

 /**
  * Min/Max sizes for product images
  */
 define("PDC_IMG_MINX", 100);
 define("PDC_IMG_MINY", 100);
 define("PDC_IMG_MAXX", 500);
 define("PDC_IMG_MAXY", 500);

/**
 *
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Base
 * @subpackage Store
 */
class ProductCategoryControl extends DataControl {
	var $table = "ProductCategory";
	var $key = "Id";
	var $sequence = "ProductCategory_Id_seq";
	var $defaultOrder = "Name";
	var $searchFields = array("Name");

	function init() {

		$this->fieldMeta["Id"] = new FieldMeta(
			"Id", "", FM_TYPE_INTEGER, null, FM_STORE_NEVER, false);

		$this->fieldMeta["Name"] = new FieldMeta(
			"Name", "", FM_TYPE_STRING, 50, FM_STORE_ALWAYS, false);
			
		$this->fieldMeta["Name"]->setFormatter(CoreFactory::getBodyTextFormatter());
		
		$this->fieldMeta["Description"] = new FieldMeta(
			"Description", "", FM_TYPE_STRING, 1000, FM_STORE_ALWAYS, true);
			
		$this->fieldMeta["Description"]->setFormatter(CoreFactory::getBodyTextFormatter());

		$this->fieldMeta["ImageId"] = new FieldMeta(
			"Image", "", FM_TYPE_BINARY, null, FM_STORE_ALWAYS, true);

		$this->fieldMeta["SectionId"] = new FieldMeta(
			"Section Id", "", FM_TYPE_RELATION, null, FM_STORE_ALWAYS, true);

		$this->fieldMeta["SectionId"]->setRelationControl(BaseFactory::getSectionControl());

		$this->fieldMeta["ParentId"] = new FieldMeta(
			"Parent Id", "", FM_TYPE_RELATION, null, FM_STORE_ALWAYS, true);

		$this->fieldMeta["SystemUse"] = new FieldMeta(
			"System Use", "", FM_TYPE_BOOLEAN, null, FM_STORE_ALWAYS, true);

		$this->fieldMeta["AudioId"] = new FieldMeta(
			"Audio", "", FM_TYPE_BINARY, null, FM_STORE_ALWAYS, true);
	}

	function retrieveForSection($sectionId) {
		$filter = $this->getFilter();
		if ($filter == null) {
			$filter = CoreFactory::getFilter();
		}
		$filter->addConditional($this->table, "SectionId", $sectionId);
		$this->setFilter($filter);
		$this->retrieveAll();
	}

	function retrieveForParent(&$productCategory) {
		$filter = $this->getFilter();
		if ($filter == null) {
			$filter = CoreFactory::getFilter();
		}
		if ($productCategory != null) {
			$filter->addConditional($this->table, "ParentId", $productCategory->get("Id"));
		} else {
			$filter->addConditional($this->table, "ParentId", "0");
		}
		$this->setFilter($filter);
		$this->retrieveAll();
	}

	function buildTree($productCategory = null, $level = -1) {
		$productCategories = array();
		$tmp["Data"] = $productCategory;
		$tmp["Level"] = $level;
		if ($productCategory != null) {
			$productCategories[] = $tmp;
		}
		$productCategoryControl = BaseFactory::getProductCategoryControl();
		$productCategoryControl->retrieveForParent($productCategory);
		$level++;
		while ($childproductCategory = $productCategoryControl->getNext()) {
			$productCategories = array_merge($productCategories, $this->buildTree($childproductCategory, $level));
		}
		return $productCategories;
	}

	function retrieveAncestors($productCategory) {
		$parents = array();
		while ($productCategory = $this->item($productCategory->get("ParentId"))) {
			array_unshift($parents, $productCategory);
		}
		return $parents;
	}

	function retrieveFirst($product, $exclude = null) {
		$return = null;
		if ($productCategoryControl = $product->getManyToManyControl("ProductCategory")) {		
			while ($productCategory = $productCategoryControl->getNext()) {				
				if ($productCategory->get("Id") == $exclude) {
					continue;
				}
				$return = $productCategory;
			}
			return $return;
		}	
	}
	
	function getTopLevelParent($productCategory) {
		if ($productCategory->get("ParentId") == 0) {
			return $productCategory;
		} else {
			if ($parentCategory = $this->item($productCategory->get("ParentId"))) {
				if ($parentCategory->get("ParentId") == 0) {
					return $parentCategory;
				} else {
					return $this->getTopLevelParent($parentCategory);
				}
			}
		}			
	}	
	
	function getSubCategories($id, $order = false, $desc = false) {
		$filter = CoreFactory::getFilter();
		$filter->addConditional($this->table, "ParentId", $id);
		if ($order) {
			$filter->addOrder($order, $desc);
		}	
		$this->setFilter($filter);
		$this->retrieveAll();
	}
	
	function afterInsert(&$dataEntity) {
		$cacheControl = &CoreFactory::getCacheControl();
		$cacheControl->deleteWebPageCache("MainMenu");
		$cacheControl->deleteWebPageCache("Static");			
	}

	function afterUpdate(&$dataEntity) {
		$this->afterInsert($dataEntity);
	}

	function afterDelete(&$dataEntity) {
		$this->afterInsert($dataEntity);
	}
}