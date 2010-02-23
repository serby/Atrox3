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
require_once("Atrox/3.0/Core/Data/Data.php");

/**
 *
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2007
 * @version 3.0 - $Revision$ - $Date$
 * @package Base
 * @subpackage Store
 */
class DonationControl extends DataControl {
	var $table = "Donation";
	var $key = "Id";
	var $sequence = "Donation_Id_seq";
	var $defaultOrder = "Id";

	function init() {

		$this->fieldMeta["Id"] = new FieldMeta(
			"Id", "", FM_TYPE_INTEGER, null, FM_STORE_NEVER, false);

		$this->fieldMeta["SectionId"] = new FieldMeta(
			"Section Id", "", FM_TYPE_RELATION, null, FM_STORE_ALWAYS, false);
		$this->fieldMeta["SectionId"]->setRelationControl(BaseFactory::getSectionControl());

		$this->fieldMeta["TransactionId"] = new FieldMeta(
			"Transaction Id", "", FM_TYPE_RELATION, null, FM_STORE_ALWAYS, false);
		$this->fieldMeta["TransactionId"]->setRelationControl(BaseFactory::getTransactionControl());

		$this->fieldMeta["Motivation"] = new FieldMeta(
			"Motivation", "", FM_TYPE_STRING, null, FM_STORE_ALWAYS, true);

		$this->fieldMeta["GiftAid"] = new FieldMeta(
			"Gift Aid", "t", FM_TYPE_BOOLEAN, null, FM_STORE_ALWAYS, true);
	}

	function getTotal($sectionId) {
		$this->initControl();

		$sql = "SELECT SUM(\"Transaction\" .\"Amount\") AS \"Total\"
			FROM \"$this->table\" LEFT JOIN \"Transaction\"
			ON \"Transaction\" .\"Id\" = \"$this->table\" .\"TransactionId\"
			WHERE \"SectionId\"='$sectionId'";

		/*
		if ($result = $this->databaseControl->query($sql)) {
			if ($result === null) {
				return 0;
			} else {
				if ($rowData = $this->databaseControl->fetchRow($result)) {
					return $rowData["Total"];
				} else {
					return 0;
				}
			}
		}
		*/

		$result = $this->databaseControl->query($sql);
		if ($result !== null && $rowData = $this->databaseControl->fetchRow($result)) {
			return $rowData["Total"];
		}
		return 0;
	}
}