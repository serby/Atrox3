<?php
/**
 * @package Base
 * @subpackage Campaign
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 */
 
/**
 * Include Data.php so that DataControl can be extended.
 * Include Report.php so that ReportControl can be extended.
 */
require_once("Atrox/Core/Data/Data.php");
require_once("Atrox/Core/Data/Report.php");


/**
 *
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @package Base
 * @subpackage Campaign
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 */
class CampaignResultControl extends DataControl {

	var $table = "CampaignResult";
	var $key = "Id";
	var $sequence = "CampaignResult_Id_seq";
	var $defaultOrder = "Id";

	function init() {

		$this->fieldMeta["Id"] = new FieldMeta(
			"Id", "", FM_TYPE_INTEGER, null, FM_STORE_NEVER, false);

		$this->fieldMeta["CampaignId"] = new FieldMeta(
			"Campaign Id", "", FM_TYPE_RELATION, null, FM_STORE_ALWAYS, false);

		$this->fieldMeta["EmailAddress"] = new FieldMeta(
			"Email Address", "", FM_TYPE_EMAILADDRESS, 255, FM_STORE_ALWAYS, false);

		$this->fieldMeta["DateCreated"] = new FieldMeta(
			"Date Created", "", FM_TYPE_DATE, null, FM_STORE_NEVER, false);

		$this->fieldMeta["Url"] = new FieldMeta(
			"Url", "", FM_TYPE_STRING, 255, FM_STORE_ALWAYS, false);

		$this->fieldMeta["SessionId"] = new FieldMeta(
			"Session Id", "", FM_TYPE_STRING, 40, FM_STORE_ALWAYS, false);

		$this->fieldMeta["UserAgent"] = new FieldMeta(
			"User Agent", "", FM_TYPE_STRING, 255, FM_STORE_ALWAYS, true);

	}
	
	function retrieveForCampaign($campaignId) {
		$filter = $this->getFilter();
		if ($filter == null) {
			$filter = CoreFactory::getFilter();
		}
		$filter->addConditional($this->table, "CampaignId", $campaignId);
		$this->setFilter($filter);
		$this->retrieveAll();
	}

	
	function getUrlToCountForCampaign($campaignId) {
		$this->initControl();
		$sql = "SELECT \"Url\", COUNT(\"Url\") AS \"Count\" FROM \"{$this->table}\" WHERE \"CampaignId\"='{$campaignId}' AND \"Url\" != 'Viewed' GROUP BY \"Url\";";
		$result = @pg_fetch_all($this->databaseControl->query($sql));
		if($result) {				
			return $result;
		} else {
			return false;
		}
	}
	
	function getNumClickedLinks() {
			$campaignResultControl = BaseFactory::getCampaignResultControl();
			
			return $campaignResultControl->getNumRows();			
		}
}

class CampaignResultReportControl extends ReportControl {		

	function getUniqueInteractive($campaignId, $excludeListId = null) {
		$this->init();		
		$excludeSql = "";
		if ($excludeListId) {
			$mailingListControl = BaseFactory::getMailingListControl();
			if ($mailingListControl->item($excludeListId)) {
				$excludeSql = "\"EmailAddress\" NOT IN (SELECT \"EmailAddress\" FROM \"MailingListItem\" WHERE \"MailingListId\" = $excludeListId) AND";
			}
		}
		$sql = "SELECT COUNT(DISTINCT(\"EmailAddress\")) FROM \"CampaignResult\" WHERE {$excludeSql} \"CampaignId\" = '$campaignId';";
		return $this->databaseControl->getValueFromQuery($sql);
	}		
	
	function getUrlReport($campaignId, $excludeListId = null) {
		$this->init();
		$excludeSql = "true";
		if ($excludeListId) {
			$mailingListControl = BaseFactory::getMailingListControl();
			if ($mailingListControl->item($excludeListId)) {
				$excludeSql = "\"EmailAddress\" NOT IN (SELECT \"EmailAddress\" FROM \"MailingListItem\" WHERE \"MailingListId\" = $excludeListId)";
			}
		}
		$sql = "SELECT \"Url\", COUNT(*) AS \"Count\" FROM \"CampaignResult\" WHERE $excludeSql AND \"CampaignId\"='$campaignId' AND \"Url\" != 'Viewed' GROUP BY \"Url\" ORDER BY \"Count\" DESC";			
		$this->results = $this->databaseControl->query($sql);			
	}

