<?php
//require_once("PHPUnit/");
require_once "Atrox/Core/Internet/Cheetahmail/CheetahmailServiceAdaptor.php";

/**
 * @author Paul Serby <paul.serby@clock.co.uk>
 * @copyright Clock Limited 2011
 */
class CheetahmailServiceAdaptorTest extends PHPUnit_Framework_TestCase {

	/**
	 * @var string
	 */
	protected $userName = "sl_tom";

	/**
	 * @var string
	 */
	protected $password = "Bolivia2";

	/**
	 * @var integer
	 */
	protected $subscriberListId = 2086316516;

	/**
	 * @var array
	 */
	protected $validSubscribedUser = array(
		"email" => "genuine-user@clock.co.uk",
		"HTML" => null,
		"BEHAVIOR_3" => null,
		"INCOME" => null,
		"DATE_OF_BIRTH" => "19780501000000",
		"BEHAVIOR_2" => null,
		"MOBILE_PHONE" => null,
		"HOME_POSTCODE" => null,
		"BEHAVIOR_1" => null,
		"GENDER" => "M",
		"WORK_POSTCODE" => "Genuine",
		"LNAME" => "User",
		"sub" => "2086316516&aid"
	);

	protected function setup() {
		$this->httpRequest = CoreFactory::getHttpRequest();
		$this->cheetahmailServiceAdaptor = new CheetahmailServiceAdaptor($this->userName, $this->password, 0, $this->httpRequest);
	}

//	public function testAValidAuthenticationCookieIsReturnedForValidCredentials() {
//		$this->assertEquals("", $this->cheetahmailService->getAuthenticationCookie());
//	}

	public function testAuthenticationSuccess() {
		$cheetahmailServiceAdaptor  = $this->cheetahmailServiceAdaptor->authenticate();

		$this->assertInstanceOf("cheetahmailServiceAdaptor", $cheetahmailServiceAdaptor);
	}

	public function testAuthenticationFailure() {
		$cheetahmailServiceAdaptor = new cheetahmailServiceAdaptor($this->userName, "bad-password", 0, $this->httpRequest);
		$this->setExpectedException("Exception");
		$cheetahmailServiceAdaptor->authenticate();
	}

	public function testGetUserFailsWithNonExistantUser() {
		$this->cheetahmailServiceAdaptor->authenticate();
		$this->setExpectedException("Exception");
		$this->assertEquals(array(), $this->cheetahmailServiceAdaptor->getUser("doesnotexist@test.com"));
	}

	public function testGetUserSucceedsWithValidUser() {
		$this->cheetahmailServiceAdaptor->authenticate();
		$user = $this->cheetahmailServiceAdaptor->getUser($this->validSubscribedUser["email"]);
		$arrayDiff = array_diff($user, $this->validSubscribedUser);

		$this->assertEquals(0, count($arrayDiff));
	}

	public function testAddingNewUserWithVaildFields() {
		$emailAddress = $this->getRandomEmailAddress();

		$this->cheetahmailServiceAdaptor->authenticate();
		$this->cheetahmailServiceAdaptor->setUser($this->subscriberListId, $emailAddress,
			array("FNAME" => "Paul", "LNAME" => "Serby", "GENDER" => "M", "DATE_OF_BIRTH" => "01-May-1978"));
		$user = $this->cheetahmailServiceAdaptor->getUser($emailAddress);
		$this->assertEquals("Paul", $user["FNAME"]);
	}

	public function testRegisterFollowedByUpdateWithOneAuthenticateSucceed() {

		$this->cheetahmailServiceAdaptor->authenticate();
		$emailAddress = $this->getRandomEmailAddress();
		$this->cheetahmailServiceAdaptor->setUser($this->subscriberListId, $emailAddress,
			array("FNAME" => "Paul", "LNAME" => "Serby", "GENDER" => "M", "DATE_OF_BIRTH" => "01-May-1978"));

		$this->cheetahmailServiceAdaptor->setUser($this->subscriberListId, $emailAddress,
			array("FNAME" => "Pauly", "LNAME" => "Serby", "GENDER" => "M", "DATE_OF_BIRTH" => "01-May-1978"));
		$user = $this->cheetahmailServiceAdaptor->getUser($emailAddress);
		$this->assertEquals("Paul", $user["FNAME"]);

	}

	public function testDoubleAuthenticationSucceed() {

		$this->cheetahmailServiceAdaptor->authenticate();
		$emailAddress = $this->getRandomEmailAddress();
		$this->cheetahmailServiceAdaptor->setUser($this->subscriberListId, $emailAddress,
			array("FNAME" => "Paul", "LNAME" => "Serby", "GENDER" => "M", "DATE_OF_BIRTH" => "01-May-1978"));

		$this->cheetahmailServiceAdaptor->authenticate();
		$this->cheetahmailServiceAdaptor->setUser($this->subscriberListId, $emailAddress,
			array("FNAME" => "Pauly", "LNAME" => "Serby", "GENDER" => "M", "DATE_OF_BIRTH" => "01-May-1978"));
		$user = $this->cheetahmailServiceAdaptor->getUser($emailAddress);
		$this->assertEquals("Pauly", $user["FNAME"]);
	}

	public function testAddingNewUserWithInvaildEmailAddress() {
		$this->cheetahmailServiceAdaptor->authenticate();
		$this->setExpectedException("Exception");
		$this->cheetahmailServiceAdaptor->setUser($this->subscriberListId, "abc");
	}

	public function testUpdatingEmailAddressSuccess() {
		$emailAddress = $this->getRandomEmailAddress();
		$newEmailAddress = $this->getRandomEmailAddress();

		$this->cheetahmailServiceAdaptor->authenticate();
		$this->cheetahmailServiceAdaptor->setUser($this->subscriberListId, $emailAddress, array("FNAME" => "ChangeAddress"));
		$cheetahmailServiceAdaptor = $this->cheetahmailServiceAdaptor->updateEmailAddress(
			$this->subscriberListId, $emailAddress, $newEmailAddress);

		$this->assertInstanceOf("cheetahmailServiceAdaptor", $cheetahmailServiceAdaptor);
	}

	public function testUnsubscribingEmailFromSubscriberList() {
		$emailAddress = $this->getRandomEmailAddress();

		$this->cheetahmailServiceAdaptor->authenticate();
		$this->cheetahmailServiceAdaptor->setUser($this->subscriberListId, $emailAddress, array("FNAME" => "Unsubscribe"));
		$user = $this->cheetahmailServiceAdaptor->getUser($emailAddress);
		$this->assertEquals($this->subscriberListId . "&aid", $user["sub"]);

		$this->cheetahmailServiceAdaptor->removeFromList($this->subscriberListId, $emailAddress);
		$user = $this->cheetahmailServiceAdaptor->getUser($emailAddress);

		$this->assertFalse(isset($user["sub"]));
	}

	protected function getRandomEmailAddress() {
		return uniqid() . "-test@clock.co.uk";
	}
}
