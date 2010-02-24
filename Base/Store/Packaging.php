<?php
/**
 * @package Base
 * @subpackage Store
 * @copyright Clock Limited 2010
 * @version 3.2
 */

/**
 * Include Data.php so that DataControl can be extended.
 */
require_once("Atrox/Core/Data/Data.php");


/**
* Min/Max sizes for product images
*/
define("PKG_IMG_MINX", 100);
define("PKG_IMG_MINY", 100);
define("PKG_IMG_MAXX", 400);
define("PKG_IMG_MAXY", 400);

/**
 *
 * @author Robert Arnold (Clock Ltd) {@link mailto:robert.arnold@clock.co.uk robert.arnold@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2
 * @package Base
 * @subpackage Store
 */
class PackagingControl extends DataControl {
	var $table = "Packaging";
	var $key = "Id";
	var $sequence = "Packaging_Id_seq";
	var $defaultOrder = "Id";
	var $searchFields = array("Name", "Description");

	function init() {

		$this->fieldMeta["Id"] = new FieldMeta(
			"Id", "", FM_TYPE_INTEGER, null, FM_STORE_NEVER, false);
		
		$this->fieldMeta["Name"] = new FieldMeta(
			"Name", "", FM_TYPE_STRING, null, FM_STORE_ADD, false);
			
		$this->fieldMeta["Description"] = new FieldMeta(
			"Description", "", FM_TYPE_STRING, null, FM_STORE_ALWAYS, true);	
			
		$this->fieldMeta["NetPrice"] = new FieldMeta(
			"Net Price", 0, FM_TYPE_CURRENCY, 16, FM_STORE_ALWAYS, false);
			
		$this->fieldMeta["ImageId"] = new FieldMeta(
			"ImageId", "", FM_TYPE_INTEGER, null, FM_STORE_ALWAYS, true);
			
		$this->fieldMeta["Weight"] = new FieldMeta(
			"Weight", "", FM_TYPE_INTEGER, 16, FM_STORE_ALWAYS, false);
	}
}