	function getResultCount($campaignId, $excludeListId = null) {
		$this->init();
		$excludeSql = "true";
		if ($excludeListId) {
			$mailingListControl = BaseFactory::getMailingListControl();
			if ($mailingListControl->item($excludeListId)) {
				$excludeSql = "\"EmailAddress\" NOT IN (SELECT \"EmailAddress\" FROM \"MailingListItem\" WHERE \"MailingListId\" = $excludeListId)";
			}
		}			
		return $this->databaseControl->getValueFromQuery("SELECT COUNT(*) AS \"Count\" FROM \"CampaignResult\" WHERE \"CampaignId\"='$campaignId' AND \"Url\" != 'Viewed';");
	}

	function getViewTrackerCount($campaignId, $excludeListId = null) {
		$this->init();
		$excludeSql = "true";
		if ($excludeListId) {
			$mailingListControl = BaseFactory::getMailingListControl();
			if ($mailingListControl->item($excludeListId)) {
				$excludeSql = "\"EmailAddress\" NOT IN (SELECT \"EmailAddress\" FROM \"MailingListItem\" WHERE \"MailingListId\" = $excludeListId)";
			}
		}			
		return $this->databaseControl->getValueFromQuery("SELECT COUNT(*) AS \"Count\" FROM \"CampaignResult\" WHERE $excludeSql AND \"CampaignId\"='$campaignId' AND \"Url\" = 'Viewed';");
	}

	function getTimeProfile($campaignId, $resolution = 30, $excludeListId = null) {
		$resolution = $resolution * 60;
		$this->init();
		$excludeSql = "true";
		if ($excludeListId) {
			$mailingListControl = BaseFactory::getMailingListControl();
			if ($mailingListControl->item($excludeListId)) {
				$excludeSql = "\"EmailAddress\" NOT IN (SELECT \"EmailAddress\" FROM \"MailingListItem\" WHERE \"MailingListId\" = $excludeListId)";
			}
		}			
		$sql = "SELECT CEIL(EXTRACT(EPOCH FROM \"DateCreated\")/" . $resolution . ") AS \"Timestamp\", COUNT(*) AS \"Count\" FROM \"CampaignResult\" WHERE $excludeSql AND \"CampaignId\"='$campaignId' AND \"Url\" != 'Viewed' GROUP BY \"Timestamp\" ORDER BY \"Timestamp\"";
		$this->results = $this->databaseControl->query($sql);
	}
	
	function getUniqueEmailCount($campaignId, $excludeListId = null) {
		$this->init();
		$excludeSql = "true";
		if ($excludeListId) {
			$mailingListControl = BaseFactory::getMailingListControl();
			if ($mailingListControl->item($excludeListId)) {
				$excludeSql = "\"EmailAddress\" NOT IN (SELECT \"EmailAddress\" FROM \"MailingListItem\" WHERE \"MailingListId\" = $excludeListId)";
			}
		}			
		$sql = "SELECT COUNT(DISTINCT \"EmailAddress\") FROM \"CampaignResult\" WHERE $excludeSql AND \"CampaignId\"='$campaignId' AND \"Url\" != 'Viewed';";
		return $this->databaseControl->getValueFromQuery($sql);
	}

	function getUniqueViews($campaignId, $excludeListId = null) {
		$this->init();
		$excludeSql = "true";
		if ($excludeListId) {
			$mailingListControl = BaseFactory::getMailingListControl();
			if ($mailingListControl->item($excludeListId)) {
				$excludeSql = "\"EmailAddress\" NOT IN (SELECT \"EmailAddress\" FROM \"MailingListItem\" WHERE \"MailingListId\" = $excludeListId)";
			}
		}			
		$sql = "SELECT COUNT(DISTINCT \"EmailAddress\") FROM \"CampaignResult\" WHERE $excludeSql AND \"CampaignId\"='$campaignId' AND \"Url\" = 'Viewed';";
		return $this->databaseControl->getValueFromQuery($sql);
	}	
			
