<?php
/**
 * @package Core
 * @subpackage Payment
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 */

/**
  * Include Data.php so that DataControl can be extended.
 */
require_once("Atrox/Core/Data/Data.php");

/**
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @updated Edward Pearson (Clock Ltd) {@link mailto:edward.pearson@clock.co.uk edward.pearson@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Core
 * @subpackage Payment
 */
class CustomXPayControl {

	var $host = null;
	var $port = null;
	var $siteReference = null;
	var $certificatePath = null;

	function CustomXPayControl($siteReference, $certificatePath, $port = 5000, $host = "127.0.0.1") {
		$this->application = CoreFactory::getApplication();
		$this->host = $this->application->registry->get("Payment/Xpay/Host", "127.0.0.1");
		$this->port = $this->application->registry->get("Payment/Xpay/Port", 5000);
		$this->siteReference = $siteReference;
		$this->certificatePath = $certificatePath;
	}

	function isAlive() {
		if (!$socket = fsockopen($this->host, $this->port)) {
			return false;
		} else {
			fclose($socket);
			return true;
		}
	}
		
	/**
	 * This function makes a ST3DAuthRequest request to SecureTrading 
	 * @param DataEntity $trasaction The Transaction to be authorized.
	 * @return DataEntity $trasaction
	 */
	function makeST3DAuthRequest(&$transaction) {
		if (!($transaction instanceof DataEntity)) {
			trigger_error("Invalid class. Expected Class DataEntity ", E_USER_ERROR);
			exit;
		}
		
		$xml = "\n<RequestBlock Version=\"3.15\">\n";
		$xml.= "<Request Type=\"ST3DAUTH\">\n";

		// Operation
		$xml.= "<Operation>\n";
		$xml.= "	<Amount>" . round($transaction->get("Amount") * 100). "</Amount>\n";
		$xml.= "	<Currency>GBP</Currency>\n";
		$xml.= "	<SiteReference>" . $this->siteReference. "</SiteReference>\n";
		$xml.= "	<SettlementDay>1</SettlementDay>\n";
		$xml.= "</Operation>\n\n";

		// Get the transaction address relation
		$address = $transaction->getRelation("AddressId");

		// Customer Info
		$xml.= "<CustomerInfo>\n";
		$xml.= "	<Postal>\n";
		$xml.= "		<Name>\n";
		$xml.= "			<NamePrefix><![CDATA[" . $address->get("NamePrefix"). "]]></NamePrefix>\n";
		$xml.= "			<FirstName><![CDATA[" . $address->get("FirstName"). "]]></FirstName>\n";
		$xml.= "			<MiddleName></MiddleName>\n";
		$xml.= "			<LastName><![CDATA[" . $address->get("LastName"). "]]></LastName>\n";
		$xml.= "			<NameSuffix></NameSuffix>\n";
		$xml.= "		</Name>\n";
		$xml.= "		<Company><![CDATA[" . $address->get("CompanyOrHouseName") . "]]></Company>\n";
		$xml.= "		<Street><![CDATA[" . $address->get("CompanyOrHouseName") . " " . $address->get("AddressLine1") . ", " . $address->get("AddressLine2") . "]]></Street>\n";
		$xml.= "		<City><![CDATA[" . $address->get("Town"). "]]></City>\n";
		$xml.= "		<StateProv><![CDATA[" . $address->get("Region"). "]]></StateProv>\n";
		$xml.= "		<PostalCode><![CDATA[" . $address->get("Postcode"). "]]></PostalCode>\n";
		$xml.= "		<CountryCode><![CDATA[" . $address->get("Country"). "]]></CountryCode>\n";
		$xml.= "	</Postal>\n";
		$xml.= "	<Telecom>\n";
		$xml.= "		<Phone>" . $address->get("TelephoneNumber"). "</Phone>\n";
		$xml.= "	</Telecom>\n";
		$xml.= "	<Online>\n";
		$xml.= "		<Email>" . $address->get("EmailAddress"). "</Email>\n";
		$xml.= "	</Online>\n";
		$xml.= "</CustomerInfo>\n\n";

		// Payment Method
		$xml.= "<PaymentMethod>\n";
		$xml.= "	<CreditCard>\n";
		$xml.= "		<Type>" . $transaction->get("CardType"). "</Type>\n";
		$xml.= "		<Number>" . $transaction->get("CardNumber"). "</Number>\n";
		$xml.= "		<Issue>" . $transaction->get("CardIssue"). "</Issue>\n";
		$xml.= "		<StartDate>" . $transaction->get("CardStartDate"). "</StartDate>\n";
		$xml.= "		<SecurityCode>" . $transaction->get("SecurityCode"). "</SecurityCode>\n";
		$xml.= "		<ExpiryDate>" . $transaction->get("CardExpiryDate"). "</ExpiryDate>\n";
		$xml.= "		<ParentTransactionReference>" . $transaction->get("TransactionReference") . "</ParentTransactionReference>";
		$xml.= "	</CreditCard>\n";
		
		// 3-D Secure details. These are added by makeST3DCardQuery() method.
		$xml.= "	<ThreeDSecure>";
		$xml.= "		<Enrolled>" . $transaction->get("Enrolled") . "</Enrolled>";
		$xml.= "		<PaRes>" . $transaction->get("PaRes") . "</PaRes>";
		$xml.= "		<MD>" . $transaction->get("MD") . "</MD>";
		$xml.= "	</ThreeDSecure>";
		
		$xml.= "</PaymentMethod>\n\n";

		// Order
		$xml.= "<Order>\n";
		$xml.= "	<OrderReference>" . $transaction->get("OrderReference"). "</OrderReference>\n";
		$xml.= "	<OrderInformation>" . $transaction->get("OrderInformation"). "</OrderInformation>\n";
		$xml.= "</Order>\n\n";
		$xml.= "</Request>\n\n";


		// Certificate
		$xml.= "<Certificate>" .fread(fopen($this->certificatePath, "r"),
				filesize($this->certificatePath)). "</Certificate>\n\n";

		$xml.= "</RequestBlock>\n\n";

		$return_xml = "";

		$openSocket = fsockopen($this->host, $this->port, $errno, $errstr, 30);
		if (!$openSocket) {
			echo "$errstr ($errno)\n";
		} else {
			stream_set_timeout($openSocket, 120);
			fputs($openSocket, $xml);

			$i = 0;
			while ($line = fgets($openSocket, 4096)) {
				if ($i > 2)
					$return_xml.= $line;
				$i++;
			}
			fclose($openSocket);
		}

		$string = " - ST3DAuthRequest\nRequest:\n{$xml}\n\nResponse:\n{$return_xml}\n\n\n";

		$application = &CoreFactory::getApplication();
		$application->log($string, $type = "xpay");
		$index = "";
		$p = xml_parser_create();
		xml_parser_set_option($p, XML_OPTION_CASE_FOLDING, 0);
		xml_parser_set_option($p, XML_OPTION_SKIP_WHITE, 1);
		xml_parse_into_struct($p, $return_xml, $vals, $index);
		xml_parser_free($p);

		@$transaction->set("TransactionReference", $vals[$index["TransactionReference"][0]]["value"]);
		@$transaction->set("AuthCode", $vals[$index["AuthCode"][0]]["value"]);
		@$transaction->set("Result", $vals[$index["Result"][0]]["value"]);
		@$transaction->set("Message", $vals[$index["Message"][0]]["value"]);

		@$transaction->set("SecurityMessage", $vals[$index["SecurityMessage"][0]]["value"]);
		@$transaction->set("TransactionCompletedTimestamp", $vals[$index["TransactionCompletedTimestamp"][0]]["value"]);
		@$transaction->set("TransactionVerifier", $vals[$index["TransactionVerifier"][0]]["value"]);
		return $transaction;
	}

