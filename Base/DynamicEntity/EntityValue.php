<?php
/**
 * @package Base
 * @subpackage DynamicEntity
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 */

/**
 * Include Data.php so that DataControl can be extended.
 * Include DataEntity.php so that DataEntity can be extended
 */
require_once("Atrox/Core/Data/Data.php");
require_once("Atrox/Core/Data/DataEntity.php");

/**
 *
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Base
 * @subpackage DynamicEntity
 */
class EntityValueControl extends DataControl {
	var $table = "EntityValue";
	var $key = "Id";
	var $sequence = "EntityValue_Id_seq";
	var $defaultOrder = "Id";
	var $searchFields = array("Value");

	function init() {

		$this->fieldMeta["Id"] = new  FieldMeta(
			"Id", "", FM_TYPE_INTEGER, null, FM_STORE_NEVER, false);

		$this->fieldMeta["Value"] = new FieldMeta(
			"Value", "", FM_TYPE_STRING, 100000, FM_STORE_ALWAYS, true);
			
		$this->fieldMeta["EntityId"] = new  FieldMeta(
			"EntityId", "", FM_TYPE_RELATION, null, FM_STORE_ALWAYS, false);

		$this->fieldMeta["EntityId"]->setRelationControl(BaseFactory::getEntityControl());
			
		$this->fieldMeta["EntityTemplateElementId"] = new  FieldMeta(
			"EntityTemplateElementId", "", FM_TYPE_RELATION, null, FM_STORE_ALWAYS, false);

		$this->fieldMeta["EntityTemplateElementId"]->setRelationControl(BaseFactory::getEntityTemplateElementControl());
	}
	/**
 * Retrieve all entity values that belong to the entity passed in.
 * @param Dataentity $entity
 * @param String $order
 * @return void
 */	
	function retrieveForEntity(&$entity, $order = null) {
		$filter = CoreFactory::getFilter();
		$filter->addConditional($this->table, "EntityId", $entity->get("Id"));
			
		if ($order) {	
			$filter->addOrder($order);
		}
		$this->setFilter($filter);
		$this->retrieveAll();
	}
	
	/**
	 * Create and array of all entity values that belong to the entity passed in.
	 * @param Dataentity $entity
	 * @return Array $entityValueArray 
	 */	
	function retrieveEntityValueArray($entity) {
		$entityValueArray = array($entity->get("EntityTemplateId") => array());
		$this->retrieveForEntity($entity);
		while ($entityValue = $this->getNext()) {
			 $entityValueArray[$entity->get("EntityTemplateId")][$entityValue->get("EntityTemplateElementId")] = $entityValue->get("Value");
		}
		return $entityValueArray[$entity->get("EntityTemplateId")];
	}
	
	/**
	 * Save entity values
	 * @param Dataentity $entity
	 * @param Array $entityValues
	 * @return void
	 */
	function saveEntityValueForEntity($entity, $entityValues) {
		$entityTemplateElementControl = BaseFactory::getEntityTemplateElementControl();
		$tagToDataControl = BaseFactory::getTagToDataControl();
		$additionalTags = array("New" => array(), "Remove" => array());
		foreach ($entityValues as $entityTemplateElementId => $value) {
			if ($entityTemplateElement = $entityTemplateElementControl->item($entityTemplateElementId)) {
				$data["Id"] = $this->checkIfEntityValueExists($entity, $entityTemplateElementId);
				$data["EntityId"] =  $entity->get("Id");
				$data["EntityTemplateElementId"] = $entityTemplateElementId;
				$data["Value"] = $value;
				switch ($entityTemplateElement->get("Type")) {
					case ENTITY_ELEMENT_TYPE_DATETIME:
						$this->initControl();
						$this->fieldMeta["Value"]->type = FM_TYPE_DATE;
						if ($data["Value"] != "") {
							$data["Value"] .= ":00";
							$data["Value"] = $this->application->formatDBDateTime($data["Value"]);
						}
						break;
					case ENTITY_ELEMENT_TYPE_IMAGE:
					case ENTITY_ELEMENT_TYPE_VIDEO:
					case ENTITY_ELEMENT_TYPE_FLASH:
					case ENTITY_ELEMENT_TYPE_ATTACHMENT:
						$this->initControl();
						$this->fieldMeta["Value"]->type = FM_TYPE_BINARY;
						break;
					case ENTITY_ELEMENT_TYPE_BOOLEANTAG:
						$this->initControl();
						$this->fieldMeta["Value"]->type = FM_TYPE_STRING;
						if ($data["Value"] == "t") {
							$additionalTags["New"][] = $entityTemplateElement->get("Options");
						} else {
							$additionalTags["Remove"][] = $entityTemplateElement->get("Options");
						}
					default:
						$this->initControl();
						$this->fieldMeta["Value"]->type = FM_TYPE_STRING;
						break;
				}
				$entityValue = $this->map($data);
				
				$entityValue->save();
			}
		}
		return $additionalTags;
	}
	
