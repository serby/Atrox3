<?php
/**
 * @package Base
 * @subpackage News
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 */

/**
 * Include Data.php so that DataControl can be extended.
 */
require_once("Atrox/Core/Data/Data.php");


define("NEWS_IMG_MINX", 30);
define("NEWS_IMG_MINY", 30);
//	define("NEWS_IMG_MAXX", 563);
//	define("NEWS_IMG_MAXY", 157);
/* TODO: We need to sort this - SARACENS BELOW */
//define("NEWS_IMG_MAXX", 359);
//define("NEWS_IMG_MAXY", 195);
define("NEWS_IMG_MAXX", 650);
define("NEWS_IMG_MAXY", 257);


/**
 *
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Base
 * @subpackage News
 */
class NewsControl extends DataControl {

	var $table = "News";
	var $key = "Id";
	var $sequence = "News_Id_seq";
	var $defaultOrder = array(array("Field" => "LiveDate", "Desc" => "true"));		
	var $searchFields = array("Subject", "Summary", "Body");
	
	function init() {

		$this->fieldMeta["Id"] = new FieldMeta(
			"Id", "", FM_TYPE_INTEGER, null, FM_STORE_NEVER, false);

		$this->fieldMeta["Subject"] = new FieldMeta(
			"Subject", "", FM_TYPE_STRING, 100, FM_STORE_ALWAYS, false);

		$this->fieldMeta["Summary"] = new FieldMeta(
			"Summary", "", FM_TYPE_STRING, 2000, FM_STORE_ALWAYS, false);

		$this->fieldMeta["Summary"]->setFormatter(CoreFactory::getBodyTextFormatter());

		$this->fieldMeta["Body"] = new FieldMeta(
			"Body", "", FM_TYPE_STRING, 5000, FM_STORE_ALWAYS, false);

		$this->fieldMeta["Body"]->setFormatter(CoreFactory::getBodyTextFormatter());

		$this->fieldMeta["CategoryId"] = new FieldMeta(
			"Category Id", "", FM_TYPE_RELATION, null, FM_STORE_ALWAYS, false);

		$this->fieldMeta["CategoryId"]->setRelationControl(BaseFactory::getNewsCategoryControl());

		$this->fieldMeta["DateCreated"] = new FieldMeta(
			"Date Created", "", FM_TYPE_DATE, 1, FM_STORE_NEVER, false);

		$this->fieldMeta["DateCreated"]->setFormatter(CoreFactory::getDateTimeFieldFormatter());

		$this->fieldMeta["LiveDate"] = new FieldMeta(
			"Live Date", $this->application->getCurrentUtcDateTime(), FM_TYPE_DATE, null, FM_STORE_ALWAYS, false);

		$this->fieldMeta["LiveDate"]->setFormatter(CoreFactory::getDateTimeFieldFormatter());
		$this->fieldMeta["LiveDate"]->setEncoder(CoreFactory::getArrayDateTimeEncoder());

		$this->fieldMeta["ExpiryDate"] = new FieldMeta(
			"Expiry Date", "", FM_TYPE_DATE, null, FM_STORE_ALWAYS, true);
		$this->fieldMeta["ExpiryDate"]->setFormatter(CoreFactory::getDateTimeFieldFormatter());
		$this->fieldMeta["ExpiryDate"]->setEncoder(CoreFactory::getArrayDateTimeEncoder());

		$this->fieldMeta["AuthorId"] = new FieldMeta(
			"Author", "", FM_TYPE_RELATION, null, FM_STORE_ALWAYS, false);

		$this->fieldMeta["AuthorId"]->setRelationControl(BaseFactory::getMemberControl());
		$this->fieldMeta["AuthorId"]->setAutoData(CoreFactory::getCurrentMember());

		$this->fieldMeta["ImageId"] = new FieldMeta(
			"Image", "", FM_TYPE_BINARY, null, FM_STORE_ALWAYS, true);

		$this->fieldMeta["ImageId"]->setRelationControl(CoreFactory::getBinaryControl());
		$this->fieldMeta["ImageId"]->setValidation(CoreFactory::getImageValidation(
				NEWS_IMG_MINX,
				NEWS_IMG_MINY,
				NEWS_IMG_MAXX,
				NEWS_IMG_MAXY));

		$this->fieldMeta["AttachmentId"] = new FieldMeta(
			"Attachment", "", FM_TYPE_BINARY, null, FM_STORE_ALWAYS, true);

		$this->fieldMeta["Attachment2Id"] = new FieldMeta(
			"Attachment 2", "", FM_TYPE_BINARY, null, FM_STORE_ALWAYS, true);

		$this->manyToMany["Sections"] = CoreFactory::getManyToMany($this->table,
			"NewsToSection", "NewsId",
			"SectionId", BaseFactory::getSectionControl());
			
		$this->manyToMany["RelatedNews"] = CoreFactory::getManyToMany($this->table,
			"RelatedNews", "NewsId",
			"RelatedNewsId", BaseFactory::getNewsControl());

		
		$this->fieldMeta["ExtraDate1"] = new FieldMeta(
			"Extra Date 1", "", FM_TYPE_DATE, null, FM_STORE_ALWAYS, true);
		$this->fieldMeta["ExtraDate1"]->setFormatter(CoreFactory::getDateFieldFormatter());
		$this->fieldMeta["ExtraDate1"]->setEncoder(CoreFactory::getArrayDateEncoder());
		
		/*
		To add the fields announcement and hidden, use sql below:
		
		ALTER TABLE "News"
			 ADD COLUMN "Announcement" bool;
		ALTER TABLE "News"
			 ALTER COLUMN "Announcement" SET DEFAULT false;
		ALTER TABLE "News"
			 ADD COLUMN "Hidden" bool;
		ALTER TABLE "News"
			 ALTER COLUMN "Hidden" SET DEFAULT false;
		*/
		$this->fieldMeta["Announcement"] = new FieldMeta(
			"Announcement", "f", FM_TYPE_BOOLEAN, 1, FM_STORE_ALWAYS, true);
			
		$this->fieldMeta["Hidden"] = new FieldMeta(
			"Hidden", "f", FM_TYPE_BOOLEAN, 1, FM_STORE_ALWAYS, true);
	}