	function getOverallUniqueViews() {
		$this->init();
		$count = 0;
		$excludeSql = "true";
		$campaignResultControl = BaseFactory::getCampaignResultControl();
		
		$campaignHistoryControl = BaseFactory::getCampaignHistoryControl();
		$filter = CoreFactory::getFilter();
		$filter->addConditional($campaignHistoryControl->table, "Status", CPH_SENT);	
		$campaignHistoryControl->setFilter($filter);			
		
		$campaignIds = array();
		
		while ($campaignHistory = $campaignHistoryControl->getNext()) {
			$campaignIds[] = $campaignHistory->get("CampaignId");
		}
		
		foreach ($campaignIds as $campaignId) {
			$mailingListControl = BaseFactory::getMailingListControl();	
			$sql = "SELECT COUNT(DISTINCT \"EmailAddress\") FROM \"CampaignResult\" WHERE $excludeSql AND \"CampaignId\"='" . $campaignId . "';";
			$count = $count + $this->databaseControl->getValueFromQuery($sql);
			$tmpCount = $this->databaseControl->getValueFromQuery($sql);
		}
		
/*		while ($campaignResult = $campaignResultControl->getNext()) {

			echo $this->databaseControl->getValueFromQuery($sql) . "<br />";			
		}*/
		return $count;
	}		
			
	function getCampaignUniqueViews($campaignId, $excludeListId = null) {
		$this->init();
		$excludeSql = "true";
		if ($excludeListId) {
			$mailingListControl = BaseFactory::getMailingListControl();
			if ($mailingListControl->item($excludeListId)) {
				$excludeSql = "\"EmailAddress\" NOT IN (SELECT \"EmailAddress\" FROM \"MailingListItem\" WHERE \"MailingListId\" = $excludeListId)";
			}
		}			
		$sql = "SELECT COUNT(DISTINCT \"EmailAddress\") FROM \"CampaignResult\" WHERE $excludeSql AND \"CampaignId\"='$campaignId';";
		return $this->databaseControl->getValueFromQuery($sql);
	}		
		
	function getViews($campaignId, $excludeListId = null) {
		$this->init();
		$excludeSql = "true";
		if ($excludeListId) {
			$mailingListControl = BaseFactory::getMailingListControl();
			if ($mailingListControl->item($excludeListId)) {
				$excludeSql = "\"EmailAddress\" NOT IN (SELECT \"EmailAddress\" FROM \"MailingListItem\" WHERE \"MailingListId\" = $excludeListId)";
			}
		}			
		$sql = "SELECT COUNT(*) FROM \"CampaignResult\" WHERE $excludeSql AND \"CampaignId\"='$campaignId' AND \"Url\" = 'Viewed';";
		return $this->databaseControl->getValueFromQuery($sql);
	}		

	function getUniqueViewsThatDidNotReport($campaignId, $excludeListId = null) {
		$this->init();
		$excludeSql = "true";
		if ($excludeListId) {
			$mailingListControl = BaseFactory::getMailingListControl();
			if ($mailingListControl->item($excludeListId)) {
				$excludeSql = "\"EmailAddress\" NOT IN (SELECT \"EmailAddress\" FROM \"MailingListItem\" WHERE \"MailingListId\" = $excludeListId)";
			}
		}			
		$sql = "SELECT COUNT(*) FROM \"CampaignResult\" WHERE {$excludeSql} AND \"CampaignId\" = '$campaignId' AND \"EmailAddress\" NOT IN (SELECT \"EmailAddress\" FROM \"CampaignResult\" WHERE \"CampaignId\" = '$campaignId' AND \"Url\" = 'Viewed');";
		return $this->databaseControl->getValueFromQuery($sql);			
	}	
}