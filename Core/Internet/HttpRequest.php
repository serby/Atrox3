<?php
/**
 * @author Paul Serby <paul.serby@clock.co.uk>
 * @copyright Clock Limited 2011
 * @version 3.2 - $Revision: 777 $ - $Date: 2008-10-30 12:49:04 +0000 (Thu, 30 Oct 2008) $
 * @package Internet
 */
class HttpRequest {

	/**
	 *
	 * @var string
	 */
	protected $url;

	/**
	 *
	 * @var array
	 */
	protected $headers = array("Expect: ");

	/**
	 *
	 * @var string
	 */
	protected $username;

	/**
	 *
	 * @var string
	 */
	protected $password;

	/**
	 *
	 * @var int
	 */
	protected $timeout = 30;

	/**
	 *
	 * @var string
	 */
	protected $userAgent = "Atrox";

	/**
	 *
	 *
	 * @var string
	 */
	protected $referer = "";

	/**
	 *
	 * @var string
	 */
	protected $postData;

	/**
	 *
	 * @var resource
	 */
	protected $curl;

	/**
	 * @var boolean Should recieved cookies be sent back with subsequent requests.
	 */
	protected $cookiesEnabled = false;

	/**
	 * @var string Where to store cookies. This is set when enbleCookies is called.
	 */
	protected $cookieJarFile = "";

	public function __construct($url = null) {
		if ($url) {
			$this->url = $url;
		}
	}

	/**
	 * 
	 * @return HttpRequest
	 */
	public function reset() {
		$this->headers = array("Expect: ");
		$this->postData = null;
		$this->referer = null;
		$this->enableCookies(false);
		return $this;
	}

	/**
	 * 
	 * @return unknown_type
	 */
	public function send() {
		if (!$this->curl) {
			$this->curl = curl_init();
		}

		curl_setopt($this->curl, CURLOPT_URL, $this->url);
		curl_setopt($this->curl, CURLOPT_TIMEOUT, $this->timeout);
		curl_setopt($this->curl, CURLOPT_HEADER, true);
		curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, false);
		curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($this->curl, CURLOPT_SSL_VERIFYHOST, false);


		if ($this->username) {
			curl_setopt($this->curl, CURLOPT_USERPWD, $this->username . ":" . $this->password);
		}

		if ($this->postData) {
			curl_setopt($this->curl, CURLOPT_POST, true);
			curl_setopt($this->curl, CURLOPT_POSTFIELDS, $this->postData);
		}

		if ($this->userAgent) {
			curl_setopt($this->curl, CURLOPT_USERAGENT, $this->userAgent);
		}

		if ($this->referer) {
			curl_setopt($this->curl, CURLOPT_REFERER, $this->referer);
		}

		curl_setopt($this->curl, CURLOPT_HTTPHEADER, $this->headers);

		if ($this->cookiesEnabled) {
			curl_setopt ($this->curl, CURLOPT_COOKIEJAR, $this->cookieJarFile);
		}

		$response = new stdClass();
		if ($responseText = curl_exec($this->curl)) {
			list($response->header,
			$response->body) =
			explode("\r\n\r\n", $responseText);

			$response->status = curl_getinfo($this->curl, CURLINFO_HTTP_CODE);
		}

		return $response;
	}

	/**
	 * 
	 * @return HttpRequest
	 */
	public function close() {
		curl_close($this->curl);
		return $this;
	}

	/**
	 *
	 * @param array $data
	 * @return HttpRequest
	 */
	public function setPostData(array $data) {
		$this->postData = http_build_query($data);
		return $this;
	}

	/**
	 *
	 * @param string $data
	 * @return HttpRequest
	 */
	public function setRawPostData($data) {
		$this->postData = $data;
		return $this;
	}

	/**
	 *
	 * @param string $header
	 * @param strign $value
	 * @return HttpRequest
	 */
	public function addHeader($header, $value) {
		$this->headers[] = "$header: $value";
		return $this;
	}

	/**
	 * 
	 * @param $oauthHeaders
	 * @return HttpRequest
	 */
	public function addOAuthHeaders($oauthHeaders) {
		$urlParts = parse_url($this->url);
		$oauth = "Authorization: OAuth realm=\"" . $urlParts["path"] . "\",";
		foreach($oauthHeaders as $name => $value)
		{
			$oauth .= "{$name}=\"{$value}\",";
		}
		$this->headers[] = substr($oauth, 0, -1);
		return $this;
	}

	public function addCookie($name, $value, $expire = 0, $path = false, $domain = false, $secure = false) {
		throw new Exception("Not yet implemented");
	}

	/**
	 *
	 * @param string $username
	 * @param string $password
	 * @return HttpRequest
	 */
	public function setAuthenticationDetails($username, $password) {
		$this->username = $username;
		$this->password = $password;
		return $this;
	}

	/**
	 * 
	 * @param int $timeout
	 * @return HttpRequest
	 */
	public function setTimeout($timeout) {
		$this->timeout = $timeout;
		return $this;
	}

	/**
	 * 
	 * @return string The current URL
	 */
	public function getUrl() {
		return $this->url;
	}

	/**
	 * 
	 * @param $url
	 * @return HttpRequest
	 */
	public function setUrl($url) {
		$this->url = $url;
		return $this;
	}

	/**
	 * 
	 * @param $value
	 * @return HttpRequest
	 */
	public function enableCookies($value = true) {
		if ($this->cookiesEnabled = $value) {
			$this->cookieJarFile = tempnam("/tmp", "AtroxCookies");
		}
		return $this;
	}
}