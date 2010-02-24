<?php
/**
 * @package Base
 * @subpackage Store
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 */

/**
 * Include Data.php so that DataControl can be extended.
 */
require_once("Atrox/Core/Data/Data.php");


/**
 * E-mail templates.
 */	
define("SUB_7DAY_EMAIL", SITE_ADDRESS . "/includes/emailtemplate/sub_7day_expiry.tpl");
define("SUB_7DAY_EMAIL_PLAIN", SITE_ADDRESS . "/includes/emailtemplate/sub_7day_expiry_plain.tpl");
define("SUB_RENEWED_EMAIL", SITE_ADDRESS . "/includes/emailtemplate/sub_renewed.tpl");
define("SUB_RENEWED_EMAIL_PLAIN", SITE_ADDRESS . "/includes/emailtemplate/sub_renewed_plain.tpl");

/**
 *
 * @author Adam Forster (Clock Ltd) {@link mailto:adam.forster@clock.co.uk adam.forster@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Base
 * @subpackage Store
 */
class SubscriptionControl extends DataControl {
	var $table = "Subscriptions";
	var $key = "Id";
	var $sequence = "Subscriptions_Id_seq";
	var $defaultOrder = "LastRenewal";

	function init() {

		$this->fieldMeta["Id"] = new FieldMeta(
			"Id", "", FM_TYPE_INTEGER, null, FM_STORE_NEVER, false);

		$this->fieldMeta["MemberId"] = new FieldMeta(
			"Member Id", "", FM_TYPE_RELATION, null, FM_STORE_ALWAYS, false);

		$this->fieldMeta["MemberId"]->setRelationControl(BaseFactory::getMemberControl());

		$this->fieldMeta["ProductId"] = new FieldMeta(
			"Product Id", "", FM_TYPE_RELATION, null, FM_STORE_ALWAYS, false);

		$this->fieldMeta["ProductId"]->setRelationControl(BaseFactory::getProductControl());

		$this->fieldMeta["LastRenewal"] = new FieldMeta(
			"Last Renewal", "", FM_TYPE_DATE, null, FM_STORE_ALWAYS, false);
		
		$this->fieldMeta["LastRenewal"]->setFormatter(
			CoreFactory::getDateTimeFieldFormatter());
		
		$this->fieldMeta["DateOfExpiry"] = new FieldMeta(
			"Date of Expiry", "", FM_TYPE_DATE, null, FM_STORE_ALWAYS, false);
		
		$this->fieldMeta["DateOfExpiry"]->setFormatter(
			CoreFactory::getDateFieldFormatter());	
		
		$this->fieldMeta["DateCreated"] = new FieldMeta(
			"DateCreated", "", FM_TYPE_DATE, null, FM_STORE_NEVER, false);

		$this->fieldMeta["DateCreated"]->setFormatter(
			CoreFactory::getDateTimeFieldFormatter());		
	}
	
	function create(&$product, $member, $stockItem) {
		$subscription = $this->makeNew();
		$subscription->set("ProductId", $product->get("Id"));
		$subscription->set("MemberId", $member->get("Id"));
		$subscription->set("LastRenewal", $this->application->getCurrentDateTime());

		$expiry = date(
			"Y-m-d",
			strtotime($subscription->get("LastRenewal")
				. "+ " . $stockItem->get("SubscriptionLength") . "months")
		);
		
		$subscription->set("DateOfExpiry", $expiry);
		if ($subscription->save()) {
			return true;
		} else {
			return false;
		}
	}
	
	function retrieveActiveForProduct(&$product) {
		$filter = CoreFactory::getFilter();
		$filter->addConditional($this->table, "ProductId", $product->get("Id"));
		$filter->addConditional($this->table, "DateOfExpiry", $this->application->getCurrentDateTime(), ">");
		$this->setFilter($filter);
		$this->retrieveAll();
	}
	
	function getForLibraryItem($libraryItem) {
		$filter = CoreFactory::getFilter();
		$filter->addConditional($this->table, "MemberId", $libraryItem->get("MemberId"));
		$filter->addConditional($this->table, "ProductId", $libraryItem->get("ProductId"));
		
		$timeLower = date(
			"Y-m-d H:i:s",
			strtotime(mb_substr($libraryItem->get("DatePurchased"), 0, mb_strpos($libraryItem->get("DatePurchased"), "."))
				. "-1 minute")
		);
		
		$timeHigher = date(
			"Y-m-d H:i:s",
			strtotime(mb_substr($libraryItem->get("DatePurchased"), 0, mb_strpos($libraryItem->get("DatePurchased"), "."))
				. "+1 minute")
		);
					
		$filter->addConditional($this->table, "DateCreated", $timeLower, ">");
		$filter->addConditional($this->table, "DateCreated", $timeHigher, "<");
		$this->setFilter($filter);
		$this->retrieveAll();
		return $this->getNext();
	}
	
	function getActiveNumberForProduct(&$product) {
		$filter = CoreFactory::getFilter();
		$filter->addConditional($this->table, "ProductId", $product->get("Id"));
		$filter->addConditional($this->table, "DateOfExpiry", $this->application->getCurrentDateTime(), ">");
		$this->setFilter($filter);
		return $this->getNumRows();
	}		
	
