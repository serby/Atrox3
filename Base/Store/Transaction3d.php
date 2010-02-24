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


if (!defined("TRN_FAILED")) {
	define("TRN_FAILED", 0);
}

if (!defined("TRN_SUCCESSFUL")) {
	define("TRN_SUCCESSFUL", 1);
}

// X-Pay error code for declined transactions
if (!defined("TRN_DECLINED")) {
	define("TRN_DECLINED", 2);
}

/**
 *
 * @author Edward Pearson (Clock Ltd) {@link mailto:ed.pearson@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Base
 * @subpackage Store
 */
class TransactionControl extends DataControl {
	var $table = "Transaction";
	var $key = "Id";
	var $sequence = "Transaction_Id_seq";
	var $defaultOrder = "Id";
	var $xPayControl = null;
	var $searchFields = array("TransactionReference");

	var $resultList = array(
		TRN_FAILED => "Failed",
		TRN_SUCCESSFUL => "Successful");

	function TransactionControl(&$xPayControl) {
		$this->xPayControl = $xPayControl;
		$this->DataControl();
	}

	function init() {

		$this->fieldMeta["Id"] = new FieldMeta(
			"Id", "", FM_TYPE_INTEGER, null, FM_STORE_NEVER, false);

		$this->fieldMeta["Amount"] = new FieldMeta(
			"Amount", "", FM_TYPE_CURRENCY, 10, FM_STORE_ALWAYS, false);

		$this->fieldMeta["AmountRefunded"] = new FieldMeta(
			"Amount Refunded", "", FM_TYPE_CURRENCY, 10, FM_STORE_ALWAYS, true);

		$this->fieldMeta["AddressId"] = new FieldMeta(
			"Address", "", FM_TYPE_RELATION, null, FM_STORE_ALWAYS, false);

		$this->fieldMeta["AddressId"]->setRelationControl(BaseFactory::getAddressControl());

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

		$this->fieldMeta["OrderReference"] = new FieldMeta(
			"Order Reference", "", FM_TYPE_STRING, 20, FM_STORE_ALWAYS, true);

		$this->fieldMeta["OrderReference"]->setEncoder(CoreFactory::getLeftCropEncoder(20));

		$this->fieldMeta["OrderInformation"] = new FieldMeta(
			"Order Information", "", FM_TYPE_STRING, 1000, FM_STORE_ALWAYS, true);

		$this->fieldMeta["TransactionReference"] = new FieldMeta(
			"Transaction Reference", "", FM_TYPE_STRING, 20, FM_STORE_ALWAYS, true);

		$this->fieldMeta["AuthCode"] = new FieldMeta(
			"Auth Code", "", FM_TYPE_STRING, 50, FM_STORE_ALWAYS, true);

		$this->fieldMeta["Result"] = new FieldMeta(
			"Result", "", FM_TYPE_INTEGER, null, FM_STORE_ALWAYS, true);

		$this->fieldMeta["Result"]->setFormatter(CoreFactory::getArrayRelationFormatter($this->resultList, TRN_FAILED));

		$this->fieldMeta["Message"] = new FieldMeta(
			"Message", "", FM_TYPE_STRING, 255, FM_STORE_ALWAYS, true);

		$this->fieldMeta["SecurityMessage"] = new FieldMeta(
			"Security Message", "", FM_TYPE_STRING, 30, FM_STORE_ALWAYS, true);

		$this->fieldMeta["TransactionCompletedTimestamp"] = new FieldMeta(
			"Transaction Completed Timestamp", "", FM_TYPE_DATE, null, FM_STORE_ALWAYS, true);

		$this->fieldMeta["TransactionCompletedTimestamp"]->setFormatter(CoreFactory::getDateTimeFieldFormatter());

		$this->fieldMeta["TransactionVerifier"] = new FieldMeta(
			"Transaction Verifier", "", FM_TYPE_STRING, 255, FM_STORE_ALWAYS, true);

		$this->fieldMeta["DateCreated"] = new FieldMeta(
			"DateCreated", "", FM_TYPE_DATE, null, FM_STORE_NEVER, false);

		$this->fieldMeta["DateCreated"]->setFormatter(CoreFactory::getDateTimeFieldFormatter());

		$this->fieldMeta["TermsAgreed"] = new FieldMeta(
			"Terms and Conditions", "", FM_TYPE_BOOLEAN, 1, FM_STORE_ALWAYS, false);

		$this->fieldMeta["TermsAgreed"]->setValidation(CoreFactory::getMustAgreeValidation());

		$this->fieldMeta["IpAddress"] = new FieldMeta(
			"Ip Address", "", FM_TYPE_STRING, 15, FM_STORE_ALWAYS, false);

		$this->fieldMeta["IpAddress"]->setAutoData(CoreFactory::getIpAddress());

		$this->fieldMeta["Enrolled"] = new FieldMeta(
			"Enrolled", "", FM_TYPE_STRING, 15, FM_STORE_ALWAYS, true);

		$this->fieldMeta["Html"] = new FieldMeta(
			"Html", "", FM_TYPE_STRING, 5000, FM_STORE_NEVER, true);

		$this->fieldMeta["PaRes"] = new FieldMeta(
			"PaRes", "", FM_TYPE_STRING, 1050, FM_STORE_ALWAYS, true);
			
		$this->fieldMeta["MD"] = new FieldMeta(
			"MD", "", FM_TYPE_STRING, 1050, FM_STORE_ALWAYS, true);		
	}

	function makePayment(&$transaction) {
		if ($transaction->validate()) {
			if (!$this->xPayControl->isAlive()) {
				$this->errorControl->addError("Payment service is unavailable. Please try again later.");
				$transaction->save();
				return false;
			}
							
			if ($transaction->get("Enrolled") == "") {
				$transaction->save();
				$this->application->redirect("/store/checkout/processing.php?TransactionId=" . $transaction->get("Id"));
				return false;
			}
			
			switch ($transaction->get("Enrolled")) {
				case "Y":
				case "N":
				case "U":
					$this->xPayControl->makeST3DAuthRequest($transaction);
					break;
				case "N/A":
					$this->xPayControl->makeAuthRequest($transaction);
					break;
			}
			
			if ($transaction->get("Result") == TRN_DECLINED) {
				$this->errorControl->addError("Transaction Declined. Check details and try again.");
				$transaction->save();
				return false;
			} else if ($transaction->get("Result") == 0) {
				switch($transaction->get("Message")) {
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
					case "(3100) Invalid StartDate":
						$this->errorControl->addError("Invalid Start Date");
						break;	
					case "(3100) Invalid Issue Number":
						$this->errorControl->addError("Invalid Issue Number");
						break;														
					default:
						$this->errorControl->addError("Transaction Failed. '" . $transaction->get("Message") . "'");
						break;
				}
				$transaction->save();
				return false;
			} else {
				return $transaction->save();
			}
		}
		return false;
	}
	
	function getTransactionFromMD($md) {
		$filter = $this->getFilter();
		if ($filter == null) {
			$filter = CoreFactory::getFilter();
		}
		$filter->addConditional($this->table, "MD", $md, " = ");
		$this->setFilter($filter);
		return $this->getNext();
	}

	function getResults() {
		return $this->results;
	}
}