	/**
	 * This function is here for backward compatibility.
	 * @param DataEntity $trasaction The Transaction to be authorized.
	 * @return DataEntity $trasaction
	 */
	function commit(&$transaction) {
		return $this->makeAuthRequest($transaction);
	}

	/**
	 * This function makes a standard AUTH request to SecureTrading 
	 * @param DataEntity $trasaction The Transaction to be authorized.
	 * @return DataEntity $trasaction
	 */
	function makeAuthRequest(&$transaction) {
		if (!($transaction instanceof DataEntity)) {
			trigger_error("Invalid class. Expected Class DataEntity ", E_USER_ERROR);
			exit;
		}
		
		$xml = "\n<RequestBlock Version=\"3.15\">\n";
		$xml.= "<Request Type=\"AUTH\">\n";

		// Operation
		$xml.= "<Operation>\n";
		$xml.= "	<Amount>" . round($transaction->get("Amount") * 100). "</Amount>\n";
		$xml.= "	<Currency>GBP</Currency>\n";
		$xml.= "	<SiteReference>" . $this->siteReference. "</SiteReference>\n";
		$xml.= "	<SettlementDay>1</SettlementDay>\n";
		$xml.= "</Operation>\n\n";

		// Get the transaction address relation
		$address = $transaction->getRelation("AddressId");

		// Customer Info
		$xml.= "<CustomerInfo>\n";
		$xml.= "	<Postal>\n";
		$xml.= "		<Name>\n";
		$xml.= "			<NamePrefix><![CDATA[" . $address->get("NamePrefix"). "]]></NamePrefix>\n";
		$xml.= "			<FirstName><![CDATA[" . $address->get("FirstName"). "]]></FirstName>\n";
		$xml.= "			<MiddleName></MiddleName>\n";
		$xml.= "			<LastName><![CDATA[" . $address->get("LastName"). "]]></LastName>\n";
		$xml.= "			<NameSuffix></NameSuffix>\n";
		$xml.= "		</Name>\n";
		$xml.= "		<Company><![CDATA[" . $address->get("CompanyOrHouseName") . "]]></Company>\n";
		$xml.= "		<Street><![CDATA[" . $address->get("CompanyOrHouseName") . " " . $address->get("AddressLine1") . ", " . $address->get("AddressLine2") . "]]></Street>\n";
		$xml.= "		<City><![CDATA[" . $address->get("Town"). "]]></City>\n";
		$xml.= "		<StateProv><![CDATA[" . $address->get("Region"). "]]></StateProv>\n";
		$xml.= "		<PostalCode><![CDATA[" . $address->get("Postcode"). "]]></PostalCode>\n";
		$xml.= "		<CountryCode><![CDATA[" . $address->get("Country"). "]]></CountryCode>\n";
		$xml.= "	</Postal>\n";
		$xml.= "	<Telecom>\n";
		$xml.= "		<Phone>" . $address->get("TelephoneNumber"). "</Phone>\n";
		$xml.= "	</Telecom>\n";
		$xml.= "	<Online>\n";
		$xml.= "		<Email>" . $address->get("EmailAddress"). "</Email>\n";
		$xml.= "	</Online>\n";
		$xml.= "</CustomerInfo>\n\n";

		// Paymentmethod
		$xml.= "<PaymentMethod>\n";
		$xml.= "	<CreditCard>\n";
		$xml.= "		<Type>" . $transaction->get("CardType"). "</Type>\n";
		$xml.= "		<Number>" . $transaction->get("CardNumber"). "</Number>\n";
		$xml.= "		<Issue>" . $transaction->get("CardIssue"). "</Issue>\n";
		$xml.= "		<StartDate>" . $transaction->get("CardStartDate"). "</StartDate>\n";
		$xml.= "		<SecurityCode>" . $transaction->get("SecurityCode"). "</SecurityCode>\n";
		$xml.= "		<ExpiryDate>" . $transaction->get("CardExpiryDate"). "</ExpiryDate>\n";
		$xml.= "	</CreditCard>\n";
		$xml.= "</PaymentMethod>\n\n";

		// Order
		$xml.= "<Order>\n";
		$xml.= "	<OrderReference>" . $transaction->get("OrderReference"). "</OrderReference>\n";
		$xml.= "	<OrderInformation>" . $transaction->get("OrderInformation"). "</OrderInformation>\n";
		$xml.= "</Order>\n\n";
		$xml.= "</Request>\n\n";


		// Certificate
		$xml.= "<Certificate>" .fread(fopen($this->certificatePath, "r"),
				filesize($this->certificatePath)). "</Certificate>\n\n";

		$xml.= "</RequestBlock>\n\n";

		$return_xml = "";

		$openSocket = fsockopen($this->host, $this->port, $errno, $errstr, 30);
		if (!$openSocket) {
			echo "$errstr ($errno)\n";
		} else {
			stream_set_timeout($openSocket, 120);
			fputs($openSocket, $xml);

			$i = 0;
			while ($line = fgets($openSocket, 4096)) {
				if ($i > 2)
					$return_xml.= $line;
				$i++;
			}
			fclose($openSocket);
		}

		$string = " - Commit\nRequest:\n{$xml}\n\nResponse:\n{$return_xml}\n\n\n";

		$application = &CoreFactory::getApplication();
		$application->log($string, $type = "xpay");
		$index = "";
		$p = xml_parser_create();
		xml_parser_set_option($p, XML_OPTION_CASE_FOLDING, 0);
		xml_parser_set_option($p, XML_OPTION_SKIP_WHITE, 1);
		xml_parse_into_struct($p, $return_xml, $vals, $index);
		xml_parser_free($p);

		@$transaction->set("TransactionReference", $vals[$index["TransactionReference"][0]]["value"]);
		@$transaction->set("AuthCode", $vals[$index["AuthCode"][0]]["value"]);
		@$transaction->set("Result", $vals[$index["Result"][0]]["value"]);
		@$transaction->set("Message", $vals[$index["Message"][0]]["value"]);

		@$transaction->set("SecurityMessage", $vals[$index["SecurityMessage"][0]]["value"]);
		@$transaction->set("TransactionCompletedTimestamp", $vals[$index["TransactionCompletedTimestamp"][0]]["value"]);
		@$transaction->set("TransactionVerifier", $vals[$index["TransactionVerifier"][0]]["value"]);
		return $transaction;
	}

