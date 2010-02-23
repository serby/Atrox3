<?php
	/**
	 * @package Base
	 * @subpackage DynamicEntity
	 */

	/**
	 * Include Data.php so that DataControl can be extended.
	 * Include Dataentity.php so that DataEntity can be extended
	 */
	require_once("Atrox/3.0/Core/Data/Data.php");
	require_once("Atrox/3.0/Core/Data/DataEntity.php");
	
	
	
	/**
	 *
	 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
	 * @copyright Clock Limited 2007
	 * @version 3.0 - $Revision$ - $Date$
	 * @package Base
	 * @subpackage DynamicEntity
	 */
	class EntityControl extends DataControl {
		var $table = "Entity";
		var $key = "Id";
		var $sequence = "Entity_Id_seq";
		var $defaultOrder = "Id";
		var $searchFields = array("Id");	

		function init() {

			$this->fieldMeta["Id"] = new  FieldMeta(
				"Id", "", FM_TYPE_INTEGER, null, FM_STORE_NEVER, false);
				
			$this->fieldMeta["EntityTemplateId"] = new  FieldMeta(
				"EntityTemplateId", "", FM_TYPE_RELATION, null, FM_STORE_ALWAYS, false);

			$this->fieldMeta["EntityTemplateId"]->setRelationControl(BaseFactory::getEntityTemplateControl());
				
			$this->fieldMeta["MemberId"] = new  FieldMeta(
				"MemberId", "", FM_TYPE_RELATION, null, FM_STORE_ALWAYS, false);
				
			$this->fieldMeta["MemberId"]->setRelationControl(BaseFactory::getMemberControl());
			
			$this->fieldMeta["DateCreated"] = new FieldMeta(
				"DateCreated", "", FM_TYPE_DATE, 1, FM_STORE_NEVER, true);

			$this->fieldMeta["DateCreated"]->setFormatter(CoreFactory::getDateTimeFieldFormatter());

			$this->fieldMeta["LastModified"] = new FieldMeta(
				"Last Modified", "", FM_TYPE_DATE, 1, FM_STORE_ALWAYS, false);

			$this->fieldMeta["LastModified"]->setFormatter(CoreFactory::getDateTimeFieldFormatter());
			
			$this->fieldMeta["Tags"] = new FieldMeta(
				"Tags", "", FM_TYPE_STRING, 5000, FM_STORE_ALWAYS, true);
			
			$this->fieldMeta["Tags"]->setFormatter(CoreFactory::getBodyTextFormatter());
		}
		
		/**
		 * Retrieve all entities which are based on a particular template
		 * @param DataEntity $entityTemplate
		 * @param String $order Fieldname to order the results by
		 * @return void
		 */
		function retrieveForEntityTemplate(&$entityTemplate, $order = null) {
			$filter = CoreFactory::getFilter();
			$filter->addConditional($this->table, "EntityTemplateId", $entityTemplate->get("Id"));
			if ($order) {	
				$filter->addOrder($order);
			}
			$this->setFilter($filter);
		}
		
		/**
		 * Map fileInfo to the relavant fields in the post data ready for saving
		 * @param Array $postData
		 * @param Array $fileInfo
		 * @return Array $postData Array containing all values and file information
		 */
		function mapEntity($postData, $fileInfo) {
			foreach ($postData as $entityTemplateId => $data) {
				if (is_array($data)) {
					foreach ($data as $entityTemplateElementId => $entityValue) {
						$binary = "Binary" . $entityTemplateElementId;
						if (isset($fileInfo[$entityTemplateId]["name"][$binary])) {
							$postData[$entityTemplateId][$entityTemplateElementId] = array("CurrentId" => $entityValue, 
								"FileInfo"=> array(
									"Remove"=>isset($_POST[$entityTemplateId]["Remove-" . $entityTemplateElementId]),
									"Filename"=>$fileInfo[$entityTemplateId]["name"][$binary],
									"Type"=>$fileInfo[$entityTemplateId]["type"][$binary],
									"TempName"=>$fileInfo[$entityTemplateId]["tmp_name"][$binary],
									"Error"=>$fileInfo[$entityTemplateId]["error"][$binary],
									"Size"=>$fileInfo[$entityTemplateId]["size"][$binary]
								)
							);
						}
					}
				}
			}
			return $postData;
		}
		
		function search($value, $entityTemplateElement = null, $operator = null) {
			$filter = $this->getFilter();
			$filter->addJoin($this->table, "Id","EntityValue", "EntityId");
			switch ($operator) {
				case "==":
				case null:
						$filter->addFreeTextCondition("EntityValue", "TextIndex", $value);
					break;
				case "===":
					$filter->addConditional("EntityValue", "Value", "%{$value}%", "ILIKE");
					break;					
				case "<":
				case ">":
				case ">=":
				case "<=":
					$filter->addConditional("EntityValue", "Value", $value, $operator);
					break;
			}
			
			if ($entityTemplateElement) {
				$filter->addConditional("EntityValue", "EntityTemplateElementId", $entityTemplateElement->get("Id"));
			}
			$filter->setDistinct(true, "Entity", "Id");
			$this->setFilter($filter);
		}
		
		/**
		 * Delete entities that are in the list of Ids, and any tags associated with the entity
		 * @param String $ids CSV of ids
		 */
		function delete($ids) {
			$explodedIds = explode(",", $ids);
			$tagToDataControl = BaseFactory::getTagToDataControl();
			foreach ($explodedIds as $id) {
				if ($entity = $this->item($id)) {
					$tagToDataControl->deleteTagsForEntity($entity, "Entity::" . $entity->getRelationValue("EntityTemplateId", "Name"));
				}
			}
			return parent::delete($ids);	
		}
		
		/**
		 * Create a new entity and entity values and map the relevant post data
		 * @param Dataentity $entityTemplate
		 * @param Array $data Post data including file information
		 * @return Interger Id of saved entity
		 */
		function createNewEntityAndSaveValues($entityTemplate, $data) {
			
			$application = CoreFactory::getApplication();
			$entityValueControl = BaseFactory::getEntityValueControl();
			
			if ((!isset($data["EntityId"])) || (!$entity = $this->item($data["EntityId"]))) {
				$entity = $this->makeNew($entityTemplate->get("Id"));
			}
			$entity->set("MemberId", $application->securityControl->getMemberId());
			$entity->set("LastModified", $application->getCurrentDateTime());
			
			$tagArray = $this->convertStringsToUniqueArray($data["Tags"], isset($data["RequiredTags"]) ? $data["RequiredTags"] : null);
			
			$tags = implode("\n", $tagArray);

			$entity->set("Tags", $tags);
			if ($entity->save()) {
				$additionalTags = $entityValueControl->saveEntityValueForEntity($entity, $data[$entityTemplate->get("Id")]);
				$tagArray = array_merge($additionalTags["New"], $tagArray);
				$tagArray = array_unique($tagArray);
				foreach ($additionalTags["Remove"] as $removeTag) {
					foreach ($tagArray as $key => $tag) {
						if ($leftOver = strstr($tag, $removeTag)) {
							unset($tagArray[$key]);
						}
					}
				}
				$tagToDataControl = BaseFactory::getTagToDataControl();
				$tagToDataControl->saveTags($entity, $tagArray, "Entity::" . $entityTemplate->get("Name"));
				$this->updateField($entity, "Tags", implode("\n", $tagArray));
			} 
			return $entity->get("Id");
		}
		
		/**
		 * Converts to strings into an array and removes empty values and duplicates
		 * @param String $string1 first string to be combined
		 * @param String $string2 second string to be combined
		 * @param String $seperator seperator used to explode strings
		 * @return Array $uniqueArray Unique array of exploded strings
		 **/
		function convertStringsToUniqueArray($string1, $string2, $seperator = "\n") {
			//Explode each string on seperator
			$string1Array = explode($seperator, $string1);
			$string2Array = explode($seperator, $string2);
			
			//Combine two arrays
			$combinedArray = array_merge($string1Array, $string2Array);
			
			//$combinedArray = array_map("trim", $combinedArray);
			
			$uniqueArray = array();
			foreach($combinedArray as $element) {
				$uniqueArray[] = trim($element);
			}
			
			//Remove duplicate values
			$uniqueArray = array_unique($uniqueArray);
			 
			//Remove any empty values
			$emptyValues = array_keys($uniqueArray, "");
			foreach ($emptyValues as $key) {
				unset($uniqueArray[$key]);
			}
			return $uniqueArray;
		}
		
		function getOptionsAsArray($entityTemplateName, $fieldName) {
			$entityTemplateElementControl = BaseFactory::getEntityTemplateElementControl();
			$filter = CoreFactory::getFilter();
			$filter->addJoin($entityTemplateElementControl->table, "EntityTemplateId", "EntityTemplate", "Id");
			$filter->addConditional("EntityTemplate", "Name", $entityTemplateName, "=");
			$filter->addConditional($entityTemplateElementControl->table, "Name", $fieldName, "=");
			$entityTemplateElementControl->setFilter($filter);
			if ($entityTemplateElement = $entityTemplateElementControl->getNext()) {
				return explode(",", $entityTemplateElement->get("Options"));
			} else {
				return false;
			}
		}
		
		/**
		 * Get an instance of the extended data entity
		 * @return Dataentity EntityDataEntity
		 */
		function getDataEntity() {
			return new EntityDataEntity($this);
		}
	
		function beforeUpdate(&$dataEntity) {
		}
		
		function beforeInsert(&$dataEntity) {
			$this->beforeUpdate($dataEntity);
		}
		
		/**
		 * Extend outputMap to generate instance of entityValues when item function is called
		 * @param Array $data Array of values to be set
		 * @return Dataentity $return new Entity Dataentity
		 **/
		function outputMap(&$data) {
			$return = parent::outputMap($data);
			return $return;
		}

		/**
		 * Extend makeNew to generate instance of entityValues when makeNew function is called
		 * @param Integer $entityTemplateId Id of entity template to use to generate the entity values
		 * @return Dataentity $return new Entity Dataentity
		 **/
		function makeNew($entityTemplateId) {
			$return = parent::makeNew();
			$return->set("EntityTemplateId", $entityTemplateId);
			$return = $this->createEntityValue($return);
			return $return;
		}
		
		/**
		 * Create entity values for the entity dataentity passed in, nd assign to the entity
		 * @param Dataentity $entity
		 * @return Dataentity $entity
		 **/
		function createEntityValue(&$entity) {
			$entityValueControl = BaseFactory::getEntityValueControl();
			$entityTemplate = $entity->getRelation("EntityTemplateId");
			
			$entityTemplateElementControl = BaseFactory::getEntityTemplateElementControl();
			$entityTemplateElementControl->retrieveForEntityTemplate($entityTemplate);
			while ($entityTemplateElement = $entityTemplateElementControl->getNext()) {
				$entityValue = $entityValueControl->makeNew();
				$entityValue->set("EntityTemplateElementId", $entityTemplateElement->get("Id"));
				$entity->entityValueObject["Id"][$entityTemplateElement->get("Id")] = $entityValueControl->getEntityValueObject($entityValue);
				$entity->entityValueObject["Name"][$entityTemplateElement->get("Name")] = &$entity->entityValueObject["Id"][$entityTemplateElement->get("Id")];
			}
			if ($entity->get("Id") != "") {
				$entityValueControl->retrieveForEntity($entity);
				while ($entityValue = $entityValueControl->getNext()) {
					if (isset($entity->entityValueObject["Id"][$entityValue->get("EntityTemplateElementId")])) {
						$entity->entityValueObject["Id"][$entityValue->get("EntityTemplateElementId")]->entityValue->set("Id", $entityValue->get("Id"));
						$entity->entityValueObject["Id"][$entityValue->get("EntityTemplateElementId")]->entityValue->set("EntityId", $entityValue->get("EntityId"));
						$entity->entityValueObject["Id"][$entityValue->get("EntityTemplateElementId")]->entityValue->set("EntityTemplateElementId", $entityValue->get("EntityTemplateElementId"));
						$entity->entityValueObject["Id"][$entityValue->get("EntityTemplateElementId")]->entityValue->set("Value", $entityValue->get("Value"));
					}				
				}
			}
			return $entity;
		}
		
		/**
		 * 
		 */
		function getRelatedEntitiesViewHtml($entity) {
			$filter = CoreFactory::getFilter();
			$tagToDataControl = BaseFactory::getTagToDataControl();
			
			$relatedEntitiesArray = array();
			
			$relatedTag = str_replace("{ID}", 
				$entity->get("Id"),
				$entity->getRelationValue("EntityTemplateId", "LinkingTagPattern"));
				
			if ($relatedTag == "") {
				$relatedTag = $entity->getRelationValue("EntityTemplateId", "LinkingTagPattern") . "/Id/" . $entity->get("Id");
			}
			$filter->addConditional($tagToDataControl->table, "Tag", $relatedTag);
			$filter->addOrder("Type");
			$tagToDataControl->setFilter($filter);
			
			while ($tagToData = $tagToDataControl->getNext()) {
				$entity = $this->item($tagToData->get("DataId"));
				$entityTemplate = $entity->getRelation("EntityTemplateId");
				$relatedEntitiesArray[$entityTemplate->get("Name")][$entity->get("Id")] = $entity->getTitle();
			}
			return $relatedEntitiesArray;
		}
		
		function getRandomEntity($entityTemplateName) {
			$entityTemplateControl = BaseFactory::getEntityTemplateControl();
			if ($entityTemplate = $entityTemplateControl->itemByField($entityTemplateName, "Name")) {
				$filter = CoreFactory::getFilter();
				$filter->addConditional($this->table, "EntityTemplateId", $entityTemplate->get("Id"));
				$this->setFilter($filter);
				if ($randomEntity = $this->getRandom()) {
					return $randomEntity;
				}
			}
			return $this->makeNew($entityTemplate->get("Id"));
		}
		
		function getRandomEntityByTag($tag, $entityTemplateName = null, $limit = 1) {
			$tagToDataControl = BaseFactory::getTagToDataControl();
			$tagToDataControl->retrieveEntities($tag, "Entity::" . $entityTemplateName);
			$tagToDataControl->filter->clearOrder();
			$tagToDataControl->filter->addOrder(-1);
			
			$count = 0;
			$entities = array();
			
			while (($count++ < $limit) && ($tagToData = $tagToDataControl->getNext())) {
				$entities[] = $this->item($tagToData->get("DataId"));
			}
			
			if (sizeof($entities) == 1) {
				$entities = $entities[0];
			}
			
			return $entities;
		}
		
		function getEntitiesForTag($tags, $entityTypes, $orderBy = null, $desc = false, 
			$searchValue = null, $excludeTags = null, $specificDataId = null, $tagLogicOperator = "AND") {
					
			if (!is_array($tags)) {
				$tags = array($tags);
			}
			if (!is_array($entityTypes)) {
				$entityTypes = array($entityTypes);
			}			

			$tagsToDataControl = BaseFactory::getTagToDataControl();
			$filter = CoreFactory::getFilter();
			$filter->addConditional("TagToData", "Tag", $tags[0] . "%", "ILIKE");
			for ($i = 1; $i < count($tags); $i++) {
				$filter->addJoin("TagToData", "DataId", "TagToData", "DataId", "TagToData{$i}");
				$filter->addConditional("TagToData{$i}", "Tag", $tags[$i] . "%", "ILIKE", $tagLogicOperator);
			}
			if ($specificDataId) {
				$filter->addConditional($tagsToDataControl->table, "DataId", $specificDataId);	
			}
			
			$conditionalGroup = array();
			foreach ($entityTypes as $entityType) {
				$conditionalGroup[] = $filter->makeConditional($tagsToDataControl->table, "Type", $entityType, "=", "OR");
			}
			
			$filter->addConditionalGroup($conditionalGroup);
			
			$tagsToDataControl->setFilter($filter);			
			$ids = array();
			if ($excludeTags) {
				if (!is_array($excludeTags)) {
					$excludeTags = array($excludeTags);
				}
			}
			while ($tagToData = $tagsToDataControl->getNext()) {
				$entity = $this->item($tagToData->get("DataId"));
				if ($excludeTags) {
					foreach ($excludeTags as $excludeTag) {
						if (!$entity->hasTag($excludeTag)) {
							$ids[] = $tagToData->get("DataId");
							continue;
						}
					}
				} else {
					$ids[] = $tagToData->get("DataId");
				}
			}
			if (count($ids) < 1) {
				$ids[] = 0;
			}
			$this->searchListOfIds($ids, $orderBy, $desc, $searchValue);
		}
		
		function getEntitiesForByTemplateName($templateName, $orderBy = null, $desc = false, $searchValue = null, $entityTemplateElement = null, $operator = null) {
			$filter = CoreFactory::getFilter();
			$filter->addJoin($this->table, "EntityTemplateId", "EntityTemplate", "Id");
			$filter->addJoin($this->table, "Id","EntityValue", "EntityId");
			$filter->addOrder("Id");
			$filter->addConditional("EntityTemplate", "Name", $templateName, "=");
			switch ($operator) {
				case "==":
				case null:
						$filter->addFreeTextCondition("EntityValue", "TextIndex", $searchValue);
					break;
				case "===":
					$filter->addConditional("EntityValue", "Value", "%{$searchValue}%", "ILIKE");
					break;					
				case "<":
				case ">":
				case ">=":
				case "<=":
					$filter->addConditional("EntityValue", "Value", $searchValue, $operator);
					break;
			}
			
			if ($entityTemplateElement) {
				$filter->addConditional("EntityValue", "EntityTemplateElementId", $entityTemplateElement->get("Id"));
			}
			$filter->setDistinct(true, "Entity", "Id");
			$this->setFilter($filter);
			$ids = array();
			while ($entity = $this->getNext()) {
				$ids[] = $entity->get("Id");
			}
			if (count($ids) < 1) {
				$ids[] = 0;
			}
			$this->searchListOfIds($ids, $orderBy, $desc, $searchValue);
		}
		
		function searchListOfIds($ids, $orderBy = null, $desc = false, $searchValue = null) {
			if ($searchValue) {
				$filter = CoreFactory::getFilter();
				$filter->addConditional($this->table, "Id", $ids, "IN");
				$filter->addJoin($this->table, "Id","EntityValue", "EntityId");
				$filter->addJoin("EntityValue", "EntityTemplateElementId", "EntityTemplateElement", "Id");
				$filter->addFreeTextCondition("EntityValue", "TextIndex", $searchValue);
				$this->setFilter($filter);
				$ids = array();
				$this->retrieveAll();
				while ($entity = $this->getNext()) {
					$ids[] = $entity->get("Id");
				}
				if (count($ids) < 1) {
					$ids[] = 0;
				}
			}
			$filter = CoreFactory::getFilter();
			$filter->addConditional($this->table, "Id", $ids, "IN");
			if ($orderBy) {
				$filter->addJoin($this->table, "Id","EntityValue", "EntityId");
				$filter->addJoin("EntityValue", "EntityTemplateElementId", "EntityTemplateElement", "Id");
				$filter->addConditional("EntityTemplateElement", "Name", $orderBy, "=");
				$filter->addOrder("Value", $desc);
			}
			$this->setFilter($filter);
			$this->retrieveAll();
		}
		
		function retrieveEntitiesByCatergory($templateName, $categoryField, $categoryName, $orderBy, $desc) {
			$filter = CoreFactory::getFilter();
			$filter->addJoin($this->table, "Id","EntityValue", "EntityId");
			$filter->addJoin($this->table, "EntityTemplateId", "EntityTemplate", "Id");
			$filter->addJoin("EntityValue", "EntityTemplateElementId", "EntityTemplateElement", "Id");
			$filter->addConditional("EntityTemplate", "Name", $templateName, "=");
			$filter->addConditional("EntityTemplateElement", "Name", $categoryField, "=");
			$filter->addConditional("EntityValue", "Value", $categoryName, "=");
			$this->clearFilter();
			$this->setFilter($filter);
			$ids = array();
			while ($entity = $this->getNext()) {
				$ids[] = $entity->get("Id");
			}
			if (count($ids) < 1) {
				$ids[] = 0;
			}
			$this->searchListOfIds($ids, $orderBy, $desc);
		}
	}
	
	class EntityDataEntity extends DataEntity {
		
		/**
		 * @var $entityValueObject array of entityValue objects that belong to this entity
		 */
		var $entityValueObject;
		
		/**
		 * Return the tags with the required tags removed from the list of tags so not to confuse the user
		 * @param Mixed $excludedTags
		 * @return String $tags
		 **/
		function getTags($excludedTags = null) {
			if (!is_array($excludedTags)) {
				$excludedTags = explode(";", $excludedTags);
			}
			$tags = $this->get("Tags");
			foreach ($excludedTags as $excludedTag) {
				$tags = str_replace($excludedTag, "", $tags);
			}
			return $tags;
		}
		
		function getLinkingTag() {
			return str_replace("{ID}", 
				$this->get("Id"),
				$this->getRelationValue("EntityTemplateId", "LinkingTagPattern"));
		}			
		
		function getTitle() {
			if (isset($this->title)) {
				return $this->title;
			}
			$entityTemplate = $this->getRelation("EntityTemplateId");
			$title = $entityTemplate->get("TitleFormat");
			$entityValues = $this->getValues();
			preg_match_all("/\{(.*?)\}/", $title, $matches);
			foreach ($matches[1] as $match) {
				$title = str_replace("{" . $match ."}", $entityValues[$match]->getFormatted(), $title);
			}
			
			return $this->title = $title;
		}
	
	
		/**
		 * Return the entityValue objects
		 * @return Array $entityValueObject
		 **/
		function getValue($name) {
			if (!isset($this->entityValueObject["Name"])) {
				// This doesn't work in PHP 5 in PHP 4 compatablity mode 
				// $this = $this->control->createEntityValue($this);
				$this->control->createEntityValue($this);
			}
			return $this->entityValueObject["Name"][$name];
		}		
		
		function getValues() {
			if (!isset($this->entityValueObject["Name"])) { 
				$this->control->createEntityValue($this);
			}			
			return $this->entityValueObject["Name"];
		}
		
		function getRelated($tagPrefix) {
			$returnEntities = false;
			if ($tags = $this->get("Tags")) {
				$tags = explode("\n", $tags);
				$prefixLength = mb_strlen($tagPrefix);
				foreach ($tags as $tag) {
					if ($tagPrefix == mb_substr($tag, 0, $prefixLength)) {
						$relatedId = mb_substr($tag, $prefixLength);
						if ($relatedEntity = $this->control->item($relatedId)) {
							$returnEntities[] = $relatedEntity;
						}
					}
				}
			}
			return $returnEntities;
		}
		
		
		/**
		 * If the Entity has a tag with the given prefix then return remaining part of the tag
		 * @author Paul Serby
		 * @param String $tagPrefix The starting part of the tag to match
		 * @param Boolean $returnIdOnMatch Should the ID part of the matching tag be returned
		 * @return Mixed The remaining tag or false
		 */
		function hasTag($tagPrefix, $returnIdOnMatch = false) {
			if ($tags = $this->get("Tags")) {
				$tags = explode("\n", $tags);
				$prefixLength = mb_strlen($tagPrefix);
				foreach ($tags as $tag) {
					if ($tagPrefix == mb_substr($tag, 0, $prefixLength)) {
						if ($returnIdOnMatch) {
							return mb_substr($tag, $prefixLength);
						} else {
							return true;
						}
					}
				}
			}
			return false;
		}
		
		function stripTag($tagPrefix) {
			$newTags = array(); 
			if ($tags = $this->get("Tags")) {
				$tags = explode("\n", $tags);
				$prefixLength = mb_strlen($tagPrefix);
				foreach ($tags as $tag) {
					if ($tagPrefix != mb_substr($tag, 0, $prefixLength)) {
						$newTags[] = $tag;
					}
				}
				echo implode("\n", $newTags);
				$this->set("Tags", implode("\n", $newTags));
				return true;
			}			
			return false;
		}
		
		/**
		 * Maps the post data to the entity values that belong to this entity
		 * @param Array $data Array of post data
		 * @return String $tags
		 **/
		function map($data) {
			if (!isset($this->entityValueObject["Id"])) { 
				$this->control->createEntityValue($this);
			}
			foreach ($data[$this->get("EntityTemplateId")] as $entityTemplateElementId => $value) {
				if (is_numeric($entityTemplateElementId)) {
					$this->entityValueObject["Id"][$entityTemplateElementId]->entityValue->set("Value", $value);
				}
			}
		}
		
		/**
		 * Generate the Html for the entity using the entity values
		 * @param String $pageType Which type of page the Html is being used on such as, View or Control
		 * @return String $output String of Html
		 **/
		function toHtml($pageType, $requiredTags = null) {
			$output = "";
			$currentGroup = "START";
			foreach ($this->getValues() as $entityValueObject) {
				$newGroup = $entityValueObject->entityValue->getRelationValue("EntityTemplateElementId", "Group");
				if ($newGroup == "") {
					if (($currentGroup == "START") || ($currentGroup == "")) {
						$newGroup = "";
					} else {
						$newGroup = "Other Details";
					}
				}				
				if ($currentGroup != $newGroup) {
					if ($currentGroup != "START") {
						$output .= "</div>";
					}
					
					$tmp = str_replace(" ", "-", mb_strtolower($newGroup));
					
					if ($newGroup != "") {
						$output .= "<h4 id=\"entitytemplate-group-{$tmp}-header\">{$newGroup}</h4>\n";
					}
					
					$output .= "<div id=\"entitytemplate-group-{$tmp}-panel\" class=\"entitytemplate-group\">";
					$currentGroup = $newGroup;
				}
				$output .= $entityValueObject->toHtml($pageType);
			}
			$output .= "</div>";
					
			$output .= "<input type=\"hidden\" name=\"EntityTemplateId\" value=\"" . $this->get("EntityTemplateId") . "\" />\n";
			$output .= "<input type=\"hidden\" name=\"RequiredTags\" value=\"{$requiredTags}\" />\n";
			$output .= "<input type=\"hidden\" id=\"id-input\" name=\"EntityId\" value=\"" . $this->get("Id") . "\" />\n";			
			$output .= $this->tagsToHtml($pageType, $requiredTags);			
			return $output;
		}
		
		/**
		 * Generate the Html for tags.
		 * @param String $pageType Which type of page the Html is being used on such as, View or Control
		 * @return String $output String of Html
		 **/
		function tagsToHtml($pageType, $requiredTags = null) {
			$output  = "";
			switch ($pageType) {
				case "View":
					if ($this->get("Tags") != "") {
						$output .= "<p><strong>Tags:</strong>" . str_replace("\n", ", " , $this->get("Tags")) . "</p>";
					}	
					break;
				case "Control":
					$id = $this->get("Id");
					$name = $this->getRelationValue("EntityTemplateId", "Name");
					$output .= <<<HTML

<div class="tags">
	<p>Tags:</p>
	<div class="new">
		<label><strong>New Tag:</strong><input type="text" id="tag-field" name="TagField" class="textbox" /><a class="micro-button" href="#" onclick="tagControl.add(); return false;">Add</a></label>
	</div>
	<div id="matching-tags" class="autocomplete">&nbsp;</div> 
	<ul id="tag-list">
		<li>&nbsp;</li>
	</ul> 
</div>
<input id="tags" name="Tags" class="textbox" type="hidden" value="" />
<script type="text/javascript">
	var tags = new Array();
	tagControl = new TagControl(tags, "tag-field", "tag-list", "tags", "matching-tags");
	tagControl.load("/resource/application/service/tag.php?Action=Load&Id={$id}&Type=Entity::{$name}&Exclude={$requiredTags}");
	new Ajax.Autocompleter("tag-field", "matching-tags", "/resource/application/service/tag.php?Action=ListAutoCompleteTags");
</script>

HTML;
					break;
			}
			return $output;
		}
	}