	function getLatest($sectionId, $catagoryId = null) {
		$this->retrieveForSection($sectionId, false, $catagoryId);
		return $this->getNext(1);
	}
	
	function retrieveForSection($sectionId, $announcement = false, $categoryId = null, $limit = null, $excludeIds = false, $order = false, $desc = false) {
		$this->initControl();

		$filter = $this->getFilter();
		if ($filter == null) {
			$filter = CoreFactory::getFilter();
		}
		
		// Add the category clause
		if ($categoryId != null) {
			$filter->addConditional($this->table, "CategoryId", $categoryId);
		}

		if ($sectionId != null) {
			$filter->addJoin($this->table, "Id", "NewsToSection", "NewsId");
			$filter->addConditional("NewsToSection", "SectionId", $sectionId);
		}
					
		$filter->addConditional("News", "LiveDate", $this->databaseControl->getCurrentDateTime(), "<=");
		
		if ($announcement) {
			$filter->addConditional($this->table, "Announcement", "t");				
		} else {
			$filter->addConditional($this->table, "Hidden", "t", "!=");
		}
		
		if ($excludeIds) {
			foreach ($excludeIds as $id) {
				$filter->addConditional($this->table, "Id", $id, "!=");
			}
		}
	
		$conditions[] = $filter->makeConditional("News", "ExpiryDate", 
			null, "IS", "OR");
		$conditions[] = $filter->makeConditional("News", "ExpiryDate", 
			$this->databaseControl->getCurrentDateTime(), ">=",  "OR");

		$filter->addConditionalGroup($conditions);
		
		if ($order) {				
			$filter->addOrder($order, $desc);
		}

		if ($limit != null) {
			$filter->addLimit($limit);
		}
		
		$this->setFilter($filter);
		$this->retrieveAll();
	}

	function excludeCategory($categoryId) {
		if ($categoryId == null) {
			trigger_error("Invalid Category");
		}
		$filter = $this->getFilter();
		if ($filter == null) {
			$filter = CoreFactory::getFilter();
		}
		
		$filter->addConditional($this->table, "CategoryId", $categoryId, "!=");
		$this->setFilter($filter);
	}
	
	function includeCategory($categoryId) {
		if ($categoryId == null) {
			trigger_error("Invalid Category");
		}
		$filter = $this->getFilter();
		if ($filter == null) {
			$filter = CoreFactory::getFilter();
		}
		$filter->addConditional($this->table, "CategoryId", $categoryId, "=");
		$this->setFilter($filter);
	}
	
	function retrieveHeadlinesAsArray($limit, $ids, $categoryId = "", $order = "LiveDate", $desc = "true") {
		$filter = $this->getFilter();
		if ($filter == null) {
			$filter = CoreFactory::getFilter();
		}
		if ($categoryId != "") {
			$filter->addConditional($this->table, "CategoryId", $categoryId, "!=");
		}
		$filter->addOrder($order, $desc);
		$filter->addLimit($limit);
		$this->setFilter($filter);			
		$this->retrieveAll();
		$news = array();
		while ($news = $this->getNext()) {
			if ($ids) {
				$id = $news->get("Id");
				$newsArray[$id] = $news->get("Subject");
			} else {
				$newsArray[] = $news->get("Subject");
			}
		}
		return $newsArray;
	}
	
	function getSection($dataentity, $exclude) {
		$databaseControl = &CoreFactory::getDatabaseControl();
		$value = $dataentity->get("Id");
		if (is_numeric($value)) {
			$query = "SELECT * FROM \"NewsToSection\" WHERE \"NewsId\" = '$value'";
			$result = pg_query($databaseControl->dbConnection, $query);
			$row = pg_fetch_array($result);
			foreach ($exclude as $value) {
				if ($row["SectionId"] == $value) {
					continue;
				} else {
					break;
				}
			}
		}
		return $row["SectionId"];
	}
	
	function afterInsert(&$dataEntity) {
		$cacheControl = &CoreFactory::getCacheControl();
		$cacheControl->deleteWebPageCache("LatestNews");
		$cacheControl->deleteWebPageCache("Static");			
	}

	function afterUpdate(&$dataEntity) {
		$this->afterInsert($dataEntity);
	}

	function afterDelete(&$dataEntity) {
		$this->afterInsert($dataEntity);
	}
}