	/**
	 * This function makes a ST3DCardQuery to SecureTrading to check if a card is enrolled in the 3-D Secure scheme
	 * @param DataEntity $trasaction The Transaction with the details to be check with 3-D Secure.
	 * @return DataEntity $trasaction
	 */
	function makeST3DCardQuery(&$transaction, $tid) {
		if (!($transaction instanceof DataEntity)) {
			trigger_error("Invalid class. Expected Class DataEntity ", E_USER_ERROR);
			exit;
		}

		$xml = "\n<RequestBlock Version=\"3.15\">\n";
		$xml.= "<Request Type=\"ST3DCARDQUERY\">\n";

		// Operation
		$xml.= "<Operation>\n";
		$xml.= "	<Amount>" . round($transaction->get("Amount") * 100). "</Amount>\n";
		$xml.= "	<Currency>GBP</Currency>\n";
		$xml.= "	<SiteReference>" . $this->siteReference. "</SiteReference>\n";
		$xml.= "	<TermUrl>" . $this->application->registry->get("Site/Address") . "/term.php?TransactionId=" . $tid . "</TermUrl>\n";
		$xml.= "	<MerchantName>Clock Limited</MerchantName>\n";
		$xml.= "</Operation>\n\n";

		// Customer Info
		$headers = apache_request_headers();
		$xml.= "<CustomerInfo>\n";
		$xml.= "	<Accept>" .	$headers["Accept"]	. "</Accept>\n";
		$xml.= "	<UserAgent>" .	$headers["User-Agent"]	. "</UserAgent>\n";
		$xml.= "</CustomerInfo>\n\n";

		// Paymentmethod
		$xml.= "<PaymentMethod>\n";
		$xml.= "	<CreditCard>\n";
		$xml.= "		<Type>" . $transaction->get("CardType"). "</Type>\n";
		$xml.= "		<Number>" . $transaction->get("CardNumber"). "</Number>\n";
		$xml.= "		<Issue>" . $transaction->get("CardIssue"). "</Issue>\n";
		$xml.= "		<StartDate>" . $transaction->get("CardStartDate"). "</StartDate>\n";
		$xml.= "		<SecurityCode>" . $transaction->get("SecurityCode"). "</SecurityCode>\n";
		$xml.= "		<ExpiryDate>" . $transaction->get("CardExpiryDate"). "</ExpiryDate>\n";
		$xml.= "	</CreditCard>\n";
		$xml.= "</PaymentMethod>\n\n";

		// Order
		$xml.= "<Order>\n";
		$xml.= "	<OrderReference>" . $transaction->get("OrderReference"). "</OrderReference>\n";
		$xml.= "	<OrderInformation>" . $transaction->get("OrderInformation"). "</OrderInformation>\n";
		$xml.= "</Order>\n\n";
		$xml.= "</Request>\n\n";


		// Certificate
		$xml.= "<Certificate>" .fread(fopen($this->certificatePath, "r"),
				filesize($this->certificatePath)). "</Certificate>\n\n";

		$xml.= "</RequestBlock>\n\n";

		$return_xml = "";

		$openSocket = fsockopen($this->host, $this->port, $errno, $errstr, 30);
		if (!$openSocket) {
			echo "$errstr ($errno)\n";
		} else {
			stream_set_timeout($openSocket, 120);
			fputs($openSocket, $xml);

			$i = 0;
			while ($line = fgets($openSocket, 4096)) {
				if ($i > 2)
					$return_xml.= $line;
				$i++;
			}
			fclose($openSocket);
		}

		$string = " - ST3DCardQuery\nRequest:\n{$xml}\n\nResponse:\n{$return_xml}\n\n\n";

		$application = &CoreFactory::getApplication();
		$application->log($string, $type = "xpay");
		$index = "";
		$p = xml_parser_create();
		xml_parser_set_option($p, XML_OPTION_CASE_FOLDING, 0);
		xml_parser_set_option($p, XML_OPTION_SKIP_WHITE, 1);
		xml_parse_into_struct($p, $return_xml, $vals, $index);
		xml_parser_free($p);

		@$transaction->set("TransactionReference", $vals[$index["TransactionReference"][0]]["value"]);
		@$transaction->set("Enrolled", $vals[$index["Enrolled"][0]]["value"]);
		@$transaction->set("MD", $vals[$index["MD"][0]]["value"]);
		@$transaction->set("Html", $vals[$index["Html"][0]]["value"]);			
		
		return $transaction;
	}

