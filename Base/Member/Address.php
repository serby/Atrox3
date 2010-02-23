<?php
/**
 * @package Base
 * @subpackage Member
 * @copyright Clock Limited 2007
 * @version 3.0 - $Revision$ - $Date$
 */

/**
 * Include Data.php so that DataControl can be extended.
 */
require_once("Atrox/3.0/Core/Data/Data.php");

/**
 *
 * @author Adam Forster (Clock Limited) {@link mailto:adam.forster@clock.co.uk adam.forster@clock.co.uk }
 * @copyright Clock Limited 2007
 * @version 3.0 - $Revision$ - $Date$
 * @package Base
 * @subpackage Member
 */
class AddressControl extends DataControl {

	var $table = "Address";
	var $key = "Id";
	var $sequence = "Address_Id_seq";
	var $defaultOrder = "LastName";
	var $searchFields = array(
		"FirstName",
		"LastName",
		"CompanyOrHouseName",
		"AddressLine1",
		"EmailAddress");

	function init() {

		$this->fieldMeta["Id"] = new FieldMeta(
			"Id", "", FM_TYPE_INTEGER, null, FM_STORE_NEVER, false);

		$this->fieldMeta["NamePrefix"] = new FieldMeta(
			"Title", "", FM_TYPE_STRING, 10, FM_STORE_ALWAYS, true);

		$this->fieldMeta["FirstName"] = new FieldMeta(
			"First Name", "", FM_TYPE_STRING, 50, FM_STORE_ALWAYS, false);

		$this->fieldMeta["LastName"] = new FieldMeta(
			"Last Name", "", FM_TYPE_STRING, 50, FM_STORE_ALWAYS, false);

		$this->fieldMeta["CompanyOrHouseName"] = new FieldMeta(
			"Company or House Name", "", FM_TYPE_STRING, 50, FM_STORE_ALWAYS, true);

		$this->fieldMeta["AddressLine1"] = new FieldMeta(
			"Address Line 1", "", FM_TYPE_STRING, 255, FM_STORE_ALWAYS, false);

		$this->fieldMeta["AddressLine2"] = new FieldMeta(
			"Address Line 2", "", FM_TYPE_STRING, 255, FM_STORE_ALWAYS, true);

		$this->fieldMeta["Town"] = new FieldMeta(
			"Town", "", FM_TYPE_STRING, 255, FM_STORE_ALWAYS, false);

		$this->fieldMeta["Region"] = new FieldMeta(
			"Region", "", FM_TYPE_STRING, 255, FM_STORE_ALWAYS, false);

		$this->fieldMeta["Country"] = new FieldMeta(
			"Country", "", FM_TYPE_STRING, 50, FM_STORE_ALWAYS, false);

		$this->fieldMeta["CountryId"] = new FieldMeta(
			"Country Id", "1", FM_TYPE_RELATION, null, FM_STORE_ALWAYS, false);

		$this->fieldMeta["CountryId"]->setRelationControl(BaseFactory::getCountryControl());

		$this->fieldMeta["Postcode"] = new FieldMeta(
			"Postcode", "", FM_TYPE_STRING, 20, FM_STORE_ALWAYS, false);

		$this->fieldMeta["TelephoneNumber"] = new FieldMeta(
			"Telephone Number", "", FM_TYPE_STRING, 20, FM_STORE_ALWAYS, true);

		$this->fieldMeta["EmailAddress"] = new FieldMeta(
			"E-mail Address", "", FM_TYPE_EMAILADDRESS, 50, FM_STORE_ALWAYS, false);

		$this->fieldMeta["Description"] = new FieldMeta(
			"Description", "", FM_TYPE_STRING, 20, FM_STORE_ALWAYS, false);
	}

	function createRegistrationAddress(&$address, $member) {
		$address->set("NamePrefix", $member->get("NamePrefix"));
		$address->set("FirstName", $member->get("FirstName"));
		$address->set("LastName", $member->get("LastName"));
		$address->set("EmailAddress", $member->get("EmailAddress"));

		if (!$address->save()) {
			return false;
		}

		return $address;
	}
		
	function afterInsert(&$dataEntity) {
		$noteControl = BaseFactory::getNoteControl();	
		$noteControl->addNote("Address: Added (Id:" . $dataEntity->get("Id") . ")", "Address", $dataEntity->get("Id"), 1, "member/address");					
	}

	function afterUpdate(&$dataEntity) {
		$noteControl = BaseFactory::getNoteControl();	
		$noteControl->addNote("Address: Edited (Id:" . $dataEntity->get("Id") . ")", "Address", $dataEntity->get("Id"), 2, "member/address");					
	}

	function afterDelete(&$dataEntity) {
		$noteControl = BaseFactory::getNoteControl();	
		$noteControl->addNote("Address: Deleted (Id:" . $dataEntity->get("Id") . ")", "Address", $dataEntity->get("Id"), 3, "member/address");		
	}
}

/**
 *
 * @author Robert Arnold (Clock Ltd) {@link mailto:robert.arnold@clock.co.uk robert.arnold@clock.co.uk }
 * @copyright Clock Ltd 2007
 * @version 3.0 - $Revision$ - $Date$
 * @package Base
 * @subpackage Member
 */
class AddressFormatter {

	function create(&$address) {
		$formattedAddress = "";

		if ($address->get("CompanyOrHouseName") != null) {
			$formattedAddress .= $address->get("CompanyOrHouseName") . "\n";
		}
		$formattedAddress .= $address->get("AddressLine1") . "\n";
		if ($address->get("AddressLine2") != null) {
			$formattedAddress .= $address->get("AddressLine2") . "\n";
		}
		$formattedAddress .= $address->get("Town") . "\n";
		$formattedAddress .= $address->get("Region") . "\n";
		$formattedAddress .= $address->get("Postcode") . "\n";
		$formattedAddress .= $address->getRelationValue("CountryId", "Name") . "\n";

		return $formattedAddress;
	}
}
