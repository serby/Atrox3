<?php
/**
 * @package Base
 * @subpackage Article
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 */

/**
 * Include TaggedData.php so that TaggedDataControl can be extended.
 */
require_once("Atrox/Core/Data/TaggedData.php");

/**
 *
 * @author Dom Udall (Clock Ltd) {@link mailto:dom.udall@clock.co.uk dom.udall@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Base
 * @subpackage Article
 */

class ArticleControl extends TaggedDataControl {
	var $table = "Article";
	var $key = "Id";
	var $sequence = "Article_Id_seq";
	var $defaultOrder = array(array("Field" => "LiveDate", "Desc" => "true"));
	var $searchFields = array("Subject", "Summary", "Body", "Tags");
	var $imageSize = array(
		"MinWidth" => 50,
		"MinHeight" => 50,
		"MaxWidth" => 1000,
		"MaxHeight" => 1000);

	function init() {

		$this->fieldMeta["Id"] = new FieldMeta(
			"Id", "", FM_TYPE_INTEGER, null, FM_STORE_NEVER, false);

		$this->fieldMeta["Subject"] = new FieldMeta(
			"Subject", "", FM_TYPE_STRING, 500, FM_STORE_ALWAYS, false);

		$this->fieldMeta["Summary"] = new FieldMeta(
			"Summary", "", FM_TYPE_SAFEHTML, 5000, FM_STORE_ALWAYS, false);

		$this->fieldMeta["Body"] = new FieldMeta(
			"Body", "", FM_TYPE_SAFEHTML, null, FM_STORE_ALWAYS, false);

		$this->fieldMeta["DateCreated"] = new FieldMeta(
			"Date Created", "", FM_TYPE_DATE, 1, FM_STORE_NEVER, false);

		$this->fieldMeta["DateCreated"]->setFormatter(CoreFactory::getDateTimeFieldFormatter());

		$this->fieldMeta["LiveDate"] = new FieldMeta(
			"Live Date", $this->application->getCurrentUtcDateTime(), FM_TYPE_DATE, null, FM_STORE_ALWAYS, false);

		$this->fieldMeta["LiveDate"]->setFormatter(CoreFactory::getDateFieldFormatter());
		$this->fieldMeta["LiveDate"]->setEncoder(CoreFactory::getArrayDateTimeEncoder());

		$this->fieldMeta["ExpiryDate"] = new FieldMeta(
			"Expiry Date", "", FM_TYPE_DATE, null, FM_STORE_ALWAYS, true);

		$this->fieldMeta["ExpiryDate"]->setFormatter(CoreFactory::getDateFieldFormatter());
		$this->fieldMeta["ExpiryDate"]->setEncoder(CoreFactory::getArrayDateTimeEncoder());

		$this->fieldMeta["AuthorId"] = new FieldMeta(
			"Author", "", FM_TYPE_RELATION, null, FM_STORE_ALWAYS, false);

		$this->fieldMeta["AuthorId"]->setRelationControl(BaseFactory::getMemberControl());
		$this->fieldMeta["AuthorId"]->setAutoData(CoreFactory::getCurrentMember());

		$this->fieldMeta["Image1Id"] = new FieldMeta(
			"Image 1", "", FM_TYPE_BINARY, null, FM_STORE_ALWAYS, true);

		$this->fieldMeta["Image1Id"]->setRelationControl(CoreFactory::getBinaryControl());

		$this->fieldMeta["Image1Id"]->setValidation(CoreFactory::getImageValidation(
			$this->imageSize["MinWidth"],
			$this->imageSize["MinHeight"],
			$this->imageSize["MaxWidth"],
			$this->imageSize["MaxHeight"]));

		$this->fieldMeta["Image2Id"] = new FieldMeta(
			"Image 2", "", FM_TYPE_BINARY, null, FM_STORE_ALWAYS, true);

		$this->fieldMeta["Image2Id"]->setRelationControl(CoreFactory::getBinaryControl());

		$this->fieldMeta["Image2Id"]->setValidation(CoreFactory::getImageValidation(
			$this->imageSize["MinWidth"],
			$this->imageSize["MinHeight"],
			$this->imageSize["MaxWidth"],
			$this->imageSize["MaxHeight"]));

		$this->fieldMeta["Image3Id"] = new FieldMeta(
			"Image 3", "", FM_TYPE_BINARY, null, FM_STORE_ALWAYS, true);

		$this->fieldMeta["Image3Id"]->setRelationControl(CoreFactory::getBinaryControl());

		$this->fieldMeta["Image3Id"]->setValidation(CoreFactory::getImageValidation(
			$this->imageSize["MinWidth"],
			$this->imageSize["MinHeight"],
			$this->imageSize["MaxWidth"],
			$this->imageSize["MaxHeight"]));

		$this->fieldMeta["Image4Id"] = new FieldMeta(
			"Image 4", "", FM_TYPE_BINARY, null, FM_STORE_ALWAYS, true);

		$this->fieldMeta["Image4Id"]->setRelationControl(CoreFactory::getBinaryControl());

		$this->fieldMeta["Image4Id"]->setValidation(CoreFactory::getImageValidation(
			$this->imageSize["MinWidth"],
			$this->imageSize["MinHeight"],
			$this->imageSize["MaxWidth"],
			$this->imageSize["MaxHeight"]));

		$this->fieldMeta["Attachment1Id"] = new FieldMeta(
			"Attachment 1", "", FM_TYPE_BINARY, null, FM_STORE_ALWAYS, true);

		$this->fieldMeta["Attachment2Id"] = new FieldMeta(
			"Attachment 2", "", FM_TYPE_BINARY, null, FM_STORE_ALWAYS, true);

		$this->fieldMeta["Attachment3Id"] = new FieldMeta(
			"Attachment 3", "", FM_TYPE_BINARY, null, FM_STORE_ALWAYS, true);

		$this->fieldMeta["Attachment4Id"] = new FieldMeta(
			"Attachment 4", "", FM_TYPE_BINARY, null, FM_STORE_ALWAYS, true);

		$this->fieldMeta["ExtraDate1"] = new FieldMeta(
			"Extra Date 1", "", FM_TYPE_DATE, null, FM_STORE_ALWAYS, true);

		$this->fieldMeta["ExtraDate1"]->setFormatter(CoreFactory::getDateFieldFormatter());

		$this->fieldMeta["ExtraDate1"]->setEncoder(CoreFactory::getArrayDateEncoder());

		$this->fieldMeta["Active"] = new FieldMeta(
			"Active", "t", FM_TYPE_BOOLEAN, null, FM_STORE_ALWAYS, true);

		$this->fieldMeta["Tags"] = new FieldMeta(
			"Tags", "", FM_TYPE_TAG, null, FM_STORE_ALWAYS, true);
	}

