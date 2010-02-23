<?php
/**
 *
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Core
 */

/**
 *
 * @author Edward Pearson (Clock Ltd) {@link mailto:ed.pearson@clock.co.uk ed.pearson@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Core
 */

class AccessControl {
	var $accessControlList = array();
	/**
	 * Adds a number to the access control list
	 * @param string $service
	 * @param string $number
	 */
	function addNumber($service, $number) {
		$this->accessControlList[$service][] = $number;
	}
	
	/**
	 * See if a certain number has authority.
	 * @param string $service
	 * @param string $number
	 */
	function isAllowed($service, $number) {
		return isset($this->accessControlList[$service]) && in_array($number, $this->accessControlList[$service]);
	}
}

class SmsSubscriptionControl {
	var $service = array();
	var $description = null;
	var $keyword = null;
	var $smsGateway = null;
	var $dbConnection = null;
	var $services = array();
	var $descriptions = array();
	var $infos = array();
	var $accessControl = null;
	var $freeNumbers = array();
	
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

	/**
	 *  Creates the accessControl and dbConnection objects
	 */				
	function SmsSubscriptionControl() {
		$this->accessControl = new AccessControl();
		$this->dbConnection = $this->getDatabaseConnection();
	}
	
	/**
	 * Selects the service (they must be added first)
	 * @param string $service
	 */
	function selectService($service) {
		$i = 0;
		foreach ($this->services as $value) {
			if ($value == $service) {
				$this->service["Keyword"] = $value;
				$this->service["Description"] = $this->descriptions[$i];
				$this->service["Information"] = $this->infos[$i];
				return 1;
			}
			$i++;
		}
		return "Service does not exist.";
	}
	
	/**
	 * Returns an array containing information about a given service.
	 * @param string $service
	 * @return array
	 */
	function getService($service) {
	$i = 0;
		foreach ($this->services as $value) {
			if ($value == $service) {
				return array(
				"Keyword" => $value,
				"Description" => $this->descriptions[$i],
				"Information" => $this->infos[$i]);
			}
			$i++;
		}
	}
	
	/**
	 * Adds a service, along with its description and information.
	 * @param string $service
	 * @param string $description
	 * @param string $info
	 */
	function addService($service, $description, $info) {
		if (!in_array($service, $this->services)) {
			$this->services[] = $service;
			$this->descriptions[] = $description;
			$this->infos[] = $info;
		} else {
			return "Service already exists.";
		}
	}
	
	/**
	 * Processes an incoming message and performs the required actions.
	 * @param SmsMessage $message
	 */
	function processMessageIn($message) {
		$service = $message->getKeyword();
		$keyword = $message->getKeyword(2);

		switch ($keyword) {
			case "get":
				$this->addSubscriber($message);
			break;
			case "yes":
				$this->confirmSubscriber($message);
			break;
			case "cancel":
			case "quit":
			case "end":
			case "unsubscribe":
			case "stop":
				$this->removeSubscriber($message);
			break;
			case "#":
				$this->processMessageOut($message, null);
			break;
			case "#uk":
				$this->processMessageOut($message, "UK");
			break;
			case "#us":
				$this->processMessageOut($message, "US");
			break;
			case "status":
				$this->statusCheck($message, $service);
			break;
		}

	}
	
	/**
	 * Mass-mailing functionality. When it's number is on the accessControl list
	 * a mobile can send a message to everybody subscribed to a particular service.
	 * @param SmsMessage $smsMessage
	 * @param string $countryCode Optional: Specify which country to send to (US|UK)
	 * @param string $countryIncoming The country the incoming message came from (for the reply)
	 */
	function processMessageOut($smsMessage, $countryCode) {
		$countryIncoming = $smsMessage->getCountry();
		
		$senderNumber = $smsMessage->getOriginator();
		if (!$this->accessControl->isAllowed($smsMessage->getKeyword(),$senderNumber)) { 
			$message = "You do not have permission to send to this list.";
			$this->sendResponse($smsMessage, $message, $countryIncoming);
		} else {
			$message = $this->sendMessage($smsMessage->getKeyword(),$smsMessage->getMessageText(),$countryCode);
			$this->sendResponse($smsMessage, $message, $countryIncoming);
		}
	}
	
