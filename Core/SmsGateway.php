<?php
/**
 *
 * @copyright Clock Limited 2007
 * @version 3.0 - $Revision$ - $Date$
 * @package Core
 */

/**
 * Include Data.php so that DataControl can be extended.
 */
require_once("Atrox/Core/Data/Data.php");


define("SMSMSG_TARIFF_ZERO", 0);
define("SMSMSG_TARIFF_25", 25);	
define("SMSMSG_TARIFF_50", 50);		
define("SMSMSG_TARIFF_100", 100);
define("SMSMSG_TARIFF_150", 150);
define("SMSMSG_TARIFF_300", 300);
define("SMSMSG_TARIFF_500", 500);

define("SMSMSG_OPERATOR_NOTSET", -1);
define("SMSMSG_OPERATOR_O2", 23410);
define("SMSMSG_OPERATOR_VODAFONE", 23415);	
define("SMSMSG_OPERATOR_THREE", 23420);
define("SMSMSG_OPERATOR_TMOBILE", 23430);
define("SMSMSG_OPERATOR_ORANGE", 23433);

// US SMS Operators
define("SMSMSG_OPERATOR_CINGULAR_BLUE", 31001);
define("SMSMSG_OPERATOR_CINGULAR_ORANGE", 31002);
define("SMSMSG_OPERATOR_VERIZON", 31003);
define("SMSMSG_OPERATOR_TMOBILE_US", 31004);
define("SMSMSG_OPERATOR_SPRINT", 31005);
define("SMSMSG_OPERATOR_DOBSON", 31006);
define("SMSMSG_OPERATOR_NEXTEL", 31007);
define("SMSMSG_OPERATOR_BOOST", 31008);
define("SMSMSG_OPERATOR_ALLTEL", 31009);
define("SMSMSG_OPERATOR_USCELLULAR", 31012);
	
define("SMSMSG_PROFILE_ZERORATED", 11192);
define("SMSMSG_PROFILE_PSMS", 11193);

define("SMSMSG_TYPE_INCOMING", 1);
define("SMSMSG_TYPE_OUTGOING", 2);

define("SMSMSG_STATE_NOTSENT", -1);
define("SMSMSG_STATE_OK", 0);

/**
 *
 * @author Edward Pearson (Clock Ltd) {@link mailto:ed.pearson@clock.co.uk ed.pearson@clock.co.uk }
 * @copyright Clock Limited 2007
 * @version 3.0 - $Revision$ - $Date$
 * @package Core
 */
class SmsMessage {
	var $recipient = "";
	var $originator = "80012";
	var $messageText = null;
	var $country = null;
	var $operator = SMSMSG_OPERATOR_NOTSET;
	var $tariff = SMSMSG_TARIFF_ZERO;
	var $responseCode= SMSMSG_STATE_NOTSENT;
	var $responseText = "Not Sent";
	var $application;
	var $smsGateway;
	var $type = SMSMSG_TYPE_OUTGOING;
	var $transactionId = -1;
	var $keywords = null;
	var $dirty = true;
	var $tariffCodes = array();				
	function SmsMessage($smsGateway) {
		$this->smsGateway = $smsGateway;
		$this->application = &CoreFactory::getApplication();
		$this->tariffCodes = array(SMSMSG_TARIFF_25 => "60030",
													SMSMSG_TARIFF_50 => "60040",
													SMSMSG_TARIFF_100 => "60070",
													SMSMSG_TARIFF_150 => "80160",
													SMSMSG_TARIFF_300 => "83833",
													SMSMSG_TARIFF_500 => "84300");
	}

	function getOriginator() {
		return $this->originator;
	}

	function getMessageText() {
		return $this->messageText;
	}

	function getOperator() {
		return $this->operator;
	}

	function getTariff() {
		return $this->tariff;
	}
	
	function getType() {
		return $this->type;
	}
	
	function getRecipient() {
		return $this->recipient;
	}

	function getResponseCode() {
		return $this->responseCode;
	}

	function getResponseText() {
		return $this->responseText;
	}

	function getTransactionId() {
		return $this->transactionId;
	}		
	
	function getKeyword($position = 1) {
		if ($this->dirty) {
			$this->keywords = explode(" ", trim($this->messageText));
			
			$this->dirty = false;
		}
		if (isset($this->keywords[$position - 1])) {
			return mb_strtolower($this->keywords[$position - 1]);
		} else {
			return null;
		}
	}

