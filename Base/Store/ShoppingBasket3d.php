<?php
/**
 * @package Base
 * @subpackage Store
 * @copyright Clock Limited 2007
 * @version 3.0 - $Revision$ - $Date$
 */


/**
 * Include Data.php so that DataControl can be extended.
 * Include ShoppingBasket.php so that ShoppingBasketControl can be extended.
 */
require_once("Atrox/Core/Data/Data.php");
require_once("ShoppingBasket.php");

/**
 *
 * @author Ed Pearson
 * @copyright Clock Limited 2007
 * @@version 3.0 - $Revision$ - $Date$
 * @package Base
 * @subpackage Store
 */
class CustomShoppingBasketControl extends ShoppingBasketControl {
	
	function init() {
		parent::init();
	}
	

	/**
	 * Performs checkout using a specified Transaction entity.
	 * order
	 */
	function checkout($shoppingBasket, $transaction = null) {
		if ($transaction == null) {
			$newTransaction = true;
			$transactionId = -1;
		} else {
			$newTransaction = false;			
			$transactionId = $transaction->get("Id");
		}
		
		$currencyFormatter = CoreFactory::getCurrencyFormatter();
		
		if ($shoppingBasket->get("TermsAgreed") != "t") {
			$this->errorControl->addError("You must agree to the terms and conditions");
			return false;
		}

		$orderControl = BaseFactory::getOrderControl();

		$order = $orderControl->makeNew();
		$order->set("BillingAddressId", $shoppingBasket->get("BillingAddressId"));
		
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

		if ($order->validate()) {		
			$order->save();
		}
			
		if ($shoppingBasket->get("UseCredit") == "f") {
			$transactionControl = BaseFactory::getTransactionControl();
			if ($newTransaction == true) {
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
			}				

			if ($order->validate()) {		
				if (!$transactionControl->makePayment(&$transaction)) {
					$orderControl = BaseFactory::getOrderControl();
					$orderControl->delete($order->get("Id"));
					return false;
				}
				if (!$order->save()) {
					return false;
				}
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
			if (!$order->save()) {
				return false;
			}			
			$orderControl->updateField($order, "TransactionId", $transactionId);	
			$orderControl->processShoppingBasket($order, $shoppingBasket);
			$orderControl->finalise($order);
			return $order;
		}
	}

}