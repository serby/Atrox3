<?php
/**
 * @package Base
 * @subpackage Campaign
 * @copyright Clock Limited 2007
 * @version 3.0 - $Revision$ - $Date$
 */
 
/**
 * Include Data.php so that DataControl can be extended.
 */
require_once("Atrox/3.0/Core/Data/Data.php");


define("NEWSLETTER_SUBSCRIPTIONS", 1);
define("SMS_SUBSCRIPTIONS", 2);
define("EMAIL_SUBSCRIPTIONS", 3);

/**
 *
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @package Base
 * @subpackage Campaign
 * @copyright Clock Limited 2007
 * @version 3.0 - $Revision$ - $Date$
 */
class MailingListItemControl extends DataControl {

	var $table = "MailingListItem";
	var $key = "Id";
	var $sequence = "MailingListItem_Id_seq";
	var $defaultOrder = "EmailAddress";
	var $searchFields = array("EmailAddress", "FirstName", "LastName");

	function init() {

		$this->fieldMeta["Id"] = new FieldMeta(
			"Id", "", FM_TYPE_INTEGER, null, FM_STORE_NEVER, false);

		$this->fieldMeta["MailingListId"] = new FieldMeta(
			"MailingListId", "", FM_TYPE_RELATION, null, FM_STORE_ALWAYS, false);
			
		$this->fieldMeta["MailingListId"]->setRelationControl(BaseFactory::getMailingListControl());				

		$this->fieldMeta["EmailAddress"] = new FieldMeta(
			"Email Address", "", FM_TYPE_EMAILADDRESS, 100, FM_STORE_ALWAYS, true);
			
		$this->fieldMeta["MobileNumber"] = new FieldMeta(
			"Mobile Number", "", FM_TYPE_STRING, 20, FM_STORE_ALWAYS, true);
			
		$this->fieldMeta["MobileNumber"]->setValidation(CoreFactory::getMobileValidation());

		$this->fieldMeta["FirstName"] = new FieldMeta(
			"First Name", "", FM_TYPE_STRING, 50, FM_STORE_ALWAYS, true);

		$this->fieldMeta["LastName"] = new FieldMeta(
			"Last Name", "", FM_TYPE_STRING, 50, FM_STORE_ALWAYS, true);

		$this->fieldMeta["LastActive"] = new FieldMeta(
			"Last Active", "", FM_TYPE_DATE, 1, FM_STORE_NEVER, false);

		$this->fieldMeta["LastActive"]->setFormatter(CoreFactory::getDateTimeFieldFormatter());

		$this->fieldMeta["Subscribed"] = new FieldMeta(
			"Subscribed", "", FM_TYPE_BOOLEAN, 1, FM_STORE_NEVER, false);

		$this->fieldMeta["UniqueId"] = new FieldMeta(
			"UniqueId", "", FM_TYPE_UID, null, FM_STORE_ADD, true);
			
		$this->fieldMeta["Field1"] = new FieldMeta(
			"Field 1", "", FM_TYPE_STRING, 1000, FM_STORE_ALWAYS, true);
	}

	function getActiveCount($mailingListId, $activeDate = true) {
		$filter = CoreFactory::getFilter();
		$filter->addConditional($this->table, "MailingListId", $mailingListId);
		if ($activeDate === true) {
			$filter->addConditional($this->table, "LastActive", null, "IS NOT");
		} else if ($activeDate === false) {
			$filter->addConditional($this->table, "LastActive", null, "IS");
		} else {
			$filter->addConditional($this->table, "LastActive", $activeDate, ">=");
		}
		$this->setFilter($filter);
		return $this->count();
	}
	
	function getSubscribedCount($mailingListId, $subscribed = true) {
		$filter = CoreFactory::getFilter();
		$filter->addConditional($this->table, "MailingListId", $mailingListId);
		if ($subscribed === true) {
			$filter->addConditional($this->table, "Subscribed", "t");
		} else {
			$filter->addConditional($this->table, "Subscribed", "f");
		}
		$this->setFilter($filter);
		return $this->count();
	}		
	
	function getSubscribedCountByType($mailingListId, $type) {
		$filter = CoreFactory::getFilter();
		$filter->addConditional($this->table, "MailingListId", $mailingListId);
		$filter->addConditional($this->table, "Subscribed", "t");		
		$filter->addConditional($this->table, $type, "", "!=");
		$this->setFilter($filter);
		return $this->count();
	}				
	
