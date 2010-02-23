<?php
/**
 * @package Base
 * @subpackage Store
 * @copyright Clock Limited 2007
 * @version 3.0 - $Revision$ - $Date$
 */

/**
 * Include data.php so that DataControl can be extended.
 */
require_once("Atrox/3.0/Core/Data/Data.php");


define("SBS_POSTAGE", 0.96);
define("SBS_GIFTWRAP", 3);
define("SB_VAT", 0.175);
define("SB_MAXQUANTITY", 99);

/**
 *
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2007
 * @version 3.0 - $Revision$ - $Date$
 * @package Base
 * @subpackage Store
 */
class ShoppingBasketControl extends DataControl {

	var $table = "ShoppingBasket";
	var $key = "Id";
	var $sequence = "ShoppingBasket_Id_seq";
	var $defaultOrder = "Description";

	function init() {

		$this->fieldMeta["Id"] = new FieldMeta(
			"Id", "", FM_TYPE_INTEGER, null, FM_STORE_NEVER, false);

		$this->fieldMeta["DateCreated"] = new FieldMeta(
			"DateCreated", "", FM_TYPE_DATE, null, FM_STORE_NEVER, false);

		$this->fieldMeta["DateCreated"]->setFormatter(CoreFactory::getDateTimeFieldFormatter());

		$this->fieldMeta["UniqueId"] = new FieldMeta(
			"Unique Id", "", FM_TYPE_GUID, null, FM_STORE_ALWAYS, false);

		$this->fieldMeta["TransactionId"] = new FieldMeta(
			"Transaction Id", "", FM_TYPE_RELATION, null, FM_STORE_ALWAYS, true);

		$this->fieldMeta["TransactionId"]->setRelationControl(BaseFactory::getTransactionControl());

		$this->fieldMeta["BillingAddressId"] = new FieldMeta(
			"Billing Address Id", "-1", FM_TYPE_RELATION, null, FM_STORE_ALWAYS, true);

		$this->fieldMeta["BillingAddressId"]->setRelationControl(BaseFactory::getAddressControl());

		$this->fieldMeta["DeliveryAddressId"] = new FieldMeta(
			"Delivery Address Id", "-1", FM_TYPE_RELATION, null, FM_STORE_ALWAYS, true);

		$this->fieldMeta["DeliveryAddressId"]->setRelationControl(BaseFactory::getAddressControl());

		$this->fieldMeta["AlternativeDeliveryAddress"] = new FieldMeta(
			"Alternative Delivery Address", "f", FM_TYPE_BOOLEAN, null, FM_STORE_ALWAYS, false);

		$this->fieldMeta["CountryId"] = new FieldMeta(
			"Country Id", 1, FM_TYPE_RELATION, null, FM_STORE_ALWAYS, false);

		$this->fieldMeta["CountryId"]->setRelationControl(BaseFactory::getCountryControl());

		$this->fieldMeta["GiftWrap"] = new FieldMeta(
			"Gift Wrap", "f", FM_TYPE_BOOLEAN, null, FM_STORE_ALWAYS, true);

		$this->fieldMeta["FreeDelivery"] = new FieldMeta(
			"Free Delivery", "f", FM_TYPE_BOOLEAN, null, FM_STORE_ALWAYS, true);

		$this->fieldMeta["ExtraCharge"] = new FieldMeta(
			"Extra Charge", 0, FM_TYPE_CURRENCY, null, FM_STORE_ALWAYS, true);

		$this->fieldMeta["ExtraChargeDescription"] = new FieldMeta(
			"Extra Charge Descripiton", "", FM_TYPE_STRING, null, FM_STORE_NEVER, false);

		$this->fieldMeta["CreditBought"] = new FieldMeta(
			"Credit Bought", 0, FM_TYPE_CURRENCY, null, FM_STORE_ALWAYS, false);

		$this->fieldMeta["UseCredit"] = new FieldMeta(
			"Use Credit", "t", FM_TYPE_BOOLEAN, null, FM_STORE_ALWAYS, false);

		$this->fieldMeta["VoucherCode"] = new FieldMeta(
			"Voucher Code", "", FM_TYPE_RELATION, 50, FM_STORE_ALWAYS, true);

		$this->fieldMeta["VoucherCode"]->setRelationControl(BaseFactory::getVoucherControl(), "VoucherCode");

		$this->fieldMeta["TermsAgreed"] = new FieldMeta(
			"Terms Agreed", "f", FM_TYPE_BOOLEAN, null, FM_STORE_ALWAYS, false);

		$this->fieldMeta["DownloadOnly"] = new FieldMeta(
			"Download Only", "t", FM_TYPE_BOOLEAN, null, FM_STORE_ALWAYS, false);

		$this->fieldMeta["ItemCount"] = new FieldMeta(
			"Item Count", 0, FM_TYPE_INTEGER, null, FM_STORE_ALWAYS, false);

		$this->fieldMeta["DownloadCount"] = new FieldMeta(
			"Download Count", 0, FM_TYPE_INTEGER, null, FM_STORE_ALWAYS, false);
			
		$this->fieldMeta["CardType"] = new FieldMeta(
			"Card Type", "", FM_TYPE_STRING, 10, FM_STORE_ALWAYS, true);

		$this->fieldMeta["CardNumber"] = new FieldMeta(
			"Card Number", "", FM_TYPE_STRING, 20, FM_STORE_ALWAYS, true);

		$this->fieldMeta["CardNumber"]->setValidation(CoreFactory::getMod10Validation());

		$this->fieldMeta["CardNumber"]->setFormatter(CoreFactory::getCardNumberFormatter());

		$this->fieldMeta["CardIssue"] = new FieldMeta(
			"Card Issue Number", "", FM_TYPE_INTEGER, 5, FM_STORE_ALWAYS, true);

		$this->fieldMeta["CardStartDate"] = new FieldMeta(
			"Start Date", "", FM_TYPE_STRING, 5, FM_STORE_ALWAYS, true);

		$this->fieldMeta["CardStartDate"]->setEncoder(CoreFactory::getArrayMonthYearEncoder());

		$this->fieldMeta["CardExpiryDate"] = new FieldMeta(
			"Expiry Date", "", FM_TYPE_STRING, 5, FM_STORE_ALWAYS, true);

		$this->fieldMeta["CardExpiryDate"]->setEncoder(CoreFactory::getArrayMonthYearEncoder());

		$this->fieldMeta["SecurityCode"] = new FieldMeta(
			"Security Code", "", FM_TYPE_STRING, 4, FM_STORE_ALWAYS, true);
	}

