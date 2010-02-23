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
class TagControl extends DataControl {
	var $table = "Tag";
	var $key = "Id";
	var $sequence = "Tag_Id_seq";
	var $defaultOrder = "Tag";
	var $searchFields = array("Tag");

	function init() {

		$this->fieldMeta["Id"] = new  FieldMeta(
			"Id", "", FM_TYPE_INTEGER, null, FM_STORE_NEVER, false);
		
		$this->fieldMeta["Tag"] = new FieldMeta(
			"Tag", "", FM_TYPE_STRING, 5000, FM_STORE_ALWAYS, false, FM_OPTIONS_UNIQUE);
			
		$this->fieldMeta["BrokenTag"] = new FieldMeta(
			"Broken down tag", "", FM_TYPE_STRING, 5000, FM_STORE_ALWAYS, false);
	}
	
	function addTag($tag) {
		// Don't validate as save will fail if tag is already in table.
		$data = $this->makeNew();
		$data->set("Tag", $tag);
		$data->set("BrokenTag", implode(" ", explode("/", $tag)));
		$data->save();
		$this->errorControl->clear();
	}
	
	function deleteTag($tag) {
		$this->retrieveWithPrefix($tag);
		while ($tag = $this->getNext()) {
			$this->delete($tag->get("Id"));
		}
	}

	function showTagsAsString() {
		$string = "";
		while ($tag = $this->getNext()) {
				$string .= "'" . $tag->get("Tag") . "',";
		}
		return mb_substr($string, 0, -1);
	}
	
	function retrieveWithPrefix($prefix = null) {
		if ($prefix) {
			$filter = CoreFactory::getFilter();
			$filter->addOrder("Tag");
			$filter->addConditional($this->table, "Tag", $prefix . "%", "ILIKE");
			$this->setFilter($filter);
		}
	}
	
	function getTagTree($prefix = null) {
		$tagTree = array();
		$this->retrieveWithPrefix($prefix);
		while ($tag = $this->getNext()) {
			$tags = explode("/", $this->stripPrefix($tag->get("Tag"), $prefix));
			$tagTree = $this->buildTree($tags, $tagTree);
		}
		return $tagTree;
	}
	
	function buildTree($tags, $tagTree) {
		if ($firstTag = array_shift($tags)) {
			if (!isset($tagTree[$firstTag])) {
				$tagTree[$firstTag] = array();
			}
			$tagTree[$firstTag] = $this->buildTree($tags, $tagTree[$firstTag]);
		}
		return $tagTree;
	}

	function updateAllChildrenTags($search, $replace) {
		$this->retrieveWithPrefix($search);
		while ($tag = $this->getNext()) {
			$tag->set("Tag", str_replace($search, $replace, $tag->get("Tag")));
			$tag->set("BrokenTag", implode(" ", explode("/", $tag->get("Tag"))));
			$tag->save();
			$this->errorControl->clear();
		}
	}
	
	function stripPrefix($tag, $prefix) {
		return trim(mb_substr($tag, mb_strlen($prefix)), "/");
	}
	
	function retrieveSuffix($tag = null) {
		if ($tag) {
			if (mb_substr_count($tag, "/") > 0) {
				return mb_substr(strrchr($tag, "/"), 1);
			}
			return $tag;
		}
		return false;
	}
	
	/*
	 * Removes a tag if it exists in the tags field of the data entity.
	 * @param DataEntity Data Entity for the tag to be removed from
	 * @param DataEntity Tag Data entity that is removed
	 * @return void
	 */
	function removeTagFromDataEntity($dataEntity, $tagToRemove) {
		$tags = explode("\n", $dataEntity->get("Tags"));
		unset($tags[array_search($tagToRemove, $tags)]);
		$dataEntity->set("Tags", implode("\n", $tags));
		if ($dataEntity->save()) {
			return true;		
		}
		return false;
	}
}