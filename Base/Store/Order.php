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
	

	define("ORDER_DISP_EMAIL", SITE_ADDRESS . "/includes/emailtemplate/dispatch.php");
	define("ORDER_DISP_EMAIL_PLAIN", SITE_ADDRESS . "/includes/emailtemplate/dispatchplain.php");
	define("ORDER_INVOICE_EMAIL", SITE_ADDRESS . "/includes/emailtemplate/order.php");
	define("ORDER_INVOICE_EMAIL_PLAIN", SITE_ADDRESS . "/includes/emailtemplate/orderplain.php");
	define("ORDER_PLACED_EMAIL", SITE_ADDRESS . "/includes/emailtemplate/orderplaced.php");
	define("ORDER_PLACED_EMAIL_PLAIN", SITE_ADDRESS . "/includes/emailtemplate/orderplacedplain.php");

	define("ORD_NOTPROCESSED", 1);
	define("ORD_PROCESSED", 2);
	define("ORD_FAILED", 3);
	define("ORD_CANCELLED", 4);
	define("ORD_DISPATCHED", 5);
	define("ORD_DOWNLOADONLY", 6);	

	/**
	 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
	 * @copyright Clock Limited 2010
	 * @version 3.2 - $Revision$ - $Date$
	 * @package Base
	 * @subpackage Store
	 */
	class OrderControl extends DataControl {
		var $table = "Order";
		var $key = "Id";
		var $sequence = "Order_Id_seq";
		var $defaultOrder = "Id";

		var $status = array(
			ORD_NOTPROCESSED => "Not Processed",
			ORD_PROCESSED => "Processing",
			ORD_FAILED => "Failed",
			ORD_CANCELLED => "Cancelled",
			ORD_DISPATCHED => "Dispatched",
			ORD_DOWNLOADONLY => "Download Only"
			);

		var $searchFields = array("Id", "EmailAddress", "NetPrice");

		function init() {

			$this->fieldMeta["Id"] = new FieldMeta(
				"Id", "", FM_TYPE_INTEGER, null, FM_STORE_NEVER, false);

			$this->fieldMeta["Id"]->setFormatter(CoreFactory::getPadFormatter(8, "0"));

			$this->fieldMeta["BillingAddressId"] = new FieldMeta(
				"Billing Address Id", "", FM_TYPE_RELATION, null, FM_STORE_ALWAYS, false);

			$this->fieldMeta["BillingAddressId"]->setRelationControl(BaseFactory::getAddressControl());

			$this->fieldMeta["AlternativeDeliveryAddress"] = new FieldMeta(
				"Alternative Delivery Address", "f", FM_TYPE_BOOLEAN, null, FM_STORE_ALWAYS, false);

			$this->fieldMeta["DeliveryAddressId"] = new FieldMeta(
				"Delivery Address Id", "", FM_TYPE_RELATION, null, FM_STORE_ALWAYS, false);

			$this->fieldMeta["DeliveryAddressId"]->setRelationControl(BaseFactory::getAddressControl());

			$this->fieldMeta["TransactionId"] = new FieldMeta(
				"Transaction Id", "", FM_TYPE_RELATION, null, FM_STORE_ALWAYS, false);

			$this->fieldMeta["TransactionId"]->setRelationControl(BaseFactory::getTransactionControl());

			$this->fieldMeta["DateCreated"] = new FieldMeta(
				"DateCreated", "", FM_TYPE_DATE, null, FM_STORE_NEVER, false);

			$this->fieldMeta["DateCreated"]->setFormatter(CoreFactory::getDateTimeFieldFormatter());

			$this->fieldMeta["Status"] = new FieldMeta(
				"Status", ORD_FAILED, FM_TYPE_INTEGER, null, FM_STORE_NEVER, false);

			$this->fieldMeta["Status"]->setFormatter(
				CoreFactory::getArrayRelationFormatter($this->status, ORD_FAILED));

			$this->fieldMeta["EmailAddress"] = new FieldMeta(
				"E-mail Address", "", FM_TYPE_EMAILADDRESS, null, FM_STORE_ALWAYS, false);

			$this->fieldMeta["NetPrice"] = new FieldMeta(
				"Net Amount", 0, FM_TYPE_CURRENCY, null, FM_STORE_ALWAYS, true);

			$this->fieldMeta["Vat"] = new FieldMeta(
				"Vat", "", FM_TYPE_CURRENCY, null, FM_STORE_ALWAYS, true);

			$this->fieldMeta["DispatchCost"] = new FieldMeta(
				"DispatchCost", "", FM_TYPE_CURRENCY, null, FM_STORE_ALWAYS, true);

			$this->fieldMeta["ProductionCost"] = new FieldMeta(
				"ProductionCost", "", FM_TYPE_CURRENCY, null, FM_STORE_ALWAYS, true);

			$this->fieldMeta["PostageCost"] = new FieldMeta(
				"PostageCost", 0, FM_TYPE_CURRENCY, null, FM_STORE_ALWAYS, false);

			$this->fieldMeta["GiftWrapCost"] = new FieldMeta(
				"GiftWrapCost", "", FM_TYPE_CURRENCY, null, FM_STORE_ALWAYS, true);

			$this->fieldMeta["ExtraCharge"] = new FieldMeta(
				"Extra Charge", 0, FM_TYPE_CURRENCY, null, FM_STORE_ALWAYS, true);

			$this->fieldMeta["CreditBought"] = new FieldMeta(
				"Credit Bought", 0, FM_TYPE_CURRENCY, null, FM_STORE_ALWAYS, true);

			$this->fieldMeta["CreditUsed"] = new FieldMeta(
				"Credit Used", 0, FM_TYPE_CURRENCY, null, FM_STORE_ALWAYS, true);

			$this->fieldMeta["VoucherCode"] = new FieldMeta(
				"Voucher Code", "", FM_TYPE_RELATION, null, FM_STORE_ALWAYS, true);

			$this->fieldMeta["VoucherCode"]->setRelationControl(BaseFactory::getVoucherControl(), "VoucherCode");

			$this->fieldMeta["Discount"] = new FieldMeta(
				"Discount", 0, FM_TYPE_CURRENCY, null, FM_STORE_ALWAYS, true);

			$this->fieldMeta["GrossPrice"] = new FieldMeta(
				"Gross Price", 0, FM_TYPE_CURRENCY, null, FM_STORE_ALWAYS, true);

			$this->fieldMeta["ExtraText"] = new FieldMeta(
				"Extra Text", "", FM_TYPE_STRING, null, FM_STORE_NEVER, true);

			$this->fieldMeta["ExtraText"]->setFormatter(CoreFactory::getBodyTextFormatter());

			$this->fieldMeta["MemberId"] = new FieldMeta(
				"Member Id", "", FM_TYPE_RELATION, null, FM_STORE_ALWAYS, true);

			$this->fieldMeta["MemberId"]->setRelationControl(BaseFactory::getMemberControl());

			$this->fieldMeta["DownloadOnly"] = new FieldMeta(
				"Download Only", 'f', FM_TYPE_BOOLEAN, null, FM_STORE_ALWAYS, true);

			$this->fieldMeta["ItemCount"] = new FieldMeta(
				"Item Count", 0, FM_TYPE_INTEGER, null, FM_STORE_ALWAYS, false);

			$this->fieldMeta["DownloadCount"] = new FieldMeta(
				"Download Count", 0, FM_TYPE_INTEGER, null, FM_STORE_ALWAYS, false);
		
			$this->fieldMeta["DeliveryMethodId"] = new FieldMeta(
				"Delivery Method Id", "", FM_TYPE_RELATION, null, FM_STORE_ALWAYS, true);

			$this->fieldMeta["DeliveryMethodId"]->setRelationControl(BaseFactory::getDeliveryMethodControl());			
		}

		/**
		 * Convert a shopping basket into an order
		 */
		function processShoppingBasket(&$order, $shoppingBasket) {

			$orderId = $order->get("Id");
			$orderItemControl = BaseFactory::getOrderItemControl();
			$shoppingBasketItemControl = BaseFactory::getShoppingBasketItemControl();
			$libraryItemControl = BaseFactory::getLibraryItemControl();
			$subscriptionControl = BaseFactory::getSubscriptionControl();
			$memberControl = BaseFactory::getMemberControl();
			$packagingControl = BaseFactory::getPackagingControl();

			// Only members can make orders 
			$member = $memberControl->item($this->application->securityControl->getMemberId());
			$order->set("MemberId", $member->get("Id"));

			$stockItemControl = BaseFactory::getStockItemControl();

			// Initalize counters and totals
			$itemCount = 0;
			$vatFreeCount = 0;
			$downloadCount = 0;
			$giftWrapCount = 0;
			$totalNetSubTotal = 0;
			$totalNet = 0;
			$totalNetGiftWrap = 0;
			$totalGross = 0;
			$totalVat = 0;
			$totalNetDispatchCost = 0;
			$totalNetProductionCost = 0;
			$totalDiscount = 0;
			$credit = 0;
			$totalWeight = 0;			
			$totalNetPostage = 0;						
			$totalGrossPostage = 0;
			$deliveryMethodId = null;
			$grossDispatchCost = 0;

			// Create an array to hold low stock items
			$lowStockItems = array();

			$deliveryAddress = $order->getRelation("DeliveryAddressId");
			
			// Is this order going to a VAT exempt country
			if ($country = $shoppingBasket->getRelation("CountryId")) {
				$vatExemptCountry = $country->get("VatExempt") == "t";
			}			

			// Should there be a delivery charge?
			$freeDelivery = ($shoppingBasket->get("FreeDelivery") == 't');

			$voucherControl = BaseFactory::getVoucherControl();
			$voucher = $order->getRelation("VoucherCode");

			// Process everything that is in the shopping basket
			$shoppingBasketItemControl->retrieveShoppingBasket($shoppingBasket);
			while ($shoppingBasketItem = $shoppingBasketItemControl->getNext()) {

				// Construct a new Order Item
				$orderItem = $orderItemControl->makeNew();
				$orderItem->set("StockItemId", $shoppingBasketItem->get("StockItemId"));
				$orderItem->set("OrderId", $order->get("Id"));

				// Get the stock Item
				$stockItem = $shoppingBasketItem->getRelation("StockItemId");
				$quantity = $shoppingBasketItem->get("Quantity");
				
				if ($stockItem->get("Type") == STOCK_TYPE_NORMAL) {
					$itemCount += $quantity;
				}
				
				$orderItem->set("Quantity", $quantity);

				// Work out any order discount
				$discount = 0;

				// Adjust the price according to the Voucher
				if ($voucher) {
					if ($stockItem->get("ProductId") == $voucher->get("ProductId")) {
						$discount = $voucherControl->getDiscount($voucher, $stockItem->get("Price"), $quantity);
						$voucherControl->useVoucher($voucher, $quantity);						
					} else {
						echo "no match";
					}
				}

				$totalDiscount += $discount;
				$price = $stockItem->get("Price") * $quantity;

				// Set the Supplier split
				$product = $stockItem->getRelation("ProductId");
				$orderItem->set("SupplierId", $product->get("SupplierId"));
				$supplier = $product->getRelation("SupplierId");

				$orderItem->set("SupplierProfitShare", $supplier->get("ProfitShare"));
				$orderItem->set("ShareBeforeCosts", $supplier->get("ShareBeforeCosts"));

				// Work out the items dispatch cost
				$netDispatchCost = 0;

				if ($product->isDownload()) {
					// Don't add the discount items to the library
					if ($stockItem->get("Type") == STOCK_TYPE_NORMAL) {
						if ($product->get("Type") == PCT_TYPE_AUDIOSUB) {
							$subscriptionControl->create($product, $member, $stockItem);
						}
						$libraryItemControl->addStockItem($member, $order, $stockItem);
						// Removed as it sets the dispatch cost to 0 if a download item is added
						// $netDispatchCost = 0;
						$downloadCount++;
					}
				} else {
					// Calculate Packaging Cost and weight
					if ($packaging = $stockItem->getRelation("PackagingId")) {
						// Package cost added directly to the dispatch cost and has VAT applied in the same way 
						// as the rest of the order
						$netDispatchCost = $packaging->get("NetPrice") * $quantity;
						if (($vatExemptCountry) || ($stockItem->get("VatExempt") == "t")) {
							$grossDispatchCost += $packaging->get("NetPrice") * $quantity;
						}	else {
							$grossDispatchCost += round($packaging->get("NetPrice") * $quantity * (1 + SB_VAT), 2);					
						}
						$totalWeight += $packaging->get("Weight") * $quantity;
					}
					$totalWeight += $stockItem->get("Weight") * $quantity;
				}

				// Calculate Net Production Cost
				$totalNetProductionCost += $orderItem->setWithoutFormatting("ProductionCost", $stockItem->get("ProductionCost") * $quantity);
				
				// Handling of VAT Exempt Stock Items
				if (($vatExemptCountry) || ($stockItem->get("VatExempt") == "t")) {
					$vatFreeCount += $quantity;
					$totalNetSubTotal += $orderItem->setWithoutFormatting("NetPrice", $stockItem->get("Price") * $quantity);
					$orderItem->setWithoutFormatting("Vat", 0);
					$orderItem->setWithoutFormatting("GrossPrice", $stockItem->get("Price") * $quantity);
				} else {
					$netPrice = $stockItem->get("Price") / (1 + SB_VAT);
					$totalNetSubTotal += $orderItem->setWithoutFormatting("NetPrice", $netPrice * $quantity);
					$orderItem->setWithoutFormatting("Vat", ($stockItem->get("Price") - $netPrice) * $quantity);
					$orderItem->setWithoutFormatting("GrossPrice", $orderItem->get("NetPrice") + $orderItem->get("Vat"));					
				}
				$totalGross += $stockItem->get("Price") * $quantity;
				
				$totalNetDispatchCost += $netDispatchCost;
				$orderItem->setWithoutFormatting("DispatchCost", $netDispatchCost);
				$orderItem->setWithoutFormatting("ItemGrossPrice", $stockItem->get("Price"));
				
				// Manage the stock levels
				if ($stockItem->get("Quantity") !== "") {
					$stockItemControl->updateField($stockItem, "Quantity", $stockItem->get("Quantity") - $quantity);
				}
				
				if (!$orderItem->save()) {
					trigger_error("Unable to create order correctly: " . 
						print_r($stockItemControl->errorControl->getErrors(), true));
				}
				
				if ($stockItemControl->checkStockItemQuantity($stockItem)) {
					$lowStockItems[] = $stockItem->get("Id");
				}
			}
			
			// Send out Low Stock E-mails
			if (sizeof($lowStockItems) > 0) {
				$dispatchEmailTemplate = CoreFactory::getTemplate();
				$lowStockItems = implode("," , $lowStockItems);
				$dispatchEmailTemplate->setTemplateFile(STOCK_LOW_EMAIL . "?Items=" . $lowStockItems);
				$dispatchEmailTemplate->setData($stockItem);
				$dispatchEmailTemplate->set("SITE_NAME", SITE_NAME);
				$dispatchEmailTemplate->set("SITE_ADDRESS", SITE_ADDRESS);
				$dispatchEmailTemplate->set("SITE_SALES_EMAIL", SITE_SALES_EMAIL);

				$email = CoreFactory::getEmail();
				$email->setTo(SITE_SALES_EMAIL);
				$email->setFrom(SITE_SALES_EMAIL);
				$email->setSubject("Low Stock from " . SITE_NAME, SITE_SALES_EMAIL);
				$email->setBody($dispatchEmailTemplate->parseTemplate());
				$email->sendMail();
			}

			// Track the amount of giftwrap needed
			if ($shoppingBasket->get("GiftWrap") == "t") {
				$giftWrapCount = $itemCount - $downloadCount;
			}

			// Charge VAT at a percentage of the items elagable for VAT
			$vatPercentage = 1 - ($vatFreeCount / $itemCount);

			$totalNetGiftWrap = ((SBS_GIFTWRAP * $giftWrapCount) * $vatPercentage) / 
				(1 + SB_VAT) + ((SBS_GIFTWRAP * $giftWrapCount) * (1 - $vatPercentage));							

			$totalNet = $totalNetSubTotal + $totalNetDispatchCost + $totalNetGiftWrap;
			
			$totalGrossDispatchCost = $grossDispatchCost;

			// If there are some non-download items then added shipping
			if ($itemCount > $downloadCount) {
				
				// Is the order is big enough to warrent free delivery 
				if (($country->get("FreeShippingThreshold") != "") &&
					($totalGross >= $country->get("FreeShippingThreshold"))) {
					$totalNetDispatchCost = 0;
				} else {
	
					$postageRateControl = BaseFactory::getPostageRateControl();
					if ($deliveryAddress = $shoppingBasket->getRelation("DeliveryAddressId")) {
						$deliveryCountry = $deliveryAddress->getRelation("CountryId");
						$postageRate = $postageRateControl->getPostageRate($totalWeight, $deliveryCountry->get("Zone"));
					} else {
						$postageRate = $postageRateControl->getPostageRate($totalWeight, $country->get("Zone"));
					}
					$totalNetPostage = $postageRate->get("NetPrice");
					$totalGrossPostage = $totalNetPostage;					
					
					$deliveryMethodId = $postageRate->get("DeliveryMethodId");

					if (!$vatExemptCountry) {
						$totalGrossPostage = $totalNetPostage * $vatPercentage * (1 + SB_VAT) - ($totalNetPostage * $vatPercentage) + $totalNetPostage;
					}


//					if ($deliveryMethod = $postageRate->getRelation("DeliveryMethodId")) {				
//						if ($deliveryMethod->get("VatExempt") != "t") {
//						$totalGrossPostage = $totalNetPostage * (1 + SB_VAT);
//						$totalNetPostage = ($totalGrossPostage * $vatPercentage) / (1 + SB_VAT) + ($totalGrossPostage * (1 - $vatPercentage));						
//						}
//					}
				}
			}
			
			$totalGross += $totalGrossDispatchCost + $totalGrossPostage;
			$totalNetDispatchCost += $totalNetPostage;
			$totalNet += $totalNetPostage;


			// Adjust the price according to the Voucher
			if ($voucher) {
				if ($voucher->get("ProductId") == "") {
					$totalDiscount += $voucherControl->getDiscount($voucher, $totalGross, 1);
					// Make sure the discount is not larger than the gross price
					$totalDiscount = min($totalDiscount, $totalGross);
					$voucherControl->useVoucher($voucher, 1);
				}
			}
			$totalVat = $totalGross - $totalNet;
			$totalGross += $totalDiscount;

			echo "Net Postage: $totalNetPostage\n";
			echo "Gross Postage: $totalGrossPostage\n";		
			echo "Net Dispatch Cost: $totalNetDispatchCost\n";
			echo "Gross Dispatch Cost: $totalGrossPostage\n";			
			echo "VAT: $totalVat\n";						
			//exit;
			// Sort out credit
			$application = &CoreFactory::getApplication();
			$memberControl = BaseFactory::getMemberControl();
			$member = $memberControl->item($application->securityControl->getMemberId());
			if (($member) && ($shoppingBasket->get("UseCredit") == "t")) {
				$credit = $member->get("PrepaidCredit");
				if ($credit >= $totalGross) {
					$memberControl->updateField($member, "PrepaidCredit", $credit - $totalGross);
					$order->set("CreditUsed", $totalGross);
				} else {
					return false;
				}
			} else if (($member) && ($shoppingBasket->get("UseCredit") == "f") &&
				($shoppingBasket->get("CreditBought") > 0)) {
					$memberControl->incrementField($member, "PrepaidCredit", $shoppingBasket->get("CreditBought"));
					$totalGross += $shoppingBasket->get("CreditBought");
			}

			$order->setWithoutFormatting("ItemCount", $itemCount);
			$order->setWithoutFormatting("DownloadCount", $downloadCount);
			$order->setWithoutFormatting("NetPrice", $totalNetSubTotal);
			$order->setWithoutFormatting("DispatchCost", $totalNetDispatchCost);
			$order->setWithoutFormatting("ProductionCost", $totalNetProductionCost);
			$order->setWithoutFormatting("PostageCost", $totalGrossPostage);			
			$order->setWithoutFormatting("Discount", $totalDiscount);
			$order->setWithoutFormatting("GrossPrice", $totalGross);
			$order->setWithoutFormatting("GiftWrapCost", $totalNetGiftWrap);
			$order->setWithoutFormatting("Vat", $totalVat);
			$order->setWithoutFormatting("DeliveryMethodId", $deliveryMethodId);
			$order->setWithoutFormatting("DownloadOnly", ($itemCount == $downloadCount ?  "t" : "f"));

			// Once the order is made clean up shopping basket
			// and duplicate addresses if the order isn't a download only order.
			$deliveryAddress = $order->getRelation("DeliveryAddressId");
			$billingAddress = $order->getRelation("BillingAddressId");		

			$addressControl = $billingAddress->getControl();

			// Only create one copy if they are the same address
			if ($billingAddress->get("Id") == $deliveryAddress->get("Id")) {
				$billingAddressId = $addressControl->add($billingAddress);
				$deliveryAddressId = $billingAddressId;
			} else {
				$billingAddressId = $addressControl->add($billingAddress);
				$deliveryAddressId = $addressControl->add($deliveryAddress);
			}

			$order->set("BillingAddressId", $billingAddressId);
			$order->set("DeliveryAddressId", $deliveryAddressId);

			// If the order was not made on credit.
			if ($order->get("TransactionId") != -1) {
				// Set the transaction address to the copy of the billing addresses
				$transaction = $order->getRelation("TransactionId");
				$transaction->set("AddressId", $billingAddressId);
				$transaction->save();
			}
			$order->save();
			$shoppingBasketControl = $shoppingBasket->getControl();
			$shoppingBasketControl->delete($shoppingBasket->get("Id"));
		}

		function getStatusArray(){
			return $this->status;
		}

		function setStatus($order, $status) {
			$currentStatus = $order->get("Status");

			$this->updateField($order, "Status", $status);

			if ($currentStatus != $status) {

				$orderNoteControl = BaseFactory::getOrderNoteControl();
				$orderNote = $orderNoteControl->makeNew();

				$orderNote->set("OrderId", $order->get("Id"));
				$orderNote->set("Status", $status);

				switch ($status) {
					case ORD_NOTPROCESSED:
						$orderNote->set("Description", "Placed");
						break;
					case ORD_PROCESSED:
						$orderNote->set("Description", "Processing");
						break;
					case ORD_CANCELLED:
						$orderNote->set("Description", "Cancelled");
						break;
					case ORD_FAILED:
						$orderNote->set("Description", "Failed");
						break;
					case ORD_DISPATCHED:
						$orderNote->set("Description", "Dispatched");
						break;
					case ORD_DOWNLOADONLY:
						$orderNote->set("Description", "Download Only");
						break;						
				}
				$orderNote->save();
			}
		}

		function finalise($order) {

			$this->setStatus($order, ORD_NOTPROCESSED);

			$emailTemplate = CoreFactory::getTemplate();
			$emailTemplate->setTemplateFile(ORDER_INVOICE_EMAIL . "?OrderId=" . $order->get("Id"));
			$emailTemplate->set("SITE_NAME", SITE_NAME);
			$emailTemplate->set("SITE_ADDRESS", SITE_ADDRESS);
			$emailTemplate->set("SITE_SALES_EMAIL", SITE_SALES_EMAIL);

			$plainEmailTemplate = CoreFactory::getTemplate();
			$plainEmailTemplate->setTemplateFile(ORDER_INVOICE_EMAIL_PLAIN . "?OrderId=" . $order->get("Id"));
			$plainEmailTemplate->set("SITE_NAME", SITE_NAME);
			$plainEmailTemplate->set("SITE_ADDRESS", SITE_ADDRESS);
			$plainEmailTemplate->set("SITE_SALES_EMAIL", SITE_SALES_EMAIL);

			$email = CoreFactory::getEmail();
			$email->setTo($order->get("EmailAddress"));
			$email->setSubject("Your order from " . SITE_NAME . " - Order Number:" .
				$order->getFormatted("Id"));
			$email->setBody($emailTemplate->parseTemplate());
			$email->setPlainBody($plainEmailTemplate->parseTemplate());
			$email->setFrom(SITE_SALES_EMAIL);
			$email->sendMail();

			$emailTemplate = CoreFactory::getTemplate();
			$emailTemplate->setTemplateFile(ORDER_PLACED_EMAIL . "?OrderId=" . $order->get("Id"));
			$emailTemplate->set("SITE_NAME", SITE_NAME);
			$emailTemplate->set("SITE_ADDRESS", SITE_ADDRESS);
			$emailTemplate->set("SITE_SALES_EMAIL", SITE_SALES_EMAIL);

			$plainEmailTemplate = CoreFactory::getTemplate();
			$plainEmailTemplate->setTemplateFile(ORDER_PLACED_EMAIL_PLAIN . "?OrderId=" . $order->get("Id"));
			$plainEmailTemplate->set("SITE_NAME", SITE_NAME);
			$plainEmailTemplate->set("SITE_ADDRESS", SITE_ADDRESS);
			$plainEmailTemplate->set("SITE_SALES_EMAIL", SITE_SALES_EMAIL);

			$email = CoreFactory::getEmail();
			$email->setTo(SITE_SALES_EMAIL);
			$email->setSubject("New Order Placed - Order Number:" .
				$order->getFormatted("Id") . " from " . SITE_NAME, SITE_SALES_EMAIL);
			$email->setBody($emailTemplate->parseTemplate());
			$email->setPlainBody($plainEmailTemplate->parseTemplate());
			$email->setFrom(SITE_SUPPORT_EMAIL);
			$email->sendMail();
			
			
			if ($order->get("DownloadOnly") == "t") {
				$this->setStatus($order, ORD_DOWNLOADONLY);
				return true;
			}
		}

		function dispatch($order) {

			// Only dispatch processed orders
			if ($order->get("Status") == ORD_PROCESSED) {

				$this->setStatus($order, ORD_DISPATCHED);

				$emailTemplate = CoreFactory::getTemplate();
				$emailTemplate->setTemplateFile(ORDER_DISP_EMAIL . "?OrderId=" . $order->get("Id"));
				$emailTemplate->setData($order);
				$emailTemplate->set("SITE_NAME", SITE_NAME);
				$emailTemplate->set("SITE_ADDRESS", SITE_ADDRESS);
				$emailTemplate->set("SITE_SUPPORT_EMAIL", SITE_SALES_EMAIL);
				$emailTemplate->set("SITE_SALES_EMAIL", SITE_SALES_EMAIL);

				$plainEmailTemplate = CoreFactory::getTemplate();
				$plainEmailTemplate->setTemplateFile(ORDER_DISP_EMAIL_PLAIN . "?OrderId=" . $order->get("Id"));
				$plainEmailTemplate->setData($order);
				$plainEmailTemplate->set("SITE_NAME", SITE_NAME);
				$plainEmailTemplate->set("SITE_ADDRESS", SITE_ADDRESS);
				$plainEmailTemplate->set("SITE_SUPPORT_EMAIL", SITE_SALES_EMAIL);
				$plainEmailTemplate->set("SITE_SALES_EMAIL", SITE_SALES_EMAIL);				

				$email = CoreFactory::getEmail();
				$email->setTo($order->get("EmailAddress"));
				$email->setSubject("Dispatch for Order Number: " . $order->getFormatted("Id") . " from " . SITE_NAME, SITE_SUPPORT_EMAIL);
				$email->setBody($emailTemplate->parseTemplate());
				$email->setPlainBody($plainEmailTemplate->parseTemplate());
				$email->setFrom(SITE_SALES_EMAIL);
				$email->sendMail();

				return true;
			} else {
				$this->errorControl->addError("Unable to dispatch order. You can only
					dispatch an order once the invoice has been printed.");
				return false;
			}
		}

		function dispatchMany($ids) {
			if (!is_array($ids)) {
				return false;
			}
			foreach ($ids as $id) {
				if ($order = $this->item($id)) {
					$this->dispatch($order);
				}
			}
			return true;
		}

		function cancel($order) {
			if ($order->get("Status") != ORD_CANCELLED) {

				$this->setStatus($order, ORD_CANCELLED);

				// Return stock
				$orderItemControl = BaseFactory::getOrderItemControl();
				$orderItemControl->retrieveForOrder($order->get("Id"));

				while ($orderItem = $orderItemControl->getNext()) {
					$stockItem = $orderItem->getRelation("StockItemId");
					$product = $stockItem->getRelation("ProductId");
					if (!$product->isDownload()) {
						$newQuantity = $stockItem->get("Quantity") + $orderItem->get("Quantity");
						$stockItem->set("Quantity", $newQuantity);
						$stockItem->save();
					}					
				}
			} else {
				$orderNoteControl = BaseFactory::getOrderNoteControl();
				$orderNote = $orderNoteControl->makeNew();
				$orderNote->set("OrderId", $order->get("Id"));
				$orderNote->set("Description", "Unable to Cancel Order");
				$orderNote->set("Status", $order->get("Status"));
				$this->errorControl->addError("Unable to cancel order. This order has already been cancelled");
				$orderNote->save();
			}
		}

		function getGrossPrice($order) {
			return $order->get("NetPrice") + $order->get("Vat") + $order->get("DispatchCost") + $order->get("GiftWrapCost");
		}

		function getDateRange($rangeType, $limiter, $date) {

			if($date == null){
				$date = time();
			}

			if($rangeType == DTR_WEEK){
				$todaysDay = date("w");
				$daysToStart = 0 - $todaysDay;
				$daysToEnd = 6 - $todaysDay;
			} else if($rangeType == DTR_MONTH){
				$todaysDay = date("d");
				$daysInMonth = date("t", $date);
				$daysToStart = 0 - ($todaysDay-1);
				$daysToEnd = $daysInMonth - $todaysDay;
			}

			if($limiter == "Start") {
				$startTimestamp = $date + ($daysToStart * 86400);
				$startDate = date('d', $startTimestamp);
				return $startDate;
			} else if($limiter == "End") {
				$endTimestamp = $date + ($daysToEnd * 86400);
				$endDate = date('d', $endTimestamp);
				return $endDate;
			}
		}

		function afterInsert(&$dataEntity) {
			$cacheControl = &CoreFactory::getCacheControl();
			$cacheControl->deleteWebPageCache("TopProducts");
		}

		function afterUpdate(&$dataEntity) {
			$this->afterInsert($dataEntity);
		}

		function afterDelete(&$dataEntity) {
			$this->afterInsert($dataEntity);
		}
		
		function getMemberPurchases($memberId) {					
			$filter = CoreFactory::getFilter();
			$filter->addConditional($this->table, "MemberId", $memberId);						
			$this->setFilter($filter);
			return $this->retrieveAll();			
		}
		
		function getTotalSpent($member) {
			$filter = CoreFactory::getFilter();
			$filter->addConditional($this->table, "MemberId", $member->get("Id"));
			$this->setFilter($filter);
			return $this->sumField("GrossPrice");
		}
		
		function getPaymentMethod($order) {
			if ($order->get("CreditUsed") > 0) {
				return "Prepaid Credit";
			} else if ($transaction = $order->getRelation("TransactionId")) {
				return $transaction->getFormatted("CardType");
			}
			return "Unknown";
		}
	}