	function getCurrent() {		
		if (sizeof($_COOKIE) <= 0) {
			$this->application->errorControl->addError("Your browser is  not set to accept Cookies. This will severely affect your experience of the this site. Please consider upgrading to a browser that supports Cookies.", "Cookie");
			return false;
		}
	
		if (isset($_COOKIE["ShoppingBasketId"]) &&
			($shoppingBasket = $this->itemByField($_COOKIE["ShoppingBasketId"], "UniqueId"))) {			
		} else {		
			$shoppingBasket = $this->makeNew();
			setcookie("ShoppingBasketId", $shoppingBasket->get("UniqueId"), time() + 2592000, "/");
			$application = &CoreFactory::getApplication();
			$application->errorControl->clear();
			$shoppingBasket->set("CountryId", $application->getCountryId());
			if (!$shoppingBasket->save()) {
				trigger_error("Invalid shopping basket creation parameters: " . 
				print_r($application->errorControl->getErrors(), true));
			}
		}
		return $shoppingBasket;
	}

	function addGiftWrap(&$shoppingBasket) {
		if ($shoppingBasket->get("DownloadOnly") == "f") {
			return $this->updateField($shoppingBasket, "GiftWrap", "t");
		} else {
			return false;
		}
	}

	function dropGiftWrap(&$shoppingBasket) {
		$shoppingBasket->set("GiftWrap", "f");
		$shoppingBasket->save();
		// TODO: This needs removing
		if ($this->errorControl->hasErrors()) {
			print_r($this->errorControl->getErrors());
			exit;
		}
	}

