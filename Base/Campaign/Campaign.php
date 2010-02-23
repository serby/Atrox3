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
require_once("Atrox/Core/Data/Data.php");

define("CP_TYPE_EMAIL", "1");
define("CP_TYPE_SMS", "2");	

define("CP_STAGE_CREATED", "1");
define("CP_STAGE_PARTIAL", "2");
define("CP_STAGE_COMPLETE", "3");
		
define("CP_SENDTOMEMBERS", "-1");

/**
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @package Base
 * @subpackage Campaign
 * @copyright Clock Limited 2007
 * @version 3.0 - $Revision$ - $Date$
 */
class CampaignControl extends DataControl {

	var $table = "Campaign";
	var $key = "Id";
	var $sequence = "Campaign_Id_seq";
	var $defaultOrder = "Id";
	var $searchFields = array("Name", "Description");
	var $lastSentTo = "";
	var $total = 0;
	var $count = 0;
	var $currentSendCount = 0;
	
	var $types = array(
		CP_TYPE_EMAIL => "email-campaign",
		CP_TYPE_SMS => "sms-campaign");		
	
	var $stages = array(
		CP_STAGE_CREATED => "campaign-started",
		CP_STAGE_PARTIAL => "campaign-partial",
		CP_STAGE_COMPLETE => "campaign-complete");
		
	var $predefinedTags = array(
		"EMAILADDRESS", "FIRSTNAME", "LASTNAME", 
		"PRODUCT_DETAILS", "PRODUCT_SUMMARY", "PRODUCT_IMAGE", "PRODUCT_NAME", "PRODUCT_DESCRIPTION",
		"STOCKITEM_DETAILS", "STOCKITEM_DESCRIPTION", "STOCKITEM_PRICE"	,
		"NEWS_SUBJECT", "NEWS_SUMMARY", "UNSUBSCRIBE_LINK"		
	);

	var $linkWrapperUrl = "/log.php";
	var $unsubscribeUrl = "/unsubscribe.php";

	function CampaignControl() {
			parent::DataControl();
			$this->linkWrapperUrl = $this->application->registry->get("Site/Address") . "/log.php";
			$this->unsubscribeUrl = $this->application->registry->get("Site/Address") . "/unsubscribe.php";
		}
		