	/**
	 * Send a message to everybody subscribed to a particular service.
	 * @param string $service
	 * @param string $message
	 * @param string $countryCode
	 * @return string
	 */
	function sendMessage($service, $message, $countryCode = null) {
			$message = explode(" ", $message);
			unset($message[0]);
			unset($message[1]);
			$message = implode(" ", $message);
									    	    	
    	if ($countryCode !== null) {
    		$whereClause = "AND \"CountryCode\" = '{$countryCode}'";
    	} else {
    		$whereClause = null;
    	}
    	
    	$sql = "SELECT * FROM \"Subscriptions\" WHERE \"Service\" = '$service' AND \"Status\" = 'Subscribed' $whereClause";    	    	
			$result = pg_query($this->dbConnection, $sql);
						
			// Make sure message isn't too long
			$message = mb_substr($message,0, 160);
			
			$sentCount = array();
			
			$sentCount["UK"] = 0;
			$sentCount["US"] = 0;
			
			while ($data = pg_fetch_array($result)) {
				$smsGateway = SmsGatewayFactory::getSmsGateway($data["CountryCode"]);
				$smsMessage = $smsGateway->createMessage($data["Number"],$message);
				$smsGateway->send($smsMessage);
				$sentCount[$data["CountryCode"]]++;
			}
			
			$output = "";
			$total = 0;
			foreach ($sentCount as $key => $value) {
				$output .= "{$key}={$value},";
				$total += $value;
			}
			$output = mb_substr($output, 0, -1) . ". Total={$total}";
			return "Message successfully sent to the following: {$output}";
    }
		
	/**
	 * Creates a new database connection
	 * @return dbConnection $dbConnection
	 */
	function getDatabaseConnection() {
		if (!$dbHost = $_SERVER["DB_SERVER_PGSQL"]) {
			$dbHost = "localhost";
		}
		// Database Connection
		if (!$dbConnection = pg_connect("host={$dbHost} port=5432 dbname=SmsGateway user=WebUser password=test")) {
			trigger_error("Database Error", E_USER_ERROR);
			exit;
		}
		return $dbConnection;
	}

	/**
	 * Gets the status of a number given the service in question.
	 * @param string $number
	 * @param string $service
	 * @return string
	 */
	function isSubscribed($number, $service) {
		$sql = "SELECT * FROM \"Subscriptions\" WHERE \"Number\" = '$number' " . 
		"AND \"Service\" = '$service'";
		$result = pg_query($this->dbConnection, $sql);
		if (pg_num_rows($result) > 0) {
			$subscription = pg_fetch_array($result);
			return $subscription["Status"];
		} else {
			return "Not Subscribed";
		}
	}
	
	/**
	 * Adds a new subscriber to the database.
	 * @param SmsMessage $smsMessage
	 * @param string $country
	 */
	function addSubscriber($smsMessage) {
		$country = $smsMessage->getCountry();
		$this->keyword = mb_strtolower($smsMessage->getKeyword(2));
		if ($this->service["Keyword"] == null) {
			$this->sendResponse($smsMessage, "Unknown Service. Please check and try again.");
		} else {
			$number = $smsMessage->getOriginator();
			$operator = $smsMessage->getOperator();
			$keyword = $this->service["Keyword"];
			$shortcode = $smsMessage->getRecipient();
		
		  	switch ($this->isSubscribed($number, $keyword)) {
				case "Not Subscribed":
					$message = "Thanks for requesting {$this->service["Description"]}. You must now text: \"{$this->service["Keyword"]} YES\" to {$shortcode} to confirm and start your subscription.";
				  	$sql = "INSERT INTO \"Subscriptions\" (\"Number\", \"Operator\", \"Service\",\"Status\", \"CountryCode\") VALUES ('$number', '$operator', '$keyword', 'Awaiting Confirmation', '$country')";
					pg_query($this->dbConnection,$sql);		
				break;
				case "Subscribed":
					$message = "You're already subscribed to {$this->service["Description"]}. Text \"{$this->service["Keyword"]} STOP\" to unsubscribe.";
				break;	  		
				case "Awaiting Confirmation":
					$message = "You have already requested this service, to confirm, TEXT '$keyword YES' to $shortcode.";
				break;
			}
		$this->sendResponse($smsMessage, $message);
		}
	}
	