	function addProductToShoppingBasket(&$shoppingBasket, $product) {
		$stockItemControl = BaseFactory::getStockItemControl();
		$stockItemControl->retrieveForProduct($product);

		if ($loggedOn = $this->application->securityControl->isLoggedOn()) {
			$free = $this->application->securityControl->isAllowed("Free Downloads", false);
			$libraryItemControl = BaseFactory::getLibraryItemControl();
			$memberControl = BaseFactory::getMemberControl();
			$member = $memberControl->item($this->application->securityControl->getMemberId());
		}
		
		while ($stockItem = $stockItemControl->getNext()) {			
			if ($loggedOn) {
				if (($free) || ($stockItem->get("Price") == 0)) {
					$libraryItemControl->addFreeStockItem($member, $stockItem);
				} else {
					$this->addStockItemToShoppingBasket($shoppingBasket, $stockItem);
				}
			} else {					
				if ($stockItem->get("Price") != 0) {
					$this->addStockItemToShoppingBasket($shoppingBasket, $stockItem);
				}
			}
		}
		$this->discountCheck($shoppingBasket);
	}		
	
	function hasForDrmProducts(&$shoppingBasket) {
		$shoppingBasketItemControl = BaseFactory::getShoppingBasketItemControl();
		$stockItemControl = BaseFactory::getStockItemControl();
		
		if (!$stockItemControl->isField("DRMKeyId")) {
			return false;
		}
		
		$shoppingBasketItemControl->retrieveShoppingBasket($shoppingBasket);
		
		while ($shoppingBasketItem = $shoppingBasketItemControl->getNext()) {	
			if ($shoppingBasketItem->getRelationValue("StockItemId", "DRMKeyId") != null) {
				return true;
			}
		}
		return false;
	}
	
	// Added 30/03/07 - Ed
	function removeDrmProducts(&$shoppingBasket) {
		$shoppingBasketItemControl = BaseFactory::getShoppingBasketItemControl();
		$stockItemControl = BaseFactory::getStockItemControl();

		if (!$stockItemControl->isField("DRMKeyId")) {
			return false;
		}
		$shoppingBasketItemControl->retrieveShoppingBasket($shoppingBasket);
		$ids = array();
		while ($shoppingBasketItem = $shoppingBasketItemControl->getNext()) {	
			if ($shoppingBasketItem->getRelationValue("StockItemId", "DRMKeyId") != null) {
				$ids[] = $shoppingBasketItem->get("Id");
			}
		}
		$this->deleteFromShoppingBasket($shoppingBasket, $ids);
		$shoppingBasket->save();
	}
	
	function addToShoppingBasket(&$shoppingBasket, $stockItemId, $quantity = 1) {
		$stockItemControl = BaseFactory::getStockItemControl();
		
		if (!$stockItem = $stockItemControl->item($stockItemId)) {
			$this->errorControl->addError("No such item in stock");
			return false;
		}

		if ($this->addStockItemToShoppingBasket($shoppingBasket, $stockItem, $quantity)) {
			$this->discountCheck($shoppingBasket);
			return true;
		}
		return false;
	}

	function addStockItemToShoppingBasket(&$shoppingBasket, $stockItem, $quantity = 1) {

		if (!isset($shoppingBasket) || (!($shoppingBasket instanceof DataEntity))) {
			return false;
		}		
			
		if ($quantity > SB_MAXQUANTITY) {
			return;
		}

		$product = $stockItem->getRelation("ProductId");
		$description = $product->getFormatted("Name") . " / " . 
			$stockItem->getFormatted("Description");
			
		if (!$stockItem->control->inStock($stockItem)) {
			$this->errorControl->addError("Sorry '$description' is out of stock");
			return false;
		}
		if ($product->get("Available") == 'f') {
			$this->errorControl->addError("Sorry '$description' is no longer vailable");
			return false;
		}
		
		$shoppingBasketItemControl = BaseFactory::getShoppingBasketItemControl();
		$shoppingBasketItem = $shoppingBasketItemControl->makeNew();

		$shoppingBasketItem->set("ShoppingBasketId", $shoppingBasket->get("Id"));
		$shoppingBasketItem->set("StockItemId", $stockItem->get("Id"));
		
		if ($isDownload = $product->isDownload()) {
			$quantity = 1;
		}
		
		$shoppingBasketItem->set("Quantity", $quantity);
		$this->errorControl->clear();
		if (!$shoppingBasketItem->save()) {
			$this->errorControl->clear();
			$this->errorControl->addError("Unable to add '$description' to the shopping basket.");
			return false;
		} else {
			$shoppingBasket->set("ItemCount", $shoppingBasket->get("ItemCount") + $quantity);
			if ($isDownload) {
				$shoppingBasket->set("DownloadCount", $shoppingBasket->get("DownloadCount") + $quantity);
			} else {
				$shoppingBasket->set("DownloadOnly", "f");
			}
			$shoppingBasket->save();
		}
		return true;
	}

