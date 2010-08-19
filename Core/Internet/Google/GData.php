<?php
/**
 * @package Internet/Google
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 */

/**
 * Implementation of the GoogleData API
 * 
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package 
 */
class GData {
 
	function __construct() {
	}
 
	function authorize($username, $password) {
		
		$httpRequest = CoreFactory::getHttpRequest();
		
		$httpRequest->setUrl("https://www.google.com/youtube/accounts/ClientLogin");
		$httpRequest->addHeader("Content-type:", "application/x-www-form-urlencoded");
		
		$httpRequest->setRawPostData(http_build_query(
		   array(
	     	"Email" => $username,
	     	"Passwd" => $password,
		   	"service" => "youtube",
		   	"source" => "Clock"
		   )
	   ));
		$response = $httpRequest->send();
		$token = explode("\n", $response->body);
		$token = explode("=", $token[0]);
		if (strlen($token[1]) == 162) {
			return $token[1];
		} else if ($token[1] == "BadAuthentication") {
			throw new Exception("Bad Authentication");			
		}
	}
}