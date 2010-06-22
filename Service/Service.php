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
class Service {
	
	static function callService($serviceRequest, $customPath = false) {
		
		$className = $serviceRequest->service . "Service";
		$serverName = str_replace("_", "/", $serviceRequest->service) . "Service";
				
		if ($customPath) {
			$path = $customPath . "/" . $serverName . ".php";
		} else {
			$path = "Application/Atrox/{$serverName}.php";			
		}

		try {
			include_once($path);
			$service = new $className();
			return $service->serve($serviceRequest);
		} catch (Exception $e) {
			throw new Exception("File: '{$serverName}.php' does not exist");
			return false;
		}
	}
	
	function serve($request) {
		if (isset($request->action)) {
			$action = $request->action;
			if (is_callable(array($this, $action))) {				
				
				if (isset($request->token)) {
					$this->token = $request->token;
				}
								
				if (isset($request->parameters)) {
					return $this->$action($request->parameters);
				} else {
					return $this->$action();
				} 
			} else {
				throw new Exception("Action: '$action' does not exist");
			}
		} else {
			throw new Exception("No Action Specified");
		}
		return false;
	}
	
	/**
	 * Checks that the Server is responding
	 *
	 * @return boolean
	 */	
	function isAlive() {
		$response->success = true;
		return $response; 
	}
	
	/**
	 * Checks that the issued token is value 
	 *
	 * @return boolean
	 */	
	function isTokenValid() {
		// $this->token
		return true;
	}
	
	function forUnitTest($parameters) {
		return $parameters;
	}
}