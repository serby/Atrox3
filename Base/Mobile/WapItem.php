<?php
/**
 * @package Base
 * @subpackage Mobile
 * @copyright Clock Limited 2007
 * @version 3.0 - $Revision$ - $Date$
 */

/**
 * Include Data.php so that DataControl can be extended.
 */
require_once("Atrox/Core/Data/Data.php");


define("WI_TYPE_TRUETONE", 1);
define("WI_TYPE_POLYTONE", 2);
define("WI_TYPE_PICTURE", 3);
define("WI_TYPE_VIDEO", 4);

define("WI_CHARGETYPE_25", 1);
define("WI_CHARGETYPE_50", 2);
define("WI_CHARGETYPE_150", 3);
define("WI_CHARGETYPE_300", 4);

/**
 * Wap Items are the electronic downloads available for people to downloads
 * to their phone.
 *
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @package Base
 * @subpackage Mobile
 * @copyright Clock Limited 2007
 * @version 3.0 - $Revision$ - $Date$
 */
class WapItemControl extends DataControl {
	var $table = "WapItem";
	var $key = "Id";
	var $sequence = "WapItem_Id_seq";
	var $defaultOrder = "DateCreated";
	var $searchFields = array("Name", "Code");

	var $types = array(
		WI_TYPE_TRUETONE => "True Tone",
		WI_TYPE_POLYTONE => "Poly Tone",
		WI_TYPE_PICTURE => "Picture",
		WI_TYPE_VIDEO => "Video"
	);

	var $chargeTypes = array(
		WI_CHARGETYPE_25 => "25p",
		WI_CHARGETYPE_50 => "50p",
		WI_CHARGETYPE_150 => "&pound;1.50",
		WI_CHARGETYPE_300 => "&pound;3.00"
	);

	function init() {
		$this->fieldMeta["Id"] = new FieldMeta(
			"Id", "", FM_TYPE_INTEGER, null, FM_STORE_NEVER, false);

		$this->fieldMeta["Code"] = new FieldMeta(
			"Code", "", FM_TYPE_STRING_LOWER, 50, FM_STORE_ALWAYS, false, FM_OPTIONS_UNIQUE);
			
		$this->fieldMeta["Code"]->setValidation(CoreFactory::getNotContainValidation(" "));

		$this->fieldMeta["Name"] = new FieldMeta(
			"Name", "", FM_TYPE_STRING, 50, FM_STORE_ALWAYS, false);

		$this->fieldMeta["Description"] = new FieldMeta(
			"Description", "", FM_TYPE_STRING, 1000, FM_STORE_ALWAYS, false);

		$this->fieldMeta["Description"]->setFormatter(CoreFactory::getBodyTextFormatter());

		$this->fieldMeta["SampleId"] = new FieldMeta(
			"Sample", "", FM_TYPE_BINARY, null, FM_STORE_ALWAYS, false);

		$this->fieldMeta["Item1Id"] = new FieldMeta(
			"Item 1", "", FM_TYPE_BINARY, null, FM_STORE_ALWAYS, false);

		$this->fieldMeta["Item2Id"] = new FieldMeta(
			"Item 2", "", FM_TYPE_BINARY, null, FM_STORE_ALWAYS, true);

		$this->fieldMeta["Item3Id"] = new FieldMeta(
			"Item 3", "", FM_TYPE_BINARY, null, FM_STORE_ALWAYS, true);

		$this->fieldMeta["Type"] = new FieldMeta(
			"Type", "", FM_TYPE_INTEGER, null, FM_STORE_ALWAYS, false);

		$this->fieldMeta["Type"]->setFormatter(CoreFactory::getArrayRelationFormatter($this->types, WI_TYPE_TRUETONE));

		$this->fieldMeta["ChargeType"] = new FieldMeta(
			"Charge Type", "", FM_TYPE_INTEGER, null, FM_STORE_ALWAYS, false);

		$this->fieldMeta["ChargeType"]->setFormatter(CoreFactory::getArrayRelationFormatter($this->chargeTypes, WI_TYPE_TRUETONE));
		
		$this->fieldMeta["DateCreated"] = new FieldMeta(
			"DateCreated", "", FM_TYPE_DATE, 1, FM_STORE_NEVER, true);

		$this->fieldMeta["DateCreated"]->setFormatter(CoreFactory::getDateTimeFieldFormatter());
	}

	function getTypes() {
		return $this->types;
	}

	function getChargeTypes() {
		return $this->chargeTypes;
	}

	function retrieveForType($type) {				
		$filter = CoreFactory::getFilter();			
		$filter->addConditional($this->table, "Type", $type);
		$this->setFilter($filter);
		return $this->retrieveAll();
	}
	
	function giftWapItem(&$member, $wapItem, 
		$sendEmail = false, $sendSMS = false) {	
		$wapDownloadControl = BaseFactory::getWapDownloadControl();
		$wapDownload = $wapDownloadControl->makeNew();
		$wapDownload->set("WapItemId", $wapItem->get("Id"));
		$wapDownload->set("MobileNumber", $member->get("MobileNumber"));
		$wapDownload->set("UniqueId", mb_substr(md5(time() + rand(0,1000)),0, 6));			
		if ($wapDownload->save()) {
			if ($sendEmail) {				
				$message = "You have been gifted a ringtone, this may be due to a technical issue we are trying to resolve or just to be kind!.Please try pointing your mobile phone to this address: " . $this->application->registry->get("Site/Address") . "/mobile/download.php?Id=" . $wapDownload->get("UniqueId") . " Regards " . $this->application->registry->get("EmailAddress/Support");
				// Send E-mail
				$emailTemplate = CoreFactory::getTemplate();					
				$emailTemplate->setTemplateFile(
					$this->application->registry->get("Template/Path", "/resource/template") . 
					$this->application->registry->get("Template/Email/Default/Html", "/emails/default.tpl"));
				
				$emailTemplate->set("BODY", nl2br($message));
				$emailTemplate->set("SITE_NAME", $this->application->registry->get("Name"));
				$emailTemplate->set("SITE_ADDRESS", $this->application->registry->get("Site/Address"));
				$emailTemplate->set("SITE_SUPPORT_EMAIL", $this->application->registry->get("EmailAddress/Support"));
						
				$email = CoreFactory::getEmail();
				$email->setTo($member->get("EmailAddress"));
				$email->setFrom($this->application->registry->get("EmailAddress/Support"));
				$email->setSubject($this->application->registry->get("Name"));	
				
				$email->setBody($emailTemplate->parseTemplate());
				$email->sendMail();
			}				
			if ($sendSMS) {				
				$smsControl = CoreFactory::getSmsControl();
				$smsMessage = $smsControl->makeNew();
				$smsMessage->setRecipients($member->get("MobileNumber"));
				$smsMessage->setOriginator($this->application->registry->get("Code", "CLk"));
				$smsMessage->setMessageText("You have been gifted a ringtone. Please point your phone to:" . $this->application->registry->get("Site/Address") . "/mobile/download.php?Id=" . $wapDownload->get("UniqueId"));
				$smsMessage->setOperator("");
				$smsMessage->setBillingType("ZERO");
				//print_r($smsMessage);
				$smsControl->send($smsMessage);
			}
			return true;
		}
		return false;
	} 		
}