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
class EntityTemplateControl extends DataControl {
	var $table = "EntityTemplate";
	var $key = "Id";
	var $sequence = "EntityTemplate_Id_seq";
	var $defaultOrder = "Name";
	var $searchFields = array("Name");

	function init() {

		$this->fieldMeta["Id"] = new  FieldMeta(
			"Id", "", FM_TYPE_INTEGER, null, FM_STORE_NEVER, false);

		$this->fieldMeta["Name"] = new FieldMeta(
			"Name", "", FM_TYPE_STRING, 100, FM_STORE_ALWAYS, false, FM_OPTIONS_UNIQUE);
			
		$this->fieldMeta["TitleFormat"] = new FieldMeta(
			"Title Format", "{}", FM_TYPE_STRING, 500, FM_STORE_ALWAYS, false);
			
		$this->fieldMeta["LinkingTagPattern"] = new FieldMeta(
			"Linking Tag Pattern", "", FM_TYPE_STRING, 500, FM_STORE_ALWAYS, false);	
			
		$this->fieldMeta["AllowedRelatedItems"] = new FieldMeta(
			"Allowed Related Items", "", FM_TYPE_STRING, null, FM_STORE_ALWAYS, true);
		
		$this->fieldMeta["ColumnHeaders"] = new FieldMeta(
			"Column headers", "", FM_TYPE_STRING, null, FM_STORE_ALWAYS, true);
			
		$this->fieldMeta["DateCreated"] = new FieldMeta(
			"DateCreated", "", FM_TYPE_DATE, "", FM_STORE_NEVER, false);
			
		$this->fieldMeta["DateCreated"]->setFormatter(CoreFactory::getDateTimeFieldFormatter());				

		$this->fieldMeta["MemberId"] = new FieldMeta(
			"MemberId", "", FM_TYPE_RELATION, "", FM_STORE_ALWAYS, false);	

		$this->fieldMeta["MemberId"]->setRelationControl(BaseFactory::getMemberControl());
	}
	/**
	 * Copy a an existing template, making new instance of the template and the elements which belong to the template
	 * @param Array $templateIds List of template Ids to copy.
	 * @return void
	 **/
	function duplicate($templateIds) {
		$application = CoreFactory::getApplication();
		foreach ($templateIds as $id) {
			if ($entityTemplate = $this->item($id)) {
				$newEntityTemplate = $entityTemplate;
				$newEntityTemplate->set("Id", null);
				$newEntityTemplate->set("Name", "Copy of " . $entityTemplate->get("Name"));
				$newEntityTemplate->set("MemberId", $application->securityControl->getMemberId());
				if ($newEntityTemplate->save()) {
					$entityTemplateElementControl = BaseFactory::getEntityTemplateElementControl();
					$entityTemplateElementControl->retrieveForEntityTemplate($entityTemplate);
					while ($entityTemplateElement = $entityTemplateElementControl->getNext()) {
						$newEntityTemplateElement = $entityTemplateElementControl->makeNew();
						$newEntityTemplateElement->set("Name", $entityTemplateElement->get("Name"));
						$newEntityTemplateElement->set("Type", $entityTemplateElement->get("Type"));
						$newEntityTemplateElement->set("DefaultValue", $entityTemplateElement->get("DefaultValue"));
						$newEntityTemplateElement->set("Required", $entityTemplateElement->get("Required"));
						$newEntityTemplateElement->set("ValidationMessage", $entityTemplateElement->get("ValidationMessage"));
						$newEntityTemplateElement->set("Order", $entityTemplateElement->get("Order"));
						$newEntityTemplateElement->set("Options", $entityTemplateElement->get("Options"));
						$newEntityTemplateElement->set("ShowField", $entityTemplateElement->get("ShowField"));
						$newEntityTemplateElement->set("EntityTemplateId", $newEntityTemplate->get("Id"));
						$entityTemplateElementControl->quickAdd($newEntityTemplateElement);
					}
				}						
			}
		}
	}
}