	function discountCheck($shoppingBasket) {
								
		$itemCount = 0;
		$shoppingBasketItemControl = BaseFactory::getShoppingBasketItemControl();
		$shoppingBasketItemControl->retrieveShoppingBasket($shoppingBasket);

		$shoppingBasketProducts = array();
		$products = array();
		
		$stockItemControl = BaseFactory::getStockItemControl();
		
		// Build a list of products and stockitems in the shopping basket.
		while ($shoppingBasketItem = $shoppingBasketItemControl->getNext()) {
			if (!$stockItem = $shoppingBasketItem->getRelation("StockItemId")) {
				continue;
			}
			$stockItemControl = $shoppingBasketItem->getRelationControl("StockItemId");
			$product = $stockItem->getRelation("ProductId");
			$productControl = $stockItem->getRelationControl("ProductId");
			$productId = $product->get("Id");
			
			if ($stockItemControl->retrieveForProduct($product, 
				STOCK_TYPE_WHOLEPRODUCTDISCOUNT) <= 0) {
				continue;
			} else {
				$discountStockItem[$productId] = $stockItemControl->getNext();
			}
			
			// If the product is not already known about create a list of 
			// all stocks linked to this product
			if (!isset($shoppingBasketProducts[$productId])) {
				$stockItemControl->clearFilter();
				$stockItemControl->retrieveForProduct($product);
				$products[$productId]["Product"] = $product;
				$products[$productId]["DiscountStockItem"] = $discountStockItem;
				while ($stockItem2 = $stockItemControl->getNext()) {
					$products[$productId]["StockItems"][] = $stockItem2->get("Id");
				}
			}
			
			// Add the stockitem from the basket to the list
			$shoppingBasketProducts[$productId][] = $stockItem->get("Id");				
		}
	
		foreach ($products as $k => $v) {
			$products[$k]["StockItems"] = array_unique($products[$k]["StockItems"]);
			$shoppingBasketProducts[$k] = array_unique($shoppingBasketProducts[$k]);
			
			sort($products[$k]["StockItems"]);
			sort($shoppingBasketProducts[$k]);
			// Differences found therefore they can not have all stock items.
			if (sizeof(array_diff($products[$k]["StockItems"], $shoppingBasketProducts[$k])) > 0) {
				$shoppingBasketItemControl->removeProductDiscount($shoppingBasket, $products[$k]["Product"]);
			} else {					
				$this->addStockItemToShoppingBasket($shoppingBasket, $discountStockItem[$k]);
			}
		}			
	}
	
	
	function afterInsert(&$dataEntity) {
		$cacheControl = &CoreFactory::getCacheControl();
		$cacheControl->deleteWebPageCache("ShoppingSummary_" . 
			$dataEntity->get("UniqueId"));
	}

	function afterUpdate(&$dataEntity) {
		$this->afterInsert($dataEntity);
	}

	function afterDelete(&$dataEntity) {
		$this->afterInsert($dataEntity);
	}		
	
	
	function removeProductDiscount(&$shoppingBasket, $product) {
		
	}
		
	function deleteFromShoppingBasket(&$shoppingBasket, $shoppingBasketItemIds) {
		
		
		if ($shoppingBasket->get("Id") == "") {
			trigger_error("Unable to delete item to shopping basket");
		}
		
		$shoppingBasketItemControl = BaseFactory::getShoppingBasketItemControl();
		$shoppingBasketId = $shoppingBasket->get("Id");
		
		$deleteList = array();
		
		foreach ($shoppingBasketItemIds as $shoppingBasketItemId) {
			if ($shoppingBasketItem = $shoppingBasketItemControl->item($shoppingBasketItemId)) {
				if ($shoppingBasketItem->get("ShoppingBasketId") == $shoppingBasketId) {
					$stockItem = $shoppingBasketItem->getRelation("StockItemId");
					$product = $stockItem->getRelation("ProductId");
					if (isset($deleteList[$product->get("Id")])) {
						$shoppingBasketItemControl->delete($shoppingBasketItemId);
					} else {
						if ($product->get("WholeProductOnly") == 't') {
							$deleteList[] = $product->get("Id");
						}
						$shoppingBasketItemControl->delete($shoppingBasketItemId);
					}						
				}
			}
		}
		
		foreach ($deleteList as $productId) {
			$shoppingBasketItemControl->deleteProductFromShoppingBasket($productId);
		}
		
		$this->recalculate($shoppingBasket);
		return true;
	}
	


