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


define("ML_TYPE_NONE", 0);
define("ML_TYPE_MAILINGLIST", 1);
define("ML_TYPE_MEMBER", 2);
define("ML_TYPE_NORMAL", 2);
define("ML_TYPE_FREE", 1);

/**
 *
 * @author Paul Serby {@link mai lto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @package Base
 * @subpackage Campaign
 * @copyright Clock Limited 2007
 * @version 3.0 - $Revision$ - $Date$
 */
class MailingListControl extends DataControl {

	var $table = "MailingList";
	var $key = "Id";
	var $sequence = "MailingList_Id_seq";
	var $defaultOrder = "Id";
	var $searchFields = array("Name", "Description");

	function init() {

		$this->fieldMeta["Id"] = new FieldMeta(
		"Id","",FM_TYPE_INTEGER, null, FM_STORE_NEVER, false);

		$this->fieldMeta["Name"] = new FieldMeta(
		"Name", "", FM_TYPE_STRING, 50, FM_STORE_ALWAYS, false);

		$this->fieldMeta["Description"] = new FieldMeta(
		"Description", "", FM_TYPE_STRING, 1000, FM_STORE_ALWAYS, false);

		$this->fieldMeta["Description"]->setFormatter(
		CoreFactory::getBodyTextFormatter());

		$this->fieldMeta["DateCreated"] = new FieldMeta(
		"Date Created", "", FM_TYPE_DATE, null, FM_STORE_NEVER, false);

		$this->fieldMeta["DateCreated"]->setFormatter(
		CoreFactory::getDateTimeFieldFormatter());

		$this->fieldMeta["AuthorId"] = new FieldMeta(
		"Author", "", FM_TYPE_RELATION, null, FM_STORE_ALWAYS, false);

		$this->fieldMeta["AuthorId"]->setRelationControl(
		Factory::getMemberControl());

		$this->fieldMeta["AuthorId"]->setAutoData(
		CoreFactory::getCurrentMember());

		$this->fieldMeta["Count"] = new FieldMeta(
		"Count","",FM_TYPE_INTEGER, null, FM_STORE_NEVER, false);

		$this->fieldMeta["SubscribedCount"] = new FieldMeta(
		"SubscribedCount","",FM_TYPE_INTEGER, null, FM_STORE_NEVER, false);

		$this->fieldMeta["Field1"] = new FieldMeta(
		"Field 1", "", FM_TYPE_STRING, 1000, FM_STORE_ALWAYS, true);

		$this->fieldMeta["Notes"] = new FieldMeta(
		"Notes", "", FM_TYPE_STRING, 1000, FM_STORE_ALWAYS, true);

		$this->fieldMeta["AccountId"] = new FieldMeta(
		"Account", "", FM_TYPE_RELATION, null, FM_STORE_ALWAYS, false);

		$this->fieldMeta["AccountId"]->setRelationControl(
			Factory::getAccountControl());
		
		$this->fieldMeta["AccountId"]->setAutoData(
			Factory::getCurrentAccount());

		$this->fieldMeta["Type"] = new FieldMeta(
		"Type","",FM_TYPE_INTEGER, null, FM_STORE_NEVER, false);		
		
		$this->fieldMeta["CallbackUrl"] = new FieldMeta(
				"Callback URL", "", FM_TYPE_STRING, 1000, FM_STORE_ALWAYS, true);		

		$this->fieldMeta["FreeList"] = new FieldMeta(
				"Free List", "", FM_TYPE_BOOLEAN, null, FM_STORE_ALWAYS, true);					
	}

	function retrieveForMember($memberId) {
		$filter = $this->getFilter();
		if ($filter == null) {
			$filter = CoreFactory::getFilter();
		}
		$filter->addConditional($this->table, "AuthorId", $memberId);
		$this->setFilter($filter);
		$this->retrieveAll();
	}