	function getCountry() {
		return $this->country;
	}
 
	function setCountry($country) {	
		return $this->country = $country;
	}

	function setOperator($operator) {
		if (array_key_exists($operator, $this->smsGateway->operators)) {
			$this->operator = $operator;
		} else {
			$this->application->errorControl->addError("Invalid Operator");
		}			
	}
	
	function setTariff($value) {
		if (array_key_exists($value, $this->smsGateway->tariffs)) {
			$this->tariff = $value;
			if ($this->tariff != SMSMSG_TARIFF_ZERO) {
				$this->setOriginator($this->tariffCodes[$value]);
			}
		} else {
			$this->application->errorControl->addError("Unable to set tariff '$value' not an accepted as a tariff ");
		}
	}
	
	function setMessageText($value) {
		$this->dirty = true;
		$this->messageText = mb_substr($value, 0, 160);
	}

	function setOriginator($value) {
		return $this->originator = $value;
	}

	function setRecipient($recipient, $validate = true, $clean = true) {
		// Array to hold numbers
		if ($clean) {
			$recipient = $this->smsGateway->cleanMobileNumber($recipient);
		}
		if ($validate == true) {
			if ($this->smsGateway->validateMobileNumber($recipient)) {
				$this->recipient = $recipient;
			} else {
				$this->application->errorControl->addError("Invalid Recipient '{$recipient}'");
				return;
			}
		}
		$this->recipient = $recipient;
	}
	
	function setType($type) {
		if (array_key_exists($type, $this->smsGateway->types)) {
			$this->type = $type;
		} else {
			$this->application->errorControl->addError("Invalid Message Type");
		}
	}

	function setResponseCode($value) {
		return $this->responseCode = $value;
	}

	function setResponseText($value) {
		return $this->responseText = $value;
	}
	
	function setTransactionId($value) {
		return $this->transactionId = $value;
	}		
}

class SmsGatewayFactory {
	/**
	 * @return smsGateway
	 */
	function getSmsGateway($country) {
		switch ($country) {
			case "UK":
				$smsGateway = new SmsGatewayUK();
				break;
			case "US":
				$smsGateway = new SmsGatewayUS();
				break;
		}
		return $smsGateway;
	}

	/**
	 * @return smsGateway
	 */
	function getSmsGatewayUK() {
		$smsGateway = new SmsGatewayUK();
		return $smsGateway;
	}

	/**
	 * @return smsGateway
	 */		
	function getSmsGatewayUS() {
		$smsGateway = new SmsGatewayUS();
		return $smsGateway;
	}
}
	
class SmsGatewayUK extends SmsGateway {
	var $username = "ClockLtdUK";
	var $password = "Mp98as7a";
	var $shortcode = "80012";
	var $posturl = "xml10.mblox.com";
				
	var $operators = array(
		SMSMSG_OPERATOR_O2 => "O2",
		SMSMSG_OPERATOR_VODAFONE => "Vodafone",
		SMSMSG_OPERATOR_THREE => "Three",
		SMSMSG_OPERATOR_TMOBILE => "T-Mobile",
		SMSMSG_OPERATOR_ORANGE => "Orange"			
	);
		
	var $tariffs = array(
		SMSMSG_TARIFF_ZERO => "£0.00",
		SMSMSG_TARIFF_25 => "£0.25",
		SMSMSG_TARIFF_50 => "£0.50",
		SMSMSG_TARIFF_100 => "£1.00",
		SMSMSG_TARIFF_150 => "£1.50",					
	);
}

class SmsGatewayUS extends SmsGateway {
	var	$username = "ClockUS";
	var	$password = "tunaxe2A";
	var	$shortcode = "28444"; // Temporary test shortcode
	var	$posturl = "xml.us.mblox.com";

	var $operators = array(
		SMSMSG_OPERATOR_CINGULAR_BLUE => "Cingular Blue",
		SMSMSG_OPERATOR_CINGULAR_ORANGE => "Cingular Orange",
		SMSMSG_OPERATOR_VERIZON => "Verizon",
		SMSMSG_OPERATOR_TMOBILE_US => "T-Mobile",
		SMSMSG_OPERATOR_SPRINT => "Sprint",
		SMSMSG_OPERATOR_DOBSON => "Dobson",
		SMSMSG_OPERATOR_NEXTEL => "Nextel",
		SMSMSG_OPERATOR_BOOST => "Boost",
		SMSMSG_OPERATOR_ALLTEL => "Alltel",
		SMSMSG_OPERATOR_USCELLULAR => "US Cellular"	
	);
		
