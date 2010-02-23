<?php
/**
 * @package Base
 * @subpackage Mobile
 * @copyright Clock Limited 2007
 * @version 3.0 - $Revision$ - $Date$
 */

/**
 * Include Data.php so that DataControl can be extended.
 */
require_once("Atrox/Core/Data/Data.php");

require_once("Date.php");

/**
 * Wap Downloads are the electronic downloads available for people to downloads
 * to their phone.
 *
 * @author Adam Forster (Clock Ltd) {@link mailto:adam.forster@clock.co.uk
 * paul.serby@clock.co.uk }
 * @copyright Clock Limited 2007
 * @version 3.0 - $Revision$ - $Date$
 * @package Base
 * @subpackage Mobile
 */
class WapDownloadControl extends DataControl {
	var $table = "WapDownload";
	var $key = "Id";
	var $sequence = "WapDownload_Id_seq";
	var $defaultOrder = "DateCreated";
	var $searchFields = array("MobileNumber", "DateCreated");

	function init() {
		$this->fieldMeta["Id"] = new FieldMeta(
			"Id", "", FM_TYPE_INTEGER, null, FM_STORE_NEVER, false);

		$this->fieldMeta["WapItemId"] = new FieldMeta(
			"Wap Item", "", FM_TYPE_RELATION, null, FM_STORE_ALWAYS, false);

		$this->fieldMeta["WapItemId"]->setRelationControl(BaseFactory::getWapItemControl());

		$this->fieldMeta["MobileNumber"] = new FieldMeta(
			"Mobile Number", "", FM_TYPE_STRING, 50, FM_STORE_ALWAYS, false);

		$this->fieldMeta["RemainingDownloads"] = new FieldMeta(
			"Remaining Downloads", "3", FM_TYPE_INTEGER, null, FM_STORE_ALWAYS, false);

		$this->fieldMeta["UniqueId"] = new FieldMeta(
			"Unique Id", "", FM_TYPE_STRING, 255, FM_STORE_ALWAYS, false);

		$this->fieldMeta["DateCreated"] = new FieldMeta(
			"Date Created", "", FM_TYPE_DATE, null, FM_STORE_NEVER, false);

		$this->fieldMeta["DateCreated"]->setFormatter(CoreFactory::getDateTimeFieldFormatter());
	}

	function removeOldDownloads() {
		$date = new Date();
		$date->subtractSeconds(168 * 3600);

		$filter = CoreFactory::getFilter();
		$filter->addConditional($this->table, "RemainingDownloads", 0, "=");
		$filter->addConditional($this->table, "DateCreated", $date->getDate(), "<", "OR");
		$this->setFilter($filter);
		$this->deleteAll();
	}
}