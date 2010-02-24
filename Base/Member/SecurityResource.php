<?php
/**
 * @package Base
 * @subpackage Member
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 */

/**
 * Include Data.php so that DataControl can be extended.
 */
require_once("Atrox/Core/Data/Data.php");


/**
 *
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Base
 * @subpackage Member
 */
class SecurityResourceControl extends DataControl {
	var $table = "SecurityResource";
	var $key = "Id";
	var $sequence = "SecurityResource_Id_seq";
	var $defaultOrder = "Name";
	var $searchFields = array("Name");

	function init() {
		$this->fieldMeta["Id"] = new FieldMeta(
			"Id", "", FM_TYPE_INTEGER, null, FM_STORE_NEVER, false);

		$this->fieldMeta["Name"] = new FieldMeta(
			"Name", "", FM_TYPE_STRING, 100, FM_STORE_ALWAYS, false, FM_OPTIONS_UNIQUE);

		$this->fieldMeta["Description"] = new FieldMeta(
			"Description", "", FM_TYPE_STRING, 500, FM_STORE_ALWAYS, false);
	}
	
	function afterInsert(&$dataEntity) {
		$noteControl = BaseFactory::getNoteControl();	
		$noteControl->addNote("Security Resource: Added (Id:" . $dataEntity->get("Id") . ")", "SecurityResource", $dataEntity->get("Id"), 1, "member/securityresource");					
	}

	function afterUpdate(&$dataEntity) {
		$noteControl = BaseFactory::getNoteControl();	
		$noteControl->addNote("Security Resource: Edited (Id:" . $dataEntity->get("Id") . ")", "SecurityResource", $dataEntity->get("Id"), 2, "member/securityresource");					
	}
	
	function afterDelete(&$dataEntity) {
		$noteControl = BaseFactory::getNoteControl();	
		$noteControl->addNote("Security Resource: Deleted (Id:" . $dataEntity->get("Id") . ")", "SecurityResource", $dataEntity->get("Id"), 3, "member/securityresource");					
	}
}