	/**
	 * Check if entity value exists
	 * @param Dataentity $entity
	 * @param Integer $entityTemplateElementId
	 * @return Mixed EntityValue if true else return null
	 */
	function checkIfEntityValueExists($entity, $entityTemplateElementId) {
		$this->clearFilter();
		$filter = CoreFactory::getFilter();
		$filter->addConditional($this->table, "EntityId", $entity->get("Id"), "=", "AND");
		$filter->addConditional($this->table, "EntityTemplateElementId", $entityTemplateElementId, "=", "AND");
		$this->setFilter($filter);
		if ($entityValue = $this->getNext()) {
			return $entityValue->get("Id");
		} else {
			return null;
		}
	}
	/**
	 * Get an array of entity values that belongs to a entity from one field and value
	 * @param String $fieldName Name of entity template element
	 * @param Mixed $value
	 * @return Array $entityArray array of entityValueObjects
	 */
	function retrieveEntityByField($fieldName, $value, $entityTemplateName) {
		if (!$entity = $this->entityItemByField($fieldName, $value, $entityTemplateName)) {
			return false;
		}
		$entityArray = array();
		
		$this->clearFilter();
		$this->retrieveForEntity($entity);
		while ($entityValue = $this->getNext()) {
			$entityArray[$entityValue->getRelationValue("EntityTemplateElementId", "Name")] = $this->getEntityValueObject($entityValue);
		}
		$entityArray["Id"] = $entity->get("Id"); 
		return $entityArray;
	}
	
	/**
	 * Return multiple entities with array of entity values
	 * @param String $entityType Type of entity e.g Entity::Image
	 * @param String $tag
	 * @return Array $entityArray 2D array of entities and their entity values
	 */
	function retrieveRelatedEntities($entityType, $tag) {
		
		$entityArray = array();
		$entityControl = BaseFactory::getEntityControl();
		$tagToDataControl = BaseFactory::getTagToDataControl();
		$filter = CoreFactory::getFilter();
		$filter->addConditional($tagToDataControl->table, "Type", $entityType);
		$filter->addConditional($tagToDataControl->table, "Tag", $tag);
		$tagToDataControl->setFilter($filter);

		while ($tagToData = $tagToDataControl->getNext()) {
			$entity = $entityControl->item($tagToData->get("DataId"));
			$this->clearFilter();
			$this->retrieveForEntity($entity);
			$dataEntity = array();
			while ($entityValue = $this->getNext()) {
				$dataEntity[$entityValue->getRelationValue("EntityTemplateElementId", "Name")] = $this->getEntityValueObject($entityValue);
			}
			$dataEntity["Id"] = $entity->get("Id");
			$entityArray[] = $dataEntity;
		}
		return $entityArray;
	}
	