	function importCsv($fileName, $mailingList) {
		if (!file_exists($fileName)) {
			$this->errorControl->addError(
			"Invalid filename '$fileName'");
			return false;
		}
		
		$account = $mailingList->getRelation("AccountId");
		
		if ($mailingList->get("Type") == 2) {
			$max = $account->get("NormalListItemMaxSize");
		} else {
			$max = $account->get("FreeListItemMaxSize");
		}			

		$listItemControl = BaseFactory::getMailingListItemControl();

		$rows = file($fileName);
		
		$returnReport["FileName"] = $fileName;
		$returnReport["FileSize"] = filesize($fileName);
		$returnReport["Rows"] = sizeof($rows);
		$returnReport["Successful"] = 0;
		$returnReport["Failed"] = 0;
		$returnReport["Duplicated"] = 0;
		
		if (($mailingList->get("Count") + $returnReport["Rows"]) > $max && $max != -1) {
			$this->errorControl->addError(
			"Exceeding maximum list size. <a href=\"/contact/\">Contact administrator</a> to have this enlarged.");
			return false;
		}		

		$listItemControl->initControl();

		$listId = $mailingList->get("Id");

		$listListArray = array();
		
		foreach ($rows as $row) {
			$listListArray = explode(",", $row);
			$listItem = $listItemControl->makeNew();
			if (isset($listListArray[0]) && ($listListArray[0] != "")) {
				$listItem->set("EmailAddress", trim($listListArray[0]));
			}
			if (isset($listListArray[3]) && ($listListArray[3] != "")) {
				$listItem->set("MobileNumber", trim($listListArray[3]));
			}
			$listItem->set("FirstName", "Unknown");
			$listItem->set("LastName", "Unknown");
			isset($listListArray[1]) && $listItem->set("FirstName", trim($listListArray[1]));
			isset($listListArray[2]) && $listItem->set("LastName", trim($listListArray[2]));
			$listItem->set("MailingListId", $listId);
			$mailingList->set("Count", true);
			
			if ((!@$listItem->save()) || ($listItemControl->errorControl->hasErrors())) {
				$returnReport["Failed"]++;
			} else {
				$returnReport["Successful"]++;
			}
			$listItemControl->errorControl->clear();
		}
		return $returnReport;
	}

	function subscribeEmailAddress($email, $listId = NEWSLETTER_SUBSCRIPTIONS) {

		$mailListItemControl = BaseFactory::getMailingListItemControl();
		$memberControl = BaseFactory::getMemberControl();
			
		$newListItem = $mailListItemControl->map($email);
		$newListItem->set("MailingListId", $listId);

		$filter = CoreFactory::getFilter();
		$filter->addConditional($mailListItemControl->table, "EmailAddress", $newListItem->get("EmailAddress"));
		$filter->addConditional($mailListItemControl->table, "MailingListId", $listId);
		$mailListItemControl->setFilter($filter);

		if (!$mailingListItem = $mailListItemControl->getNumRows() > 0) {
			if ($newListItem->save()) {
				$filter = CoreFactory::getFilter();
				$filter->addConditional($memberControl->table, "EmailAddress", $newListItem->get("EmailAddress"));
				$memberControl->setFilter($filter);

				if ($memberControl->getNumRows() > 0) {
					$member = $memberControl->itemByField($newListItem->get("EmailAddress"), "EmailAddress");
					$member->set("ReceiveEmailUpdates", "t");
					$member->save();
				}
				return true;
			}
		} else {
			$mailListItemControl->errorControl->addError("E-mail Address already appears on subscribed list");
			return false;
		}
			
	}

	function addReminder($data, $type) {

		$mailListItemControl = BaseFactory::getMailingListItemControl();
		$memberControl = BaseFactory::getMemberControl();
			
		$newListItem = $mailListItemControl->map($data);

		$filter = CoreFactory::getFilter();
		if ($type == "E-mail") {
			$newListItem->set("MailingListId", EMAIL_SUBSCRIPTIONS);
			$filter->addConditional($mailListItemControl->table, "EmailAddress", $newListItem->get("EmailAddress"));
			$filter->addConditional($mailListItemControl->table, "MailingListId", EMAIL_SUBSCRIPTIONS);
		} else {
			$newListItem->set("MailingListId", SMS_SUBSCRIPTIONS);
			$filter->addConditional($mailListItemControl->table, "MobileNumber", $newListItem->get("MobileNumber"));
			$filter->addConditional($mailListItemControl->table, "MailingListId", SMS_SUBSCRIPTIONS);
		}
			
		$mailListItemControl->setFilter($filter);
		$mailListItemControl->retrieveAll();
			
		if (!$mailingListItem = $mailListItemControl->getNumRows() > 0) {
			if ($newListItem->save()) {
				return true;
			}
		} else {
			$mailListItemControl->errorControl->addError("E-mail Address/Mobile Number already appears on subscribed list");
			return false;
		}
			
	}

	function getSubscriptionType($id) {
		$mailingListItemControl = BaseFactory::getMailingListItemControl();
		$memberControl = BaseFactory::getMemberControl();
		$subscriptionType = ML_TYPE_NONE;
			
		if ($mailingListItem = $mailingListItemControl->itemByField($id, "UniqueId")) {
			if ($mailingListItem->get("Subscribed") == "t") {
				$subscriptionType = ML_TYPE_MAILINGLIST;
			}
		}
		if ($member = $memberControl->itemByField($id, "ConfirmationId")) {
			if ($member->get("ReceiveEmailUpdates") == "t") {
				$subscriptionType = ML_TYPE_MEMBER;
			}
		}
		return $subscriptionType;
	}