	var $tariffs = array(
		SMSMSG_TARIFF_ZERO => "\$0.00",
		SMSMSG_TARIFF_25 => "\$0.25",
		SMSMSG_TARIFF_50 => "\$0.50",
		SMSMSG_TARIFF_100 => "\$1.00"			
	);
}

/**
 *
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2007
 * @version 3.0 - $Revision$ - $Date$
 * @package Base
 * @subpackage News
 */
class SmsGateway {
		
	var $username = null;
	var $password = null;		
	var $shortcode = null;
	var $posturl = null;
	var $freeNumbers = array();
	
	var $types = array(
		SMSMSG_TYPE_INCOMING => "Incoming",
		SMSMSG_TYPE_OUTGOING => "Outgoing"
	);

	var $operators = array();
	var $tariffs = array();

	function isFreeNumber($number) {
		if (in_array($number, $this->freeNumbers)) {
			return 1;
		} else {
			return 0;
		}
	}
	
	function addFreeNumber($number) {
		if (!in_array($number, $this->freeNumbers)) {
			$this->freeNumbers[] = $number;
		}
	}

	function SmsGateway() {
		$this->application = &CoreFactory::getApplication();
	}
	
	function createMessage($recipient = null, $messageText = null, $checkRecipient = true) {
		
		$smsMessage = new SmsMessage($this);
		if ($recipient && $checkRecipient == true) {
			$smsMessage->setRecipient($recipient);
		} else if ($recipient) {
			$smsMessage->setRecipient($recipient, false);
		}
		if ($messageText) {
			$smsMessage->setMessageText($messageText);			
		}
		$smsMessage->smsGateway = &$this;
		return $smsMessage;
	}
	
	function send($message) {
		if ($message->getTariff() == SMSMSG_TARIFF_ZERO) {
			$profile = SMSMSG_PROFILE_ZERORATED;
		} else {
			if ($message->getOperator() == SMSMSG_OPERATOR_NOTSET) {
				$this->application->errorControl->addError("Unable to send premium messages " . 
					"without first setting the operator");
				return false;
			}
			$profile = SMSMSG_PROFILE_PSMS;
		}

		if ($this->isFreeNumber($message->getRecipient())) {
			$profile = SMSMSG_PROFILE_ZERORATED;
			$message->setTariff(SMSMSG_TARIFF_ZERO);
		}

		$xml = "<NotificationRequest Version=\"3.4\">\n" . 
			"<NotificationHeader>\n" . 
			"<PartnerName>{$this->username}</PartnerName>\n" . 
			"<PartnerPassword>{$this->password}</PartnerPassword>\n" .
			"</NotificationHeader>\n" .
			"<NotificationList BatchID=\"1\">\n" .
			"<Notification SequenceNumber=\"1\" MessageType=\"SMS\">\n";
															
		$xml .= 
			"<Message><![CDATA[" . $message->getMessageText() . "]]></Message>\n" .
			"<Profile>{$profile}</Profile>\n" . 
			"<SenderID Type=\"Shortcode\">" . $message->getOriginator() . "</SenderID>\n";

		// 	Operator and Tariff are only needed 
		if ($profile == SMSMSG_PROFILE_PSMS) {
			$xml .= "<Operator>" .  $message->getOperator() . "</Operator>\n" .
				"<Tariff>" . $message->getTariff() . "</Tariff>\n";
		}
			echo $message->getRecipient();
		$xml .= 
			"<Subscriber>\n" .
			"<SubscriberNumber>" . $message->getRecipient() . "</SubscriberNumber>\n" .
			"</Subscriber>\n" .
			"</Notification>\n" .
			"</NotificationList>\n" .
			"</NotificationRequest>\n";	

		$networkControl = CoreFactory::getNetworkControl();
		$response = $networkControl->postData($this->posturl, "/send", 8180, "XMLDATA=" . urlencode($xml));
		$response = $response["body"];
		
	    $parser = xml_parser_create();

		xml_parse_into_struct($parser, $response, $vals, $index);
		xml_parser_free($parser);
		if (isset($index["SUBSCRIBERRESULTCODE"])) {				
			$message->setResponseCode(intval($vals[$index["SUBSCRIBERRESULTCODE"][0]]["value"]));
			$message->setResponseText($vals[$index["SUBSCRIBERRESULTTEXT"][0]]["value"]);				
		}
		$this->log($message);
		$success = $message->getResponseCode() == SMSMSG_STATE_OK;
		if (!$success) {
			$this->application->errorControl->addError("Message sending failed: $xml - $response");
		}
		return $success;
	}
	
