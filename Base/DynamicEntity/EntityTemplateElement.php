<?php
/**
 * @package Base
 * @subpackage DynamicEntity
 * @copyright Clock Limited 2007
 * @version 3.0 - $Revision$ - $Date$
 */

/**
 * Include Data.php so that DataControl can be extended.
 * Include DataEntity.php so that DataEntity can be extended
 */
require_once("Atrox/3.0/Core/Data/Data.php");
require_once("Atrox/3.0/Core/Data/DataEntity.php");

define("ENTITY_ELEMENT_TYPE_NUMBER", 1);
define("ENTITY_ELEMENT_TYPE_CURRENCY", 2);
define("ENTITY_ELEMENT_TYPE_SINGLELINETEXT", 3);
define("ENTITY_ELEMENT_TYPE_MULTILINETEXT", 4);
define("ENTITY_ELEMENT_TYPE_PHONENUMBER", 5);
define("ENTITY_ELEMENT_TYPE_BOOLEAN", 6);
define("ENTITY_ELEMENT_TYPE_DATE", 7);
define("ENTITY_ELEMENT_TYPE_LISTBOX", 8);
define("ENTITY_ELEMENT_TYPE_IMAGE", 9);
define("ENTITY_ELEMENT_TYPE_ATTACHMENT", 10);
define("ENTITY_ELEMENT_TYPE_VIDEO", 11);
define("ENTITY_ELEMENT_TYPE_FLASH", 12);
define("ENTITY_ELEMENT_TYPE_EMAIL", 13);
define("ENTITY_ELEMENT_TYPE_DATETIME", 14);
define("ENTITY_ELEMENT_TYPE_TIME", 15);
define("ENTITY_ELEMENT_TYPE_BOOLEANTAG", 16);
define("ENTITY_ELEMENT_TYPE_LARGEMULTILINETEXT", 17);

/**
 *
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2007
 * @version 3.0 - $Revision$ - $Date$
 * @package Base
 * @subpackage DynamicEntity
 */
class EntityTemplateElementControl extends DataControl {
	var $table = "EntityTemplateElement";
	var $key = "Id";
	var $sequence = "EntityTemplateElement_Id_seq";
	var $defaultOrder = "Name";
	var $searchFields = array("Name");

	var $elementType = array(
		ENTITY_ELEMENT_TYPE_NUMBER => "Number",
		ENTITY_ELEMENT_TYPE_CURRENCY => "Currency",
		ENTITY_ELEMENT_TYPE_SINGLELINETEXT => "Single Line Text",
		ENTITY_ELEMENT_TYPE_MULTILINETEXT => "Multi line Text",
		ENTITY_ELEMENT_TYPE_LARGEMULTILINETEXT => "Large Multi Line Text",
		ENTITY_ELEMENT_TYPE_EMAIL => "Email Address",
		ENTITY_ELEMENT_TYPE_PHONENUMBER => "Phone Number",
		ENTITY_ELEMENT_TYPE_BOOLEAN => "Boolean",
		ENTITY_ELEMENT_TYPE_BOOLEANTAG => "Boolean Tag",
		ENTITY_ELEMENT_TYPE_TIME => "Time",
		ENTITY_ELEMENT_TYPE_DATE => "Date",
		ENTITY_ELEMENT_TYPE_DATETIME => "Date Time",
		ENTITY_ELEMENT_TYPE_LISTBOX => "List box",
		ENTITY_ELEMENT_TYPE_IMAGE => "Image",
		ENTITY_ELEMENT_TYPE_ATTACHMENT => "Attachment",
		ENTITY_ELEMENT_TYPE_VIDEO => "Video",
		ENTITY_ELEMENT_TYPE_FLASH => "Flash"
	);

	function init() {

		$this->fieldMeta["Id"] = new  FieldMeta(
		"Id", "", FM_TYPE_INTEGER, null, FM_STORE_NEVER, false);

		$this->fieldMeta["EntityTemplateId"] = new  FieldMeta(
		"EntityTemplateId", "", FM_TYPE_RELATION, null, FM_STORE_ALWAYS, false);

		$this->fieldMeta["EntityTemplateId"]->setRelationControl(BaseFactory::getEntityTemplateControl());

		$this->fieldMeta["Name"] = new FieldMeta(
		"Name", "", FM_TYPE_STRING, null, FM_STORE_ALWAYS, false);

		$this->fieldMeta["Type"] = new  FieldMeta(
		"Type", "", FM_TYPE_INTEGER, null, FM_STORE_ALWAYS, false);

		$this->fieldMeta["Type"]->setFormatter(
		CoreFactory::getArrayRelationFormatter($this->elementType, ENTITY_ELEMENT_TYPE_SINGLELINETEXT));
			
		$this->fieldMeta["DefaultValue"] = new  FieldMeta(
		"Default Value", "", FM_TYPE_STRING, null, FM_STORE_ALWAYS, true);

		$this->fieldMeta["Required"] = new  FieldMeta(
		"Required", "", FM_TYPE_BOOLEAN, null, FM_STORE_ALWAYS, true);

		$this->fieldMeta["ValidationMessage"] = new  FieldMeta(
		"Validation Message", "", FM_TYPE_STRING, null, FM_STORE_ALWAYS, true);

		$this->fieldMeta["Order"] = new  FieldMeta(
		"Order", "", FM_TYPE_INTEGER, null, FM_STORE_ADD, true);

		$this->fieldMeta["Options"] = new FieldMeta(
		"Options", "", FM_TYPE_STRING, null, FM_STORE_ALWAYS, true);

		$this->fieldMeta["ShowField"] = new FieldMeta(
		"Show Field", "", FM_TYPE_BOOLEAN, "t", FM_STORE_ALWAYS, true);
		
		$this->fieldMeta["Group"] = new FieldMeta(
		"Group", "", FM_TYPE_STRING, "", FM_STORE_ALWAYS, true);
		
	}