	function init() {

		$this->fieldMeta["Id"] = new FieldMeta(
			"Id", "", FM_TYPE_INTEGER,null, FM_STORE_NEVER, false);

		$this->fieldMeta["Name"] = new FieldMeta(
			"Name", "", FM_TYPE_STRING, 50, FM_STORE_ALWAYS, false);

		$this->fieldMeta["Description"] = new FieldMeta(
			"Description", "", FM_TYPE_STRING, 200, FM_STORE_ALWAYS, true);

		$this->fieldMeta["Description"]->setFormatter(
			CoreFactory::getBodyTextFormatter());

		$this->fieldMeta["AuthorId"] = new FieldMeta(
			"Author", "", FM_TYPE_RELATION, 0, FM_STORE_ALWAYS, false);

		$this->fieldMeta["AuthorId"]->setRelationControl(
			BaseFactory::getMemberControl());
		$this->fieldMeta["AuthorId"]->setAutoData(
			CoreFactory::getCurrentMember());

		$this->fieldMeta["Notes"] = new FieldMeta(
			"Notes", "", FM_TYPE_STRING, 5000, FM_STORE_ALWAYS, true);

		$this->fieldMeta["Notes"]->setFormatter(
			CoreFactory::getBodyTextFormatter());

		$this->fieldMeta["UniqueId"] = new FieldMeta(
			"UniqueId", "", FM_TYPE_UID, null, FM_STORE_ADD, true);

		$this->fieldMeta["From"] = new FieldMeta(
			"From", "", FM_TYPE_EMAILADDRESS, 255, FM_STORE_ALWAYS, false);

		$this->fieldMeta["Subject"] = new FieldMeta(
			"Subject", "", FM_TYPE_STRING, 255, FM_STORE_ALWAYS, false);

		$this->fieldMeta["PlainContent"] = new FieldMeta(
			"Plain Content", "", FM_TYPE_STRING, 50000, FM_STORE_ALWAYS, true);

		$this->fieldMeta["PlainContent"]->setFormatter(
			CoreFactory::getBodyTextFormatter());

		$this->fieldMeta["DateCreated"] = new FieldMeta(
			"DateCreated", "", FM_TYPE_DATE, null, FM_STORE_NEVER, false);

		$this->fieldMeta["DateCreated"]->setFormatter(
			CoreFactory::getDateTimeFieldFormatter());
					
		$this->fieldMeta["Type"] = new FieldMeta(
			"Campaign Type", "", FM_TYPE_INTEGER, null, FM_STORE_ALWAYS, true);
			
		$this->fieldMeta["Type"]->setFormatter(
			CoreFactory::getArrayRelationFormatter($this->types, CP_TYPE_EMAIL));		

		$this->fieldMeta["FormattedContent"] = new FieldMeta(
			"Formatted Content", "", FM_TYPE_HTML, 50000, FM_STORE_ALWAYS, true);							

		$this->fieldMeta["TemplateId"] = new FieldMeta(
			"Template Id", "", FM_TYPE_INTEGER, null, FM_STORE_ALWAYS, true);
							
		$this->fieldMeta["AccountId"] = new FieldMeta(
			"Account Id", "", FM_TYPE_RELATION, null, FM_STORE_ALWAYS, false);

		$this->fieldMeta["AccountId"]->setRelationControl(
			Factory::getAccountControl());
	
		$this->fieldMeta["AccountId"]->setAutoData(
			Factory::getCurrentAccount());				
		
		$this->fieldMeta["Stage"] = new FieldMeta(
			"Stage", "", FM_TYPE_INTEGER, null, FM_STORE_ALWAYS, true);

		$this->fieldMeta["Stage"]->setFormatter(
			CoreFactory::getArrayRelationFormatter($this->stages, CP_STAGE_CREATED));	
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

	function resend($campaignId, $campaignHistoryIds, $startingAfter = null) {
		$campaignHistoryControl = BaseFactory::getCampaignHistoryControl();
		foreach ($campaignHistoryIds as $campaignHistoryId) {
			$campaignHistory = $campaignHistoryControl->item($campaignHistoryId);
			$campaignHistoryControl->updateFields($campaignHistory,
				array("Status", "StatusText"), array(CPH_RESENT, "Re-sent"));			
		}

		//$mailingListId = $campaignHistory->get("MailingListId");
		//shell_exec(PHP_PATH . " " . "atrox/base/campaign/campaignsend.php \"" . $_SERVER["DOCUMENT_ROOT"] . "\" $campaignId $mailingListId  $startingAfter > /dev/null &");
		//passthru(PHP_PATH . " " . "atrox/base/campaign/campaignsend.php \"" . $_SERVER["DOCUMENT_ROOT"] . "\" $campaignId $mailingListId $startingAfter "); exit;
	}

	function send($campaignId, $mailingListIds) {
		$campaignHistoryControl = BaseFactory::getCampaignHistoryControl();
		
		if (!is_array($mailingListIds)) {
			$mailingListIds = array($mailingListIds); 
		}
		
		foreach ($mailingListIds as $mailingListId) {
			$campaignHistoryControl->clearFilter();
			$filter = CoreFactory::getFilter();
			$filter->addConditional($campaignHistoryControl->table, "CampaignId", $campaignId);
			$filter->addConditional($campaignHistoryControl->table, "MailingListId", $mailingListId);
			$conditions[] = $filter->makeConditional($campaignHistoryControl->table, "Status", CPH_NOTSENT, "=", "OR");
			$conditions[] = $filter->makeConditional($campaignHistoryControl->table, "Status", CPH_RESENT, "=", "OR");				
			$filter->addConditionalGroup($conditions);
			$campaignHistoryControl->setFilter($filter);
			while ($campaignHistory = $campaignHistoryControl->getNext()) {
				 $campaignHistoryControl->updateField($campaignHistory, "Status", CPH_SEND);
			}
		}
		
		//shell_exec(PHP_PATH . " " . "atrox/base/campaign/campaignsend.php \"" . $_SERVER["DOCUMENT_ROOT"] . "\" $campaignId $mailingListId  > /dev/null &"); 
		// Use passthru when you want to view the output. 
		// DO NOT LEAVE IT WITH PASSTHRU! 
		//passthru(PHP_PATH . " " . "atrox/base/campaign/campaignsend.php \"" . $_SERVER["DOCUMENT_ROOT"] . "\" $campaignId $mailingListId "); exit;
		//echo PHP_PATH . " " . "atrox/base/campaign/campaignsend.php \"" . $_SERVER["DOCUMENT_ROOT"] . "\" $campaignId $mailingListId"; exit;
	}
	
	function stopSending($campaignId, $campaignHistoryId) {
		$campaignHistoryControl = BaseFactory::getCampaignHistoryControl();
		$campaignHistory = $campaignHistoryControl->item($campaignHistoryId);		
		$campaignHistoryControl->updateField($campaignHistory, "Status", CPH_CANCELLED);			
		$campaignHistoryControl->updateField($campaignHistory, "StatusText", "Canceling");						
		$command = "/bin/kill " . $campaignHistory->get("ProcessId") . " > /dev/null &" ;
		shell_exec($command);	
	}

	function sendCancelled() {
		if (isset($this->currentHistory)) {
			$campaignHistory = $this->currentHistory;
			$campaignHistoryControl = $this->currentHistory->getControl();
			$updateFields = array("StatusText", "LastEmailSentTo");
			$updateValues = array("{$this->count} / {$this->total} Sent. Last Address '{$this->lastSentTo}'", $this->lastSentTo);
			$campaignHistoryControl->updateFields($campaignHistory, $updateFields, $updateValues);
			if ($campaign = $campaignHistory->getRelation("CampaignId")) {
				$this->updateField($campaign, "Sent", $this->currentSendCount + $this->count);
			}
		}
	}

	function sendEmail($campaignId, $mailingListId, $startingAfter = null, $procId = null) {
		// Hold many have been sent
		$this->count = 0;
		
		// Update Status every
		$updateEvery = 50;
		
		// Gets the campain data or fail 			
		if (!$campaign = $this->item($campaignId)) {
			return false;
		}
		
		// Keep track of Sent Count to update post sending
		$this->currentSendCount = $campaign->get("Sent");
		
		// Construct a campaign history to store the fact it is being sent out
		$campaignHistoryControl = BaseFactory::getCampaignHistoryControl();
		$campaignHistory = $campaignHistoryControl->makeNew();

		$campaignHistory->set("CampaignId", $campaignId);
		$campaignHistory->set("ProcessId", $procId);			
		
		if ($mailingListId == CP_SENDTOMEMBERS) {
			$campaignHistory->set("MailingListId", CP_SENDTOMEMBERS);
		} else {
			$campaignHistory->set("MailingListId", $mailingListId);
		}
			
		$campaignHistory->set("Status", CPH_SENDING);
		$campaignHistory->set("StatusText", "Sending...");
		$campaignHistory->save();
		
		$this->currentHistory = $campaignHistory;
		
		if ($mailingListId == CP_SENDTOMEMBERS) {				

			// Sending to Members		
			$memberControl = BaseFactory::getMemberControl();
			$filter = $memberControl->getFilter();
			$filter->addConditional($memberControl->table, "ReceiveEmailUpdates", "t");				
			$memberControl->setFilter($filter);
			$filter->addConditional($memberControl->table, "EmailAddress", $startingAfter, ">");
			$filter->addOrder("EmailAddress");
			
			// Total number of messages to send
			$total = $memberControl->getNumRows();
			
			// Get the e-mail content 
			$htmlContent = $campaign->get("HtmlContent");
			$plainContent = $campaign->get("PlainContent");

			// Create an e-mail object which will do the sending.
			$email = CoreFactory::getEmail();
			
			// Set the basic e-mail fields
			$email->setFrom($campaign->get("From"));
			$email->setSubject($campaign->get("Subject"));
			
			// Get the campaign 
			$cuId = $campaign->get("UniqueId");
			$link = $this->linkWrapperUrl . "?CmId=$cuId&Url=";

			$updateFields = array("StatusText", "LastEmailSentTo");
			
			// Parse Content 
			$htmlContent = $this->parseCampaignTemplate($htmlContent, $campaign->get("ParseNewLines"));

			// Find all the links and store the positions
			$matches = array();
			preg_match_all("/href=\"([^#]*?)\"/", $htmlContent, $matches, PREG_OFFSET_CAPTURE);

			foreach($matches[1] as $k => $match) {
				if ($match[0] == "{UNSUBSCRIBE_LINK}") {
					if ($account->get("CallbackUrl") == null) {
						$matches[1][$k][2] = urlencode($this->unsubscribeUrl);
					} else {
						$matches[1][$k][2] = urlencode($account->get("CallbackUrl"));
					}
				} else {
					$newUrl = str_replace("&amp;", "&", $match[0]);
					$matches[1][$k][2] = urlencode($newUrl);
				}
			}

			$out = "";
			$sp = 0;

			$findArray = array("/\{EMAILADDRESS\}/", "/\{FIRSTNAME\}/", "/\{LASTNAME\}/");

			// Loop though all the members DO NOT MOVE THIS LINE!!!!!
			// PARSING OF THE TEMPLATE MUST NOT BE DONE BEFORE 
			while ($member = $memberControl->getNext()) {
			
				$emailAddress = $member->get("EmailAddress");						
	
				$newHtmlContent = "";
				$sp = 0;
				
				// Replace all hrefs with tracking link
				foreach ($matches[1] as $match) {
					$ep = $match[1];
					$newHtmlContent .= mb_substr($htmlContent, $sp, $ep - $sp) . 
						$link . $match[2] . "&amp;By=" . 
						$member->get("ConfirmationId") . 
						"&amp;Member=" . $emailAddress;
					$sp = $ep + mb_strlen($match[0]);
				}
				
				$newHtmlContent .= mb_substr($htmlContent, $sp);

				// Replace all hrefs with tracking style
				$replaceArray = array(
					$emailAddress,
					$member->get("FirstName"),
					$member->get("LastName")
				);
				
				// Set the UID 
				$uid = $member->get("ConfirmationId");

				$newHtmlContent = preg_replace($findArray, $replaceArray, $newHtmlContent);
				$newPlainContent = preg_replace($findArray, $replaceArray, $plainContent);

				// Replace any tokens such as {UNSUBSCRIBE_LINK} in the plain e-mail
				$newPlainContent = preg_replace("/\{UNSUBSCRIBE_LINK\}/", $this->unsubscribeUrl . "?Id=$uid", $newPlainContent);
				
				$email->setPlainBody($newPlainContent);	
				$email->setBody($newHtmlContent, false);	
				$email->setTo($emailAddress);
				$this->lastSentTo = $emailAddress;
				$email->sendMail();
				$this->count++;

				if ($this->count % $updateEvery == 0) {
					$updateValues = array("{$this->count} / {$this->total} Sent. Last Address '$emailAddress'", $emailAddress);
					$campaignHistoryControl->updateFields($campaignHistory, $updateFields, $updateValues);
					$this->incrementField($campaign, "Sent", $updateEvery);
				}
				usleep(3);
			}			
		} else {
			
			$mailingListItemControl = BaseFactory::getMailingListItemControl();				
			$filter = $mailingListItemControl->getFilter();
			$mailingListItemControl->retrieveForMailingList($mailingListId, $startingAfter, true);				
			$mailingListItemControl->setFilter($filter);
			
			// Total number of messages to send
			$this->total = $mailingListItemControl->getNumRows();				
							 
			// Get the e-mail content 
			$htmlContent = $campaign->get("HtmlContent");					
			$plainContent = $campaign->get("PlainContent");					

			// Create an e-mail object which will do the sending.
			$email = CoreFactory::getEmail();
			
			// Set the basic e-mail fields
			$email->setFrom($campaign->get("From"));
			$email->setSubject($campaign->get("Subject"));					
			
			// Get the campaign 
			$cuId = $campaign->get("UniqueId");
			$link = $this->linkWrapperUrl . "?CmId=$cuId&Url=";

			$updateFields = array("StatusText", "LastEmailSentTo");
			
			// Parse Content 
			$htmlContent = $this->parseCampaignTemplate($htmlContent, $campaign->get("ParseNewLines"));

			// Basic templating tags, search strings
			$findArray = array("/\{EMAILADDRESS\}/", "/\{FIRSTNAME\}/", "/\{LASTNAME\}/", "/\{FIELD1\}/");
			
			$replaceArray = array(
				"CLKfb465358c27168b6bcc85d1756f5402aCLK1", 
				"CLKfb465358c27168b6bcc85d1756f5402aCLK2", 
				"CLKfb465358c27168b6bcc85d1756f5402aCLK3",
				"CLKfb465358c27168b6bcc85d1756f5402aCLK4");

			$findBackArray = array(
				"/CLKfb465358c27168b6bcc85d1756f5402aCLK1/", 
				"/CLKfb465358c27168b6bcc85d1756f5402aCLK2/", 
				"/CLKfb465358c27168b6bcc85d1756f5402aCLK3/",
				"/CLKfb465358c27168b6bcc85d1756f5402aCLK4/");

			$replaceBackArray = array("{EMAILADDRESS}", "{FIRSTNAME}", "{LASTNAME}", "{FIELD1}");
			
			// Find all the links and store the positions
			$matches = array();
			preg_match_all("/href=\"([^#]*?)\"/", $htmlContent, $matches, PREG_OFFSET_CAPTURE);
			
			foreach($matches[1] as $k => $match) {
				if ($match[0] == "{UNSUBSCRIBE_LINK}") {
					$matches[1][$k][2] = urlencode($this->unsubscribeUrl);
				} else {
					$newUrl = str_replace("&amp;", "&", $match[0]);
					$newUrl = preg_replace($findArray, $replaceArray, $newUrl);
					$newUrl = urlencode($newUrl);
					$newUrl = preg_replace($findBackArray, $replaceBackArray, $newUrl);
					$matches[1][$k][2] = $newUrl;						
				}
			}
			
			$out = "";
			$sp = 0;
			
			while ($mailingListItem = $mailingListItemControl->getNext()) {
				
				$emailAddress = $mailingListItem->get("EmailAddress");

				if (!@$email->setTo($emailAddress)) {
					$mailingListItemControl->delete($mailingListItem->get("Id"));
					continue;
				}					
				
				$uid = $mailingListItem->get("UniqueId");					
				
				$newHtmlContent = "";
				$sp = 0;
				
				// Replace all hrefs with tracking link
				foreach($matches[1] as $match) {
					$ep = $match[1];
					$newHtmlContent .= mb_substr($htmlContent, $sp, $ep-$sp) . $link . $match[2] . "&By=$uid";
					$sp = $ep + mb_strlen($match[0]);
				}
				
				$newHtmlContent .= mb_substr($htmlContent, $sp);

				// Replace all hrefs with tracking style
				$replaceArray = array(
					$mailingListItem->get("EmailAddress"),
					$mailingListItem->get("FirstName"),
					$mailingListItem->get("LastName"),
					$mailingListItem->get("Field1")
				);

				$newHtmlContent = preg_replace($findArray, $replaceArray, $newHtmlContent);
				$newPlainContent = preg_replace($findArray, $replaceArray, $plainContent);
				

				// Replace any tokens such as {UNSUBSCRIBE_LINK} in the plain e-mail
				$newPlainContent = preg_replace("/\{UNSUBSCRIBE_LINK\}/", $this->unsubscribeUrl . "?Id=$uid", $newPlainContent);							
				
				$email->setPlainBody($newPlainContent);
				$email->setTo($emailAddress);
				$email->setBody($newHtmlContent, false);
				$email->sendMail();		
				$this->lastSentTo = $emailAddress;			
				$this->count++;
				
				if ($this->count % $updateEvery == 0) {
					$updateValues = array("{$this->count} / {$this->total} Sent. Last Address '$emailAddress'", $emailAddress);
					$campaignHistoryControl->updateFields($campaignHistory, $updateFields, $updateValues);
					$this->incrementField($campaign, "Sent", $updateEvery);
				}
				usleep(3);
			}
		}		
		
		$this->updateField($campaign, "Sent", $this->currentSendCount + $this->count);
		
		if ($this->count > 1) {
			$campaignHistoryControl->updateField($campaignHistory, "StatusText", 
				"E-mail sent to {$this->count} addresses");
		} else {
			$campaignHistoryControl->updateField($campaignHistory, "StatusText", 
				"E-mail sent to {$this->count} address");
		}

		$campaignHistoryControl->updateField($campaignHistory, "Status", CPH_SENT);
		$campaignHistoryControl->updateField($campaignHistory, "FinishedAt", 
			$this->databaseControl->getCurrentDateTime());
	}
	
	function sendClockUpEmail($campaignHistory) {
					
		$campaignHistoryControl = BaseFactory::getCampaignHistoryControl();
		$bsmtpControl = CoreFactory::getBsmtpControl();
		
		$mailingList = $campaignHistory->getRelation("MailingListId", false);
		$campaign = $campaignHistory->getRelation("CampaignId", false);
		
		// Hold many have been sent
		$this->count = 0;
		
		// Update Status every
		$updateEvery = 50;
				
		// Keep track of Sent Count to update post sending
		$this->currentSendCount = $campaignHistory->get("SentCount");
		
		$campaignHistoryControl = $campaignHistory->getControl();		
		
		$campaignHistoryControl->updateField($campaignHistory, "Status", CPH_SENDING);
		
		$this->currentHistory = $campaignHistory;		

		$mailingListItemControl = BaseFactory::getMailingListItemControl();
					
		$filter = $mailingListItemControl->getFilter();
		$mailingListItemControl->retrieveForMailingList($mailingList->get("Id"),
			$campaignHistory->get("LastEmailSentTo"), true);
		$mailingListItemControl->setFilter($filter);
		
		// Total number of messages to send
		$this->total = $mailingListItemControl->getNumRows();				
						 
		// Get the e-mail content 
		$htmlContent = $campaign->get("FormattedContent");					
		$plainContent = $campaign->get("PlainContent");					
	
		// Create an e-mail object which will do the sending.
		$email = CoreFactory::getEmail();
		
		// Set the basic e-mail fields
		$email->setFrom($campaign->get("From"));
		$email->setSubject($campaign->get("Subject"));
		
		// Get the campaign 
		$cuId = $campaign->get("UniqueId");
		$link = $this->linkWrapperUrl . "?CmId=$cuId&Url=";
	
		$updateFields = array("LastEmailSentTo");
		
		// Parse Content 
		//TODO: I have removed this should we default true or false ($campaign->get("ParseNewLines"))
		$htmlContent = $this->parseCampaignTemplate($htmlContent, false);
	
		// Basic templating tags, search strings
		$findArray = array("/\{EMAILADDRESS\}/", "/\{FIRSTNAME\}/", "/\{LASTNAME\}/", "/\{FIELD1\}/");
		
		$replaceArray = array(
			"CLKfb465358c27168b6bcc85d1756f5402aCLK1", 
			"CLKfb465358c27168b6bcc85d1756f5402aCLK2", 
			"CLKfb465358c27168b6bcc85d1756f5402aCLK3",
			"CLKfb465358c27168b6bcc85d1756f5402aCLK4");
	
		$findBackArray = array(
			"/CLKfb465358c27168b6bcc85d1756f5402aCLK1/", 
			"/CLKfb465358c27168b6bcc85d1756f5402aCLK2/", 
			"/CLKfb465358c27168b6bcc85d1756f5402aCLK3/",
			"/CLKfb465358c27168b6bcc85d1756f5402aCLK4/");
	
		$replaceBackArray = array("{EMAILADDRESS}", "{FIRSTNAME}", "{LASTNAME}", "{FIELD1}");
		
		// Find all the links and store the positions
		$matches = array();
		preg_match_all("/href=\"([^#]*?)\"/", $htmlContent, $matches, PREG_OFFSET_CAPTURE);
		
		if ($mailingList->isNull("CallbackUrl")) {
			$unsubscribeLink = $this->unsubscribeUrl;
		} else {					
			$unsubscribeLink = trim($mailingList->get("CallbackUrl"));
		}
		
		foreach($matches[1] as $k => $match) {
			if ($match[0] == "{UNSUBSCRIBE_LINK}") {
				$matches[1][$k][2] = urlencode($unsubscribeLink);
			} else {
				$newUrl = str_replace("&amp;", "&", $match[0]);
				$newUrl = preg_replace($findArray, $replaceArray, $newUrl);
				$newUrl = urlencode($newUrl);
				$newUrl = preg_replace($findBackArray, $replaceBackArray, $newUrl);
				$matches[1][$k][2] = $newUrl;						
			}
		}
		
		$out = "";
		$sp = 0;
		
		// Loop through the mailing list, making all the user specific search and replaces
		while ($mailingListItem = $mailingListItemControl->getNext()) {
			
			$emailAddress = $mailingListItem->get("EmailAddress");
	
			if (!@$email->setTo($emailAddress)) {
				$mailingListItemControl->delete($mailingListItem->get("Id"));
				continue;
			}					
			
			$uid = $mailingListItem->get("UniqueId");					
			
			$newHtmlContent = "";
			$sp = 0;
			
			// Replace all hrefs with tracking link
			foreach($matches[1] as $match) {
				$ep = $match[1];
				$newHtmlContent .= mb_substr($htmlContent, $sp, $ep-$sp) . $link . $match[2] . "&By={$uid}";
				$sp = $ep + mb_strlen($match[0]);
			}
			
			$newHtmlContent .= mb_substr($htmlContent, $sp);
	
			// Replace all hrefs with tracking style
			$replaceArray = array(
				$mailingListItem->get("EmailAddress"),
				$mailingListItem->get("FirstName"),
				$mailingListItem->get("LastName"),
				$mailingListItem->get("Field1")
			);
	
			$newHtmlContent = preg_replace($findArray, $replaceArray, $newHtmlContent);
			$newPlainContent = preg_replace($findArray, $replaceArray, $plainContent);
	
			// Replace any tokens such as {UNSUBSCRIBE_LINK} in the plain e-mail
			$newPlainContent = preg_replace("/\{UNSUBSCRIBE_LINK\}/", $unsubscribeLink . "?Id=$uid", $newPlainContent);							

			$email->setPlainBody($newPlainContent);
			$email->setBody($newHtmlContent, false);	
			$email->setHeaders("Precedence: bulk\r\n");					
			$email->constructMail();		
			
			
			$mailContent = $email->finalHeaders . $email->finalHtmlBody;
			$bsmtpControl->addMailToQueue($email->getTo(), $mailContent, $campaign->get("Id") . "-errors@clockup.co.uk");
			
			$this->count++;
			$numberSent = $this->count;
		}
		
		$bsmtpControl->sendQueue();
		
		$campaignHistoryControl->updateField($campaignHistory, "SentCount", $this->currentSendCount + $this->count);
		$campaignHistoryControl->updateField($campaignHistory, "Status", CPH_SENT);	

		return $numberSent;
	}
	
	function sendSms($campaignId, $mailingListId, $startingAfter = null, $procId = null) {
		
		if (!$campaign = $this->item($campaignId)) {
			return false;
		}

		$campaignHistoryControl = BaseFactory::getCampaignHistoryControl();
		$campaignHistory = $campaignHistoryControl->makeNew();
		
		$campaignHistory->set("ProcessId", $procId);		
		
		$campaignHistory->set("CampaignId", $campaignId);
		$campaignHistory->set("MailingListId", $mailingListId);
		$campaignHistory->set("Status", CPH_SENDING);
		$campaignHistory->set("StatusText", "Sending...");
		$campaignHistory->save();

		$plainContent = $campaign->get("PlainContent");

		$smsControl = CoreFactory::getSmsControl();
		//$smsControl = CoreFactory::getSmsGateway("US");
		
		$mailingListItemControl = BaseFactory::getMailingListItemControl();			
					
		$filter = $mailingListItemControl->getFilter();
		$filter->addOrder("MobileNumber");
		$mailingListItemControl->setFilter($filter);
		
		$this->count = 0;
		$mailingListItemControl->retrieveForMailingList($mailingListId, $startingAfter, true, "SMS");

		$cuId = $campaign->get("UniqueId");			

		$link = $this->linkWrapperUrl."?CmId=$cuId&Url=";

		// Total number of messages to send
		$this->total = $mailingListItemControl->getNumRows();

		$updateFields = array("StatusText", "LastEmailSentTo");
		
		$matches = "";
		preg_match_all("/href=\"(.*?)\"/", $plainContent, $matches, PREG_OFFSET_CAPTURE);
		
		$out = "";
		$sp = 0;
		
		foreach($matches[1] as $k => $match) {
			if ($match[0] == "{UNSUBSCRIBE_LINK}") {
				$matches[1][$k][2] = urlencode($this->unsubscribeUrl);
			} else {
				$matches[1][$k][2] = urlencode($match[0]);
			}
		}

		$findArray = array("/\{EMAILADDRESS\}/", "/\{FIRSTNAME\}/", "/\{LASTNAME\}/", "/\{TRACKER\}/");

		while ($mailingListItem = $mailingListItemControl->getNext()) {
		
			$smsMessage = $smsControl->makeNew();
			
			$mobileNumber = $mailingListItem->get("MobileNumber");
			$smsMessage->setRecipients($mobileNumber);
			
			$uid = $mailingListItem->get("UniqueId");
			
			$newHtmlContent = "";
			$sp = 0;
			// Replace all hrefs with tracking link
			foreach($matches[1] as $match) {
				$ep = $match[1];
				$newHtmlContent .= mb_substr($plainContent, $sp, $ep-$sp) . $link . $match[2] . "&By=$uid";
				$sp = $ep + mb_strlen($match[0]);
			}
			$newHtmlContent .= mb_substr($plainContent, $sp);

			$replaceArray = array(
				$mailingListItem->get("EmailAddress"),
				$mailingListItem->get("FirstName"),
				$mailingListItem->get("LastName"),
				$this->unsubscribeUrl . "/cmlogview.php?CmId=$cuId&By=$uid"
			);

			$plainContent = preg_replace($findArray, $replaceArray, $plainContent);

			$smsMessage->setMessageText($plainContent);
			$smsMessage->setOriginator($campaign->get("From"));
			$smsControl->send($smsMessage);				

			$this->count++;
			$numberSent = $this->count;
		}

		$this->updateField($campaign, "Sent", $this->currentSendCount + $this->count);

		if ($this->count > 1) {
			$campaignHistoryControl->updateField($campaignHistory, "StatusText", 
				"E-mail sent to {$this->count} addresses");
		} else {
			$campaignHistoryControl->updateField($campaignHistory, "StatusText", 
				"E-mail sent to {$this->count} address");
		}

		$campaignHistoryControl->updateField($campaignHistory, "Status", CPH_SENT);
		$campaignHistoryControl->updateField($campaignHistory, "FinishedAt", 
			$this->databaseControl->getCurrentDateTime());

		return $numberSent;
	}

	function sendClockUpSms($campaignHistory) {
			
			$campaign = $campaignHistory->getRelation("CampaignId", false);
			$mailingList = $campaignHistory->getRelation("MailingListId", false);

			$campaignHistoryControl = $campaignHistory->getControl();
			$campaignHistoryControl->updateField($campaignHistory, "Status", CPH_SENDING);

			$plainContent = $campaign->get("PlainContent");
			
			$mailingListItemControl = BaseFactory::getMailingListItemControl();			
						
			$filter = $mailingListItemControl->getFilter();
			$filter->addOrder("MobileNumber");
			$mailingListItemControl->setFilter($filter);
			
			$this->count = 0;
			$mailingListItemControl->retrieveForMailingList($mailingList->get("Id"), 
				$campaignHistory->get("LastEmailSentTo"), true, "SMS");

			$cuId = $campaign->get("UniqueId");			
    
			$link = $this->linkWrapperUrl."?CmId=$cuId&Url=";

			// Total number of messages to send
			$this->total = $mailingListItemControl->getNumRows();

			$updateFields = array("StatusText", "LastEmailSentTo");
			
			$matches = "";
			preg_match_all("/href=\"(.*?)\"/", $plainContent, $matches, PREG_OFFSET_CAPTURE);
			
			$out = "";
			$sp = 0;
			
			foreach($matches[1] as $k => $match) {
				if ($match[0] == "{UNSUBSCRIBE_LINK}") {
					$matches[1][$k][2] = urlencode($this->unsubscribeUrl);
				} else {
					$matches[1][$k][2] = urlencode($match[0]);
				}
			}

			$findArray = array("/\{EMAILADDRESS\}/", "/\{FIRSTNAME\}/", "/\{LASTNAME\}/", "/\{TRACKER\}/");

			$smsGateway = CoreFactory::getSmsGateway();			
			
			while ($mailingListItem = $mailingListItemControl->getNext()) {
			
				$uid = $mailingListItem->get("UniqueId");
				
				$newHtmlContent = "";
				$sp = 0;
				// Replace all hrefs with tracking link
				foreach($matches[1] as $match) {
					$ep = $match[1];
					$newHtmlContent .= mb_substr($plainContent, $sp, $ep-$sp) . $link . $match[2] . "&By=$uid";
					$sp = $ep + mb_strlen($match[0]);
				}
				$newHtmlContent .= mb_substr($plainContent, $sp);

				$replaceArray = array(
					$mailingListItem->get("EmailAddress"),
					$mailingListItem->get("FirstName"),
					$mailingListItem->get("LastName"),
					$this->unsubscribeUrl . "/cmlogview.php?CmId=$cuId&By=$uid"
				);

				$plainContent = preg_replace($findArray, $replaceArray, $plainContent);
								
				$mobileNumber = $mailingListItem->get("MobileNumber");
				
				$smsMessage = $smsGateway->createMessage($mobileNumber, $plainContent);
				
				echo "\n send sms \n";
				// print_r($smsMessage); 
				//exit;
				$smsGateway->send($smsMessage);			
				//exit;

				$this->count++;

				$updateValues = array("{$this->count} / {$this->total} Sent. Last Number '$mobileNumber'", $mobileNumber);	
				$campaignHistoryControl->updateFields($campaignHistory, $updateFields, $updateValues);
				$this->incrementField($campaign, "Sent", 1);
				usleep(5);
			}

			$this->updateField($campaign, "Sent", $this->currentSendCount + $this->count);
			
			if ($this->count > 1) {
				$campaignHistoryControl->updateField($campaignHistory, "StatusText", 
					"Sms sent to {$this->count} addresses");
			} else {
				$campaignHistoryControl->updateField($campaignHistory, "StatusText", 
					"Sms sent to {$this->count} address");
			}

			$campaignHistoryControl->updateField($campaignHistory, "Status", CPH_SENT);
			$campaignHistoryControl->updateField($campaignHistory, "FinishedAt", 
				$this->databaseControl->getCurrentDateTime());
		}	
		
		
	function getTypes() {
			return $this->types;
		}	
		
	/**
	 * Parser Functions
	 */
	 
	 /**
	  * Return all the predefined tag that can be used.
		  * @return Array A list of all the predefined tags.
		  */
	 function getPredefinedTags() {
	 	return $this->predefinedTags;
	 }
	 
	function parseCampaignTemplate(&$text, $parseNewLines = false) {
		$template = $this->parseMarkup($text, $parseNewLines);
		
		$template = $this->parseCustomTags($template);
				
		// Internal Tags
		$template = $this->parseInternalTags($template, "'{PRODUCT_DETAILS\|(\d+)}'i", new ProductDetailsHtmlGenerator());
		$template = $this->parseInternalTags($template, "'{STOCKITEM_DETAILS\|(\d+)}'i", new StockItemDetailsHtmlGenerator());
		$template = $this->parseInternalTags($template, "'{PRODUCT_IMAGE\|(\d+)}'i", new ProductImageHtmlGenerator());
		$template = $this->parseInternalTags($template, "'{PRODUCT_SUMMARY\|(\d+)}'i", new ProductSummaryHtmlGenerator());
		$template = $this->parseInternalTags($template, "'{PRODUCT_NAME\|(\d+)}'i", new ProductNameHtmlGenerator());
		$template = $this->parseInternalTags($template, "'{PRODUCT_DESCRIPTION\|(\d+)}'i", new ProductDescriptionHtmlGenerator());			
		$template = $this->parseInternalTags($template, "'{STOCKITEM_DESCRIPTION\|(\d+)}'i", new StockItemDescriptionHtmlGenerator());
		$template = $this->parseInternalTags($template, "'{STOCKITEM_PRICE\|(\d+)}'i", new StockItemPriceHtmlGenerator());
		$template = $this->parseInternalTags($template, "'{NEWS_SUBJECT\|(\d+)}'i", new NewsSubjectHtmlGenerator());
		$template = $this->parseInternalTags($template, "'{NEWS_SUMMARY\|(\d+)}'i", new NewsSummaryHtmlGenerator());
		return $template;
	}
		 
	/**
	 * Parse the markup for simple tags and links 
	 */
	function parseMarkup(&$text, $parseNewLines = false) {
												
			$htmlControl = CoreFactory::getHtmlControl();
			
			// Added by Paul Serby 2007-05-01 to allow the default formatting toolbar to be used 
			// in campaign creation. 
			// If you find any problems with this parsing, please contact me. 
			$text = $htmlControl->parseCustomTagging($text);
												
			if ($parseNewLines == "t") {
				$text = str_replace("}\n", "}", $text);
				$text = nl2br($text);
			}
			
			return $text;
		}
	
	/**
	 * Parser for internal tags
	 */
	function parseInternalTags(&$text, $tagReg, $class) {
		
		$return = null;
		preg_match_all($tagReg, $text, $return, PREG_OFFSET_CAPTURE);

		$endPos = mb_strlen($text);
		$newText = "";

		if (sizeof($return[0]) == 0) {
			return $text;
		}
		
		for ($i = sizeof($return[0]) -1; $i >= 0; $i--) {
			
			$string = $return[0][$i][0];
			$offset = $return[0][$i][1];			
			$len = mb_strlen($string);
			$id = $return[1][$i][0];
			
			$replaceText = $class->getHtml($id);
			
			$newText = $replaceText . mb_substr($text, $offset + $len , $endPos - ($offset + $len)) . $newText;
			$endPos = $offset;									
		}
		$newText = mb_substr($text, 0, $endPos) . $newText;

		return $newText;
	}
	
	/**
	 * 
	 */
	function parseCustomTags(&$text, $customTags = null) {
		
		// Load custom tags from database
		if ($customTags === null) {
			
			$campaignTemplateControl = BaseFactory::getCampaignTemplateControl();
			$campaignTemplateControl->retrieveForType(CP_TYPE_TAG);
			while ($campaignTemplate = $campaignTemplateControl->getNext()) {
				$customTags[$campaignTemplate->get("Name")] = $campaignTemplate->get("Body");
			}
			
			$campaignImageControl = BaseFactory::getCampaignImageControl();
			$htmlControl = CoreFactory::getHtmlControl();
			while ($campaignImage = $campaignImageControl->getNext()) {
				$customTags[$campaignImage->get("Name")] = $htmlControl->getImageBinaryWithSiteAddress($campaignImage->get("ImageId"));
			}
		}
		
		$this->count = 0;
		$found = false; 
		
		if (is_array($customTags)) {
			foreach ($customTags as $key => $value) {
				$key = "{" . $key . "}";
				// Will any replacements be made
				$found |= mb_strpos($text, $key) !== false;
				$text = str_replace($key , $value, $text);
			}
		}
		if ($found > 0) {
			$text = $this->parseCustomTags($text, $customTags);
		}
		
		return $text;
	}		
	
					
	function getSentCampaigns($minSent, $type = "") {		
		$this->clearFilter();
		$filter = CoreFactory::getFilter();
		if ($type != "") {
			$filter->addConditional($this->table, "CampaignType", $type);
		}
		$filter->addConditional($this->table, "Sent", $minSent, ">=");				
		$this->setFilter($filter);			
		return $this->getNumRows();
	}
	
	function getTotalSentRecipients($type = "") {
		$this->clearFilter();
		$filter = CoreFactory::getFilter();		
		if ($type != "") {
			$filter->addConditional($this->table, "CampaignType", $type);				
		}
		$this->setFilter($filter);						
		return $this->sumField("Sent");
	}
}
	
