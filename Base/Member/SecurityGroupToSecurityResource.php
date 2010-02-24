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
class SecurityGroupToSecurityResourceControl extends DataControl {
	var $table = "SecurityGroupToSecurityResource";
	var $key = "Id";
	var $sequence = "SecurityGroupToSecurityResource_Id_seq";
	var $defaultOrder = "SecurityResourceName";
	var $searchFields = array("SecurityResource");

	function init() {
		$this->fieldMeta["Id"] = new FieldMeta(
			"Id", "", FM_TYPE_INTEGER, null, FM_STORE_NEVER, false);

		$this->fieldMeta["SecurityGroupId"] = new FieldMeta(
			"Security Group Id", "", FM_TYPE_INTEGER, null, FM_STORE_ALWAYS, false);

		$this->fieldMeta["SecurityResourceName"] = new FieldMeta(
			"Security Resource Name", "", FM_TYPE_STRING, 50, FM_STORE_ALWAYS, false);
	}

	function retrieveForSecurityGroup($securityGroup) {
		$filter = $this->getFilter();
		if ($filter == null) {
			$filter = CoreFactory::getFilter();
		}
		$filter->addOrder("SecurityResourceName");
		$filter->addConditional($this->table, "SecurityGroupId", $securityGroup->get("Id"));
		$this->setFilter($filter);
		return $this->retrieveAll();
	}
}