	function unsubscribeBySubscriptionType($id, $type = ML_TYPE_MAILINGLIST) {	
		$mailingListItemControl = BaseFactory::getMailingListItemControl();
		$memberControl = BaseFactory::getMemberControl();
		$unsubscribed = false;
			
		if ($type == ML_TYPE_MAILINGLIST) {
			if ($mailingListItem = $mailingListItemControl->itemByField($id, "UniqueId")) {
				$mailingListItemControl->setSubscribed($mailingListItem, false);
				$unsubscribed = true;
			}
		}
		if ($type == ML_TYPE_MEMBER) {
			if ($member = $memberControl->itemByField($id, "ConfirmationId")) {
				$memberControl->updateField($member, "ReceiveEmailUpdates", "f");
				$unsubscribed = true;
			}
		}
		return $unsubscribed;
	}
		
	function subscribeUsingAuthId($authId, $mailingListId, $emailAddress, $firstName = "", $lastName = "", $mobileNumber = "") {
		$mailingListItemControl = BaseFactory::getMailingListItemControl();
		$accountControl = Factory::getAccountControl();

		if ($account = $accountControl->itemByField($authId, "AuthId")) {
			$filter = CoreFactory::getFilter();
			$filter->addConditional($mailingListItemControl->table, "MailingListId", $mailingListId);
			$filter->addConditional($mailingListItemControl->table, "EmailAddress", $emailAddress);
			$mailingListItemControl->setFilter($filter);
			if ($mailingListItemControl->getNumRows() > 0) {
				$mailingListItem = $mailingListItemControl->getNext();
				$mailingListItemControl->setSubscribed($mailingListItem);
			} else {
				$mailingListItem = $mailingListItemControl->makeNew();
				$mailingListItem->set("MailingListId", $mailingListId); 
				$mailingListItem->set("EmailAddress", $emailAddress);
				$mailingListItem->set("FirstName", $firstName);
				$mailingListItem->set("LastName", $lastName);
				$mailingListItem->set("Subscribed", true);
				$mailingListItem->set("MobileNumber", $mobileNumber);
				$mailingListItem->save();				
			}
		} else {
			$this->errorControl->addError("No account found that matches the AUTHID: " . $authId);
		}
		return $this->errorControl->hasErrors();
	}
	
	function unsubscribeUsingAuthId($authId, $mailingListId, $emailAddress = "", $mobileNumber = "") {
		$mailingListItemControl = BaseFactory::getMailingListItemControl();
		$accountControl = Factory::getAccountControl();
		
		if ($account = $accountControl->itemByField($authId, "AuthId")) {
			if ($emailAddress != "") {
				if ($mailingListItem = $mailingListItemControl->itemByField($emailAddress, "EmailAddress")) {
					$mailingListItemControl->setSubscribed($mailingListItem, false);
				} else {
					$mailingListItemControl->errorControl->addError("Cannot find email address to unsubscribe");
				}
			} else {
				if ($mailingListItem = $mailingListItemControl->itemByField($mobileNumber, "MobileNumber")) {
					$mailingListItem->setSubscribed($mailingListItem, false);			
				} else {
					$mailingListItem->errorControl->addError("Cannot find mobile number to unsubscribe");
				}				
			}
		} else {
			$this->errorControl->addError("No account found that matches the AUTHID: " . $authId);
		}
	}
	
	function deleteUsingAuthId($authId, $mailingListId, $emailAddress = "", $mobileNumber = "") {
		$mailingListItemControl = BaseFactory::getMailingListItemControl();
		$accountControl = Factory::getAccountControl();
		
		if ($account = $accountControl->itemByField($authId, "AuthId")) {
			if ($emailAddress != "") {				
				if ($mailingListItem = $mailingListItemControl->itemByField($emailAddress, "EmailAddress")) {
					$mailingListItemControl->quickDelete($mailingListItem->get("Id"));
				} else {
					$mailingListItemControl->errorControl->addError("Cannot find email address to delete");
				}
			} else {
				if ($mailingListItem = $mailingListItemControl->itemByField($mobileNumber, "MobileNumber")) {
					$mailingListItemControl->quickDelete($mailingListItem->get("Id")); 			
				} else {
					$mailingListItemControl->errorControl->addError("Cannot find mobile number to delete");
				}				
			}
		} else {
			$this->errorControl->addError("No account found that matches the AUTHID: " . $authId);
		}
	}
}