	/**
	 * Get an entity based on an entity template name and a entity value value
	 * @param String $fieldName
	 * @param String $value
	 * @return Dataentity $entity
	 */
	function entityItemByField($fieldName, $value, $entityTemplateName, $caseInsentive = "=") {
		$this->clearFilter();
		$filter = CoreFactory::getFilter();
		$filter->addJoin("EntityValue", "EntityTemplateElementId", "EntityTemplateElement", "Id");
		$filter->addJoin("EntityTemplateElement", "EntityTemplateId", "EntityTemplate", "Id");
		$filter->addConditional("EntityTemplateElement", "Name", $fieldName, "=");
		$filter->addConditional($this->table, "Value", $value, $caseInsentive);
		$filter->addConditional("EntityTemplate", "Name", $entityTemplateName, "=");
		$filter->addLimit(1);
		
		$this->setFilter($filter);
		if (!$entityValue = $this->getNext()) {
			return false;
		}
		if ($entity = $entityValue->getRelation("EntityId")) {
			return $entity;
		} else {
			return false;
		}
	}
	
	/**
	 * Return instance of new Entity Value Object
	 * @param Dataentity $entityValue
	 * @return Object EntityValueObject
	 */
	function getEntityValueObject(&$entityValue) {
		return new EntityValueObject($entityValue);
	}
}

class EntityValueObject {
	var $entityValue; 
	
	/**
	 * Assign the entityValue to a variable when class is instantiated
	 * @param Dataentity $entityValue
	 */
	function EntityValueObject(&$entityValue) {
		$this->entityValue = $entityValue;
	}
	
	/**
	 * Return the instance of the entity value
	 * @return Dataentity $entityValue
	 */
	function getEntityValue() {
		return $this->entityValue;
	}
	
	/**
	 * Get the value for an entityValue
	 * @return Mixed value of entityValue
	 */
	function getData() {
		return $this->entityValue->get("Value");
	}
	
	/**
	 * Return formatted version of the value of the entiy value determined by the entity element type 
	 * @return Mixed Formatted value of entity values
	 */
	function getFormatted() {
		switch ($this->entityValue->getRelationValue("EntityTemplateElementId", "Type")) {
			case ENTITY_ELEMENT_TYPE_SINGLELINETEXT:
				return $this->getData();
				break;
			case ENTITY_ELEMENT_TYPE_NUMBER:
				$realFormatControl = CoreFactory::getRealFormatter();
				return $realFormatControl->format($this->getData());
				break;
			case ENTITY_ELEMENT_TYPE_CURRENCY;
				$currencyFormatControl = CoreFactory::getCurrencyFormatter();
				return $currencyFormatControl->format($this->getData());
				break;
			case ENTITY_ELEMENT_TYPE_PHONENUMBER:
				return $this->getData();
				break;
			case ENTITY_ELEMENT_TYPE_MULTILINETEXT:
			case ENTITY_ELEMENT_TYPE_LARGEMULTILINETEXT:
				$bodyTextFormatControl = CoreFactory::getBodyTextFormatter();
				return $bodyTextFormatControl->format($this->getData());
				break;
			case ENTITY_ELEMENT_TYPE_BOOLEAN:
			case ENTITY_ELEMENT_TYPE_BOOLEANTAG:
				$booleanFormatControl = CoreFactory::getBooleanFormatter();
				return $booleanFormatControl->format($this->getData());
				break;
			case ENTITY_ELEMENT_TYPE_DATE:
				$dateFormatControl = CoreFactory::getDateFieldFormatter();
				$value = $this->getData();
				if (mb_strlen($value) > 10) {
					$value = mb_substr($value, 0, 10);
				}
				return $dateFormatControl->format($value);
			case ENTITY_ELEMENT_TYPE_DATETIME:
				
				$dateTimeFormatControl = CoreFactory::getDateTimeFieldFormatter();
				$value = $this->getData();
				if (mb_strlen($value) > 19) {
					$value = mb_substr($value, 0, 19);
				}
				return $dateTimeFormatControl->format($value);
				break;
			case ENTITY_ELEMENT_TYPE_TIME:
				$timeFormatControl = CoreFactory::getTimeFieldFormatter();
				$value = $this->getData();
				return $timeFormatControl->format($value);
				break;
			case ENTITY_ELEMENT_TYPE_LISTBOX:
				return $this->getData();
				break;
			case ENTITY_ELEMENT_TYPE_IMAGE:
			case ENTITY_ELEMENT_TYPE_ATTACHMENT:
				$binaryControl = CoreFactory::getBinaryControl();
				if ($binary = $binaryControl->item($this->getData())) {
					return $binary->get("Filename");
				}
				break;
			case ENTITY_ELEMENT_TYPE_VIDEO:
				return $this->getData();
				break;
		}
	}
	
