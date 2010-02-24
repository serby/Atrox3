<?php
/**
 * @package Service
 * @subpackage Base/Member
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision: 765 $ - $Date: 2008-10-13 18:15:31 +0100 (Mon, 13 Oct 2008) $
 */

require_once("Atrox/Service/Service.php");

/**
 * Provides a Authentication Service to be consumed by AuthenticationClient
 *
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision: 765 $ - $Date: 2008-10-13 18:15:31 +0100 (Mon, 13 Oct 2008) $
 * @package Service
 */
class Service_Base_Member_MemberService extends Service {

	function getDetails($parameters) {

		$response->success = false;
		
		if ($this->isTokenValid()) {
			if (isset($parameters->id)) {
				$memberControl = BaseFactory::getMemberControl();
				if ($member = $memberControl->item($parameters->id)) {
					$response->data = $member->toObject();
					$response->success = true;
				} else {
					$response->errors[] = "Unable to find Member Details for '" . $parameters->id . "'";
				}
			} else if (isset($parameters->emailAddress)) {
				$memberControl = BaseFactory::getMemberControl();
				if ($member = $memberControl->itemByField($parameters->emailAddress, "EmailAddress")) {        	
          $response->data = $member->toObject();        
          $response->success = true;
        } else {
          $response->errors[] = "Unable to find Member Details for '" . $parameters->emailAddress . "'";
        }
			} else {
				throw new Exception("Missing id/emailAddress Parameter");
			}
		}
		return $response;
	}
	
	function getSecurityResources($parameters) {
		
		$application = CoreFactory::getApplication();
		$response->success = false;
		//Create array of security groups				
		$groupSql = "SELECT * FROM " . $application->databaseControl->parseTable("MemberToSecurityGroup") .
		" WHERE " . $application->databaseControl->parseField("MemberId"). " = " .
		$application->databaseControl->parseValue($parameters->id). ";";

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
		return $response;
	}
}