	function refund(&$refund) {
		if (!($refund instanceof DataEntity)) {
			trigger_error("Invalid class. Expected Class DataEntity ", E_USER_ERROR);
			exit;
		}
		$xml = "\n<RequestBlock Version=\"3.15\">\n";
		$xml.= "<Request Type=\"REFUND\">\n";

		// Operation
		$xml.= "<Operation>\n";
		$xml.= "	<Amount>" . round($refund->get("Amount") * 100). "</Amount>\n";
		$xml.= "	<SiteReference>" . $this->siteReference. "</SiteReference>\n";
		$xml.= "</Operation>\n";

		// Get the transaction and address relations
		$transaction = $refund->getRelation("TransactionId");
		$address = $transaction->getRelation("AddressId");

		// Customer Info
		$xml.= "<CustomerInfo>\n";
		$xml.= "	<Postal>\n";
		$xml.= "		<Name>\n";
		$xml.= "			<NamePrefix><![CDATA[" . $address->get("NamePrefix"). "]]></NamePrefix>\n";
		$xml.= "			<FirstName><![CDATA[" . $address->get("FirstName"). "]]></FirstName>\n";
		$xml.= "			<MiddleName></MiddleName>\n";
		$xml.= "			<LastName><![CDATA[" . $address->get("LastName"). "]]></LastName>\n";
		$xml.= "			<NameSuffix></NameSuffix>\n";
		$xml.= "		</Name>\n";
		$xml.= "		<Company><![CDATA[" . $address->get("CompanyOrHouseName") . "]]></Company>\n";
		$xml.= "		<Street><![CDATA[" . $address->get("CompanyOrHouseName") . " " . $address->get("AddressLine1") . ", " . $address->get("AddressLine2") . "]]></Street>\n";
		$xml.= "		<City><![CDATA[" . $address->get("Town"). "]]></City>\n";
		$xml.= "		<StateProv><![CDATA[" . $address->get("Region"). "]]></StateProv>\n";
		$xml.= "		<PostalCode><![CDATA[" . $address->get("Postcode"). "]]></PostalCode>\n";
		$xml.= "		<CountryCode><![CDATA[" . $address->get("Country"). "]]></CountryCode>\n";
		$xml.= "	</Postal>\n";
		$xml.= "	<Telecom>\n";
		$xml.= "		<Phone>" . $address->get("TelephoneNumber"). "</Phone>\n";
		$xml.= "	</Telecom>\n";
		$xml.= "	<Online>\n";
		$xml.= "		<Email><![CDATA[" . $address->get("EmailAddress"). "]]></Email>\n";
		$xml.= "	</Online>\n";
		$xml.= "</CustomerInfo>\n";

		// Payment Method
		$xml.= "<PaymentMethod>\n";
		$xml.= "	<CreditCard>\n";
		$xml.= "		<ParentTransactionReference>" . $transaction->get("TransactionReference"). "</ParentTransactionReference>\n";
		$xml.= "		<TransactionVerifier>" . $transaction->get("TransactionVerifier"). "</TransactionVerifier>\n";
		$xml.= "	</CreditCard>\n";
		$xml.= "</PaymentMethod>\n";
		$xml.= "</Request>\n";

		// Certificate
		$xml.= "<Certificate>" .fread(fopen($this->certificatePath, "r"),
				filesize($this->certificatePath)). "</Certificate>\n";

		$xml.= "</RequestBlock>\n";

		$return_xml = "";

		$openSocket = fsockopen($this->host, $this->port, $errno, $errstr, 30);
		if (!$openSocket) {
			echo "$errstr ($errno)\n";
		} else {
			stream_set_timeout($openSocket, 120);
			fputs($openSocket, $xml);

			$i = 0;
			while ($line = fgets($openSocket, 4096)) {
				if ($i > 2)
					$return_xml.= $line;
				$i++;

			}
			fclose($openSocket);
		}

		$string = " - Reverse\nRequest:\n{$xml}\n\nResponse:\n{$return_xml}\n\n\n";

		$application = &CoreFactory::getApplication();
		$application->log($string, $type = "xpay");

		$p = xml_parser_create();
		xml_parser_set_option($p, XML_OPTION_CASE_FOLDING, 0);
		xml_parser_set_option($p, XML_OPTION_SKIP_WHITE, 1);
		xml_parse_into_struct($p, $return_xml, $vals, $index);
		xml_parser_free($p);

		@$refund->set("TransactionReference", $vals[$index["TransactionReference"][0]]["value"]);
		@$refund->set("AuthCode", $vals[$index["AuthCode"][0]]["value"]);
		@$refund->set("Result", $vals[$index["Result"][0]]["value"]);
		@$refund->set("Message", $vals[$index["Message"][0]]["value"]);
 
		@$refund->set("SecurityMessage", $vals[$index["SecurityMessage"][0]]["value"]);
		@$refund->set("TransactionCompletedTimestamp", $vals[$index["TransactionCompletedTimestamp"][0]]["value"]);
		@$refund->set("TransactionVerifier", $vals[$index["TransactionVerifier"][0]]["value"]);

		return $refund;
	}
}