	function recalculate(&$shoppingBasket) {

		$downloadCount = 0;
		$itemCount = 0;
		$shoppingBasketItemControl = BaseFactory::getShoppingBasketItemControl();
		$shoppingBasketItemControl->retrieveShoppingBasket($shoppingBasket);

		// Check the status of the shopping basket to see if is only downloads
		while ($shoppingBasketItem = $shoppingBasketItemControl->getNext()) {
			if (!$stockItem = $shoppingBasketItem->getRelation("StockItemId")) {
				continue;
			}
			$product = $stockItem->getRelation("ProductId");
			if ($product->isDownload()) {
				$downloadCount++;
			}
			$itemCount += $shoppingBasketItem->get("Quantity");
		}
		if ($downloadCount == $itemCount) {
			$shoppingBasket->set("DownloadOnly", "t");
		}
		$shoppingBasket->set("ItemCount", $itemCount);
		$shoppingBasket->set("DownloadCount", $downloadCount);
		$shoppingBasket->save();
		$this->discountCheck($shoppingBasket);
	}

	function updateShoppingBasket(&$shoppingBasket, $shoppingBasketItems) {
		if ($shoppingBasket->get("Id") == "") {
			trigger_error("Unable to delete item to shopping basket");
		}
		$shoppingBasketItemControl = BaseFactory::getShoppingBasketItemControl();
		$shoppingBasketId = $shoppingBasket->get("Id");
		foreach($shoppingBasketItems as $id => $quantity) {
			if (!is_numeric($quantity)) {
				continue;
			}
			$shoppingBasketItem = $shoppingBasketItemControl->item($id);
			if ($shoppingBasketItem->get("ShoppingBasketId") == $shoppingBasketId) {
				if ($quantity <= 0) {
					$shoppingBasketItemControl->delete($id);
				} else {
					$stockItem = $shoppingBasketItem->getRelation("StockItemId");
					if ($quantity > SB_MAXQUANTITY) {
						break;
					}
					if ($quantity <= $stockItem->get("Quantity")) {
						$shoppingBasketItem->set("Quantity", $quantity);
						$shoppingBasketItem->save();
					} else {
						$product = $stockItem->getRelation("ProductId");
						$productName = $product->get("Name");

						$message = "Sorry there are not enough '" . $productName . "' to fulfill your request.";

						$this->errorControl->addError($message);
					}
				}
			}
		}
		$this->recalculate($shoppingBasket);
	}