	function processInbound($responseXml) {
		$responseXml = stripslashes($responseXml);
    $parser = xml_parser_create();	
    $vals = null;
    $index = null;
		xml_parse_into_struct($parser, $responseXml, $vals, $index);
		xml_parser_free($parser);			
		if (isset($index["RESPONSELIST"])) {
			$smsMessage = new SmsMessage($this);
			$smsMessage->setType(SMSMSG_TYPE_INCOMING);
			$smsMessage->setOriginator($vals[$index["ORIGINATINGNUMBER"][0]]["value"]);
			$smsMessage->setRecipient($vals[$index["DESTINATION"][0]]["value"], false);		
			$smsMessage->setOperator($vals[$index["OPERATOR"][0]]["value"]);
			$smsMessage->setTransactionId($vals[$index["TRANSACTIONID"][0]]["value"]);				
			$smsMessage->setMessageText($vals[$index["DATA"][0]]["value"]);
			$this->log($smsMessage);
			return $smsMessage;				
		} else {
			trigger_error("Unexpected input: " . $responseXml);
		}
	}
	
	function forwardRequest($smsMessage, $url) {
		$response = file($url . "?MobileNumber=" . $smsMessage->getOriginator() . 
			"&Operator=" . $smsMessage->getOperator() . 
			"&MessageText=" . urlencode($smsMessage->getMessageText()));
		
		// For debuging
		echo "Response<br />";
		echo implode("\n", $response);
	}
	
	function mailRequest($smsMessage, $emailAddress) {
		$smsMessage->smsGateway = null;
		mail($emailAddress, "SMS Gateway - Notification", print_r($smsMessage, true)); 
	}		
	
	function getDatabaseConnection() {
		if (!$dbHost = $_SERVER["DB_SERVER_PGSQL"]) {
			$dbHost = "localhost";
		}
	
		// Database Connection
		if (!$dbConnection = pg_connect("host=$dbHost port=5432 dbname=SmsGateway user=WebUser password=test")) {
			trigger_error("Database Error", E_USER_ERROR);
			exit;
		}
		return $dbConnection;
	}
	
	function log($message) {
		$dbConnection = $this->getDatabaseConnection();
		
		$sql = "INSERT INTO \"MessageLog\" (\"Originator\", \"Recipient\", \"Operator\", \"MessageText\", \"Type\", \"Tariff\", \"ResponseCode\", \"ResponseText\", \"TransactionId\") VALUES ('" . 
		addslashes($message->getOriginator()) . "', '" . 
		addslashes($message->getRecipient()) . "', '" . 
		addslashes($message->getOperator()) . "', '" . 
		addslashes($message->getMessageText()) . "', '" . 
		addslashes($message->getType()) . "', '" . 
		addslashes($message->getTariff()) . "', '" . 
		addslashes($message->getResponseCode()) . "', '" . 
		addslashes($message->getResponseText()) . "', '" . 
		addslashes($message->getTransactionId()) . "')";
		
		pg_query($dbConnection, $sql);			
	}
	
	function cleanMobileNumber($number) {			
		// Do the best to format number correctly
		$number = str_replace("(", "", $number);
		$number = str_replace(")", "", $number);
		$number = str_replace(" ", "", $number);
		$number = str_replace("-", "", $number);
		$number = str_replace("+", "", $number);

		if (mb_substr($number, 0, 4) == "0044") {
			$number = "44" . mb_substr($number, 4, mb_strlen($number) - 4);
		}
		
		if (mb_substr($number, 0, 2) == "00") {
			$number = mb_substr($number, 2, mb_strlen($number) - 2);
		}
		
		if (mb_substr($number, 0, 1) == "0") {
			$number = "44" . mb_substr($number, 1, mb_strlen($number) - 1);
		}

		return $number;
	}

	function validateMobileNumber($number) {
		if ($number == null) {

			return 0;
		} else {
			return preg_match("/\d{12}/", $number);
		}
		
	}
}