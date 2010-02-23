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
class SecurityGroupControl extends DataControl {
	var $table = "SecurityGroup";
	var $key = "Id";
	var $sequence = "SecurityGroup_Id_seq";
	var $defaultOrder = "Name";
	var $searchFields = array("Name");

	function init() {

		$this->fieldMeta["Id"] = new FieldMeta(
			"Id", "", FM_TYPE_INTEGER, null, FM_STORE_NEVER, false);

		$this->fieldMeta["Name"] = new FieldMeta(
			"Name", "", FM_TYPE_STRING, 100, FM_STORE_ALWAYS, false);

		$this->fieldMeta["Description"] = new FieldMeta(
			"Description", "", FM_TYPE_STRING, 500, FM_STORE_ALWAYS, false);

		$this->fieldMeta["DateCreated"] = new FieldMeta(
			"DateCreated", "", FM_TYPE_DATE, null, FM_STORE_NEVER, false);

		$this->fieldMeta["DateCreated"]->setFormatter(CoreFactory::getDateTimeFieldFormatter());

	}

	function retrieveForMember(&$member) {
		$filter = $this->getFilter();
		if ($filter == null) {
			$filter = CoreFactory::getFilter();
		}
		$filter->addJoin($this->table, "Id", "MemberToSecurityGroup", "SecurityGroupId");
		$filter->addConditional("MemberToSecurityGroup", "MemberId", $member->get("Id"));
		$this->setFilter($filter);
		return $this->retrieveAll();
	}


	function addResource(&$securityGroup, $securityResourceId) {
		$securityResourceControl = BaseFactory::getSecurityResourceControl();

		if (!is_array($securityResourceId)) {
			$securityResourceId = array($securityResourceId);
		}

		$securityGroupToSecurityResourceControl = BaseFactory::getSecurityGroupToSecurityResourceControl();
		foreach($securityResourceId as $sid) {

			if (!$securityResource = $securityResourceControl->item($sid)) {
				return false;
			}
			$securityGroupToSecurityResource = $securityGroupToSecurityResourceControl->makeNew();
			$securityGroupToSecurityResource->set("SecurityGroupId", $securityGroup->get("Id"));
			$securityGroupToSecurityResource->set("SecurityResourceName", $securityResource->get("Name"));
			$securityGroupToSecurityResource->save();
		}
	}

	function removeResource(&$securityGroupToSecurityResourceId) {

		if (!is_array($securityGroupToSecurityResourceId)) {
			$securityGroupToSecurityResourceId = array($securityGroupToSecurityResourceId);
		}

		$securityGroupToSecurityResourceControl = BaseFactory::getSecurityGroupToSecurityResourceControl();
		foreach($securityGroupToSecurityResourceId as $sid) {
			$securityGroupToSecurityResourceControl->delete($sid);
		}
	}
	
	function afterInsert(&$dataEntity) {
		$noteControl = BaseFactory::getNoteControl();	
		$noteControl->addNote("Security Group: Added (Id:" . $dataEntity->get("Id") . ")", "SecurityGroup", $dataEntity->get("Id"), 1, "member/securitygroup");					
	}

	function afterUpdate(&$dataEntity) {
		$noteControl = BaseFactory::getNoteControl();	
		$noteControl->addNote("Security Group: Edited (Id:" . $dataEntity->get("Id") . ")", "SecurityGroup", $dataEntity->get("Id"), 2, "member/securitygroup");					
	}
	
	function afterDelete(&$dataEntity) {
		$noteControl = BaseFactory::getNoteControl();	
		$noteControl->addNote("Security Group: Deleted (Id:" . $dataEntity->get("Id") . ")", "SecurityGroup", $dataEntity->get("Id"), 3, "member/securitygroup");					
	}
}