	/**
 * Internal Tag Class to generate content
 */
class DataHtmlGenerator {
	function getHtml($id) {
		return "-- Class $id --\n";
	}
}

class ProductDetailsHtmlGenerator extends DataHtmlGenerator {
	function getHtml($id) {
		$productControl = BaseFactory::getProductControl();
		if ($product = $productControl->item($id)) {
			return "Name: " . $product->get("Name"); 
		} else {
			return "ERROR: Invalid Product Id";
		}
	}
}

class StockItemDetailsHtmlGenerator extends DataHtmlGenerator {
	function getHtml($id) {
		$stockItemControl = BaseFactory::getStockItemControl();
		if ($stockItem = $stockItemControl->item($id)) {
			$product = $stockItem->getRelation("ProductId");
			return "Name: " . $product->get("Name") . " / " . $stockItem->get("Description"); 
		} else {
			return "ERROR: Invalid Stock Item Id";
		}
	}
}
	
class ProductImageHtmlGenerator extends DataHtmlGenerator {
	function getHtml($id) {
		$productControl = BaseFactory::getProductControl();
		$htmlControl = CoreFactory::getHtmlControl();
		if ($product = $productControl->item($id)) {
			return $htmlControl->getImageBinaryWithSiteAddress($product->get("ImageId"), $product->get("Name"), "150", "150");
		} else {
			return "ERROR: Invalid Product Id";
		}	
	}
}
	
class ProductSummaryHtmlGenerator extends DataHtmlGenerator {
	function getHtml($id) {
		$productControl = BaseFactory::getProductControl();
		if ($product = $productControl->item($id)) {
			return $product->get("Name") . "\n\n" . $product->get("Description"); 
		} else {
			return "ERROR: Invalid Product Id";
		}
	}
}

class ProductNameHtmlGenerator extends DataHtmlGenerator {
	function getHtml($id) {
		$productControl = BaseFactory::getProductControl();
		if ($product = $productControl->item($id)) {
			return $product->get("Name"); 
		} else {
			return "ERROR: Invalid Product Id";
		}
	}
}

class ProductDescriptionHtmlGenerator extends DataHtmlGenerator {
	function getHtml($id) {
		$productControl = BaseFactory::getProductControl();
		if ($product = $productControl->item($id)) {
			return $product->get("Description"); 
		} else {
			return "ERROR: Invalid Product Id";
		}
	}
}

class StockItemDescriptionHtmlGenerator extends DataHtmlGenerator {
	function getHtml($id) {
		$stockItemControl = BaseFactory::getStockItemControl();
		if ($stockItem = $stockItemControl->item($id)) {				
			return $stockItem->get("Description"); 
		} else {
			return "ERROR: Invalid Stock Item Id";
		}
	}
}
	
class StockItemPriceHtmlGenerator extends DataHtmlGenerator {
	function getHtml($id) {
		$stockItemControl = BaseFactory::getStockItemControl();
		if ($stockItem = $stockItemControl->item($id)) {				
			return $stockItem->get("Price"); 
		} else {
			return "ERROR: Invalid Stock Item Id";
		}
	}
}

class NewsSubjectHtmlGenerator extends DataHtmlGenerator {
	function getHtml($id) {
		$newsControl = BaseFactory::getNewsControl();
		if ($news = $newsControl->item($id)) {				
			return $news->get("Subject"); 
		} else {
			return "ERROR: Invalid Stock Item Id";
		}
	}
}
class NewsSummaryHtmlGenerator extends DataHtmlGenerator {
	function getHtml($id) {
		$newsControl = BaseFactory::getNewsControl();
		if ($news = $newsControl->item($id)) {				
			return $news->get("Summary"); 
		} else {
			return "ERROR: Invalid Stock Item Id";
		}
	}
}