	/**
	 * Get the binary location, creating a cached version. Based on the params passed in
	 * Requires extra validation to make sure correct params are passed in.
	 * @param Mixed $params
	 * @return String Of binary path
	 */
	function getSourcePath($params = null) {
		switch ($this->entityValue->getRelationValue("EntityTemplateElementId", "Type")) {
			case ENTITY_ELEMENT_TYPE_IMAGE:
					$params["Width"] = (isset($params["Width"]) ? $params["Width"] : null);
					$params["Height"] = (isset($params["Height"]) ? $params["Height"] : null);
					$params["Crop"] = (isset($params["Crop"]) ? $params["Crop"] : false);
					$binaryControl = CoreFactory::getBinaryControl();
					if ($binary = $binaryControl->item($this->getData())) {
						$htmlControl = CoreFactory::getHtmlControl();
						if ($imagePath = $htmlControl->getImageBinaryLocation($binary, $params["Width"], $params["Height"], false, $params["Crop"])) {
							return $imagePath;
						}
						return false;
					}
				break;
			case ENTITY_ELEMENT_TYPE_ATTACHMENT:
				$binaryControl = CoreFactory::getBinaryControl();
				if ($binary = $binaryControl->item($this->getData())) {
					$htmlControl = CoreFactory::getHtmlControl();
					if ($imagePath = $htmlControl->getImageBinaryLocation($binary)) {
						return $imagePath;
					}
					return false;
				}
				break;
			case ENTITY_ELEMENT_TYPE_FLASH:
				$binaryControl = CoreFactory::getBinaryControl();
				if ($binary = $binaryControl->item($this->getData())) {
					$htmlControl = CoreFactory::getHtmlControl();
					if ($imagePath = $htmlControl->getImageBinaryLocation($binary)) {
						return $imagePath;
					}
					return false;
				}
				break;
		}
	}
	