	/**
	 * Performs checkout - First by taking payment and then constructs the
	 * order
	 */
	function checkout(&$shoppingBasket) {
		
		$currencyFormatter = CoreFactory::getCurrencyFormatter();
		
		if ($shoppingBasket->get("TermsAgreed") != "t") {
			$this->errorControl->addError("You must agree to the terms and conditions");
			return false;
		}

		$transactionId = -1;
		$orderControl = BaseFactory::getOrderControl();

		$order = $orderControl->makeNew();
		$order->set("BillingAddressId", $shoppingBasket->get("BillingAddressId"));
		//echo $shoppingBasket->getFormatted("AlternativeDeliveryAddress"); exit;
		// If there is not a alternative delivery address set it to the billing address
		
		// If there is not a alternative delivery address set it to the billing address
		if ($shoppingBasket->get("AlternativeDeliveryAddress") == "t") {
			$order->set("DeliveryAddressId", $shoppingBasket->get("DeliveryAddressId"));
		} else {
			$order->set("DeliveryAddressId", $shoppingBasket->get("BillingAddressId"));
		}
		
		$order->set("TransactionId", $transactionId);
		$order->set("VoucherCode", $shoppingBasket->get("VoucherCode"));
		$order->set("CreditBought", $shoppingBasket->get("CreditBought"));

		$billingAddress = $shoppingBasket->getRelation("BillingAddressId");

		$order->set("AlternativeDeliveryAddress", $shoppingBasket->get("AlternativeDeliveryAddress"));
		$order->set("EmailAddress", $billingAddress->get("EmailAddress"));

		if (!$order->save()) {
			return false;
		}

		if ($shoppingBasket->get("UseCredit") == "f") {
			$transactionControl = BaseFactory::getTransactionControl();
			$transaction = $transactionControl->makeNew();
			$transaction->set("AddressId", $shoppingBasket->get("BillingAddressId"));
			$transaction->set("CardType", $shoppingBasket->get("CardType"));
			$transaction->set("CardNumber", $shoppingBasket->get("CardNumber"));
			$transaction->set("CardIssue", $shoppingBasket->get("CardIssue"));
			$transaction->set("CardStartDate", $shoppingBasket->get("CardStartDate"));
			$transaction->set("CardExpiryDate", $shoppingBasket->get("CardExpiryDate"));
			$transaction->set("SecurityCode", $shoppingBasket->get("SecurityCode"));
			
			$shoppingBasketSummary = BaseFactory::getShoppingBasketSummary($shoppingBasket);
			$transaction->setWithoutFormatting("Amount", $shoppingBasketSummary->getGross());
			$transaction->set("TermsAgreed", true);
			$transaction->set("OrderReference", SITE_NAME);			
			$transaction->set("OrderInformation", $order->getFormatted("Id"));
			
			
			if (!$transactionControl->makePayment($transaction)) {
				return false;
			}
			
			if ($transaction->save()) {
				$orderControl->updateField($order, "TransactionId", $transaction->get("Id"));	
				$orderControl->processShoppingBasket($order, $shoppingBasket);
				$orderControl->finalise($order);
				return $order;
			} else {
				return false;
			}			
		} else {
			$orderControl->updateField($order, "TransactionId", $transactionId);	
			$orderControl->processShoppingBasket($order, $shoppingBasket);
			$orderControl->finalise($order);
			return $order;
		}
	}
	
	/**
	 * Validates and saves a payment for an order
	 */
	function enterPayment($data, $shoppingBasket) {		
		
		$shoppingBasket->set("UseCredit", "f");	
		// If payment by credit them exit early
		
		if (!empty($data["UseCredit"])) {
			$shoppingBasket->set("UseCredit", $data["UseCredit"]);							
			if ($data["UseCredit"] != "f") {
				if ($shoppingBasket->get("TransactionId")) {
					$transactionControl->delete($shoppingBasket->get("TransactionId"));
				}
				$shoppingBasket->save();
				return true;
			}				
		}		

		$monthYearEncoder = CoreFactory::getArrayMonthYearEncoder();
		
		if (empty($data["CardType"])) {
			$this->errorControl->addError("You must select a Card Type");
		} else {
			$shoppingBasket->set("CardType", $data["CardType"]);
		}
		
		if ((empty($data["CardNumber"])) || 
			(!@Validation::mod10($data["CardNumber"]))) {
			$this->errorControl->addError("You must enter a valid Card Number");
		} else {
			$shoppingBasket->set("CardNumber", $data["CardNumber"]);
		}			
		
		if ((is_array($data["CardStartDate"])) && ($data["CardStartDate"]["Month"] != "") && ($data["CardStartDate"]["Year"] != "")) {
			$shoppingBasket->set("CardStartDate", $monthYearEncoder->format($data["CardStartDate"]));
		}
		
		if ((!is_array($data["CardExpiryDate"])) && (count($data["CardExpiryDate"] < 2))) {
			$this->errorControl->addError("You must enter a Card Expiry Date");
		} else {
			$shoppingBasket->set("CardExpiryDate", $monthYearEncoder->format($data["CardExpiryDate"]));
		}
		if (empty($data["SecurityCode"])) {
			$this->errorControl->addError("You must enter a Security Code");
		} else {
			$shoppingBasket->set("SecurityCode", $data["SecurityCode"]);
		}
		
		if (empty($data["CardIssue"])) {
			$shoppingBasket->set("CardIssue", null);
		} else {
			$shoppingBasket->set("CardIssue", $data["CardIssue"]);
		}
		
		if (!empty($data["CreditBought"]) && ($data["CreditBought"] > 0)) {
			$shoppingBasket->set("CreditBought", $data["CreditBought"]);			
		} else {
			$shoppingBasket->set("CreditBought", 0);			
		}
		
		if ($this->errorControl->hasErrors()) { 
			return false;
		} else {	
			$shoppingBasket->save();				
			return true;
		}
	}
}

