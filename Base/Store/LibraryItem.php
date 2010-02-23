<?php
/**
 * @package Base
 * @subpackage Store
 * @copyright Clock Limited 2007
 * @version 3.0 - $Revision$ - $Date$
 */

/**
 * Include Data.php so that DataControl can be extended.
 */
require_once("Atrox/Core/Data/Data.php");


define("LB_DOWNLOADS", 3);

/**
 *
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2007
 * @version 3.0 - $Revision$ - $Date$
 * @package Base
 * @subpackage Store
 */
class LibraryItemControl extends DataControl {
	var $table = "LibraryItems";
	var $key = "Id";
	var $sequence = "LibraryItems_Id_seq";
	var $defaultOrder = "Id";

	function init() {

		$this->fieldMeta["Id"] = new FieldMeta(
			"Id", "", FM_TYPE_INTEGER, null, FM_STORE_NEVER, false);

		$this->fieldMeta["MemberId"] = new FieldMeta(
			"Member Id", "", FM_TYPE_RELATION, null, FM_STORE_ALWAYS, false);

		$this->fieldMeta["MemberId"]->setRelationControl(BaseFactory::getMemberControl());

		$this->fieldMeta["ProductId"] = new FieldMeta(
			"Product Id", "", FM_TYPE_RELATION, null, FM_STORE_ALWAYS, false);

		$this->fieldMeta["ProductId"]->setRelationControl(BaseFactory::getProductControl());

		$this->fieldMeta["StockItemId"] = new FieldMeta(
			"Stock Item Id", "", FM_TYPE_RELATION, null, FM_STORE_ALWAYS, false);

		$this->fieldMeta["StockItemId"]->setRelationControl(BaseFactory::getStockItemControl());

		$this->fieldMeta["OrderId"] = new FieldMeta(
			"Order Id", "", FM_TYPE_RELATION, null, FM_STORE_ALWAYS, true);

		$this->fieldMeta["OrderId"]->setRelationControl(BaseFactory::getOrderControl());

		$this->fieldMeta["DownloadsAllowed"] = new FieldMeta(
			"Downloads Allowed", "", FM_TYPE_INTEGER, null, FM_STORE_ALWAYS, false);

		$this->fieldMeta["DatePurchased"] = new FieldMeta(
			"Date Purchased", "", FM_TYPE_DATE, null, FM_STORE_NEVER, false);
		
		$this->fieldMeta["DatePurchased"]->setFormatter(
			CoreFactory::getDateTimeFieldFormatter());
			
		$this->fieldMeta["Length"] = new FieldMeta(
			"Length", "", FM_TYPE_INTEGER, null, FM_STORE_ALWAYS, true);
		
		$this->fieldMeta["UniqueId"] = new FieldMeta(
			"Unique Id", "", FM_TYPE_STRING, 255, FM_STORE_ADD, false);
			
		$this->fieldMeta["Subscription"] = new FieldMeta(
			"Subscription", "", FM_TYPE_BOOLEAN, 255, FM_STORE_ALWAYS, true);	

	}

	function retrieveForMember($member) {
		$filter = $this->getFilter();
		if ($filter == null) {
			$filter = &CoreFactory::getFilter();
		}
		$filter->addConditional($this->table, "MemberId", $member->get("Id"));
		$filter->addOrder("ProductId");
		$filter->addOrder("StockItemId");
		$this->setFilter($filter);
		$this->retrieveAll();
	}
	
	function retrieveActiveForMember($member) {
		$filter = CoreFactory::getFilter();
		$filter->addConditional($this->table, "MemberId", $member->get("Id"));
		$filter->addConditional($this->table, "DownloadsAllowed", 0, ">");
		$filter->addOrder("DatePurchased", true);
		$this->setFilter($filter);
		$this->retrieveAll();
	}
	
	function retrieveActiveByMemberForFeed($member) {
		$filter = CoreFactory::getFilter();
		$filter->addJoin($this->table, "ProductId", "Products", "Id");
		$filter->addConditional($this->table, "MemberId", $member->get("Id"));
		$filter->addConditional($this->table, "DownloadsAllowed", 0, ">");
		$filter->addOrder("DatePurchased", true);
		$this->setFilter($filter);
		$this->retrieveAll();
	}
	
