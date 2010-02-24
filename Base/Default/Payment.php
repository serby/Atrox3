<?php
/**
 * @package Base
 * @subpackage Default
 * @copyright Clock Limited 2010
 * @version 3.2
 */

/**
 * Include Data.php so that DataControl can be extended.
 * Include Xpay.php so that PaymentControl can be extended.
 */
require_once("Atrox/Core/Data/Data.php");
require_once("Atrox/Core/Payment/Xpay.php");

define("PYM_FAILED", 0);
define("PYM_SUCCESSFUL", 1);

// X-Pay error code for declined Payments
define("PYM_DECLINED", 2);

define("PYM_TYPE_CREDITS", 1);

/**
 *
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2
 * @package Base
 * @subpackage Default
 */
class PaymentControl extends DataControl {
	var $table = "Payment";
	var $key = "Id";
	var $sequence = "Payment_Id_seq";
	var $defaultOrder = "Id";
	var $xPayControl = null;
	var $searchFields = array("OrderReference");

	var $resultList = array(
		PYM_FAILED => "Failed",
		PYM_SUCCESSFUL => "Successful");
		
	var $paymentType = array(
		PYM_TYPE_CREDITS => "Credits");

	function PaymentControl(&$xPayControl) {
		$this->xPayControl = $xPayControl;
		$this->DataControl();
	}

	function init() {

		$this->fieldMeta["Id"] = new FieldMeta(
			"Id", "", FM_TYPE_INTEGER, null, FM_STORE_NEVER, false);

		$this->fieldMeta["Amount"] = new FieldMeta(
			"Amount", "", FM_TYPE_CURRENCY, 10, FM_STORE_ALWAYS, false);

		$this->fieldMeta["Type"] = new FieldMeta(
			"Address", "", FM_TYPE_RELATION, null, FM_STORE_ALWAYS, false);

		$this->fieldMeta["CardType"] = new FieldMeta(
			"Card Type", "", FM_TYPE_STRING, 10, FM_STORE_ALWAYS, false);

		$this->fieldMeta["CardNumber"] = new FieldMeta(
			"Card Number", "", FM_TYPE_STRING, 20, FM_STORE_ALWAYS, false);

		$this->fieldMeta["CardNumber"]->setValidation(CoreFactory::getMod10Validation());

		$this->fieldMeta["CardNumber"]->setFormatter(CoreFactory::getCardNumberFormatter());

		$this->fieldMeta["CardIssue"] = new FieldMeta(
			"Card Issue Number", "", FM_TYPE_INTEGER, 5, FM_STORE_ALWAYS, true);

		$this->fieldMeta["CardStartDate"] = new FieldMeta(
			"Start Date", "", FM_TYPE_STRING, 5, FM_STORE_ALWAYS, true);

		$this->fieldMeta["CardStartDate"]->setEncoder(CoreFactory::getArrayMonthYearEncoder());

		$this->fieldMeta["CardExpiryDate"] = new FieldMeta(
			"Expiry Date", "", FM_TYPE_STRING, 5, FM_STORE_ALWAYS, false);

		$this->fieldMeta["CardExpiryDate"]->setEncoder(CoreFactory::getArrayMonthYearEncoder());

		$this->fieldMeta["SecurityCode"] = new FieldMeta(
			"Security Code", "", FM_TYPE_STRING, 4, FM_STORE_ALWAYS, false);

		$this->fieldMeta["Reference"] = new FieldMeta(
			"Order Reference", "", FM_TYPE_STRING, 20, FM_STORE_ALWAYS, true);

		$this->fieldMeta["Reference"]->setEncoder(CoreFactory::getLeftCropEncoder(20));

		$this->fieldMeta["OrderInformation"] = new FieldMeta(
			"Order Information", "", FM_TYPE_STRING, 1000, FM_STORE_ALWAYS, true);

		$this->fieldMeta["OrderReference"] = new FieldMeta(
			"Payment Reference", "", FM_TYPE_STRING, 20, FM_STORE_ALWAYS, true);

		$this->fieldMeta["AuthCode"] = new FieldMeta(
			"Auth Code", "", FM_TYPE_STRING, 20, FM_STORE_ALWAYS, true);

		$this->fieldMeta["Result"] = new FieldMeta(
			"Result", "", FM_TYPE_INTEGER, null, FM_STORE_ALWAYS, true);

		$this->fieldMeta["Result"]->setFormatter(CoreFactory::getArrayRelationFormatter($this->resultList, PYM_FAILED));

		$this->fieldMeta["Message"] = new FieldMeta(
			"Message", "", FM_TYPE_STRING, 255, FM_STORE_ALWAYS, true);

		$this->fieldMeta["SecurityMessage"] = new FieldMeta(
			"Security Message", "", FM_TYPE_STRING, 30, FM_STORE_ALWAYS, true);

		$this->fieldMeta["PaymentCompletedTimestamp"] = new FieldMeta(
			"Payment Completed Timestamp", "", FM_TYPE_DATE, null, FM_STORE_ALWAYS, true);

		$this->fieldMeta["PaymentCompletedTimestamp"]->setFormatter(CoreFactory::getDateTimeFieldFormatter());

		$this->fieldMeta["PaymentVerifier"] = new FieldMeta(
			"Payment Verifier", "", FM_TYPE_STRING, 255, FM_STORE_ALWAYS, true);

		$this->fieldMeta["DateCreated"] = new FieldMeta(
			"DateCreated", "", FM_TYPE_DATE, null, FM_STORE_NEVER, false);

		$this->fieldMeta["DateCreated"]->setFormatter(CoreFactory::getDateTimeFieldFormatter());

		$this->fieldMeta["IpAddress"] = new FieldMeta(
			"Ip Address", "", FM_TYPE_STRING, 15, FM_STORE_ALWAYS, false);

		$this->fieldMeta["IpAddress"]->setAutoData(CoreFactory::getIpAddress());
		
		$this->fieldMeta["AddressId"] = new FieldMeta(
			"Address", "", FM_TYPE_RELATION, null, FM_STORE_ALWAYS, false);

		$this->fieldMeta["AddressId"]->setRelationControl(BaseFactory::getAddressControl());
	}