	function afterInsert(&$dataEntity) {
		parent::afterInsert($dataEntity);
		$this->addNote($dataEntity, 1);
		$this->clearSectionCache();
	}

	function afterUpdate(&$dataEntity) {
		parent::afterUpdate($dataEntity);
		$this->addNote($dataEntity, 2);
		$this->clearSectionCache();
	}

	function afterDelete(&$dataEntity) {
		parent::afterDelete($dataEntity);
		$this->addNote($dataEntity, 3);
		$this->clearSectionCache();
	}

	function addNote(&$dataEntity, $status) {
		$noteControl = BaseFactory::getNoteControl();
		$noteControl->addNote($this->type . ": " . $noteControl->status[$status] . " (Id:" . $dataEntity->get("Id") . ")", $this->type, $dataEntity->get("Id"), $status);
	}

	function clearSectionCache() {
		$cacheControl = CoreFactory::getCacheControl();
		$cacheControl->deleteWebPageCache($this->type);
	}
	/*
	 * Sets the control's filter to the latest articles with tag Announcement
	 * @param Integer $limit Maximum number of results returned
	 * @return void
	 */
	function getLatestAnnouncements($limit = 3) {
		$this->clearFilter();

		if ($this->type != null) {
			$this->retrieveByTag($this->type . "/Announcement");
		} else {
			$this->retrieveByTag($this->table . "/Announcement");
		}

		$filter = $this->getFilter();
		$filter->addOrder("LiveDate", true);

		$application = CoreFactory::getApplication();
		$date = $application->getCurrentDateTime();

		$liveDateConditions[] = $filter->makeConditional($this->table, "LiveDate", $date, "<=");
		$filter->addConditionalGroup($liveDateConditions, "AND");

		$expiryDateConditions[] = $filter->makeConditional($this->table, "ExpiryDate", $date, ">=", "OR");
		$expiryDateConditions[] = $filter->makeConditional($this->table, "ExpiryDate", null, "IS", "OR");
		$filter->addConditionalGroup($expiryDateConditions, "AND");

		$filter->addLimit($limit);
		$this->setFilter($filter);
	}

	/*
	 * Sets the control's filter to the latest articles
	 * @param Integer $limit Maximum number of results returned
	 * @param Mixed $excludeIds All items to be excluded in the filter
	 * @param Mixed $includeTags All tags to be included in the filter
	 * @param Mixed $excludeTags All tags to be excluded in the filter
	 * @param Boolean $showActive Whether to use the active column
	 * @return void
	 */
	function getLatest($limit = 3, $excludeIds = null, $includeTags = null, $excludeTags = null, $includeTagOperator = "AND", $excludeTagOperator = "OR", $showActive = false) {
		$this->clearFilter();

		if ($includeTags == null) {
			if ($this->type != null) {
				$includeTags = $this->type;
			} else {
				$includeTags = $this->table;
			}
		}

		$this->retrieveByTag($includeTags, $includeTagOperator, $excludeTags, $excludeTagOperator);
		$filter = $this->getFilter();

		$filter->addOrder("LiveDate", true);

		if ($excludeIds) {
			if (is_array($excludeIds)) {
				foreach ($excludeIds as $excludeId) {
					$filter->addConditional($this->table, "Id", $excludeId, "!=");
				}
			} else {
				$filter->addConditional($this->table, "Id", $excludeIds, "!=");
			}
		}

		$date = $this->application->getCurrentDateTime();

		$liveDateConditions[] = $filter->makeConditional($this->table, "LiveDate", $date, "<=");
		$filter->addConditionalGroup($liveDateConditions, "AND");

		$expiryDateConditions[] = $filter->makeConditional($this->table, "ExpiryDate", $date, ">=", "OR");
		$expiryDateConditions[] = $filter->makeConditional($this->table, "ExpiryDate", null, "IS", "OR");
		$filter->addConditionalGroup($expiryDateConditions, "AND");

		if ($showActive) {
			$filter->addConditional($this->table, "Active", true);
		}

		if ($limit != null) {
			$filter->addLimit($limit);
		}
		$this->setFilter($filter);
	}


	/*
	 * Get live and active articles
	 * @param Mixed $includeTags All tags to be included in the filter
	 * @return void
	 */
	function getLiveAndActive($includeTags = null) {
		$this->getLatest(null, null, $includeTags, null, "AND", "OR", true);
	}
}