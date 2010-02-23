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


define("VOUC_PERCENTAGE", 2);
define("VOUC_FIXED", 3);
define("VOUC_MAXNUMBER", 20000);

/**
 *
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Base
 * @subpackage Store
 */
class VoucherControl extends DataControl {
	var $table = "Voucher";
	var $key = "Id";
	var $sequence = "Voucher_Id_seq";
	var $defaultOrder = "DateCreated";
	var $types = array(VOUC_PERCENTAGE => "Percentage", VOUC_FIXED => "Fixed Amount");
	var $searchFields = array("VoucherCode", "Description");

	function init() {

		$this->fieldMeta["Id"] = new FieldMeta(
			"Id", "", FM_TYPE_INTEGER, null, FM_STORE_NEVER, false);

		$this->fieldMeta["VoucherCode"] = new FieldMeta(
			"Voucher Code", "", FM_TYPE_STRING_UPPER, null, FM_STORE_ALWAYS, false);

		$this->fieldMeta["DateCreated"] = new FieldMeta(
			"DateCreated", "", FM_TYPE_DATE, null, FM_STORE_NEVER, false);

		$this->fieldMeta["DateCreated"]->setFormatter(CoreFactory::getDateTimeFieldFormatter());

		$this->fieldMeta["Type"] = new FieldMeta(
			"Type", "", FM_TYPE_INTEGER, null, FM_STORE_ALWAYS, false);

		$this->fieldMeta["Type"]->setFormatter(CoreFactory::getArrayRelationFormatter($this->types, VOUC_PERCENTAGE));

		$this->fieldMeta["Amount"] = new FieldMeta(
			"Amount", "", FM_TYPE_FLOAT, null, FM_STORE_ALWAYS, true);

		$this->fieldMeta["Description"] = new FieldMeta(
			"Description", "", FM_TYPE_STRING, 4000, FM_STORE_ALWAYS, false);

		$this->fieldMeta["Description"]->setFormatter(CoreFactory::getBodyTextFormatter());

		$this->fieldMeta["Quantity"] = new FieldMeta(
			"Quantity", "", FM_TYPE_INTEGER, null, FM_STORE_ALWAYS, false);

		$this->fieldMeta["ProductId"] = new FieldMeta(
			"Product Id", "", FM_TYPE_RELATION, null, FM_STORE_ALWAYS, true);
		$this->fieldMeta["ProductId"]->setRelationControl(BaseFactory::getProductControl());

		$this->fieldMeta["ValidFromDate"] = new FieldMeta(
			"Valid From Date", $this->application->getCurrentUtcDateTime(), FM_TYPE_DATE, null, FM_STORE_ALWAYS, false);

		$this->fieldMeta["ValidFromDate"]->setFormatter(CoreFactory::getDateTimeFieldFormatter());
		$this->fieldMeta["ValidFromDate"]->setEncoder(CoreFactory::getArrayDateTimeEncoder());

		$this->fieldMeta["ExpiryDate"] = new FieldMeta(
			"Expiry Date", "", FM_TYPE_DATE, null, FM_STORE_ALWAYS, true);
			
		$this->fieldMeta["ExpiryDate"]->setFormatter(CoreFactory::getDateTimeFieldFormatter());
		$this->fieldMeta["ExpiryDate"]->setEncoder(CoreFactory::getArrayDateTimeEncoder());
		
		$this->fieldMeta["Threshold"] = new FieldMeta(
			"Minimum Spend Threshold", "", FM_TYPE_CURRENCY, null, FM_STORE_ALWAYS, true);			
	}

	function getTypeArray() {
		return $this->types;
	}

	function generate($number, $prefix, $voucherTemplate) {
		$productId = 0;
		if (isset($product)) {
			$productId = $product->get("Id");
			return;
		}
		$voucher = $this->makeNew();
		$voucher->set("Description", $voucherTemplate->get("Description"));
		$voucher->set("Quantity", $voucherTemplate->get("Quantity"));
		$voucher->set("Amount", $voucherTemplate->get("Amount"));
		$voucher->set("Threshold", $voucherTemplate->get("Threshold"));
		$voucher->set("Type", $voucherTemplate->get("Type"));
		$voucher->set("ProductId", $voucherTemplate->get("ProductId"));
		$voucher->set("ValidFromDate", $voucherTemplate->get("ValidFromDate"));
		$voucher->set("ExpiryDate", $voucherTemplate->get("ExpiryDate"));	
		
		for ($i = 0; $i < $number; $i++) {
			$voucher->set("Id", "");
			$voucher->set("VoucherCode", $prefix . mb_substr(md5(uniqid(rand(), true)), 0 ,8));
			if (!$this->quickAdd($voucher)) {
				$i--;
			}
		}
	}

	function validateVoucher($voucherCode, $currentSpendAmount = 0) {
		if ($voucher = $this->itemByField($voucherCode, "VoucherCode")) {
			if ($voucher->get("Quantity") < 1) {
				$this->errorControl->addError("This Voucher has already been used");
				return false;
			}
			if (($voucher->get("ValidFromDate") != "") && ($voucher->get("ValidFromDate") > $this->application->getCurrentDateTime())) {
				$this->errorControl->addError("This voucher is not valid until: ".
					$voucher->getFormatted("ValidFromDate"));
				return false;
			}
			if (($voucher->get("ExpiryDate") != "") && ($voucher->get("ExpiryDate") < $this->application->getCurrentDateTime())) {
				$this->errorControl->addError("This voucher expired: ".
					$voucher->getFormatted("ExpiryDate"));
				return false;
			}
			if ($voucher->get("Threshold") > $currentSpendAmount) {
				$this->errorControl->addError("To use this voucher you must spend over ".
					$voucher->getFormatted("Threshold"));
				return false;
			}
			return true;
		}
		$this->errorControl->addError("Voucher Code not accepted");
		return false;
	}

	/**
	 * Returns the given amount after being adjusted by a given voucher
	 */
	function getDiscount(&$voucher, $amount, $quantity) {
		// Only discount the Quantity number of items
		$discountQuantity = min($voucher->get("Quantity"), $quantity);
		if ($discountQuantity > 0) {
			switch ($voucher->get("Type")) {
				case VOUC_PERCENTAGE:
					return -($amount * $discountQuantity * ($voucher->get("Amount") / 100));
				case VOUC_FIXED:
					return -max(0, $discountQuantity * $voucher->get("Amount"));
			}
		}
		return $amount * $quantity;
	}

	/**
	 * Use the voucher
	 */
	function useVoucher(&$voucher, $quantity) {
		// Only discount the Quantity number of items
		$useQuantity = min($voucher->get("Quantity"), $quantity);
		$this->incrementField($voucher, "Quantity", -$useQuantity);
	}
}