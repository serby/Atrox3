<?php
/**
 * @package Service
 * @subpackage Core
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision: 765 $ - $Date: 2008-10-13 18:15:31 +0100 (Mon, 13 Oct 2008) $
 */

require_once("Atrox/Service/Client.php");

/**
 * Provides a Authentication Client which consumes AuthenticationServer
 *
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision: 765 $ - $Date: 2008-10-13 18:15:31 +0100 (Mon, 13 Oct 2008) $
 * @package Service
 */
class Service_Core_AuthenticationClient extends Service_Client {
	
	function __construct($serviceUrl, $token = null) {
		$this->serviceUrl = $serviceUrl;
		$this->token = $token;
	}
	
	function authenticate($username, $password) {
		
		$call->service = "Service_Core_Authentication";
		$call->action = "authenticate";
		
		$call->parameters->username = $username;
		$call->parameters->password = $password;
		
		$response = $this->makeRequest($call);
		
		if ($response->success) {
			$_SESSION["LoggedOn"] = true; 
			$_SESSION["MemberSecurityResources"] = $response->securityResources; 
			return true;
		} else {
			$application = CoreFactory::getApplication();
			$application->errorControl->errorList += $response->errors;
			return false;
		}
	}
	
	function changePassword($username, $password) {
		
		$call->service = "Service_Core_Authentication";
		$call->action = "changePassword";
		$call->token = $this->token;
		
		$call->parameters->username = $username;
		$call->parameters->password = $password;
		
		$response = $this->makeRequest($call);
		
		if ($response->success) {
			$_SESSION["LoggedOn"] = true; 
			return true;
		}
	}

	function isLoggedOn() {
		return isset($_SESSION["LoggedOn"]) && $_SESSION["LoggedOn"] == true;
	}
	
	function isAllowed($resourceName, $redirect = true) {		
		$application = CoreFactory::getApplication();
		if(!in_array($resourceName, $_SESSION["MemberSecurityResources"])) {
			if ($redirect) {
				$application->goToLastPage();
			} else {
				return false;
			}
		}
		return true;
	}
}