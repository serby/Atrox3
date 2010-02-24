<?php
/**
 * @package Base
 * @subpackage Campaign
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 */
	 
/**
 * Include Data.php so that DataControl can be extended.
 * Include Formatting.php so that Formatting can be extended.
 */
require_once("Atrox/Core/Data/Data.php");
require_once("Atrox/Core/Formatting.php");

define("CPH_SEND",-1);
define("CPH_NOTSENT",0);
define("CPH_SENDING",1);
define("CPH_SENT",2);
define("CPH_CANCELLED",3);
define("CPH_RESENT",4);

/**
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @package Base
 * @subpackage Campaign
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 */
class CampaignHistoryControl extends DataControl {

	var $table = "CampaignHistory";
	var $key = "Id";
	var $sequence = "CampaignHistory_Id_seq";
	var $defaultOrder = "Id";

	var $statusText = array(
		CPH_SEND => "Queued to send",
		CPH_NOTSENT => "Not Sent",
		CPH_SENDING => "Sending...",
		CPH_SENT => "Mailing list sent",
		CPH_CANCELLED => "Cancelling",
		CPH_RESENT => "Queued to re-send");
	
	function init() {

		$this->fieldMeta["Id"] = new FieldMeta(
			"Id","",FM_TYPE_INTEGER,1,FM_STORE_NEVER,false);

		$this->fieldMeta["CampaignId"] = new FieldMeta(
			"CampaignId","",FM_TYPE_RELATION,50,FM_STORE_ALWAYS,false);

		$this->fieldMeta["CampaignId"]->setRelationControl(BaseFactory::getCampaignControl());

		$this->fieldMeta["MailingListId"] = new FieldMeta(
			"MailingListId","",FM_TYPE_RELATION,200,FM_STORE_ALWAYS,false);

		$this->fieldMeta["MailingListId"]->setRelationControl(BaseFactory::getMailingListControl());

		$this->fieldMeta["DateCreated"] = new FieldMeta(
			"Date Created","",FM_TYPE_DATE, null, FM_STORE_NEVER,false);

		$this->fieldMeta["DateCreated"]->setFormatter(CoreFactory::getDateTimeFieldFormatter());

		$this->fieldMeta["Status"] = new FieldMeta(
			"Status","",FM_TYPE_INTEGER,1,FM_STORE_ALWAYS,false);

		$this->fieldMeta["Status"]->setFormatter(
			CoreFactory::getArrayRelationFormatter($this->statusText, CPH_NOTSENT));
			
		$this->fieldMeta["StatusText"] = new FieldMeta(
			"StatusText","",FM_TYPE_STRING,255,FM_STORE_ALWAYS,false);

		$this->fieldMeta["Notes"] = new FieldMeta(
			"Notes","",FM_TYPE_STRING,5000,FM_STORE_ALWAYS,true);

		$this->fieldMeta["LastEmailSentTo"] = new FieldMeta(
			"Last E-mail Sent To","",FM_TYPE_STRING, 255, FM_STORE_ALWAYS,true);				

		$this->fieldMeta["FinishedAt"] = new FieldMeta(
			"Finished At","",FM_TYPE_DATE,null,FM_STORE_ALWAYS,true);

		$this->fieldMeta["FinishedAt"]->setFormatter(CoreFactory::getDateTimeFieldFormatter());
		
		$this->fieldMeta["ProcessId"] = new FieldMeta(
			"Process Id","",FM_TYPE_INTEGER,1,FM_STORE_ALWAYS,true);
			
		$this->fieldMeta["SentCount"] = new FieldMeta(
			"SentCount","",FM_TYPE_INTEGER,1,FM_STORE_ALWAYS,true);				
	}

	function retrieveForCampaign($campaignId) {
		$filter = $this->getFilter();
		if ($filter == null) {
			$filter = CoreFactory::getFilter();
		}
		$filter->addConditional($this->table,"CampaignId", $campaignId);
		$this->setFilter($filter);
		$this->retrieveAll();
	}
	function getMailingListsForCampaign($campaignId) {
		$mailingListIds = array();
		$this->retrieveForCampaign($campaignId);
		while ($campaignHistory = $this->getNext()) {
			$mailingListIds[] = $campaignHistory->get("MailingListId");
		}
		return $mailingListIds;
	}
	
	function getCampaignSentCount($accountId) {
		$campaignControl = BaseFactory::getCampaignControl();
		$campaignHistoryControl = BaseFactory::getCampaignHistoryControl();		
		$filter = CoreFactory::getFilter();
		$filter->addJoin($campaignHistoryControl->table, "CampaignId", $campaignControl->table, "Id");
		$filter->addConditional($campaignHistoryControl->table,"Status", CPH_SENT); 
		$filter->addConditional($campaignControl->table, "AccountId", $accountId);
		$campaignHistoryControl->setFilter($filter); 

		return $campaignHistoryControl->getNumRows();
	}
	
