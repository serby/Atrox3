<?php
/**
 * @package Base
 * @subpackage Community
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision: 456 $ - $Date: 2008-03-16 21:09:59 +0000 (Sun, 16 Mar 2008) $
 */

/**
 * Include Data.php so that DataControl can be extended.
 */
	require_once("Atrox/Core/Data/Data.php");


/**
 *
 * @author Robert Arnold (Clock Ltd) {@link mailto:robert.arnold@clock.co.uk robert.arnold@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision: 456 $ - $Date: 2008-03-16 21:09:59 +0000 (Sun, 16 Mar 2008) $
 * @package Base
 * @subpackage Community
 */
class PrivateMessageControl extends DataControl {
	var $table = "PrivateMessage";
	var $key = "Id";
	var $sequence = "PrivateMessage_Id_seq";
	var $defaultOrder = "Id";

	function init() {

		$this->fieldMeta["Id"] = new FieldMeta(
			"Id", "", FM_TYPE_INTEGER, null, FM_STORE_NEVER, false);
			
		$this->fieldMeta["Message"] = new FieldMeta(
			"Message", "", FM_TYPE_STRING, 5000, FM_STORE_ALWAYS, false);

		$this->fieldMeta["Message"]->setFormatter(CoreFactory::getBodyTextFormatter());
		
		$this->fieldMeta["OriginatorId"] = new FieldMeta(
			"Originator Id", "", FM_TYPE_RELATION, null, FM_STORE_ALWAYS, false);

		$this->fieldMeta["OriginatorId"]->setRelationControl(BaseFactory::getMemberControl());
		$this->fieldMeta["OriginatorId"]->setAutoData(CoreFactory::getCurrentMember());
		
		$this->fieldMeta["RecipientId"] = new FieldMeta(
			"Recipient Id", "", FM_TYPE_RELATION, null, FM_STORE_ALWAYS, false);		

		$this->fieldMeta["DateSent"] = new FieldMeta(
			"Date Sent", "", FM_TYPE_DATE, null, FM_STORE_NEVER, false);

		$this->fieldMeta["DateSent"]->setFormatter(CoreFactory::getDateTimeFieldFormatter());
	}
	
	function retrieveMessages($member, $num = "") {
		$filter = $this->getFilter();
      if ($filter == null) {
        $filter = CoreFactory::getFilter();
      }
      $filter->addConditional($this->table, "RecipientId", $member->get("Id"));
		if ($num != "") {
			$filter->addLimit($num);
		}
      $this->setFilter($filter);
      $this->retrieveAll();
	}
 
}