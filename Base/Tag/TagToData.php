<?php
/**
 * @package Base
 * @subpackage Tag
 * @copyright Clock Limited 2007
 * @version 3.0 - $Revision$ - $Date$
 */

/**
 * Include data.php so that DataControl can be extended.
 * Include DataEntity.php so that DataEntityControl can be extended.
 */
require_once("Atrox/Core/Data/Data.php");
require_once("Atrox/Core/Data/DataEntity.php");

/**
 *
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2007
 * @version 3.0 - $Revision$ - $Date$
 * @package Base
 * @subpackage Tag
 */
class TagToDataControl extends DataControl {
	var $table = "TagToData";
	var $key = "Id";
	var $sequence = "TagToData_Id_seq";
	var $defaultOrder = "Id";
	var $searchFields = array("Id");

	function init() {

		$this->fieldMeta["Id"] = new  FieldMeta(
				"Id", "", FM_TYPE_INTEGER, null, FM_STORE_NEVER, false);
			
		$this->fieldMeta["Tag"] = new FieldMeta(
				"Tag", "", FM_TYPE_STRING, 5000, FM_STORE_ALWAYS, false);

		$this->fieldMeta["Type"] = new FieldMeta(
				"Data type", "", FM_TYPE_STRING, 5000, FM_STORE_ALWAYS, false);
			
		$this->fieldMeta["DataId"] = new  FieldMeta(
				"DataId", "", FM_TYPE_INTEGER, null, FM_STORE_ALWAYS, false);
	}

	function deleteTagsForEntity($dataEntity, $entityType, $tagField = "Tags") {
		$tagsToDelete = explode("\n", $dataEntity->get($tagField));
		foreach($tagsToDelete as $tag) {
			$this->deleteWhere(array("DataId", "Type"), array($dataEntity->get("Id"), $entityType));
			if (!$this->itemByField($tag, "Tag")) {
				$tagControl = BaseFactory::getTagControl();
				$tagControl->deleteWhere("Tag", $tag);
			}
		}
	}

	/**
	 * Updates all dataentities that match old tag and replaces with new tag,
	 * other wises delete tag from dataentity
	 *
	 * @param String $oldTag current tag
	 * @param Object $tagTypeController class that defines tag field and types and return correct dataentity
	 * @param String $newTag if false delete other wise update to this tag
	 */

	function updateDataEntities($oldTag, $tagTypeController, $newTag = false) {
		$filter = CoreFactory::getFilter();
		$filter->addConditional($this->table, "Tag", $oldTag . "%", "ILIKE");
		$this->setFilter($filter);
			
		while ($tagToData = $this->getNext()) {
			if ($dataEntity = $tagTypeController->getDataEntity($tagToData->get("Type"), $tagToData->get("DataId"))) {
				$tags = $dataEntity->get($tagTypeController->tagField[$tagToData->get("Type")]);
				$tagArray = explode("\n", $tags);
				foreach ($tagArray as $k => $tag) {
					if (false !== strpos($tag, $oldTag)) {
						unset($tagArray[$k]);
					}
				}
				if ($newTag) {
					$tagArray[] = $newTag;
					array_unique($tagArray);
				}
				$dataEntity->set($tagTypeController->tagField[$tagToData->get("Type")], implode("\n", $tagArray));
				$dataEntity->save();
			}
		}
	}

	function saveTags($dataEntity, $tags, $type) {
		$tagControl = BaseFactory::getTagControl();

		//Search for all tags for entity
		$filter = CoreFactory::getFilter();
		$filter->addConditional($this->table, "Type", $type);
		$filter->addConditional($this->table, "DataId", $dataEntity->get("Id"));
		$this->setFilter($filter);
		$tagIds = "";
		//Loop through matches
		while ($savedTag = $this->getNext()) {
			$tagIds .= $savedTag->get("Id") . ",";
		}

		//Then delete tags from tagtodata table
		if ($tagIds != "") {
			$tagIds = mb_substr($tagIds, 0, -1);
			$this->delete($tagIds);
		}
			
		//For each tag add a new record to tagToData
		foreach ($tags as $tag) {
			if ($tag != "") {
				$tagControl->addTag($tag);
				$tagToData = $this->makeNew();
				$tagToData->set("Type", $type);
				$tagToData->set("Tag", $tag);
				$tagToData->set("DataId", $dataEntity->get("Id"));
				$tagToData->save();
			}
		}
	}

	function retrieveEntities($tags, $entityTypes = null) {
		if (!is_array($tags)) {
			$tags = array($tags);
		}
		$filter = CoreFactory::getFilter();
		foreach ($tags as $tag) {
			$conditions[] = $filter->makeConditional($this->table, "Tag", $tag, "=", "OR");
		}
		$filter->addConditionalGroup($conditions);
		if ($entityTypes) {
			if (!is_array($entityTypes)) {
				$entityTypes = array($entityTypes);
			}
			foreach ($entityTypes as $entityType) {
				$typeConditions[] = $filter->makeConditional($this->table, "Type", $entityType, "=", "OR");
			}
			$filter->addConditionalGroup($typeConditions);
		}
		$this->setFilter($filter);
	}
		
	function retrieveTagsForEntities($entityId, $entityType) {
		$filter = CoreFactory::getFilter();
		$filter->addConditional($this->table, "DataId", $entityId);
		$filter->addConditional($this->table, "Type", $entityType);
		$this->setFilter($filter);
	}

	/**
	 * Removes all tags and rebuilds them using the TagToData table
	 * Warning: Use this function with Caution
	 */
	function refreshTags() {
		$tagControl = BaseFactory::getTagControl();
		$tagControl->deleteAll();
		$this->retrieveAll();
		$tagToData = $this->getNext();
		$tagControl->addTag($tagToData->get("Tag"));
	}
	
	/**
	 * Returns the most popular tags
	 * @param $limit Integer
	 */
	function getMostPopularTags($limit = -1) {
		$sql = "SELECT \"Tag\", count(*) FROM \"{$this->table}\" GROUP BY \"Tag\" ORDER BY count(*) DESC " .  
			(($limit != -1) ? "LIMIT " . $limit: "");
			
		$this->runQuery($sql);
	}
}