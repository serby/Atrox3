<?php

/**
 * Implemetation of the Cheetahmail API
 * @author Paul Serby <paul.serby@clock.co.uk>
 * @copyright Clock Limited 2011
 * @version 3.2 - $Revision: 1329 $ - $Date: 2010-02-24 22:41:33 +0000 (Wed, 24 Feb 2010) $
 * @package Core
 */
class CheetahmailServiceAdaptor {

	protected $autheticationCookie;

	/**
	 * @var string Username
	 */
	protected $userName;

	/**
	 * @var string Password
	 */
	protected $password;

	/**
	 * @var unknown_type
	 */
	protected $affiliateId;

	/**
	 * @var string Hostname
	 */
	protected $host;

	/**
	 * @var integer Port number
	 */
	protected $port;

	/**
	 *
	 * @var HttpRequest
	 */
	protected $httpRequest;


	/**
	 * Has a Cheetahmail auth been made.
	 */
	protected $authenticated = false;

	/**
	 * @param string $userName
	 * @param string $password
	 * @param string $affiliateId
	 * @param string $host
	 * @param integer $port
	 */
	public function __construct($userName, $password, $affiliateId = null,
		HttpRequest $request, $host = "ebm.cheetahmail.com", $port = 80) {

		$this->userName = $userName;
		$this->password = $password;
		$this->affiliateId = $affiliateId;
		$this->host = $host;
		$this->port = $port;

		$this->httpRequest = $request;
		$this->httpRequest->enableCookies(true);
	}

	/**
	 * Authenticate with the provided credentials
	 *
	 * @return CheetahmailAdaptor
	 */
	public function authenticate() {
		if ($this->authenticated === false) {
			$this->httpRequest->setUrl($this->host . "/api/login1?name={$this->userName}&amp;cleartext={$this->password}");
			$this->parseResponse($this->httpRequest->send()->body);
			$this->authenticated = true;
		}
		return $this;
	}

	/**
	 * The primary function to insert and update a User
	 *
	 * @param integer $subscriberListId
	 * @param string $emailAddress
	 * @param array $data
	 *
	 * @return CheetahmailAdaptor
	 */
	public function setUser($emailAddress, array $postData = array(), $subscriberListId = null) {

		$url = "/api/setuser1";

		$postData["email"] = $emailAddress;

		if ($subscriberListId !== null) {
			$postData["sub"] = $subscriberListId;
		}

		$this->httpRequest
			->setUrl($this->host . $url)
			->setPostData($postData);

		$this->parseResponse($this->httpRequest->send()->body);
		return $this;
	}

	public function formatDate($date) {
		if ($date != "") {
			return date("d-M-Y", strtotime($date));
		} else {
			return null;
		}
	}

	/**
	 * A special function is used to change the email address on a pre-registered User
	 *
	 * @param integer $subscriberListId
	 * @param string $currentEmailAddress
	 * @param string $newEmailAddress
	 *
	 * @return CheetahmailAdaptor
	 */
	public function updateEmailAddress($subscriberListId, $currentEmailAddress, $newEmailAddress) {
		$url = "/api/setuser1";
		$postData = array("email" => $currentEmailAddress, "newemail" => $newEmailAddress, "sub" => $subscriberListId,
			"aid" => $this->affiliateId);

		$this->httpRequest
			->setUrl($this->host . $url)
			->setPostData($postData);

		$this->parseResponse($this->httpRequest->send()->body);

		return $this;
	}

	/**
	 * Remove an email address from a subscriber list
	 *
	 * @param integer $subscriberListId
	 * @param string $emailAddress
	 *
	 * @return CheetahmailAdaptor
	 */
	public function removeFromList($subscriberListId, $emailAddress) {
		$url = "/api/setuser1";
		$postData = array("email" => $emailAddress, "unsub" => $subscriberListId,
			"aid" => $this->affiliateId);

		$this->httpRequest
			->setUrl($this->host . $url)
			->setPostData($postData);

		$this->parseResponse($this->httpRequest->send()->body);

		return $this;
	}

	/**
	 * @param string $emailAddress
	 *
	 * @return array User data
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

		$data = explode("\r\n", $data);
		$returnData = array();
		foreach ($data as $row) {

			$keyValuePair = explode("=", $row);

			if (count($keyValuePair) === 2) {
				$key = $keyValuePair[0];
				$value = trim($keyValuePair[1]);
				$returnData[$key] = $value;
			} else {
				parse_str($row, $output);
				if (!isset($returnData["subs"])) {
					$returnData["subs"] = array();
				}
				$returnData["subs"][] = $output;
			}

		}
		return $returnData;
	}

	protected function parseRowData($data) {

		// code...
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
