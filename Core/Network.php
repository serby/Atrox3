<?php	
/**
 * @copyright Clock Limited 2007
 * @version 3.0 - $Revision$ - $Date$
 * @package Core
 */

/**
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serbyr@clock.co.uk }
 * @copyright Clock Limited 2007
 * @version 3.0 - $Revision$ - $Date$
 * @package Core
 */
class NetworkControl {
			
function proxyPost($host, $page, $port, $literal = null) {
		$postvals = "";
		$headers = "";
		$requestHeaders = apache_request_headers();
		$requestHeaders["Host"] = $host;
		unset($requestHeaders["Cookie"]);
		unset($requestHeaders["Accept-Encoding"]);
		unset($requestHeaders["Connection"]);
		$errno = "";
		$errstr = "";
		$sock = fsockopen ($host, $port, $errno, $errstr, 30);
		if (!$sock) {
			return "$errstr ($errno)<br>\n";
		} else {
			$postvals = file_get_contents('php://input');
			foreach ($requestHeaders as $key => $value) {
				$headers .= "$key: $value\r\n";			
			}
			$method = $_SERVER["REQUEST_METHOD"];
			$request = "$method $page HTTP/1.0\r\n" . 
				"$headers" . 
				"\r\n" . 
				"$postvals\r\n" . 
				"\r\n";
			fputs($sock, $request);
			$headers = "";
			$body = "";
			while (!feof($sock)) {
				$body .= fread($sock, 4096);
			}
			fclose($sock);
		}

	// Get HTTP response code.
	$responseArray = explode("\n", $body, 1);
	$firstLine = explode(" ", $responseArray[0]);
	if ($firstLine[1] != "200") {
		mail("ed.pearson@clock.co.uk", "SMSGateway: Request", $request, true);
		mail("ed.pearson@clock.co.uk", "SMSGateway: Unexpected Response", $body, true);			
		mail("paul.serby@clock.co.uk", "SMSGateway: Request", $request, true);
		mail("paul.serby@clock.co.uk", "SMSGateway: Unexpected Response", $body, true);			
	}
	
	return $body;
	}
	
	function postData($host, $page, $port, $data, $userHeader = null) {
		$errno = "";
		$errstr = "";
		$sock = fsockopen ($host, $port, $errno, $errstr, 30);

		if (!$sock) {
			return "$errstr ($errno)<br>\n";
		} else {
			fputs($sock, "POST $page HTTP/1.0\r\n");
			fputs($sock, "Host: $host\r\n");

			// Add user header
			if ($userHeader != null) {
				fputs($sock, $userHeader);
			}
			fputs($sock, "Content-type: application/x-www-form-urlencoded\r\n");
			fputs($sock, "Content-length: " . mb_strlen($data) . "\r\n");
			fputs($sock, "\r\n");
			fputs($sock, "$data\r\n");
			fputs($sock, "\r\n");
			$headers = "";
			while ($str = trim(fgets($sock, 4096))) {
				$headers .= "$str\n";
			}
			$body = "";
			while (!feof($sock)) {
				$body .= fgets($sock, 4096);
			}
			fclose($sock);
		}
		$response = array();
		$response["headers"] = $headers;
		$response["body"] = $body;
		return $response;
	}
	
	function getData($host, $page, $port, $userHeader = null) {
		$errno = "";
		$errstr = "";
		$sock = @fsockopen($host, $port, $errno, $errstr, 30);

		if (!$sock) {
			return "$errstr ($errno)<br>\n";
		} else {
			fputs($sock, "GET $page HTTP/1.1\r\n");
			fputs($sock, "Host: $host\r\n");
			// Add user header
			if ($userHeader != null) {
				fputs($sock, $userHeader);
			}
			fputs($sock, "\r\n\r\n");
			$headers = "";
			while ($str = trim(fgets($sock, 4096))) {
				$headers .= "$str\n";
			}
			$body = "";
			while (!feof($sock)) {
				$body .= fgets($sock, 4096);
			}
			fclose($sock);
		}
		$response = array();
		$response["headers"] = $headers;
		$response["body"] = $body;
		return $response;
	}
	
}

class CookieControl {
	var $cookies = array();

	/**
	 * Adds a cookie to the $cookies array
	 * @param string $host
	 * @param string $value
	 */
	function addCookie($host, $value) {
		$this->cookies[] = array(
		"Value" => $value,
		"Host" => $host);
	}
	
	/**
	 * Parses HTTP headers and extracts the cookies, then sends them to addCookie()
	 *
	 * @param string $headers
	 * @param string $host
	 */
	function parseHeaders($headers, $host) {
		$cookieArray = array();
		$headers = explode("\n", $headers);
		foreach ($headers as $headerLine) {
			if (strstr(mb_strtolower($headerLine), "set-cookie")) {
				$cookieArray[] = $headerLine;
			}
		}

		foreach ($cookieArray as $cookieLine) {
			$cookie = str_replace("set-cookie: ", "", $cookieLine);
			$cookie = explode(";", $cookie);
			foreach ($cookie as $value) {
				$value = str_replace(" ", "", $value);
				if (!strstr($value, "expiry=")) {
					$this->addCookie($host, $value);
				} else {
					break;
				}
			}
		}
	}
	
	/**
	 * Builds a HTTP Header from the stored cookies
	 *
	 * @param array $cookieArray
	 * @return string
	 */
	function buildHttpString($cookieArray) {
		$cookieString = "Cookie: ";
		foreach ($cookieArray as $cookie) {
			$cookieString .= $cookie["Value"] . "; ";
		}
		return $cookieString;
	}
	
	/**
	 * Gets the relevant cookies for a certain host and return an array
	 *
	 * @param string $host
	 * @return array
	 */
	function getCookieArray($host) {
		$cookies = array();
		foreach ($this->cookies as $cookie) {
			if ($cookie["Host"] == $host) {
				$cookies[] = $cookie;
			}
		}
		return $cookies;
	}
}