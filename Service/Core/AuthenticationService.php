<?php
/**
 * @package Service
 * @subpackage Core
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision: 765 $ - $Date: 2008-10-13 18:15:31 +0100 (Mon, 13 Oct 2008) $
 */

/**
 * Include DataConnection.php so that DataConnectionControl can be extended.
 */
require_once("Atrox/Core/Data/DataConnection.php");
require_once("Atrox/Service/Service.php");

/**
 * Provides a Authentication Service to be consumed by AuthenticationClient
 *
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision: 765 $ - $Date: 2008-10-13 18:15:31 +0100 (Mon, 13 Oct 2008) $
 * @package Service
 */
class Service_Core_AuthenticationService extends Service {

	function authenticate($parameters) {
		
		$response->success = false;
		if (isset($parameters->username) && isset($parameters->password)) {
			
			$application = CoreFactory::getApplication();
			
			$sql = "SELECT * FROM " . $application->databaseControl->parseTable("Member") .
					" WHERE " . $application->databaseControl->parseField("EmailAddress"). " ILIKE " .
					$application->databaseControl->parseValue($parameters->username). ";";

			$result = $application->databaseControl->query($sql);
			
			if ($data = $application->databaseControl->fetchRow($result)) {
				if ($data["Password"] == sha1($parameters->password)) {
					
					//Create array of security groups				
					$groupSql = "SELECT * FROM " . $application->databaseControl->parseTable("MemberToSecurityGroup") .
					" WHERE " . $application->databaseControl->parseField("MemberId"). " = " .
					$application->databaseControl->parseValue($data["Id"]). ";";
	
					$securityGroups = "";
					$securityResources = array();
					if ($groupResult = $application->databaseControl->query($groupSql)) {
						while ($securityGroupData = $application->databaseControl->fetchRow($groupResult)) {
							
							// Get array of allowed resources 
						
							$resourceSql = "SELECT * FROM " . $application->databaseControl->parseTable("SecurityGroupToSecurityResource") .
							" WHERE " . $application->databaseControl->parseField("SecurityGroupId"). " = " .
							$application->databaseControl->parseValue($securityGroupData["SecurityGroupId"]). ";";
							if ($resourceResult = $application->databaseControl->query($resourceSql)) {
								while ($securityGroupToSecurityResource = $application->databaseControl->fetchRow($resourceResult)) {
									$securityResources[] = $securityGroupToSecurityResource["SecurityResourceName"];								
								}
							}							
						}
					}					
					
					$response->success = true;
					$response->securityResources = $securityResources;
				} else {
					$response->errors[] = "Bad Password";
				}
			} else {
				$response->errors[] = "Bad Username";
			}
			
			$response->token = sha1(mt_rand(time(), 0));
			return $response;
		} else {
			throw new Exception("Missing Password Parameter");
		}
		return $response;
	}
	
	function changePassword($parameters) {
		$response->success = false;
		if ($this->isTokenValid()) {
			if (isset($parameters->username) && isset($parameters->password)) {
				$sql = "UPDATE " . $application->databaseControl->parseTable("Member") .
					" SET " . $application->databaseControl->parseField("Password") . " = " . sha1($parameters->password) .
					" WHERE " . $application->databaseControl->parseField("EmailAddress") . " = " . $application->databaseControl->parseValue($parameters->username) . ";";
			}
		} else {
			$response->errors[] = "Invalid Token";
		}
		return $response;
	}	
}