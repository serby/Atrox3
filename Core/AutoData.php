<?php
/**
 * @package Core
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 */


/**
 * Include Security.php so that SecurityControl can be extended.
 */
require_once("Atrox/Core/Security.php");

/**
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Core
 */
class AutoData {
	function get() {
		return null;
	}
}

class CurrentMember extends AutoData {
	var $i = 1;
	function get() {
		$application = &CoreFactory::getApplication();
		return $application->securityControl->getMemberId();
	}
}

class IpAddress extends AutoData {
	function get() {
		$application = &CoreFactory::getApplication();
		$value = $application->getRemoteIpAddress();
		return $value;
	}
}

class Guid extends AutoData {
	function get() {
		return sha1(mt_rand());
	}
}

class Uid extends AutoData {
	function get() {
		return uniqid(mt_rand());
	}
}

class DateTimeAutoData extends AutoData {
	function get() {
		$databaseControl = &CoreFactory::getDatabaseControl();
		return $databaseControl->getCurrentDateTime();
	}
}

class UtcDateTimeAutoData extends AutoData {
	function get() {
		$application = &CoreFactory::getApplication();
		return $application->getCurrentUtcDateTime();
	}
}