	/**
	 * Retrieve all entity template elements that part of the entity template passed in.
	 * @param Object $entityTemplate
	 * @param String $order
	 */	
	function retrieveForEntityTemplate(&$entityTemplate, $order = "Order", $desc = false) {
		$filter = CoreFactory::getFilter();
		$filter->addConditional($this->table, "EntityTemplateId", $entityTemplate->get("Id"));
		if ($order) {
			$filter->addOrder($order, $desc);
		}
		$this->setFilter($filter);
		$this->retrieveAll();
	}

	/**
	 * Return the next order number for a template
	 * @param Object $entityTemplate
	 * @param String $order
	 * @return Integer Next order sequence
	 */	
	function getNextOrder(&$entityTemplate) {
			
		$filter = CoreFactory::getFilter();
		$filter->addConditional($this->table, "EntityTemplateId", $entityTemplate->get("Id"));
		$filter->addConditional($this->table, "Order", 0, ">");		
		$filter->addOrder("Order", true);
			
		$this->setFilter($filter);
		$this->retrieveAll();
			
		if ($entityTemplateElement = $this->getNext()) {
			return $entityTemplateElement->get("Order") + 1;
		} else {
			return 1;
		}
	}

	function beforeInsert(&$dataEntity) {
		$dataEntity->set("Order", $this->getNextOrder($dataEntity->getRelation("EntityTemplateId")));
	}

	/**
	 * Retrieve entity template element based on template and order number.
	 * @param Object $entityTemplate
	 * @param Integer $order
	 * @return Object $entityTemplateElement
	 */	
	function itemByOrder(&$entityTemplate, $order) {
		$filter = $this->getFilter();
		$filter->addConditional($this->table, "EntityTemplateId", $entityTemplate->get("Id"));
		$filter->addConditional($this->table, "Order", $order);
		$this->setFilter($filter);
		if ($entityTemplateElement = $this->getNext()) {
			return $entityTemplateElement;
		} else {
			return false;
		}
	}
	
	/**
	 * Reorder Elements using list of Ids
	 * @param Array $entityTemplateElementIds
	 */	
	function reorderEntityTemplateElements($entityTemplateElementIds) {
		$i = 1;
		foreach ($entityTemplateElementIds as $entityTemplateElementId) {
			if ($entityTemplateElement = $this->item($entityTemplateElementId)) {
				$this->updateField($entityTemplateElement, "Order", $i++);
			}
		}
	}

	/**
	 * Validate the Form data, to ensure required fields have been met and to cleanse the form data.
	 * @param Dataentity $entityTemplate
	 * @param Array $postData
	 * @return Array $errors
	 */
	function validateFormSubmission($entityTemplate, $postData) {
		$errors = array();
		foreach($postData as $entityTemplateElementId => $value) {
			if ($entityTemplateElement = $this->item($entityTemplateElementId)) {
				if ($error = $this->validateEntityValue($entityTemplateElement, $value)) {
					$errors[$entityTemplateElement->get("Id")] = $error;
				}
			}
		}
		return $errors;
	}

	/**
	 * Validation and generate error message if required.
	 * @param Dataentity $entityTemplateElement
	 * @param Mixed $value
	 * @return String Error message to be shown to user.
	 */
	function validateEntityValue($entityTemplateElement, $value) {
		if (($entityTemplateElement->get("Required") == "t") && ($value == null)) {
			if($entityTemplateElement->get("ValidationMessage") == ""){
				return "'" . $entityTemplateElement->get("Name") . "' must be entered";
			} else {
				return $entityTemplateElement->get("ValidationMessage");
			}
		}
		// Only needed if we add extra validation such as an email type
		switch ($entityTemplateElement->get("Type")) {
			case ENTITY_ELEMENT_TYPE_SINGLELINETEXT:
			case ENTITY_ELEMENT_TYPE_NUMBER:
			case ENTITY_ELEMENT_TYPE_CURRENCY;
			case ENTITY_ELEMENT_TYPE_PHONENUMBER:
			case ENTITY_ELEMENT_TYPE_MULTILINETEXT:
			case ENTITY_ELEMENT_TYPE_LARGEMULTILINETEXT:
			case ENTITY_ELEMENT_TYPE_BOOLEAN:
			case ENTITY_ELEMENT_TYPE_BOOLEANTAG:
			case ENTITY_ELEMENT_TYPE_DATE:
			case ENTITY_ELEMENT_TYPE_TIME:
			case ENTITY_ELEMENT_TYPE_DATETIME:
			case ENTITY_ELEMENT_TYPE_EMAIL:
			case ENTITY_ELEMENT_TYPE_LISTBOX:
			case ENTITY_ELEMENT_TYPE_IMAGE:
			case ENTITY_ELEMENT_TYPE_ATTACHMENT:
			case ENTITY_ELEMENT_TYPE_VIDEO:
		}
	}
}
