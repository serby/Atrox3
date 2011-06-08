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
  'email' => 'genuine-user@clock.co.uk',
  'PASSION_CINEMA' => '',
  'OPT_IN' => '',
  'PASSION_GADGETS' => '',
  'REGION_BIRMINGHAM' => '',
  'PASSION_HOLIDAYS' => '',
  'REGION_LEEDS' => '',
  'HOL_END' => '',
  'DATE_OF_BIRTH' => '19780501000000',
  'BEHAVIOR_2' => '',
  'PASSION_TECHNOLOGY' => '',
  'UNSUBSCRIBED_DATE' => '',
  'REGION_MANCHESTER' => '',
  'BEHAVIOR_1' => '',
  'FREQ_MON' => '',
  'PASSION_ARTS' => '',
  'WORK_POSTCODE' => '',
  'FNAME' => 'Genuine',
  'PASSION_FOOD' => '',
  'ES_REG' => '',
  'FREQ_WED' => '',
  'REGION_GLAS_ED' => '',  'PASSION_BARS' => '',
  'SOURCE_TEST' => '',  'PASSION_ENT' => '',
  'PASSION_SHOES' => '',  'PASSION_TRAVEL' => '',
  'FREQ_THU' => '',
  'LNAME' => 'User',
  'PASSION_ENTREPRENEURSHIP' => '',
  'HOL_START' => '',
  'REGION_LONDON' => '',
  'PASSION_BEAUTY' => '',
  'FREQ_TUE' => '',
  'MOBILE_PHONE' => '',
		'GENDER' => 'M',
		'PASSION_FASHION' => '',
		'FREQ_FRI' => '',
		'CUSTOM_1' => '',
		'PASSION_INTERIORS' => '',
		'DATE_CREATED' => '',
		'HTML' => '',
		'BEHAVIOR_3' => '',
		'INCOME' => '',
		'FREQ_SUN' => '',
		'COUNTRY_RESIDENCE' => '',
		'ACQ_POINT' => '',
		'PASSION_BAGS' => '',
		'HOME_POSTCODE' => '',
		'AUTH_HASH' => '',
		'PASSION_BOOKS' => '',
		'PASSION_CAREERS' => '',
		'PASSION_TELEVISION' => '',
		'FREQ_SAT' => '',
		'subs' =>
		array (
			0 =>
			array (
				'sub' => '2086316516',
				'aid' => '2086472001',
				'date' => '20110118060948',
				'rcode' => '',
			),
		),
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
		$this->cheetahmailServiceAdaptor->setUser($emailAddress,
			array("FNAME" => "Paul", "LNAME" => "Serby", "GENDER" => "M", "DATE_OF_BIRTH" => "01-May-1978"));
		$user = $this->cheetahmailServiceAdaptor->getUser($emailAddress);
		$this->assertEquals("Paul", $user["FNAME"]);
	}

	public function testSetUserFollowedByUpdateWithOneAuthenticateSucceed() {

		$this->cheetahmailServiceAdaptor->authenticate();
		$emailAddress = $this->getRandomEmailAddress();
		$this->cheetahmailServiceAdaptor->setUser($emailAddress,
			array("FNAME" => "Paul", "LNAME" => "Serby", "GENDER" => "M", "DATE_OF_BIRTH" => "01-May-1978"));

		$this->cheetahmailServiceAdaptor->setUser($emailAddress,
			array("FNAME" => "Pauly", "LNAME" => "Serby", "GENDER" => "M", "DATE_OF_BIRTH" => "01-May-1978"));
		$user = $this->cheetahmailServiceAdaptor->getUser($emailAddress);
		$this->assertEquals("Pauly", $user["FNAME"]);

	}

	public function testDoubleAuthenticationSucceed() {

		$this->cheetahmailServiceAdaptor->authenticate();
		$emailAddress = $this->getRandomEmailAddress();
		$this->cheetahmailServiceAdaptor->setUser($emailAddress,
			array("FNAME" => "Paul", "LNAME" => "Serby", "GENDER" => "M", "DATE_OF_BIRTH" => "01-May-1978"));

		$this->cheetahmailServiceAdaptor->authenticate();
		$this->cheetahmailServiceAdaptor->setUser($emailAddress,
			array("FNAME" => "Pauly", "LNAME" => "Serby", "GENDER" => "M", "DATE_OF_BIRTH" => "01-May-1978"));
		$user = $this->cheetahmailServiceAdaptor->getUser($emailAddress);
		$this->assertEquals("Pauly", $user["FNAME"]);
	}

	public function testAddingNewUserWithInvaildEmailAddressThrowsException() {
		$this->cheetahmailServiceAdaptor->authenticate();
		$this->setExpectedException("Exception");
		$this->cheetahmailServiceAdaptor->setUser("abc");
	}

	public function testUpdatingEmailAddressSuccess() {
		$emailAddress = $this->getRandomEmailAddress();
		$newEmailAddress = $this->getRandomEmailAddress();

		$this->cheetahmailServiceAdaptor->authenticate();
		$this->cheetahmailServiceAdaptor->setUser($emailAddress, array("FNAME" => "ChangeAddress"), $this->subscriberListId);
		$cheetahmailServiceAdaptor = $this->cheetahmailServiceAdaptor->updateEmailAddress(
			$this->subscriberListId, $emailAddress, $newEmailAddress);

		$this->assertInstanceOf("cheetahmailServiceAdaptor", $cheetahmailServiceAdaptor);
	}

	public function testUnsubscribingEmailFromSubscriberList() {
		$emailAddress = $this->getRandomEmailAddress();

		$this->cheetahmailServiceAdaptor->authenticate();
		$this->cheetahmailServiceAdaptor->setUser($emailAddress, array("FNAME" => "Unsubscribe"), $this->subscriberListId);
		$user = $this->cheetahmailServiceAdaptor->getUser($emailAddress);
		$this->assertEquals($this->subscriberListId, $user["subs"][0]["sub"]);

		$this->cheetahmailServiceAdaptor->removeFromList($this->subscriberListId, $emailAddress);
		$user = $this->cheetahmailServiceAdaptor->getUser($emailAddress);

		$this->assertFalse(isset($user["sub"]));
	}

	public function testSendingEmailUsingValidEventIdAndValidEmailAddress() {
		$emailAddress = $this->getRandomEmailAddress();
		$this->cheetahmailServiceAdaptor->authenticate();
		$cheetahmailServiceAdaptor = $this->cheetahmailServiceAdaptor->triggerEmail("161546", $emailAddress);
		$this->assertInstanceOf("cheetahmailServiceAdaptor", $cheetahmailServiceAdaptor);
	}

	public function testSendingEmailUsingInvalidEventIdAndValidEmailAddressThrowsException() {
		$emailAddress = $this->getRandomEmailAddress();
		$this->cheetahmailServiceAdaptor->authenticate();
		$this->setExpectedException("Exception");
		$this->cheetahmailServiceAdaptor->triggerEmail("123456789", $emailAddress);
	}

	public function testSendingEmailWithValidAuthHash() {
		$emailAddress = $this->getRandomEmailAddress();
		$this->cheetahmailServiceAdaptor->authenticate();
		$cheetahmailServiceAdaptor = $this->cheetahmailServiceAdaptor->triggerEmail("161546", $emailAddress, array("AUTH_HASH" => "123456789"));
		$this->assertInstanceOf("cheetahmailServiceAdaptor", $cheetahmailServiceAdaptor);
	}

	public function testSendingEmailWithValidDynamicRegion() {
		$emailAddress = $this->getRandomEmailAddress();
		$this->cheetahmailServiceAdaptor->authenticate();
		$cheetahmailServiceAdaptor = $this->cheetahmailServiceAdaptor->triggerEmail("161546", "test-tom.gallacher@clock.co.uk", array("REGION_KEY" => "5"));
		$this->assertInstanceOf("cheetahmailServiceAdaptor", $cheetahmailServiceAdaptor);
	}

	protected function getRandomEmailAddress() {
		return uniqid() . "-test@clock.co.uk";
	}
}
