<?php
/**
 * @package Base
 * @subpackage Campaign
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 */

/**
 * Include Data.php so that DataControl can be extended.
 */
require_once("Atrox/Core/Data/Data.php");

/**
 * @author Robert Arnold (Clock Ltd) {@link mailto:robert.arnold@clock.co.uk robert.arnold@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Base
 * @subpackage Campaign
 */
class CampaignImageControl extends DataControl {
	var $table = "CampaignImage";
	var $key = "Id";
	var $sequence = "CampaignImage_Id_seq";
	var $defaultOrder = "Id";
	var $searchFields = array("Id");

	function init() {
		$this->fieldMeta["Id"] = new FieldMeta(
			"Id", "", FM_TYPE_INTEGER, null, FM_STORE_NEVER, false);

		$this->fieldMeta["ImageId"] = new FieldMeta(
			"Image", "", FM_TYPE_BINARY, null, FM_STORE_ALWAYS, false);

				
		$this->fieldMeta["Name"] = new FieldMeta(
			"Name", "", FM_TYPE_STRING, null, FM_STORE_ALWAYS, false, FM_OPTIONS_UNIQUE);
	}		
}