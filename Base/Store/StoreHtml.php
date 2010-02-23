<?php
/**
 * @package Base
 * @subpackage Store
 * @copyright Clock Limited 2007
 * @version 3.0 - $Revision$ - $Date$
 */

/**
 * Include Data.php so that DataControl can be extended.
 * Include Product.php so that ProductControl can be extended.
 */
require_once("Atrox/3.0/Core/Data/Data.php");
require_once("Product.php");

/**
 *
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2007
 * @version 3.0 - $Revision$ - $Date$
 * @package Base
 * @subpackage Store
 */
class ProductHtmlControl {

	function getCreator($product) {

		switch ($product->get("Type")) {
			case PCT_TYPE_BOOK:
			case PCT_TYPE_AUDIOBOOK:
				return "Author";
			case PCT_TYPE_CD:
			case PCT_TYPE_MUSICDOWNLOAD:
				return "Artist";
			case PCT_TYPE_DVD:
			case PCT_TYPE_VIDEODOWNLOAD:
				return "Starring";
			default:
				return "By";
		}
	}
}