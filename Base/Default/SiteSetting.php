<?php
/**
 * @package Base
 * @subpackage Default
 * @copyright Clock Limited 2010
 * @version 3.2
 */

/**
 * Include Data.php so that DataControl can be extended.
 */
require_once("Atrox/Core/Data/Data.php");

/**
 *
 * @author Robert Arnold (Clock Ltd) {@link mailto:robert.arnold@clock.co.uk robert.arnold@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2
 * @package Base
 * @subpackage Default
 */
 
class SiteSettingControl extends DataControl {
	var $table = "SiteSetting";
	var $key = "Id";
	var $sequence = "SiteSetting_Id_seq";
	var $defaultOrder = "Id";
	var $searchFields = array("Name", "Value");

	function init() {

		$this->fieldMeta["Id"] = new FieldMeta(
			"Id", "", FM_TYPE_INTEGER, null, FM_STORE_NEVER, false);

		$this->fieldMeta["Name"] = new FieldMeta(
			"Name", "", FM_TYPE_STRING, 100, FM_STORE_ALWAYS, false);

		$this->fieldMeta["Value"] = new FieldMeta(
			"Value", "", FM_TYPE_STRING, 100, FM_STORE_ALWAYS, false);

		$this->fieldMeta["DateModified"] = new FieldMeta(
			"Date Modified", "", FM_TYPE_DATE, null, FM_STORE_NEVER, false);

		$this->fieldMeta["DateModified"]->setFormatter(CoreFactory::getDateTimeFieldFormatter());

	}	
}