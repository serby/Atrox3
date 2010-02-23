<?php
/**
 * @copyright Clock Limited 2007
 * @version 3.0 - $Revision$ - $Date$
 * @package Core
 * @subpackage System
 */

/**
 * System class is used for interacting with the underliying system
 *
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2007
 * @version 3.0 - $Revision$ - $Date$
 * @package Core
 * @subpackage System
 */
Class System {

	function isInstalled($fileName) {
		return file_exists($fileName);
	}
}