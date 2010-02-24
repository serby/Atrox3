<?php
/**
 * @package Service
 * @subpackage Base/Member
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision: 765 $ - $Date: 2008-10-13 18:15:31 +0100 (Mon, 13 Oct 2008) $
 */

require_once("Atrox/Service/Client.php");

/**
 * Provides a Member Client which consumes AuthenticationServer
 *
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision: 765 $ - $Date: 2008-10-13 18:15:31 +0100 (Mon, 13 Oct 2008) $
 * @package Service
 */
class Service_Base_Member_MemberClient extends Service_Client {
	
	function __construct($serviceUrl, $token = null) {
		$this->serviceUrl = $serviceUrl;
		$this->token = $token;
	}
	
	function getDetails($id) {
		
		$call->service = "Service_Base_Member_Member";
		$call->action = "getDetails";
		
		$call->parameters->id = $id;
			
		$response = $this->makeRequest($call);
		
		if ($response->success) {
			return $response->data;
		} else {
			$application = &CoreFactory::getApplication();
			foreach ($response->errors as $error) {
				$application->errorControl->addError($error);
			}			
			return false;
		}
	}
	
	function getDetailsByEmailAddress($id) {
		$call->service = "Service_Base_Member_Member";
		$call->action = "getDetails";
		
		$call->parameters->emailAddress = $id;
			
		$response = $this->makeRequest($call);
		
		if ($response->success) {
			return $response->data;
		} else {
			$application = &CoreFactory::getApplication();
			
			foreach ($response->errors as $error) {
				$application->errorControl->addError($error);
			}			
			return false;
		}
	}
	
	function getSecurityResources($id) {
		$call->service = "Service_Base_Member_Member";
		$call->action = "getSecurityResources";
		
		$call->parameters->id = $id;
			
		$response = $this->makeRequest($call);
		
		if ($response->success) {
			return $response->securityResources;
		} else {
			$application = &CoreFactory::getApplication();
			
			foreach ($response->errors as $error) {
				$application->errorControl->addError($error);
			}			
			return false;
		}
	}
}