	function getCampaignSentByType($accountId, $type) {
		$campaignControl = BaseFactory::getCampaignControl();
		$campaignHistoryControl = BaseFactory::getCampaignHistoryControl();		
		$filter = CoreFactory::getFilter();
		$filter->addJoin($campaignHistoryControl->table, "CampaignId", $campaignControl->table, "Id");
		$filter->addConditional($campaignHistoryControl->table,"Status", CPH_SENT); 
		$filter->addConditional($campaignControl->table, "AccountId", $accountId);
		$filter->addConditional($campaignControl->table, "Type", $type);
		$campaignHistoryControl->setFilter($filter); 
		return $campaignHistoryControl->getNumRows();
	}	
		
	function getSentByTypeCount($accountId, $type) {
			$campaignControl = BaseFactory::getCampaignControl();
			$campaignHistoryControl = BaseFactory::getCampaignHistoryControl();		
			$filter = CoreFactory::getFilter();
			$filter->addJoin($campaignHistoryControl->table, "CampaignId", $campaignControl->table, "Id");
			$filter->addConditional($campaignHistoryControl->table,"Status", CPH_SENT); 
			$filter->addConditional($campaignControl->table, "AccountId", $accountId);
			$filter->addConditional($campaignControl->table, "Type", $type);
			$campaignHistoryControl->setFilter($filter); 
			$count = 0;
			while($campaign = $campaignHistoryControl->getNext()) {
				$count += $campaign->get("SentCount");
			}
			return $count;
		}			
		
		
		
	function getUniqueSentTo($campaign, $chosenListId) {
		$mailingListControl = BaseFactory::getMailingListControl();
		$mailingListItemControl = BaseFactory::getMailingListItemControl();
		$this->retrieveForCampaign($campaign->get("Id"));
		$mailingListIds = array();
		$duplicatedEmails = array();
		$duplicatedEmails["Members"]["Count"] = 0;
		$duplicatedEmails["MailinglistItems"]["Count"] = 0;
		while ($campaignHistory = $this->getNext()){	
			$mailingListIds[] = $campaignHistory->get("MailingListId");					
		}
		$mailingListIds = array_unique($mailingListIds);
		foreach ($mailingListIds as $value) {		
			if ($value == $chosenListId) {
				echo "WARNING: You haven chosen to send to a list that has already received this email ";
				continue 1;
			}	
			if ($chosenListId == CP_SENDTOMEMBERS){
				$mailingList = $mailingListControl->item($value);
				$mailingListItemControl->retrieveMembersFromMailingList($mailingList);
				while ($mailingListItem = $mailingListItemControl->getNext()) {
					$duplicatedEmails[0][] = $mailingListItem->get("EmailAddress");
					$duplicatedEmails["Members"]["Count"]++;
				}
			} else if ($mailingList = $mailingListControl->item($value)) {									
				$mailingListItemControl->retrieveDuplicateUsersOnMailingLists($mailingList);
				while ($mailingListItem = $mailingListItemControl->getNext()) {
					$duplicatedEmails[0][] = $mailingListItem->get("EmailAddress");
					$duplicatedEmails["MailinglistItems"]["Count"]++;
					$duplicatedEmails["MailingList"]["Ids"][] = $value;
				}
			}													
		}
		if (isset($duplicatedEmails[0])) {
			$duplicatedEmails[0] = array_unique($duplicatedEmails[0]);
		}
		if (isset($duplicatedEmails["MailingList"]["Ids"])) {
			$duplicatedEmails["MailingList"]["Ids"] = array_unique($duplicatedEmails["MailingList"]["Ids"]);	
		}
		return $duplicatedEmails;
	}
	function getDateOfLastEmailSentToAllMembers() {
		$filter = CoreFactory::getFilter();
		$filter->addConditional($this->table, "MailingListId", "-1");
		$filter->addOrder("FinishedAt", true);
		$filter->addLimit(1);
		$this->setFilter($filter);
		if ($campaignHistory = $this->getNext()) {
			return $campaignHistory;
		}	else {
			return false;
		}
	}
	/**
	 * Returns true if there is a mailing list that is unsent, in this campaign
	 */
	function hasUnsentMailingList($campaignId, $mailingListId) {
		$filter = CoreFactory::getFilter();
		$filter->addConditional($this->table, "CampaignId", $campaignId);
		$filter->addConditional($this->table, "MailingListId", $mailingListId);
		$this->setFilter($filter);
		while ($campaignHistory = $this->getNext()) {
			if ($campaignHistory->get("Status") == CPH_NOTSENT) {
				return true;
			}
		}	
		return false;
	}
}