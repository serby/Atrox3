<?php
/**
 * @package Service
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision: 765 $ - $Date: 2008-10-13 18:15:31 +0100 (Mon, 13 Oct 2008) $
 */

/**
 * Factory for creating Service and Client Objects
 *
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision: 765 $ - $Date: 2008-10-13 18:15:31 +0100 (Mon, 13 Oct 2008) $
 * @package Service
 */
class Service_Factory {
	
	/**
	 * @return Service_Core_AuthenticationClient
	 */	
	function getService_Core_AuthenticationClient($serviceUrl) {
		require_once("Core/AuthenticationClient.php");
		return new Service_Core_AuthenticationClient($serviceUrl);
	}
	
	/**
	 * @return Service_Core_AuthenticationService
	 */
	function getService_Core_AuthenticationService() {
		require_once("Core/AuthenticationService.php");
		return new Service_Core_AuthenticationService();
	}	
	
	/**
	 * @return Service_Base_Member_MemberClient
	 */
	function getService_Base_Member_MemberClient($serviceUrl) {
		require_once("Base/Member/MemberClient.php");
		return new Service_Base_Member_MemberClient($serviceUrl);
	}
}