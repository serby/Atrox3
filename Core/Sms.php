<?php
/**
 * @package Core
 * @copyright Clock Limited 2007
 * @version 3.0 - $Revision$ - $Date$
 */

/**
 * Short Code Constants
 */
 define("SMS_SC_25", "80012");
 define("SMS_SC_50", "85239");
 define("SMS_SC_150", "87140");


/**
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2007
 * @version 3.0 - $Revision$ - $Date$
 * @package Core
 */
class SmsMessage {
	var $recipients = "";
	var $originator = "Clock";
	var $messageText = "";
	var $operator = "";
	var $billingType = "";

	function SmsMessage($recipients = "", $originator = "",
		$messageText = "", $operator = "", $billingType = "") {
		$this->setRecipients($recipients);
		$this->setOriginator($originator);
		$this->setMessageText($messageText);
		$this->getOperator($operator);
		$this->getBillingType($billingType);
	}

	function getRecipients() {
		return $this->recipients;
	}

	function getOriginator() {
		return $this->originator;
	}

	function getOperator() {
		return $this->operator;
	}

	function getBillingType() {
		return $this->billingType;
	}

	function getMessageText() {
		return $this->messageText;
	}

	function setOriginator($value) {
		$this->originator = mb_substr($value, 0, 12);
	}

	function setOperator($value) {
		$this->operator = $value;
	}

	function setBillingType($value) {
		$this->billingType = $value;
	}

	function setMessageText($value) {
		$this->messageText = mb_substr($value, 0, 160);
	}

	function setRecipients($recipients) {
		// Array to hold numbers
		$this->recipients = array();
		foreach (explode(",", $recipients) as $sendNumber) {
			if (SmsControl::validateMobileNumber($sendNumber)) {
				$this->recipients[] = $sendNumber;
			}
		}
		$this->recipients = implode(",", $this->recipients);
	}
}

/**
 * Sms Control
 *
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk
 * paul.serby@clock.co.uk }
 * @copyright Clock Limited 2007
 * @version 3.0 - $Revision$ - $Date$
 * @package Core
 */
class SmsControl {

	// Where to post the message
	var $host = "gateway.textout.net";
	var $port = 80;
	var $sendPage = "/xml/sendsms.cfm";
	var $balancePage = "/creditcheck.cfm";

	var $messageType = 0;
	var $username = "syd@clockltd.com";
	var $password = "26OM03SY";
	var $originator = "Clock.";
	var $deliveryReport = "Y";
	var $errorText = "";
	var $shortCode = "";
	var $transactionId = "";
	var $responseCode = "";
	var $credit = 0;

	function getAccountCredit() {
		$response = $this->postData($this->host, $this->balancePage, $this->port, "USERNAME=" .
			$this->username . "&PASSWORD=" . $this->password, 1);
		$credit = mb_substr(trim($response), 16);
		return $credit;
	}

	function makeNew() {
		return new SmsMessage();
	}

	function map(&$data) {
		$smsMessage = new SmsMessage();
		$smsMessage->setRecipients($data["Recipients"]);
		$smsMessage->setOriginator($data["Originator"]);
		$smsMessage->setMessageText(stripslashes($data["MessageText"]));
		return $smsMessage;
	}

	/**
	 *
	 */
	function parseResponse($xml) {
		$parser = xml_parser_create();
		$xml = stripslashes($xml);
		xml_parse_into_struct($parser, $xml, $vals, $index);
		xml_parser_free($parser);
		$this->responseCode = $vals[$index["RESPONSE"][0]]["value"];
		$this->transactionId = $vals[$index["TRANSACTIONID"][0]]["value"];
		$this->errorText = isset($vals[$index["ERRORTEXT"][0]]["value"]) ? $vals[$index["ERRORTEXT"][0]]["value"] : "";
	}