	function retrieveForMailingList($mailingListId, $startingAfter = null, 
		$subscribedOnly = false, $type = "CP_TYPE_EMAIL") {
			
		$filter = $this->getFilter();
		if ($filter == null) {
			$filter = CoreFactory::getFilter();
		}

		$filter->addConditional($this->table, "MailingListId", $mailingListId);

		if ($subscribedOnly) {
			$filter->addConditional($this->table, "Subscribed", "t");
		}
		
		if ($startingAfter != null) {
			$filter->addConditional($this->table, "EmailAddress", $startingAfter, ">");
		}

		if ($type === 0) {
			//TODO: Serby how do we use the OR parameter without brackets ?
			//$filter->addConditional($this->table, "EmailAddress", "", "!=");
			//$filter->addConditional($this->table, "MobileNumber", "", "!=", "OR");
		} else if ($type == "CP_TYPE_EMAIL") {
			$filter->addConditional($this->table, "EmailAddress", "", "!=");
		} else {
			$filter->addConditional($this->table, "MobileNumber", "", "!=");
		}

		$this->setFilter($filter);
		
		$this->retrieveAll();
	}

	function setActive(&$mailingListItem) {
		$this->updateField($mailingListItem, "LastActive", 
		$this->databaseControl->getCurrentDateTime());
	}

	function setSubscribed(&$mailingListItem, $value = true) {
		$this->updateField($mailingListItem, "Subscribed", ($value ?"t":"f"));
	}
	
	function afterInsert(&$dataEntity) {
		$mailingListControl = BaseFactory::getMailingListControl();
		$mailingList = $dataEntity->getRelation("MailingListId");
		
		$mailListItemControl = BaseFactory::getMailingListItemControl();
		
		$filter = CoreFactory::getFilter();
		$filter->addConditional($this->table, "MailingListId", $mailingList->get("Id"));		
		$mailListItemControl->setFilter($filter);
		$totalCount = $mailListItemControl->count();
		
		$filter = $this->getFilter();
		$filter->addConditional($this->table, "Subscribed", "t");
		$filter->addConditional($this->table, "MailingListId", $mailingList->get("Id"));
		$mailListItemControl->setFilter($filter);
		$subscribedCount = $mailListItemControl->count();
		
		$mailingListControl->updateFields($mailingList, 
			array("Count", "SubscribedCount"),
			array($totalCount, $subscribedCount));
	}

	function afterUpdate(&$dataEntity) {
		$this->afterInsert($dataEntity);
	}

	function afterDelete(&$dataEntity) {
		//$this->afterInsert($dataEntity);
	}
	
	function retrieveMembersFromMailingList($mailingList) {
		$this->init();
		$sql = "SELECT \"MailingListItem\".* FROM \"MailingListItem\"" . 
			"LEFT JOIN  \"Members\" ON \"MailingListItem\".\"EmailAddress\"" .
			" = \"Members\".\"EmailAddress\" WHERE \"MailingListId\" = " .
			$mailingList->get("Id") . " AND" .
			"\"Members\".\"EmailAddress\" IS NOT NULL AND" .
			"\"Members\".\"ReceiveEmailUpdates\" = 't'";
		return $this->runQuery($sql);				
	}
	
	function retrieveDuplicateUsersOnMailingLists($mailingList) {
		$this->init();
		$sql = "SELECT \"MailingListItem\".*, \"MailingListItem2\".\"MailingListId\"" . 
			"FROM \"MailingListItem\"" .
			"LEFT JOIN \"MailingListItem\" AS \"MailingListItem2\" ON" .
			"\"MailingListItem\".\"EmailAddress\" = \"MailingListItem2\".\"EmailAddress\"" .
			"WHERE \"MailingListItem\".\"MailingListId\" = '" . $mailingList->get("Id") ."' AND" .
			"\"MailingListItem2\".\"MailingListId\" != '" . $mailingList->get("Id") ."' AND" .
			"\"MailingListItem2\".\"Subscribed\" = 't'" . 
			"ORDER BY \"MailingListItem\".\"EmailAddress\"";
		return $this->runQuery($sql);		
	}	
	
	function isMemberAMailingListItem($member, $mailingListId) {
		$filter = $this->getFilter();
		if ($filter == null) {
			$filter = CoreFactory::getFilter();
		}
		$filter->addConditional($this->table, "MailingListId", $mailingListId);
		$filter->addConditional($this->table, "MemberId", $member->get("Id"));
		$this->setFilter($filter);
		$this->retrieveAll();
		if ($this->getNumRows() > 0) {
			return true;
		} else {
			return false;
		}
	}
	function isEmailInList($email, $mailingListId) {
		$filter = $this->getFilter();
		if ($filter == null) {
			$filter = CoreFactory::getFilter();
		}
		$filter->addConditional($this->table, "MailingListId", $mailingListId);
		$filter->addConditional($this->table, "EmailAddress", $email);
		$this->setFilter($filter);
		$this->retrieveAll();
		if ($this->getNumRows() > 0) {
			return true;
		} else {
			return false;
		}
	}
}