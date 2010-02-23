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


define("REFND_FAILED", 0);
define("REFND_SUCCESSFUL", 1);

// X-Pay error code for declined transactions
define("REFND_DECLINED", 2);

/**
 * Control Object used to issue Refund
 *
 * @author Adam Forster (Clock Ltd) {@link mailto:adam.forster@clock.co.uk adam.forster@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Base
 * @subpackage Store
 */
class RefundControl extends DataControl {
	var $table = "Refund";
	var $key = "Id";
	var $sequence = "Refund_Id_seq";
	var $defaultOrder = "Id";
	var $xPayControl = null;
	var $searchFields = array("RefundReference");

	var $resultList = array(
		REFND_FAILED => "Failed",
		REFND_SUCCESSFUL => "Successful");

	function RefundControl(&$xPayControl) {
		$this->xPayControl = $xPayControl;
		$this->DataControl();
	}

	function init() {

		$this->fieldMeta["Id"] = new FieldMeta(
			"Id", "", FM_TYPE_INTEGER, null, FM_STORE_NEVER, false);

		$this->fieldMeta["Amount"] = new FieldMeta(
			"Amount", "", FM_TYPE_CURRENCY, 10, FM_STORE_ALWAYS, false);

		$this->fieldMeta["TransactionId"] = new FieldMeta(
			"Transaction", "", FM_TYPE_RELATION, null, FM_STORE_ALWAYS, false);

		$this->fieldMeta["TransactionId"]->setRelationControl(BaseFactory::getTransactionControl());

		$this->fieldMeta["TransactionReference"] = new FieldMeta(
			"Transaction Reference", "", FM_TYPE_STRING, 20, FM_STORE_ALWAYS, true);

		$this->fieldMeta["AuthCode"] = new FieldMeta(
			"Auth Code", "", FM_TYPE_STRING, 20, FM_STORE_ALWAYS, true);

		$this->fieldMeta["Result"] = new FieldMeta(
			"Result", "", FM_TYPE_INTEGER, null, FM_STORE_ALWAYS, true);

		$this->fieldMeta["Result"]->setFormatter(CoreFactory::getArrayRelationFormatter($this->resultList, REFND_FAILED));

		$this->fieldMeta["Message"] = new FieldMeta(
			"Message", "", FM_TYPE_STRING, 255, FM_STORE_ALWAYS, true);

		$this->fieldMeta["SecurityMessage"] = new FieldMeta(
			"Security Message", "", FM_TYPE_STRING, 30, FM_STORE_ALWAYS, true);

		$this->fieldMeta["MemberId"] = new FieldMeta(
			"Member", "", FM_TYPE_RELATION, null, FM_STORE_ALWAYS, true);

		$this->fieldMeta["MemberId"]->setRelationControl(BaseFactory::getMemberControl());
		$this->fieldMeta["MemberId"]->setAutoData(CoreFactory::getCurrentMember());

		$this->fieldMeta["Description"] = new FieldMeta(
			"Description", "", FM_TYPE_STRING, 5000, FM_STORE_ALWAYS, false);

		$this->fieldMeta["TransactionCompletedTimestamp"] = new FieldMeta(
			"Transaction Completed Timestamp", "", FM_TYPE_DATE, null, FM_STORE_ALWAYS, true);

		$this->fieldMeta["TransactionCompletedTimestamp"]->setFormatter(CoreFactory::getDateTimeFieldFormatter());

		$this->fieldMeta["TransactionVerifier"] = new FieldMeta(
			"Transaction Verifier", "", FM_TYPE_STRING, 255, FM_STORE_ALWAYS, true);

		$this->fieldMeta["DateCreated"] = new FieldMeta(
			"DateCreated", "", FM_TYPE_DATE, null, FM_STORE_NEVER, false);

		$this->fieldMeta["DateCreated"]->setFormatter(CoreFactory::getDateTimeFieldFormatter());
	}

	function processRefund(&$refund) {
		$transaction = $refund->getRelation("TransactionId");

		if (round($transaction->get("Amount"), 2) < ($transaction->get("AmountRefunded") + $refund->get("Amount"))) {
			$this->errorControl->addError("Total amount refunded must not exceed the original transactions amount.");
			$refund->save();
			return false;
		}

		if ($refund->validate()) {
			if (!$this->xPayControl->isAlive()) {
				$this->errorControl->addError("Payment service is unavailable. Please try again later.");
				$refund->save();
				return false;
			}
			$this->xPayControl->refund($refund);
			if ($refund->get("Result") == REFND_DECLINED) {
				$this->errorControl->addError("Refund Declined. Check details and try again.");
				$refund->save();
				return false;
			} else if ($refund->get("Result") == 0) {
				switch($refund->get("Message")) {
					case "(1000) Failed to connect to a payment gatewayjava.io.IOException: Connection to gateway failed":
						$this->errorControl->addError("Payment Service is currently unavailable. Please try again later.");
						break;
					default:
						$this->errorControl->addError("Refund Failed. '" . $refund->get("Message") . "'");
						break;
				}
				return false;
			} else {
				if ($refund->save()) {
					$transaction->set("AmountRefunded", $transaction->get("AmountRefunded") + $refund->get("Amount"));
					$transaction->save();
					return true;
				}
			}
		}
		return false;
	}

	function getResults() {
		return $this->results;
	}

	function retrieveForTransaction(&$transaction) {
		$filter = $this->getFilter();
		if ($filter == null) {
			$filter = CoreFactory::getFilter();
		}
		$filter->addConditional($this->table, "TransactionId", $transaction->get("Id"), "=");
		$this->setFilter($filter);
	}
}