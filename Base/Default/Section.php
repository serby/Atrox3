<?php
/**
 * @package Base
 * @subpackage Default
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
 * @subpackage Default
 */
class SectionControl extends DataControl {
	var $table = "Section";
	var $key = "Id";
	var $sequence = "Section_Id_seq";
	var $defaultOrder = "Title";
	var $searchFields = array("Title");

	function init() {
		$this->fieldMeta["Id"] = new FieldMeta(
			"Id", "", FM_TYPE_INTEGER, null, FM_STORE_NEVER, false);

		$this->fieldMeta["Title"] = new FieldMeta(
			"Title", "", FM_TYPE_STRING, 255, FM_STORE_ALWAYS, false, FM_OPTIONS_UNIQUE);

		$this->fieldMeta["Description"] = new FieldMeta(
			"Description", "", FM_TYPE_STRING, 1000, FM_STORE_ALWAYS, false);

		$this->fieldMeta["DateCreated"] = new FieldMeta(
			"DateCreated", "", FM_TYPE_DATE, null, FM_STORE_NEVER, false);

		$this->fieldMeta["DateCreated"]->setFormatter(CoreFactory::getDateTimeFieldFormatter());
	}

	function afterInsert(&$dataEntity) {
		$cacheControl = &CoreFactory::getCacheControl();
		$cacheControl->deleteWebPageCache();
}

	function afterUpdate(&$dataEntity) {
		$this->afterInsert($dataEntity);
}

	function afterDelete(&$dataEntity) {
		$this->afterInsert($dataEntity);
	}
}