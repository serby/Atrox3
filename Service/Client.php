<?php
/**
 * @package Service
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision: 765 $ - $Date: 2008-10-13 18:15:31 +0100 (Mon, 13 Oct 2008) $
 */

/**
 * Top level class for all Framework services
 *
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision: 765 $ - $Date: 2008-10-13 18:15:31 +0100 (Mon, 13 Oct 2008) $
 * @package Service
 */
class Service_Client {
	
	/**
	 * URL of the Service
	 * example: http://mywebsite.com/resource/application/service/service.php
	 * 
	 * @var string 
	 */
	protected $serviceUrl = "";
	
	/**
	 * Token used once authentication has been established
	 * example: http://mywebsite.com/resource/application/service/service.php
	 * 
	 * @var string 
	 */
	protected $token = "";	
	
	/**
	 * Posts 
	 *
	 * @param string $call
	 * @return string JSON object with response
	 */
	function makeRequest($call) {
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $this->serviceUrl);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_HEADER, 0);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array(
			"Content-Type: text/javascript; charset=UTF-8",
			"Connection: close"		
		));
		curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($call));
		$response = curl_exec($curl);
		curl_close($curl);
		return json_decode($response);
	}
}