	/**
	 * Confirms a subscription request, the number will now recieve list texts
	 * @param SmsMessage $smsMessage
	 * @param string $country
	 */
	function confirmSubscriber($smsMessage) {
		$country = $smsMessage->getCountry();
		$this->keyword = mb_strtolower($smsMessage->getKeyword(2));
		if ($this->service == null) {
			$this->sendResponse($smsMessage, "Unknown Service. Please check and try again.");
		}

		switch ($this->isSubscribed($smsMessage->getOriginator(), $this->service["Keyword"])) {
			case "Subscribed":
				$shortcode = $smsMessage->getRecipient();
	  			$message = "You're already subscribed to {$this->service["Description"]}. Text \"{$this->service["Keyword"]} STOP\" to {$shortcode} to cancel";
				$this->sendResponse($smsMessage, $message);
			break;
			case "Awaiting Confirmation":
				$number = $smsMessage->getOriginator();
				$shortcode = $smsMessage->getRecipient();
  				$message = "You're now subscribed to {$this->service["Description"]}. {$this->service["Information"]}. Text \"{$this->service["Keyword"]} STOP\" to {$shortcode} to cancel.";
				$sql = "UPDATE \"Subscriptions\" SET \"Status\" = 'Subscribed' WHERE \"Number\" = '$number' AND \"Service\" = '{$this->service["Keyword"]}'";
				pg_query($this->dbConnection,$sql);
				$this->sendResponse($smsMessage, $message);
				break;
			default:
				$message = "Your number is not currently awaiting confirmation for any of our services.";
				$this->sendResponse($smsMessage, $message);
		}
	}
	
	/**
	 * Removes a subscriber from the list
	 * @param SmsMessage $smsMessage
	 * @param string $country
	 */
	function removeSubscriber($smsMessage) {
		$country = $smsMessage->getCountry();
		if ($this->service == null) {
			$this->sendResponse($smsMessage, "Unknown Service. Please check and try again.");
		}

		switch ($this->isSubscribed($smsMessage->getOriginator(), $this->service["Keyword"])) {	
			case "Awaiting Confirmation":
			case "Subscribed":
				$number = $smsMessage->getOriginator();
				$message = "You have now been unsubscribed from " .	$this->service["Description"];
				$sql = "DELETE FROM \"Subscriptions\" WHERE \"Number\" = '$number'";
				pg_query($this->dbConnection,$sql);
				$this->sendResponse($smsMessage, $message);
			break;
			default:
				$message = "You are not subscribed to " . $this->service["Description"] . ".";
				$this->sendResponse($smsMessage, $message);
			break;
		}
	
	}
	
	/**
	 * Checks the status of a number against all the available services
	 * and sends the results to the user.
	 * @param SmsMessage $smsMessage
	 * @param string $country
	 * @return string
	 */
	function statusCheck($smsMessage, $service) {
		$subscribed = array();
		$waiting = array();
		$country = $smsMessage->getCountry();	
		$sql = "SELECT \"Service\", \"Number\", \"Status\" FROM \"Subscriptions\" WHERE \"Number\" = '" . $smsMessage->getOriginator() . "'";
	
		$result = pg_query($this->dbConnection, $sql);
		while ($rows = pg_fetch_array($result)) {
			switch ($rows["Status"]) {
				case "Subscribed":
				if ($rows["Service"] == $service || $service == "all") {
					$subscribed[] = $rows["Service"];
				}
				break;
				case "Awaiting Confirmation":
				if ($rows["Service"] == $service || $service == "all") {
					$waiting[] = $rows["Service"];
				}
				break;
			}
		}
	
		$shortcode = $smsMessage->getRecipient();		
		
		foreach ($subscribed as $service) {
			$serviceDetails = $this->getService($service);
			$message = "You're subscribed to {$serviceDetails["Description"]}. Text \"$service STOP\" to {$shortcode} to cancel";
			$this->sendResponse($smsMessage, $message);		
		}
		
		foreach ($waiting as $service) {
			$serviceDetails = $this->getService($service);
  		$message = "We are awaiting confirmation for your subscription to {$serviceDetails["Description"]}. Text \"$service YES\" to {$shortcode} to confirm";
			$this->sendResponse($smsMessage, $message);		
		}
	}
	
	/**
	 * Responds to a SmsMessage
	 * @param SmsMessage $smsMessage
	 * @param string $messageText
	 * @param string $country
	 */
	function sendResponse($smsMessage, $messageText) {
		$country = $smsMessage->getCountry();
		$this->smsGateway = SmsGatewayFactory::GetSmsGateway($country);
		$this->smsGateway->freeNumbers = array();
		foreach ($this->freeNumbers as $number) {
			$this->smsGateway->addFreeNumber($number);
		}
		$message = $this->smsGateway->createMessage($smsMessage->getOriginator(), $messageText);
		$this->smsGateway->send($message);
	}
}