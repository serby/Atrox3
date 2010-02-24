<?php
/**
 * @package Base
 * @subpackage Store
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 */

/*
 * Include Data.php so that DataControl can be extended.
 */
require_once("Atrox/Core/Data/Data.php");

/**
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Base
 * @subpackage Store
 */
class WishListItemControl extends DataControl {
	var $table = "WishListItem";
	var $key = "Id";
	var $sequence = "WishListItem_Id_seq";
	var $defaultOrder = "Id";

	var $searchFields = array("Id", "WishListId");

	function init() {

		$this->fieldMeta["Id"] = new FieldMeta(
			"Id", "", FM_TYPE_INTEGER, null, FM_STORE_NEVER, false);

		$this->fieldMeta["WishListId"] = new FieldMeta(
			"Wish List Id", "", FM_TYPE_RELATION, null, FM_STORE_ALWAYS, false);

		$this->fieldMeta["WishListId"]->setRelationControl(BaseFactory::getWishListControl());	

		$this->fieldMeta["DateCreated"] = new FieldMeta(
			"DateCreated", "", FM_TYPE_DATE, null, FM_STORE_NEVER, false);

		$this->fieldMeta["DateCreated"]->setFormatter(CoreFactory::getDateTimeFieldFormatter());	

		$this->fieldMeta["StockItemId"] = new FieldMeta(
			"Stock Item Id", "", FM_TYPE_RELATION, null, FM_STORE_ALWAYS, false);
			
		$this->fieldMeta["StockItemId"]->setRelationControl(BaseFactory::getStockItemControl());	
		
	}
	
	function retrieveWishList($wishList) {
		$filter = CoreFactory::getFilter();
		$filter->addConditional($this->table, "WishListId", $wishList->get("Id"));
		$this->setFilter($filter);
		$this->retrieveAll();
	}
	
	function getItemCount($wishList) {
		$filter = CoreFactory::getFilter();
		$filter->addConditional($this->table, "WishListId", $wishList->get("Id"));
		$this->setFilter($filter);
		$this->retrieveAll();
		return $this->getNumRows();
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