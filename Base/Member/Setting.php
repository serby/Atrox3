<?php
/**
 * @package Base
 * @subpackage Member
 * @copyright Clock Limited 2007
 * @version 3.0 - $Revision$ - $Date$
 */

/**
 * Include Data.php so that DataControl can be extended.
 */
require_once("Atrox/3.0/Core/Data/Data.php");


/**
 *
 * @author Adam Forster (Clock Ltd) {@link mailto:adam.forster@clock.co.uk adam.forster@clock.co.uk }
 * @copyright Clock Limited 2007
 * @version 3.0 - $Revision$ - $Date$
 * @package Base
 * @subpackage Member
 */
class SettingControl extends DataControl {

	var $table = "Setting";
	var $key = "Id";
	var $sequence = "Setting_Id_seq";
	var $defaultOrder = "Name";
	var $searchFields = array(
		"Name",
		"Value");

	function init() {

		$this->fieldMeta["Id"] = new FieldMeta(
			"Id", "", FM_TYPE_INTEGER, null, FM_STORE_NEVER, false);

		$this->fieldMeta["MemberId"] = new FieldMeta(
			"Member Id", "", FM_TYPE_RELATION, null, FM_STORE_ALWAYS, true);

		$this->fieldMeta["MemberId"]->setRelationControl(BaseFactory::getMemberControl());

		$this->fieldMeta["Name"] = new FieldMeta(
			"Name", "", FM_TYPE_STRING, 255, FM_STORE_ALWAYS, false);

		$this->fieldMeta["Value"] = new FieldMeta(
			"Value", "", FM_TYPE_STRING, 1000, FM_STORE_ALWAYS, false);
	}

	function retrieveForMember(&$member) {
		$filter = CoreFactory::getFilter();
		$filter->addConditional($this->table, "MemberId", $member->get("Id"));
		$this->setFilter($filter);
		$this->retrieveAll();
	}
	
	function getSettingForMember($member, $setting) {
		$filter = CoreFactory::getFilter();
		$filter->addConditional($this->table, "MemberId", $member->get("Id"));
		$filter->addConditional($this->table, "Name", $setting);
		$this->setFilter($filter);
		$this->retrieveAll();
	}
}