	/**
	 * Generate the Html for data entity, based on the page type passed in.
	 * @param String $pageType e.g View or Control
	 * @return String $output Html for entity value
	 */
	function toHtml($pageType = "View") {
		$output = "";
		switch ($pageType) {
			// For view pages.
			case "View":
				if ($this->getData() != "") {
					$output = "<p><strong>" . $this->entityValue->getRelationValue("EntityTemplateElementId", "Name") . ":</strong>\n";
					switch ($this->entityValue->getRelationValue("EntityTemplateElementId", "Type")) {
						case ENTITY_ELEMENT_TYPE_IMAGE:
							$output .= "<img src=\"" . $this->getSourcePath(array("Width" => 100)) . "\" alt=\"" . $this->getFormatted() . "\" />";
							break;
						case ENTITY_ELEMENT_TYPE_ATTACHMENT:
							$binaryControl = CoreFactory::getBinaryControl();
							$htmlControl = CoreFactory::getHtmlControl();
							if ($binary = $binaryControl->item($this->getData())) {
								$output .= $output .= $htmlControl->showAttachmentLinkWithIcon($binary);
							}
							break;	
						case ENTITY_ELEMENT_TYPE_FLASH:
							$binaryControl = CoreFactory::getBinaryControl();
							$htmlControl = CoreFactory::getHtmlControl();
							if ($binary = $binaryControl->item($this->getData())) {
								$output .= $htmlControl->showFlashMovie($binary, 100, 100);
							}
							break;
						default:
							$output .= $this->getFormatted();
							break;
					}
					$output .= "\n</p>\n";
				}
				break;
			//For control pages.
			case "Control":
				$entityTemplateElement = $this->entityValue->getRelation("EntityTemplateElementId");
				$entityTemplate = $entityTemplateElement->getRelation("EntityTemplateId");
				$output = "";
				$entityValue = $this->getData();
				if ($entityValue == "") {
					$entityValue = $entityTemplateElement->get("DefaultValue");
				}
				
				//Dont generate
				if ($entityTemplateElement->get("ShowField") == "f") {
					$defaultValue = $entityTemplateElement->get("DefaultValue");
					if ($defaultValue == "now()") {
						$application = CoreFactory::getApplication();
						$defaultValue = $application->getCurrentDate();
					} 
					$output .= "<input type=\"hidden\" name=\"" . $entityTemplate->get("Id") . "[" . $entityTemplateElement->get("Id") . "]" . "\" value=\"{$defaultValue}\" />";
					break;
				}
				switch ($this->entityValue->getRelationValue("EntityTemplateElementId", "Type")) {
					case ENTITY_ELEMENT_TYPE_SINGLELINETEXT:
					case ENTITY_ELEMENT_TYPE_NUMBER:
					case ENTITY_ELEMENT_TYPE_CURRENCY;
					case ENTITY_ELEMENT_TYPE_PHONENUMBER:
						$output .= "<label>\n<strong class=\"required\">" . $entityTemplateElement->get("Name") . ":";
						if ($entityTemplateElement->getFormatted("Required") == "Yes") {
							$output .= "<span> *</span>";
						}
						$output .= "</strong>\n".
							"<input class=\"textbox\" type=\"text\" name=\"" . $entityTemplate->get("Id") . "[" . $entityTemplateElement->get("Id") . "]" . "\" value=\"" . $entityValue . "\" />\n".
							"</label><br />\n";
						break;
					case ENTITY_ELEMENT_TYPE_MULTILINETEXT:
						$output .= "<label>\n<strong class=\"required\">" . $entityTemplateElement->get("Name") . ":";
						if ($entityTemplateElement->getFormatted("Required") == "Yes") {
							$output .= "<span> *</span><br /><br />";
						}
						$htmlControl = CoreFactory::getHtmlControl();
						$output .= "<br /><br />";
						$output .= $htmlControl->createFormattingToolbar("Element-Id-" . $entityTemplateElement->get("Id"), true);
						$output .= "</strong>\n".
							"<textarea class=\"textbox\" name=\"" . $entityTemplate->get("Id") . "[" . $entityTemplateElement->get("Id") . "]\" id=\"Element-Id-" . $entityTemplateElement->get("Id") . "\"\">" . $entityValue . "</textarea>\n".
							"</label><br />\n";
						break;
					case ENTITY_ELEMENT_TYPE_LARGEMULTILINETEXT:
						$output .= "<label>\n<strong class=\"required\">" . $entityTemplateElement->get("Name") . ":";
						if ($entityTemplateElement->getFormatted("Required") == "Yes") {
							$output .= "<span> *</span><br /><br />";
						}
						$htmlControl = CoreFactory::getHtmlControl();
						$output .= "<br /><br />";
						$output .= $htmlControl->createFormattingToolbar("Element-Id-" . $entityTemplateElement->get("Id"), true);
						$output .= "</strong>\n".
							"<textarea class=\"textbox-large\" name=\"" . $entityTemplate->get("Id") . "[" . $entityTemplateElement->get("Id") . "]\" id=\"Element-Id-" . $entityTemplateElement->get("Id") . "\"\">" . $entityValue . "</textarea>\n".
							"</label><br />\n";
						break;
					case ENTITY_ELEMENT_TYPE_BOOLEAN:
					case ENTITY_ELEMENT_TYPE_BOOLEANTAG:
						$checked = ($entityValue == "t") ? "checked=\"checked\"" : "";
						$output .= "<label><strong class=\"required\">" . $entityTemplateElement->get("Name") . ":";
						if ($entityTemplateElement->getFormatted("Required") == "Yes") {
							$output .= "<span> *</span>";
						}
						$output .= "</strong>\n".
							"<input type=\"hidden\" name=\"" . $entityTemplate->get("Id") . "[" . $entityTemplateElement->get("Id") . "]" . "\" value=\"f\" />\n".
							"<input type=\"checkbox\" value=\"t\" class=\"checkbox\" " . $checked . " name=\"" . $entityTemplate->get("Id") . "[" . $entityTemplateElement->get("Id") . "]" . "\" />\n".
							"</label><br />\n";
						break;
					case ENTITY_ELEMENT_TYPE_TIME:
					case ENTITY_ELEMENT_TYPE_DATE:
						if ($entityValue == "now()") {
							$application = CoreFactory::getApplication();
							$entityValue = $application->getCurrentDate();
						}
						$entityValue = substr($entityValue, 0, 10);
						$output .= "<span class=\"label\">\n<strong class=\"required\">" . $entityTemplateElement->get("Name") . ":";
						if ($entityTemplateElement->getFormatted("Required") == "Yes") {
							$output .= "<span> *</span>";
						}
						$output .= "</strong>\n";							
						$formControlName = $entityTemplate->get("Id") . "[" . $entityTemplateElement->get("Id") . "]";
						$formControlId = "entity-control-" . str_replace(array("[", "]"), "-", $formControlName); 
						$output .= <<<HTML
<span id="{$formControlId}"></span>
<script type="text/javascript">
// <![CDATA[
new RumbleUI.Form.DateControl("{$formControlId}", "{$formControlName}" , "{$entityValue}", { allowNull: true });
// ]]>
</script>
</span><br />
HTML;
						
						break;
					case ENTITY_ELEMENT_TYPE_DATETIME:
						if ($entityValue == "now()") {
							$application = CoreFactory::getApplication();
							$entityValue = $application->getCurrentDateTime();
						}
						$entityValue = substr($entityValue, 0, 19);
						$application = CoreFactory::getApplication();
						//Need to pass in nullvalue as empty string otherwise breaks datecontroller
						
						$entityValue = $application->localiseDateTime($entityValue, "");
						$output .= "<span class=\"label\">\n<strong class=\"required\">" . $entityTemplateElement->get("Name") . ":";
						if ($entityTemplateElement->getFormatted("Required") == "Yes") {
							$output .= "<span> *</span>";
						}
						$output .= "</strong>\n";
						$formControlName = $entityTemplate->get("Id") . "[" . $entityTemplateElement->get("Id") . "]";
						$formControlId = "entity-control-" . str_replace(array("[", "]"), "-", $formControlName); 
						$output .= <<<HTML
<span id="{$formControlId}"></span>
<script type="text/javascript">						
// <![CDATA[	
new RumbleUI.Form.DateTimeControl("{$formControlId}", "{$formControlName}" , "{$entityValue}", { allowNull: true });
// ]]>
</script>
</span><br />
HTML;
						
						break;
					case ENTITY_ELEMENT_TYPE_LISTBOX:
						$output .= "<label>\n<strong class=\"required\">" . $entityTemplateElement->get("Name") . ":";
						if ($entityTemplateElement->getFormatted("Required") == "Yes") {
							$output .= "<span> *</span>";
						}
						$output .= "</strong>\n".
							"<select class=\"listbox\" name=\"" . $entityTemplate->get("Id") . "[" . $entityTemplateElement->get("Id") . "]" . "\">\n".
							"<option value=\"\">-- Please select --</option>\n";
						$options = str_replace("\n", "", $entityTemplateElement->get("Options"));
						$listItem = explode(",", $options);
						foreach ($listItem as $item) {
							$selected = (strcmp($item, $entityValue) == 0) ? "selected=\"selected\"" : "";
							$output .= "<option value=\"" . $item . "\"" . $selected .">" . $item . "</option>\n";
						}
						$output .="</select>\n".
							"</label><br />\n";
						break;
					case ENTITY_ELEMENT_TYPE_IMAGE:
						$output .="<label>\n<strong class=\"required\">" . $entityTemplateElement->get("Name") . ":";
						if ($entityTemplateElement->getFormatted("Required") == "Yes") {
							$output .= "<span> *</span>";
						}
						$output .= "</strong>\n";
						//Create input box for file upload
						$output .= "<input class=\"textbox\" name=\"" . $entityTemplate->get("Id") . "[Binary" . $entityTemplateElement->get("Id") . "]" . "\" type=\"file\" />\n";
						//Add hidden field for existing values.
						$output .= "<input name=\"" . $entityTemplate->get("Id") . "[" . $entityTemplateElement->get("Id") . "]" . "\" type=\"hidden\"	value=\"{$entityValue}\" />\n" . 
							"</label><br />\n";
						//Add Input for binary removal.
						$output .= "<label>\n<strong>&nbsp;</strong>\n<input name=\"" . $entityTemplate->get("Id") . "[Remove-" . $entityTemplateElement->get("Id") . "]" . "\" type=\"checkbox\" value=\"1\" /> Remove Image\n</label><br />\n";
						//Display the image if something has been uploaded
						$binaryControl = CoreFactory::getBinaryControl();
						if ($binary = $binaryControl->item($entityValue)) {
							$htmlControl = CoreFactory::getHtmlControl();
							$output .= "<label>\n<strong>Current Image:</strong>\n";
							$output .= $htmlControl->showImageBinary($binary, "Image", 100, 100, true);
							$output .= "</label><br />";
						}
						break;
					case ENTITY_ELEMENT_TYPE_ATTACHMENT:
						$output .= "<label>\n<strong class=\"required\">" . $entityTemplateElement->get("Name") . ":";
						if ($entityTemplateElement->getFormatted("Required") == "Yes") {
							$output .= "<span> *</span>";
						}
						$output .= "</strong>\n";
						//Create input box for file upload
						$output .= "<input class=\"textbox\" name=\"" . $entityTemplate->get("Id") . "[Binary" . $entityTemplateElement->get("Id") . "]" . "\" type=\"file\" />\n";
						//Add hidden field for existing values.
						$output .= "<input name=\"" . $entityTemplate->get("Id") . "[" . $entityTemplateElement->get("Id") . "]" . "\" type=\"hidden\"	value=\"{$entityValue}\" />\n" . 
							"</label><br />\n";
						//Add Input for binary removal.
						$output .= "<label>\n<strong>&nbsp;</strong>\n<input name=\"" . $entityTemplate->get("Id") . "[Remove-" . $entityTemplateElement->get("Id") . "]" . "\" type=\"checkbox\" value=\"1\" /> Remove Attachment\n</label><br />\n";
						//Display the image if something has been uploaded
						$binaryControl = CoreFactory::getBinaryControl();
						if ($binary = $binaryControl->item($entityValue)) {
							$htmlControl = CoreFactory::getHtmlControl();
							$output .= "<label>\n<strong>Current Attachment:</strong>\n";
							$output .= $htmlControl->showAttachmentLinkWithIcon($binary);
							$output .= "</label><br />";
						}
						break;
					case ENTITY_ELEMENT_TYPE_FLASH:
						$output .= "<label>\n<strong class=\"required\">" . $entityTemplateElement->get("Name") . ":";
						if ($entityTemplateElement->getFormatted("Required") == "Yes") {
							$output .= "<span> *</span>";
						}
						$output .= "</strong>\n";
						//Create input box for file upload
						$output .= "<input class=\"textbox\" name=\"" . $entityTemplate->get("Id") . "[Binary" . $entityTemplateElement->get("Id") . "]" . "\" type=\"file\" />\n";
						//Add hidden field for existing values.
						$output .= "<input name=\"" . $entityTemplate->get("Id") . "[" . $entityTemplateElement->get("Id") . "]" . "\" type=\"hidden\"	value=\"{$entityValue}\" />\n" . 
							"</label><br />\n";
						//Add Input for binary removal.
						$output .= "<label>\n<strong>&nbsp;</strong>\n<input name=\"" . $entityTemplate->get("Id") . "[Remove-" . $entityTemplateElement->get("Id") . "]" . "\" type=\"checkbox\" value=\"1\" /> Remove Flash file\n</label><br />\n";
						//Display the image if something has been uploaded
						$binaryControl = CoreFactory::getBinaryControl();
						if ($binary = $binaryControl->item($entityValue)) {
							$htmlControl = CoreFactory::getHtmlControl();
							$output .= "<label>\n<strong>Current Flash File:</strong>\n";
							$output .= $htmlControl->showFlashMovie($binary, 100, 100);
							$output .= "</label><br />";
						}
						break;
				}
				break;
		}
		return $output;
	}
}