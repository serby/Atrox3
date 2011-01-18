<?php

/**
 * Implemetation of the Cheetahmail API
 * @author Paul Serby <paul.serby@clock.co.uk>
 * @copyright Clock Limited 2011
 * @version 3.2 - $Revision: 1329 $ - $Date: 2010-02-24 22:41:33 +0000 (Wed, 24 Feb 2010) $
 * @package Core
 */
class CheetahmailAdaptor {

	protected $autheticationCookie;
	protected $userName;
	protected $password;
	protected $affiliateId;
	protected $host;
	protected $port;

	/**
	 *
	 * @var HttpRequest
	 */
	protected $httpRequest;

	public function __construct($userName, $password, $affiliateId = null, $host = "ebm.cheetahmail.com", $port = 80) {
		$this->userName = $userName;
		$this->password = $password;
		$this->affiliateId = $affiliateId;
		$this->host = $host;
		$this->port = $port;

		$this->httpRequest = CoreFactory::getHttpRequest();
		$this->httpRequest->enableCookies(true);
	}

	public function authenticate() {
		$this->httpRequest->setUrl($this->host . "/api/login1?name={$this->userName}&amp;cleartext={$this->password}");
		$this->parseResponse($this->httpRequest->send()->body);
		return $this;
	}

	public function setUser($subscriberListId, $emailAddress, $data = array()) {

		$url = "/api/setuser1";
		$postData = array("email" => $emailAddress, "sub" => $subscriberListId);
		$postData = array_merge($postData, $data);

		$this->httpRequest
			->setUrl($this->host . $url)
			->setPostData($postData);

		$this->parseResponse($this->httpRequest->send()->body);
		return $this;
	}

	function changeEmail($oldemail, $newemail) {
		if (strlen($this->cookie) < 1) {
			$this->login();
		}
		$response = $this->networkControl->getData($this->host, "/api/setuser1?email={$oldemail}&newemail={$newemail}&aid={$this->aid}&sub={$this->livesub}", 80, "Cookie: " . $this->cookie);
		return $response;
	}

	function removeFromList($email) {
		if (strlen($this->cookie) < 1) {
			$this->login();
		}
		$response = $this->networkControl->getData($this->host, "/api/setuser1?email={$email}&aid={$this->aid}&unsub={$this->livesub}", 80, "Cookie: " . $this->cookie);
		return $response;
	}

	/**
	 *
	 * @param $emailAddress
	 * @return unknown_type
	 */
	public function getUser($emailAddress) {
		$this->httpRequest->setUrl($this->host . "/api/getuser1?email={$emailAddress}");
		$response = $this->httpRequest->send()->body;
		$this->parseResponse($response);
		return $this->parseUserData($response);
	}

	/**
	 * Parses the raw Cheetahmail user data.
	 *
	 * @param string $data The raw response from Cheetahmail
	 *
	 * @return array An associative array of the processed user data.
	 */
	protected function parseUserData($data) {

		$data = explode("\n", $data);
		$returnData = array();
		foreach ($data as $row) {
			$keyValuePair = explode("=", $row);
			$returnData[$keyValuePair[0]] = trim($keyValuePair[1]);
		}
		return $returnData;
	}

	/**
	 * Processed the response from Cheetahmail and throws an exception if there is a problem.
	 * @param $response
	 * @return boolean True if response is OK
	 */
	protected function parseResponse($responseBody) {
		$responseCode =  explode("\n", trim($responseBody));
		if (count($responseCode) > 1) {
			return true;
		}
		$responseCode = trim(array_pop($responseCode));

		switch ($responseCode) {
			case "err:email:missing":
				throw new Exception("No email address entered");
				break;
			case "err:email:illegal":
				throw new Exception("Email address entered is not valid, please amend and try again");
				break;
			case "err:email:blocked":
			case "err:newemail:blocked":
				throw new Exception("The email address entered is blocked");
				break;
			case "OK":
				return true;
				break;
			case "err:auth":
			case "err:internal error":
			case "err:p:too long":
			case "err:sub:non-numeric":
			case "err:unsub:non-numeric":
			case "err:p:too short":
			case "err:email:noexist":
			case "err:e:illegal":
			case "err:field:name:illegal":
			case "err:field:missing":
			case "err:field:modifier:illegal":
			case "err:field:non-numeric":
			case "err:data:value too large for column":
			case "err:data:numeric overflow":
			case "err:data:invalid number":
			case "err:data:non-numeric char where digit expected (for date)":
			case "err:data:invalid date":
			case "err:data:other":
			case "err:resub:illegal":
			case "err:HTML:invalid":
			default:
				throw new Exception("An unexpected error has occured: " . $responseCode);
				break;
		}
		return true;
	}
}