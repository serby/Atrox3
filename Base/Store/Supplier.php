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


/**
 *
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Base
 * @subpackage Store
 */
class SupplierControl extends DataControl {
	var $table = "Supplier";
	var $key = "Id";
	var $sequence = "Supplier_Id_seq";
	var $defaultOrder = "Name";
	var $searchFields = array("Name", "EmailAddress");

	function init() {
		$this->fieldMeta["Id"] = new FieldMeta(
			"Id", "", FM_TYPE_INTEGER, null, FM_STORE_NEVER, false);

		$this->fieldMeta["Name"] = new FieldMeta(
			"Name", "", FM_TYPE_STRING, 50, FM_STORE_ALWAYS, false, FM_OPTIONS_UNIQUE);

		$this->fieldMeta["AddressLine1"] = new FieldMeta(
			"Address Line 1", "", FM_TYPE_STRING, 255, FM_STORE_ALWAYS, false);

		$this->fieldMeta["AddressLine2"] = new FieldMeta(
			"Address Line 2", "", FM_TYPE_STRING, 255, FM_STORE_ALWAYS, true);

		$this->fieldMeta["Town"] = new FieldMeta(
			"Town", "", FM_TYPE_STRING, 255, FM_STORE_ALWAYS, false);

		$this->fieldMeta["Region"] = new FieldMeta(
			"County/State", "", FM_TYPE_STRING, 255, FM_STORE_ALWAYS, false);

		$this->fieldMeta["Country"] = new FieldMeta(
			"Country", "", FM_TYPE_STRING, 50, FM_STORE_ALWAYS, false);

		$this->fieldMeta["Postcode"] = new FieldMeta(
			"Postcode", "", FM_TYPE_STRING, 20, FM_STORE_ALWAYS, false);

		$this->fieldMeta["TelephoneNumber"] = new FieldMeta(
			"Telephone Number", "", FM_TYPE_STRING, 20, FM_STORE_ALWAYS, false);

		$this->fieldMeta["EmailAddress"] = new FieldMeta(
			"E-mail Address", "", FM_TYPE_EMAILADDRESS, 50, FM_STORE_ALWAYS, false);

		$this->fieldMeta["ProfitShare"] = new FieldMeta(
			"Profit Share", "0", FM_TYPE_FLOAT, 3, FM_STORE_ALWAYS, false);

		$this->fieldMeta["ShareBeforeCosts"] = new FieldMeta(
			"Share Before Costs", "", FM_TYPE_BOOLEAN, null, FM_STORE_ALWAYS, false);

		$this->fieldMeta["Notes"] = new FieldMeta(
			"Notes", "", FM_TYPE_STRING, 2000, FM_STORE_ALWAYS, true);
	}
}