	function makePayment(&$payment) {

		if ($payment->validate()) {
			if (!$this->xPayControl->isAlive()) {
				$this->errorControl->addError("Payment service is unavailable. Please try again later.");
				$payment->save();
				return false;
			}

			$this->xPayControl->commit($payment);

			if ($payment->get("Result") == PYM_DECLINED) {
				$this->errorControl->addError("Payment Declined. Check details and try again.");
				$payment->save();
				return false;
			} else if ($payment->get("Result") == 0) {
				switch($payment->get("Message")) {
					case "(1000) Failed to connect to a payment gatewayjava.io.IOException: Connection to gateway failed":
						$this->errorControl->addError("Payment Service is currently unavailable. Please try again later.");
						break;
					case "(3100) Invalid Amount":
						$this->errorControl->addError("Invalid Payment Amount");
						break;
					case "(3100) Invalid ExpiryDate":
						$this->errorControl->addError("Invalid Expiry Date");
						break;
					case "(3100) Invalid CreditCardNumber":
						$this->errorControl->addError("Invalid Card Number");
						break;
					default:
						$this->errorControl->addError("Payment Failed. '" . $payment->get("Message") . "'");
						break;
				}
				$payment->save();
				return false;
			} else {
				return $payment->save();
			}
		}
		return false;
	}
	
	function getCreditAmount() {
		$filter = CoreFactory::getFilter();
		$filter->addConditional($this->table, "Type", PYM_TYPE_CREDITS);	
		$this->setFilter($filter);			
		return $this->sumField("Amount");
	}
	
	function getSendAmountByCredit($amount, $type = "Email") {			
		if ($type == "Email") {
			return ($amount * 100) / 0.5; 
		}
		if ($type == "SMS") {
			return ($amount * 100) / 0.6; 
		}			 
	}

	function getResults() {
		return $this->results;
	}
	
	function getPaymentTypes() {
		return $this->paymentType;
	}
}