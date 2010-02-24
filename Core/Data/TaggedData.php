<?php
/**
 * @package Core
 * @subpackage Data
 * @copyright Clock Ltd 2008
 * @version 3.2 - $Revision$ - $Date$
 */

/**
 * Include Data.php so that DataControl can be extended.
 */
require_once("Atrox/Core/Data/Data.php");

/**
 *
 * @author Dom Udall (Clock Ltd) {@link mailto:dom.udall@clock.co.uk dom.udall@clock.co.uk }
 * @copyright Clock Ltd 2008
 * @version 3.2 - $Revision$ - $Date$
 * @package Core
 * @subpackage Data
 */

class TaggedDataControl extends DataControl {
	
	/**
	 * The tag type which will appear in the 'Type' column of the 'TagToData' table
	 * If this is left as null it will become the table name.
	 *
	 * @var string
	 */
	var $type = null;
	
	function TaggedDataControl($type = null) {
		parent::DataControl();
		if ($type != null) {
			$this->type = $type;
		} else if ($this->type == null) {
			$this->type = $this->table;
		}
	}
	
	function retrieveByTag($tags = null, $tagLogicOperator = "AND", $excludeTags = null, $excludeTagLogicOperator = "AND", $orderByField = null, $orderByDescending = false) {
		$this->clearFilter();
		$filter = CoreFactory::getFilter();
		
		if ($tags || $excludeTags) {
			$tagsToDataControl = BaseFactory::getTagToDataControl();
			
			if ($excludeTags) {
				$excludeFilter = CoreFactory::getFilter();
				if (!is_array($excludeTags)) {
					$excludeTags = array($excludeTags);
				}
				for ($i = 0; $i < count($excludeTags); $i++) {
					$excludeFilter->addJoin($this->table, $this->key, "TagToData", "DataId", "TagToData{$i}");
					$excludeFilter->addConditional("TagToData{$i}", "Tag", $excludeTags[$i] . "%", "ILIKE", $excludeTagLogicOperator);
				}
				$excludeFilter->setDistinct(true);
				$this->setFilter($excludeFilter);
				
				while ($dataEntity = $this->getNext()) {
					$filter->addConditional($this->table, "Id", $dataEntity->get("Id"), "!=");
				}
				
				$this->clearFilter();
			}
			
			switch ($tagLogicOperator) {
				default:
				case "AND":
					if ($tags) {
						if (!is_array($tags)) {
							$tags = array($tags);
						}
						for ($i = 0; $i < count($tags); $i++) {
							$filter->addJoin($this->table, $this->key, "TagToData", "DataId", "TagToData{$i}");
							$filter->addConditional("TagToData{$i}", "Tag", $tags[$i] . "%", "ILIKE", $tagLogicOperator);
						}
						$filter->addConditional("TagToData0", "Type", $this->type);
					}
					
					$filter->setDistinct(true);
					break;
				case "OR":
					$filter->addJoin($this->table, $this->key, "TagToData", "DataId", "TagToData");
					
					if ($tags) {
						if (!is_array($tags)) {
							$tags = array($tags);
						}
						for ($i = 0; $i < count($tags); $i++) {
							$includeConditions[] = $filter->makeConditional("TagToData", "Tag", $tags[$i] . "%", "ILIKE", $tagLogicOperator);
						}
						$filter->addConditionalGroup($includeConditions);
					}
	
					$filter->addConditional("TagToData" , "Type", $this->type);
					$filter->setDistinct(true);
					break;
			}
		}
		if ($orderByField) {	
			$filter->addOrder($orderByField, $orderByDescending);
		}
		$this->setFilter($filter);
	}
	
	function hasTag(&$dataEntity, $tags = null) {
		if (!$tags) {
			return false;
		}
		if (!is_array($tags)) {
			$tags = array($tags);
		}

		foreach ($tags as $tag) {
			if (strchr($dataEntity->get("Tags"), $tag)) {
				return true;
			}
		}
		return false;
	}
	
	function saveTags($dataEntity) {
		$tagArray = explode("\n", $dataEntity->get("Tags"));
		$tagToDataControl = BaseFactory::getTagToDataControl();
		$tagToDataControl->saveTags($dataEntity, $tagArray, $this->type);
	}
	
	function afterUpdate(&$dataEntity) {
		$this->saveTags($dataEntity);
	}
	
	function afterInsert(&$dataEntity) {
		$this->saveTags($dataEntity);
	}
}