	function retrieveWeekToExpiry() {
		$expiry = date("Y-m-d",
			strtotime($this->application->getCurrentDate() . "+ 7days"));
		
		$filter = CoreFactory::getFilter();
		$filter->addConditional($this->table, "DateOfExpiry", $expiry);
		$this->setFilter($filter);
		$this->retrieveAll();
	}
	
	function sendWeekToExpiryNotifications() {
		$this->retrieveWeekToExpiry();
		
		while($subscription = $this->getNext()) {
			$member = $subscription->getRelation("MemberId");
			$product = $subscription->getRelation("ProductId");
			
			$emailTemplate = CoreFactory::getTemplate();
			$emailTemplate->setTemplateFile(SUB_7DAY_EMAIL);
			$emailTemplate->setData($subscription);
			$emailTemplate->set("SITE_NAME", SITE_NAME);
			$emailTemplate->set("SITE_ADDRESS", SITE_ADDRESS);
			$emailTemplate->set("SITE_SUPPORT_EMAIL", SITE_SALES_EMAIL);
			$emailTemplate->set("NAME", $member->get("Alias"));
			$emailTemplate->set("SUB_NAME", $product->get("Name"));				

			$plainEmailTemplate = CoreFactory::getTemplate();
			$plainEmailTemplate->setTemplateFile(SUB_7DAY_EMAIL_PLAIN);
			$plainEmailTemplate->setData($subscription);
			$plainEmailTemplate->set("SITE_NAME", SITE_NAME);
			$plainEmailTemplate->set("SITE_ADDRESS", SITE_ADDRESS);
			$plainEmailTemplate->set("SITE_SUPPORT_EMAIL", SITE_SALES_EMAIL);
			$plainEmailTemplate->set("NAME", $member->get("Alias"));
			$plainEmailTemplate->set("SUB_NAME", $product->get("Name"));

			$email = CoreFactory::getEmail();
			$email->setTo($member->get("EmailAddress"));
			$email->setSubject("Chortle - Imminent Subscription Expiry");
			$email->setBody($emailTemplate->parseTemplate());
			$email->setPlainBody($plainEmailTemplate->parseTemplate());
			$email->setFrom(SITE_SALES_EMAIL);
			$email->sendMail();
		}
	}
	
	function renew(&$subscription, $stockItem, $address, $transaction) {
		$transactionControl = BaseFactory::getTransactionControl();
		
		$transaction->set("Amount", $stockItem->get("Price"));
		$transaction->set("AddressId", $address->get("Id"));
		$transaction->set("OrderReference", SITE_NAME . " - Subscription Renewal");
		$transaction->set("TermsAgreed", "t");
		
		if (!$transactionControl->makePayment($transaction)) {
			return false;
		}
		
		$this->updateField($subscription, "LastRenewal", $this->application->getCurrentDateTime());
		
		$expiry = date(
			"Y-m-d",
			strtotime($subscription->get("DateOfExpiry")
				. "+ " . $stockItem->get("SubscriptionLength") . "months")
		);
		
		$this->updateField($subscription, "DateOfExpiry", $expiry);
		$this->sendRenewalConfirmation($subscription);
		
		return true;
	}
	
	function sendRenewalConfirmation(&$subscription) {
		$member = $subscription->getRelation("MemberId");
		$product = $subscription->getRelation("ProductId");
		
		$emailTemplate = CoreFactory::getTemplate();
		$emailTemplate->setTemplateFile(SUB_RENEWED_EMAIL);
		$emailTemplate->setData($subscription);
		$emailTemplate->set("SITE_NAME", SITE_NAME);
		$emailTemplate->set("SITE_ADDRESS", SITE_ADDRESS);
		$emailTemplate->set("SITE_SUPPORT_EMAIL", SITE_SALES_EMAIL);
		$emailTemplate->set("NAME", $member->get("Alias"));
		$emailTemplate->set("SUB_NAME", $product->get("Name"));				

		$plainEmailTemplate = CoreFactory::getTemplate();
		$plainEmailTemplate->setTemplateFile(SUB_RENEWED_EMAIL_PLAIN);
		$plainEmailTemplate->setData($subscription);
		$plainEmailTemplate->set("SITE_NAME", SITE_NAME);
		$plainEmailTemplate->set("SITE_ADDRESS", SITE_ADDRESS);
		$plainEmailTemplate->set("SITE_SUPPORT_EMAIL", SITE_SALES_EMAIL);
		$plainEmailTemplate->set("NAME", $member->get("Alias"));
		$plainEmailTemplate->set("SUB_NAME", $product->get("Name"));

		$email = CoreFactory::getEmail();
		$email->setTo($member->get("EmailAddress"));
		$email->setSubject("Chortle - Subscription Renewed");
		$email->setBody($emailTemplate->parseTemplate());
		$email->setPlainBody($plainEmailTemplate->parseTemplate());
		$email->setFrom(SITE_SALES_EMAIL);
		$email->sendMail();
	}
}