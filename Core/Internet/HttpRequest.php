<?php
/**
 * @package Internet
 * @copyright Clock Limited 2007
 * @version 3.0 - $Revision: 777 $ - $Date: 2008-10-30 12:49:04 +0000 (Thu, 30 Oct 2008) $
 */

/**
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2007
 * @version 3.0 - $Revision: 777 $ - $Date: 2008-10-30 12:49:04 +0000 (Thu, 30 Oct 2008) $
 * @package Internet
 */
class HttpRequest {
	
	private $method = "GET";
	private $postData = null;
	private $timeout = 30;
	private $headers = array();
	private $cookies = array();
	
	function __construct() {
		
	}
	
	/**
	 * Sets the method of the next HTTP requests
	 *
	 * @param string $method
	 * @return void
	 */
	function setMethod($method) {
		$this->method = $method;
	}
	
	/**
	 * Sets the method of the next request
	 *
	 * @param string $method
	 * @return void
	 */
	function setPostData($data) {
		$this->postData = $data;
	}
	
	/**
	 * Sets the timeout to wait for a connect 
	 *
	 * @param string $timeout
	 * @return void
	 */
	function setTimeout(int $timeout) {
		$this->timeout = $timeout;
	}

	/**
	 * Sets the cookies to be sent with the next request 
	 *
	 * @param string $method
	 * @return void
	 */
	function setCookies(array $cookies) {
		$this->cookies = $cookies;
	}
		
	/**
	 * Adds custom header to the next request
	 *
	 * @param string $method
	 * @return void
	 */
	function addHeader($header) {
		$this->headers[] = $header;
	}
	
	/**
	 * Clears all the set headers
	 * @retrun void
	 */
	function clearHeaders() {
		$this->headers = array();
	}
	
	/**
	 * Makes a requests 
	 *
	 * @param string $url
	 * @return Object { headers: [], body: string } 
	 */
	function send($url) {
		
		$urlParts = parse_url($url);
		$host = $urlParts["host"];
		$port = isset($urlParts["post"]) ? $urlParts["post"] : 80;
		$path = isset($urlParts["path"]) ? $urlParts["path"] : "/"; 
		$queryString = isset($urlParts["query"]) ? "?" . $urlParts["query"] : "";
		
		// Store whether this is a https connection 
		$secure = false;
		
		switch ($urlParts["scheme"]) {
			case "https":
				$secure = true;
				$transport = "ssl://";
				if (!isset($urlParts["post"])) {
					$port = 443;
				}
				break;		
			default: 
				$transport = "tcp://";
		}
		
		$errorNumber = "";
		$errorMessage = "";
	
		$socket = fsockopen($transport . $host, $port, $errorNumber, $errorMessage, $this->timeout);
		
		if (!$socket) {
			throw new Exception("$errorMessage ($errorNumber)");
			return false;
		} else {
			$request = "{$this->method} {$path}{$queryString} HTTP/1.1\r\n";
			$request .= "Host: $host\r\n";			
		
			if (sizeof($this->cookies) > 0) {
				$cookies = ""; 
				foreach($this->cookies as $cookie) {
					if ($this->isCookieValid($cookie, $host, $path, $secure)) {
						$cookies .= "{$cookie->name}={$cookie->value}; ";
					}
				}
				$request .= "Cookie: {$cookies}\r\n";
			}

			// Add headers
			foreach($this->headers as $header) {
				$request .= $header . "\r\n";
			}
			
			$request .= "Content-length: " . mb_strlen($this->postData) . "\r\n";
			
			$request .= "\r\n";
			if (mb_strlen($this->postData) > 0) {
				$request .= "{$this->postData}\r\n";
			}
			
			$request .= "\r\n";
			
			// Send Request
			fwrite($socket, $request);
			
			$response->request = $request;
			$response->headers = array();
			$response->cookies = array();
			$chunked = false;
			while ($header = trim(fgets($socket))) {
				$response->headers[] = $header;
				if (strtolower(substr($header, 0, 11)) == "set-cookie:") {
					$response->cookies[] = $this->parseCookies(substr($header, 11));					
				} else if ($header == "Transfer-Encoding: chunked") {
					$chunked = true;
				}
			}
			
			stream_set_blocking($socket, false);
			if ($chunked) {
				while (!feof($socket)) {
					$chunkSize = hexdec($a = trim(fgets($socket)));
					if ($chunkSize > 0) {
						$body = fgets($socket, $chunkSize + 1);
						$response->body .= $body;
					}
				}
			} else {
				while (!feof($socket) && ($body = fgets($socket))) {				
					$response->body .= $body;
				}
			}			
			fclose($socket);
		}
		return $response;
	}
	
	private function parseCookies($cookie) {
		$cookieParts = explode(";", $cookie);
		$returnCookies = array();
		$cookiePart = current($cookieParts);
		$cookiePart = explode("=", $cookiePart);
		
		$newCookie->name = trim($cookiePart[0]);
		$newCookie->value = trim($cookiePart[1]);
		$newCookie->secure = false;
		//$newCookie->httpOnly = false;
		
		
		foreach($cookieParts as $cookiePart) {
			$cookiePart = explode("=", $cookiePart);
			
			switch(trim($cookiePart[0])) {
				case "expires":
					$newCookie->expires = trim($cookiePart[1]);
					break;
				case "path":
					$newCookie->path = trim($cookiePart[1]);
					break;
				case "domain":
					$newCookie->domain = trim($cookiePart[1]);
					break;
				case "secure":
					$newCookie->secure = true;
					break;
			}
		}
		return $newCookie;
	}
	
	 function isCookieValid($cookie, $domain, $path, $secure) {
	 	$domainValid = true;
	 	if (isset($cookie->domain)) {
	 		$cookie->domain = trim($cookie->domain, ".");
		 	$domainParts = explode(".", $domain);
		 	$cookieDomainParts = explode(".", $cookie->domain);
		 	$domainParts = array_slice($domainParts, sizeof($domainParts) - sizeof($cookieDomainParts)); 	
		 	$domainValid = $domainParts == $cookieDomainParts;
	 	}
	 	$pathValid = true;
	 	if (isset($cookie->path)) {
		 	$cookie->path = "/" . $cookie->path . "/";
		 	$path = "/" . $path . "/";
		 	$cookie->path = preg_replace("/\/+/", "/", $cookie->path);
		 	$path = preg_replace("/\/+/", "/", $path);
		 	$pathValid = substr($path, 0, strlen($cookie->path)) == $cookie->path;
	 	}	 	
	 	$timeValid = true;
	 	if (isset($cookie->expires)) {
	 		$timeValid = strtotime($cookie->expires) > time();
	 	}
		return 
			(!isset($cookie->secure) || $cookie->secure ? $secure : true) && 
			$domainValid && $pathValid && $timeValid;
	}
}