	function send($smsMessage) {
		$application = &CoreFactory::getApplication();

		if ($smsMessage->recipients == "") {
			$application->errorControl->addError("'To' must be set");
		}
		if ($smsMessage->originator == "") {
			$application->errorControl->addError("'From' must be set");
		}
		if ($smsMessage->messageText == "") {
			$application->errorControl->addError("Message Body must be set");
		}
		if (mb_strlen($smsMessage->messageText) > 160) {
			$application->errorControl->addError("Message Body must be less than 160 characters");
		}
		if ($application->errorControl->hasErrors()) {
			return false;
		}

		$out = "<?xml version=\"1.0\"?>\n";
		$out .= "<smsmessage>\n";
		$out .= "<smsauthentication><username>" . 
			$this->username . "</username><password>" . 
			$this->password . "</password></smsauthentication>\n";
		$out .= "<smsmessagedata>\n";
		$out .= "<deliveryreport>" . $this->deliveryReport . "</deliveryreport>\n";
		$out .= "<recipient>" . $smsMessage->recipients . "</recipient>\n";
		$out .= "<mc></mc>\n";
		$out .= "<messagetype>". $this->messageType . "</messagetype>\n";
		$out .= "<originator>" . $smsMessage->getOriginator() . "</originator>\n";
		$out .= "<messagetext>" . $smsMessage->getMessageText() ."</messagetext>\n";
		$out .= "<controldata></controldata>\n";
		$out .= "<deliverydate></deliverydate>\n";
		$out .= "<deliverytime></deliverytime>\n";
		$out .= "</smsmessagedata>\n";
		$out .= "</smsmessage>\n";

		$xml = $this->postData($this->host, $this->sendPage, $this->port, "XML=". $out);
		$this->parseResponse($xml);

		$this->log($smsMessage);

		if ($this->errorText != "") {
			$application->errorControl->addError($this->errorText);
			return false;
		}
		return true;
	}

	function log(&$smsMessage) {
	
		//$dbHost = $_SERVER["DB_SERVER_PGSQL"];	
		if ($_SERVER["SERVER_TYPE"] == "Development") {
			$dbHost = "localhost";
		} else {
			$dbHost = "ned.ec1y.clock.private";
		}
				
	
		$dbConnectionForSms = pg_connect("host=$dbHost port=5432 dbname=SmsGateway user=WebUser password=test");
		
		$sql = "INSERT INTO \"tblOutgoingLog\" (\"sOperator\",\"sTransactionID\",\"sOriginator\",\"sRecipient\",\"sMessageText\",\"sBillingAction\",\"sShortCode\",\"iMessageType\",\"sResponse\",\"sErrorText\")
				VALUES ('". $smsMessage->getOperator() ."','$this->transactionId','" . $smsMessage->getOriginator() . "','" .  $smsMessage->recipients . "','" .
				mb_substr(addslashes($smsMessage->getMessageText()),0,160)."','". $smsMessage->getBillingType() ."','". $this->shortCode ."','$this->messageType','$this->responseCode','$this->errorText')";
		pg_query($dbConnectionForSms, $sql);
	}

	function validateMobileNumber(&$number) {
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
			$number = "44" . mb_substr($number,1, mb_strlen($number) - 1);
		}
		if(mb_substr($number, 0, 2) == "44") {
			// Vaildate number
			if (mb_strlen($number) != 12) {
				return "0";
			} else {
				return "1";
			}
		} else {
			if (is_numeric($number)) {
				return "1";
			}
		}
		return "0";
	}

	/**
	 * Post data to a url
	 */
	function postData($host, $page, $port, $data, $timeout = 30) {
		$sock = fsockopen ($host, $port, $errno, $errstr, $timeout);
		if (!$sock) {
			return "$errstr ($errno)\n";
		} else {
			fputs($sock, "POST $page HTTP/1.0\r\n");
			fputs($sock, "Host: $host\r\n");
			fputs($sock, "Content-type: application/x-www-form-urlencoded\r\n");
			fputs($sock, "Content-length: ".(mb_strlen($data))."\r\n");
			fputs($sock, "Accept: */*\r\n");
			fputs($sock, "\r\n");
			fputs($sock, "$data\r\n");
			fputs($sock, "\r\n");

			$headers = "";
			while ($str = trim(fgets($sock, 4096)))
				$headers .= "$str\n";
			$body = "";
			while (!feof($sock))
				$body .= fgets($sock, 4096);
			fclose($sock);
		}
		return $body;
	}
}


