<?php
/**
 * @package Internet
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision: 777 $ - $Date: 2008-10-30 12:49:04 +0000 (Thu, 30 Oct 2008) $
 */

/**
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision: 777 $ - $Date: 2008-10-30 12:49:04 +0000 (Thu, 30 Oct 2008) $
 * @package Internet
 */
class HttpRequest {
	
/**
	 *
	 * @var string
	 */
	private $url;

	/**
	 *
	 * @var array
	 */
	private $headers = array("Expect: ");

	/**
	 *
	 * @var string
	 */
	private $username;

	/**
	 *
	 * @var string
	 */
	private $password;

	/**
	 *
	 * @var int
	 */
	private $timeout = 30;

	/**
	 *
	 * @var string
	 */
	private $userAgent = "Atrox";

	/**
	 *
	 *
	 * @var string
	 */
	private $referer = "";

	/**
	 *
	 * @var string
	 */
	private $postData;

	/**
	 *
	 * @var resource
	 */
	private $curl;

	function __construct($url = null) {
		if ($url) {
			$this->url = $url;
		}
	}

	function reset() {
		$this->headers = array("Expect: ");
		$this->postData = null;
		$this->referer = null;
	}

	function send() {
		$curl = curl_init();

		curl_setopt($curl, CURLOPT_URL, $this->url);
		curl_setopt($curl, CURLOPT_TIMEOUT, $this->timeout);
		curl_setopt($curl, CURLOPT_HEADER, true);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);


		if ($this->username) {
			curl_setopt($curl, CURLOPT_USERPWD, $this->username . ":" . $this->password);
		}

		if ($this->postData) {
			curl_setopt($curl, CURLOPT_POST, true);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $this->postData);
		}

		if ($this->userAgent) {
			curl_setopt($curl, CURLOPT_USERAGENT, $this->userAgent);
		}

		if ($this->referer) {
			curl_setopt($curl, CURLOPT_REFERER, $this->referer);
		}

		curl_setopt($curl, CURLOPT_HTTPHEADER, $this->headers);

		$response = new stdClass();
		if ($responseText = curl_exec($curl)) {
			list($response->header,
			$response->body) =
			explode("\r\n\r\n", $responseText);

			$response->status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		}
		curl_close($curl);
		return $response;
	}

	/**
	 *
	 * @param array $data
	 */
	function setPostData(array $data) {
		$this->postData = http_build_query($data);
	}

	/**
	 *
	 * @param string $data
	 */
	function setRawPostData($data) {
		$this->postData = $data;
	}

	/**
	 *
	 * @param string $header
	 * @param strign $value
	 * @return HttpRequest
	 */
	function addHeader($header, $value) {
		$this->headers[] = "$header: $value";
		return $this;
	}
	
	function addOAuthHeaders($oauthHeaders) {
		$urlParts = parse_url($this->url);
    $oauth = 'Authorization: OAuth realm="' . $urlParts['path'] . '",';
    foreach($oauthHeaders as $name => $value)
    {
      $oauth .= "{$name}=\"{$value}\",";
    }
    $this->headers[] = substr($oauth, 0, -1);
	}

	function addCookie($name, $value, $expire = 0, $path = false, $domain = false, $secure = false) {
		throw new Exception("Not yet implemented");
	}

	/**
	 *
	 * @param string $username
	 * @param string $password
	 */
	function setAuthenticationDetails($username, $password) {
		$this->username = $username;
		$this->password = $password;
	} 

	function setTimeout($timeout) {
		$this->timeout = $timeout;
	}

	function getUrl() {
		return $this->url;
	}

	function setUrl($url) {
		$this->url = $url;
	}
}