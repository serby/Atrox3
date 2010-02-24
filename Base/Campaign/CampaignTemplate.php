<?php
/**
 * @package Base
 * @subpackage Campaign
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 */
 
/**
 * Include Data.php so that DataControl can be extended.
 */
require_once("Atrox/Core/Data/Data.php");

	
define("CP_TYPE_TEMPLATE", 1);	
define("CP_TYPE_TAG", 2);	

/**
 * @author Robert Arnold (Clock Ltd) {@link mailto:robert.arnold@clock.co.uk robert.arnold@clock.co.uk }
 * @package Base
 * @subpackage Campaign
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 */
class CampaignTemplateControl extends DataControl {

	var $table = "CampaignTemplate";
	var $key = "Id";
	var $sequence = "CampaignTemplate_Id_seq";
	var $defaultOrder = "Id";
	var $searchFields = array("Name", "Summary");
	
	var $type = array(
		CP_TYPE_TEMPLATE => "Template",
		CP_TYPE_TAG => "Tag"
	);

	function init() {

		$this->fieldMeta["Id"] = new FieldMeta(
			"Id", "", FM_TYPE_INTEGER,null, FM_STORE_NEVER, false);

		$this->fieldMeta["Name"] = new FieldMeta(
			"Name", "", FM_TYPE_STRING, 50, FM_STORE_ALWAYS, false);

		$this->fieldMeta["Summary"] = new FieldMeta(
			"Summary", "", FM_TYPE_STRING, 200, FM_STORE_ALWAYS, true);

		$this->fieldMeta["Summary"]->setFormatter(
			CoreFactory::getBodyTextFormatter());

		$this->fieldMeta["DateCreated"] = new FieldMeta(
			"DateCreated", "", FM_TYPE_DATE, null, FM_STORE_NEVER, false);

		$this->fieldMeta["DateCreated"]->setFormatter(
			CoreFactory::getDateTimeFieldFormatter());
			
		$this->fieldMeta["DateModified"] = new FieldMeta(
			"DateModified", "", FM_TYPE_DATE, null, FM_STORE_NEVER, false);

		$this->fieldMeta["DateModified"]->setFormatter(
			CoreFactory::getDateTimeFieldFormatter());

		$this->fieldMeta["Body"] = new FieldMeta(
			"Body", "", FM_TYPE_STRING, null, FM_STORE_ALWAYS, false);

		$this->fieldMeta["Body"]->setFormatter(
			CoreFactory::getBodyTextFormatter());
			
		$this->fieldMeta["ParseNewLines"] = new FieldMeta(
			"ParseNewLines", "", FM_TYPE_BOOLEAN, null, FM_STORE_NEVER, true);				

		$this->fieldMeta["Type"] = new FieldMeta(
			"Type", CP_TYPE_TEMPLATE, FM_TYPE_INTEGER, null, FM_STORE_ALWAYS, false);
			
		$this->fieldMeta["Type"]->setFormatter(
			CoreFactory::getArrayRelationFormatter($this->type, CP_TYPE_TEMPLATE));
	}		
	
	function getTypeArray(){
		return $this->type;
	}
	
	function retrieveForType($type) {
		$filter = $this->getFilter();
		if ($filter == null) {
			$filter = CoreFactory::getFilter();
		}
		$filter->addConditional($this->table, "Type", $type);
		$this->setFilter($filter);
		$this->retrieveAll();
	}		
}