/**
 *
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2007
 * @version 3.0 - $Revision$ - $Date$
 * @package Base
 * @subpackage Store
 */
class ShoppingBasketSummary {

	var $netPostage = 0;
	var $grossPostage = 0;
	var $netSubTotal = 0;
	var $grossDispatchCost = 0;
	var $giftWrap = 0;
	var $vat = 0;
	var $gross = 0;
	var $count = 0;
	var $discount = 0;
	var $grossSubTotal = 0;
	var $netDispatchCost = 0;

	function getSubTotal() {
		return $this->grossSubTotal;
	}

	function getNetDispatchCost() {
		return $this->netDispatchCost;
	}

	function getNetSubTotal() {
		return $this->netSubTotal;
	}

	function getDispatchCost() {
		return $this->grossDispatchCost;
	}

	function getGiftWrapCost() {
		return $this->giftWrap;
	}

	function getNetGiftWrapCost() {
		return $this->netGiftWrap;
	}

	function getVat() {
		return $this->vat;
	}

	function getGross() {
		return $this->gross;
	}

	function getGrossBeforeDiscount() {
		return $this->gross + $this->discount;
	}

	function getCount() {
		return $this->count;
	}

	function getDiscount() {
		return $this->discount;
	}

	function getExtraCharge() {
		return $this->extraCharge;
	}