/**
 * Sms Control
 *
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2007
 * @version 3.0 - $Revision$ - $Date$
 * @package Core
 */
class PremiumSmsControl extends SmsControl {

	var $shortCode = "80012";
	// Where to post the message
	var $host = "gateway.txtstream.net";
	var $port = 80;
	var $sendPage = "/xml/sendsms.cfm";

	var $messageType = 0;
	var $username = "clock";
	var $password = "speak3fork";
	var $accountReference = "1058";
	var $originator = "Clock.";
	var $deliveryReport = "Y";
	var $errorText = "";
	var $transactionId = "";
	var $responseCode = "";
	var $credit = 0;

	// Always ZERO charge these mobile numbers
	var $freeUsers = array("447970600331", "447765831697",
		"447771933313","447881550999", "447860954978", "447841833763",
		"447743103287", "447816862768", "4478210301856", "447788184077", "447768868612", "447727013875", "447821301856" );

	function PremiumSmsControl($shortCode = "80012") {
		$this->shortCode = $shortCode;
	}

	function setShortCode($shortCode) {
		$this->shortCode = $shortCode;
	}

	function map(&$data) {
		$smsMessage = &parent::map($data);
		$smsMessage->setOperator($data["Operator"]);
		// Default to MT billing
		$smsMessage->setBillingType("MT");
		
		return $smsMessage;
	}

	function send($smsMessage) {
		$application = &CoreFactory::getApplication();

		if ($smsMessage->recipients == "") {
			$application->errorControl->addError("'To' must be set");
		}
		if ($smsMessage->originator == "") {
			$application->errorControl->addError("'From' must be set");
		}
		if ($smsMessage->messageText == "") {
			$application->errorControl->addError("Message Body must be set");
		}
		if (mb_strlen($smsMessage->messageText) > 160) {
			$application->errorControl->addError("Message Body must be less than 160 characters");
		}
		if ($smsMessage->operator == "") {
			$application->errorControl->addError("Operator must be set");
		}
		if ($application->errorControl->hasErrors()) {
			return false;
		}

		// 
		if (sizeof(array_intersect(explode(",", $smsMessage->recipients), $this->freeUsers)) > 0) {
			$smsMessage->setBillingType("ZERO");
		}

		$out = "<?xml version=\"1.0\"?>\n";
		$out .= "<smsmessage>\n";
		$out .= "<smsauthentication><username>" . $this->username .
			"</username><password>" . $this->password .
			"</password><accountref>" . $this->accountReference .
			"</accountref></smsauthentication>\n";
		$out .= "<smsmessagedata>\n";
		$out .= "<billingaction>" . $smsMessage->billingType . "</billingaction>\n";
		$out .= "<shortcode>" . $this->shortCode . "</shortcode>\n";
		$out .= "<deliveryreport>" . $this->deliveryReport . "</deliveryreport>\n";
		$out .= "<recipient>" . $smsMessage->recipients . "</recipient>\n";
		$out .= "<mc></mc>\n";
		$out .= "<messagetype>". $this->messageType . "</messagetype>\n";
		$out .= "<originator>" . $smsMessage->getOriginator() . "</originator>\n";
		$out .= "<operator>" . $smsMessage->getOperator() . "</operator>\n";
		$out .= "<messagetext>" . $smsMessage->getMessageText() ."</messagetext>\n";
		$out .= "<controldata></controldata>\n";
		$out .= "<deliverydate></deliverydate>\n";
		$out .= "<deliverytime></deliverytime>\n";
		$out .= "</smsmessagedata>\n";
		$out .= "</smsmessage>\n";

		$xml = $this->postData($this->host, $this->sendPage, $this->port, "XML=" . $out);
		$this->parseResponse($xml);
		$this->log($smsMessage);

		if ($this->errorText != "") {
			$application->errorControl->addError($this->errorText);
			return false;
		}
		return true;
	}
}