	function getOldestForProduct(&$product, $member) {
		$filter = CoreFactory::getFilter();
		$filter->addConditional($this->table, "MemberId", $member->get("Id"));
		$filter->addConditional($this->table, "ProductId", $product->get("Id"));
		$filter->addOrder("DatePurchased");
		$this->setFilter($filter);
		$this->retrieveAll();
		return $this->getNext();
	}

	function retrieveForOrder($order) {
		$filter = $this->getFilter();
		if ($filter == null) {
			$filter = CoreFactory::getFilter();
		}
		$filter->addConditional($this->table, "OrderId", $order->get("Id"));
		$this->setFilter($filter);
		$this->retrieveAll();
	}

	function addFreeStockItem(&$member, $stockItem,
		$downloadsAllowed = LB_DOWNLOADS) {
		
		$subscriptionControl = BaseFactory::getSubscriptionControl();

		if (($stockItem->get("Price") == 0) ||
			($this->application->securityControl->isAllowed("Free Downloads"))) {
			$order = null;
			$product = $stockItem->getRelation("ProductId");
			if ($product->get("Type") == PCT_TYPE_AUDIOSUB) {
				$subscriptionControl->create($product, $member, $stockItem);
			}
			$this->addStockItem($member, $order, $stockItem);
			@$this->application->trackingLogControl->track("Free Download", "Download", $stockItem->get("Id"));
			return true;
		}
		return false;
	}

	function addStockItem(&$member, $order, $stockItem, 
		$downloadsAllowed = LB_DOWNLOADS, $subscription = false) {
			
		$libraryItem = $this->makeNew();
		$libraryItem->set("MemberId", $member->get("Id"));
		$libraryItem->set("StockItemId", $stockItem->get("Id"));
		$libraryItem->set("ProductId", $stockItem->get("ProductId"));
		$libraryItem->set("Length", $stockItem->get("Length"));
		$libraryItem->set("UniqueId", md5($member->get("Id") . rand()
			. $stockItem->get("Id") . time()));
		if ($order != null) {
			$libraryItem->set("OrderId", $order->get("Id"));
		}
		$libraryItem->set("DownloadsAllowed", $downloadsAllowed);
		if ($subscription) {
			$libraryItem->set("Subscription", $subscription);
		}
		if ($libraryItem->save()) {
			return true;
		} else {
			return false;
		}
	}
	
	function giftStockItem(&$member, $stockItem, 
		$sendEmail = false, $message = "") {
		$null = null;
		if ($this->addStockItem($member, $null, $stockItem)) {
			if ($sendEmail) {
				// Send E-mail
				$welcomeEmailTemplate = CoreFactory::getTemplate();
				$welcomeEmailTemplate->setTemplateFile(SITE_DEFAULTEMAIL);
				$welcomeEmailTemplate->set("BODY", nl2br($message));
				$welcomeEmailTemplate->set("SITE_NAME", SITE_NAME);
				$welcomeEmailTemplate->set("SITE_ADDRESS", SITE_ADDRESS);
				$welcomeEmailTemplate->set("SITE_SUPPORT_EMAIL", SITE_SUPPORT_EMAIL);
	
				$email = CoreFactory::getEmail();
				$email->setTo($member->get("EmailAddress"));
				$email->setFrom(SITE_SUPPORT_EMAIL);
				$email->setSubject(SITE_NAME);
				$email->setBody($welcomeEmailTemplate->parseTemplate());
				$email->sendMail();
			}
			return true;
		}
		return false;
	}
	
	function afterDelete($libraryItem) {
		$product = $libraryItem->getRelation("ProductId");
		if ($product->get("Type") == PCT_TYPE_AUDIOSUB) {
			$subscriptionControl = BaseFactory::getSubscriptionControl();
			if ($subscription = $subscriptionControl->getForLibraryItem($libraryItem)) {
				$subscriptionControl->delete($subscription->get("Id"));
			}
		}
	}		
}