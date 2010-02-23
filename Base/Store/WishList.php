<?php
/**
 * @package Base
 * @subpackage Store
 * @copyright Clock Limited 2007
 * @version 3.0 - $Revision$ - $Date$
 */

/*
 * Include Data.php so that DataControl can be extended.
 */
require_once("Atrox/3.0/Core/Data/Data.php");
	
/**
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2007
 * @version 3.0 - $Revision$ - $Date$
 * @package Base
 * @subpackage Store
 */
class WishListControl extends DataControl {
	var $table = "WishList";
	var $key = "Id";
	var $sequence = "WishList_Id_seq";
	var $defaultOrder = "Id";

	var $searchFields = array("Id", "MemberId");

	function init() {

		$this->fieldMeta["Id"] = new FieldMeta(
			"Id", "", FM_TYPE_INTEGER, null, FM_STORE_NEVER, false);

		$this->fieldMeta["MemberId"] = new FieldMeta(
			"Member", "", FM_TYPE_RELATION, null, FM_STORE_ALWAYS, true);

		$this->fieldMeta["MemberId"]->setRelationControl(BaseFactory::getMemberControl());
		$this->fieldMeta["MemberId"]->setAutoData(CoreFactory::getCurrentMember());

		$this->fieldMeta["DateCreated"] = new FieldMeta(
			"DateCreated", "", FM_TYPE_DATE, null, FM_STORE_NEVER, false);

		$this->fieldMeta["DateCreated"]->setFormatter(CoreFactory::getDateTimeFieldFormatter());
		
		$this->fieldMeta["HasBeenContacted"] = new FieldMeta(
			"Has Been Contacted", "false", FM_TYPE_BOOLEAN, null, FM_STORE_UPDATE, true);
			
		$this->fieldMeta["UniqueId"] = new FieldMeta(
			"Unique Id", "", FM_TYPE_GUID, null, FM_STORE_ALWAYS, false);
			
		$this->fieldMeta["HasBeenSubmitted"] = new FieldMeta(
			"Has been Submitted", "", FM_TYPE_BOOLEAN, null, FM_STORE_ALWAYS, true);
			
		$this->fieldMeta["EmailAddress"] = new FieldMeta(
			"E-mail Address", "", FM_TYPE_EMAILADDRESS, 255, FM_STORE_ALWAYS, true);
	}
	function retrieveSubmitted() {
		$filter = CoreFactory::getFilter();
		$filter->addConditional($this->table, "HasBeenSubmitted", "t");
		$this->setFilter($filter);
		$this->retrieveAll();
	}
	
	function getCurrent() {
		if (isset($_COOKIE["WishListId"]) && ($wishList = $this->itemByField($_COOKIE["WishListId"], "UniqueId"))) {
		} else {
			$wishList = $this->makeNew();
			if (setcookie("WishListId", $wishList->get("UniqueId"))) {
				$application = &CoreFactory::getApplication();				
				if (!$wishList->save()) {
					//trigger_error("Invalid wish list/rushes creation parameters");
					return false;
				}
			} else {
				return false;
			}
		}
		return $wishList;
	}
		
	function getSummary(&$wishList) {
		// Get all the shopping basket items
		$sql = "SELECT \"WishListItem\".\"*\", \"StockItems\".\"Price\",
			\"StockItems\".\"VatExempt\",
			\"StockItems\".\"DispatchCost\",
			\"StockItems\".\"ProductId\",
			\"StockItems\".\"Weight\",
			\"StockItems\".\"PackagingId\"				
		FROM \"WishListItem\"
		LEFT JOIN \"StockItems\" ON \"WishListItem\".\"StockItemId\" = \"StockItems\".\"Id\"
		WHERE \"WishListItem\".\"Id\" = '" . $wishList->get("Id") . "'";			
		
		$result = $this->databaseControl->query($sql);
		
	}
		
	function addProductToWhishList($wishList, $product) {
		$wishListItemControl = BaseFactory::getWishListItemControl();
		
		$stockItemControl = BaseFactory::getStockItemControl();
		$productControl = BaseFactory::getProductControl();
		$product = $productControl->item($product);
		$stockItem = $stockItemControl->retrieveForProduct($product);
		while ($stockItem = $stockItemControl->getNext()) {
			$wishListItem = $wishListItemControl->makeNew();
			$wishListItem->set("StockItemId", $stockItem->get("Id"));
			$wishListItem->set("WishListId", $wishList->get("Id"));
			$wishListItem->save();	
		}
	}
		
	/*
	function sendList($weddingList) {
		$mailingListControl = BaseFactory::getMailingListControl();
		$mailingListItemControl = BaseFactory::getMailingListItemControl();
		
		$mailingList = $mailingListControl->item($weddingList->get("MailingListId"));			
		$sent = "0";
		
		$filter = CoreFactory::getFilter();	
		$filter->addConditional($mailingListItemControl->table, "MailingListId", $mailingList->get("Id"));	
		$mailingListItemControl->setFilter($filter);
		$mailingListItemControl->retrieveAll();

		while ($mailingListItem = $mailingListItemControl->getNext()) {
			$sent++;
			// Send E-mail
			$weddingListTemplate = CoreFactory::getTemplate();
			$weddingListTemplate->setTemplateFile(WEDDINGLIST_REG_EMAIL);
			$weddingListTemplate->setData($weddingList);
			$weddingListTemplate->set("SITE_NAME", SITE_NAME);
			$weddingListTemplate->set("SITE_ADDRESS", SITE_ADDRESS);
			$weddingListTemplate->set("SITE_SUPPORT_EMAIL", SITE_SUPPORT_EMAIL);
			$weddingListTemplate->set("NAME", $mailingListItem->get("FirstName") . " " .$mailingListItem->get("LastName"));
			$weddingListTemplate->set("LIST_ADDRESS", SITE_ADDRESS . "/wedding/view_list.php?Id=" . $weddingList->get("Id"));
			$weddingListTemplate->set("LIST_CODE", $weddingList->get("Code"));

			$email = CoreFactory::getEmail();
			$email->setTo($mailingListItem->get("EmailAddress") );
			$email->setFrom(SITE_SUPPORT_EMAIL);
			$email->setSubject("Wedding List for " . SITE_NAME, SITE_SUPPORT_EMAIL);
			$email->setBody($weddingListTemplate->parseTemplate());
			$email->sendMail();
		}			
		
		return $sent;			
	}
	*/
}