	function ShoppingBasketSummary(&$shoppingBasket) {
		if (!isset($shoppingBasket) || (!($shoppingBasket instanceof DataEntity))) {
			return false;
		}		
		// Get all the shopping basket items
		$sql = "SELECT \"ShoppingBasketItem\".\"Quantity\", \"StockItems\".\"Price\",
			\"StockItems\".\"VatExempt\",
			\"StockItems\".\"DispatchCost\",
			\"StockItems\".\"ProductId\",
			\"StockItems\".\"Weight\",
			\"StockItems\".\"PackagingId\"				
		FROM \"ShoppingBasketItem\"
		LEFT JOIN \"StockItems\" ON \"ShoppingBasketItem\".\"StockItemId\" = \"StockItems\".\"Id\"
		WHERE \"ShoppingBasketItem\".\"ShoppingBasketId\" = '" . $shoppingBasket->get("Id") . "'";

		$shoppingBasketControl = $shoppingBasket->getControl();
		
		// Add Debit card surcharge
		$this->extraCharge = 0;
			
		/* Not Yet implemented
		if ($transaction = $shoppingBasket->getRelation("TransactionId")) {
			switch ($transaction->get("CardType")) {
				case "Switch":
				case "Delta":
				case "Solo":				
					//$this->extraCharge = 0.5;
					break;
			}
		}
		*/

		$databaseControl = &CoreFactory::getDatabaseControl();

		$result = $databaseControl->query($sql);

		$country = $shoppingBasket->getRelation("CountryId");

		$voucherControl = BaseFactory::getVoucherControl();
		$voucher = $shoppingBasket->getRelation("VoucherCode");

		$packagingControl = BaseFactory::getPackagingControl();

		$this->giftWrap = 0;
		$vatFreeCount = 0;
		$vatExemptCountry = false;
		$totalWeight = 0;
		
		// Is this order going to a VAT exempt country
		if ($country = $shoppingBasket->getRelation("CountryId")) {
			$vatExemptCountry = $country->get("VatExempt") == "t";
		}			

		$deliveryMethod = 0;
		
		while ($row = $databaseControl->fetchRow($result)) {
			$discount = 0;

			// Adjust the price according to the Voucher
			if ($voucher) {
				if ($row["ProductId"] == $voucher->get("ProductId")) {
					$discount = $voucherControl->getDiscount($voucher,
						$row["Price"], $row["Quantity"]);
				}
			}

			$grossPrice = $row["Quantity"] * $row["Price"];
			$netPrice = $grossPrice / (1 + SB_VAT);

			// Keep track of the VAT exempt items. If the country is VAT exempt 
			// then everything is.
			if (($vatExemptCountry) || ($row["VatExempt"] == "t")) {
				$vatFreeCount += $row["Quantity"];
				$netPrice = $grossPrice;
			}

			$this->discount += $discount;
			$this->count += $row["Quantity"];
			$this->grossSubTotal += $grossPrice;
			$this->netSubTotal += $netPrice;
			
			// Calculate Packaging Cost and weight
			if ($packaging = $packagingControl->item($row["PackagingId"])) {
				$this->netDispatchCost += $packaging->get("NetPrice") * $row["Quantity"];
				$totalWeight += $packaging->get("Weight") * $row["Quantity"];
				if (($vatExemptCountry) || ($row["VatExempt"] == "t")) {
					$this->grossDispatchCost += $packaging->get("NetPrice") * $row["Quantity"];
					$netPrice = $grossPrice;
				}	else {
					$this->grossDispatchCost += round($packaging->get("NetPrice") * $row["Quantity"] * (1 + SB_VAT), 2);					
				}
			}
			$totalWeight += ($row["Weight"] * $row["Quantity"]);
		}

		if ($this->count == 0) {
			return false;
		}

		$vatPercentage = 1 - $vatFreeCount / $this->count;
		if ($shoppingBasket->get("DownloadOnly") == "t") {
			$this->netDispatchCost = 0;
		} else {
			// Add Gift Wrap Payment if need be
			if ($shoppingBasket->get("GiftWrap") == "t") {
				$this->giftWrap = SBS_GIFTWRAP * ($shoppingBasket->get("ItemCount") - $shoppingBasket->get("DownloadCount"));
			}
			
			$postageRateControl = BaseFactory::getPostageRateControl();
			if ($deliveryAddress = $shoppingBasket->getRelation("DeliveryAddressId")) {
				$deliveryCountry = $deliveryAddress->getRelation("CountryId");
				$postageRate = $postageRateControl->getPostageRate($totalWeight, $deliveryCountry->get("Zone"));
			} else {		
				$postageRate = $postageRateControl->getPostageRate($totalWeight, $country->get("Zone"));
			}
			$this->netPostage = $postageRate->get("NetPrice");

			$this->grossPostage = $this->netPostage;
			
			// echo "#" . $totalWeight . "#" . $this->netPostage . "#";

			if (!$vatExemptCountry) {
				 $this->grossPostage = $this->netPostage * $vatPercentage * (1 + SB_VAT) - ($this->netPostage * $vatPercentage) + $this->netPostage;
			}

			// Taken out by Paul Serby 2007-02-12
			// All Delivery Methods MUST include VAT if country has VAT
			// to fix VAT issue
//				if ($deliveryMethod = $postageRate->getRelation("DeliveryMethodId")) {
//				  if ($deliveryMethod->get("VatExempt") != "t") {
//						 $this->grossPostage = $this->netPostage * (1 + SB_VAT);
//						 $this->netPostage = $this->grossPostage *
//							  (1 - $vatPercentage) * (1 + SB_VAT) + ($this->grossPostage * $vatPercentage);
//				  }
//				}
								
			// Order with Sub totals over the FreeShippingThreshold get free shipping
			if (($country->get("FreeShippingThreshold") != "") &&
				($this->grossSubTotal >= $country->get("FreeShippingThreshold"))) {
				$this->netDispatchCost = 0;
			}
		}
			
			
			
		$this->netDispatchCost += $this->netPostage;
		$this->grossDispatchCost += $this->grossPostage;
		$this->netGiftWrap = ($this->giftWrap * $vatPercentage) / (1 + SB_VAT) + ($this->giftWrap * (1 - $vatPercentage));			
		$this->gross = $this->grossSubTotal + $this->grossDispatchCost + $this->giftWrap + $this->extraCharge;
		$this->vat = round($this->gross - $this->netDispatchCost - $this->netSubTotal - $this->netGiftWrap - $this->extraCharge, 2);

		if ($voucher) {
			// If there is a discount on he whole shopping basket
			if ($voucher->get("ProductId") == "") {					
				$this->discount += $voucherControl->getDiscount($voucher, $this->gross, 1);
			}
		}
		$this->gross += $this->discount + $shoppingBasket->get("CreditBought");
	}
}