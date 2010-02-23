<?php
/**
 * @package Core
 * @subpackage Internet
 * @copyright Clock Limited 2007
 * @version 3.0 - $Revision$ - $Date$
 */

/**
 * Include Data.php so that DataControl can be extended.
 * Include Html.php so that HtmlControl can be extended.
 */
require_once("Atrox/3.0/Core/Data/Validation.php");
require_once("Atrox/3.0/Core/Internet/Html.php");

/**
 * Generate and send MIME Multipart and plain E-mails
 *
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright 2004 Clock Ltd
 * @version 0.l
 * @package Core
 * @subpackage Internet
 */
class Email {

	/**
	 * E-mail address of intended recipient
	 * @access public
	 * @var String
	 */
	var $to = null;
											
	/**
	 * E-mail address of intended cc recipient
	 * @access public
	 * @var String
	 */
	var $cc = null;

	/**
	 * Subject of e-mail
	 * @access public
	 * @var String
	 */
	var $subject = null;

	/**
	 * E-mail address of the sender
	 * @access public
	 * @var String
	 */
	var $from = "Unknown";

	/**
	 * HTML body of the e-mail
	 * @access public
	 * @var String
	 */
	var $body = null;

	/**
	 * Priority of the e-mail
	 * @access public
	 * @var Integer
	 */
	var $priority = 3;

	/**
	 * {description}
	 * @access public
	 * @var String
	 */
	var $headers = null;

	/**
	 * Plain-text body of the e-mail
	 * @access public
	 * @var String
	 */
	var $plainBody = null;

	/**
	 * {description}
	 * @access public
	 * @var String
	 */
	var $finalHeaders = null;

	/**
	 * {description}
	 * @access public
	 * @var String
	 */
	var $finalPlainBody = null;

	/**
	 * {description}
	 * @access public
	 * @var String
	 */
	var $finalHtmlBody = null;

	/**
	 * {description}
	 * @access public
	 * @var String
	 */
	var $needsConstruction = true;

	/**
	 * Semi-random identifier for identifying parts of mulitpart e-mails
	 * @access public
	 * @var String
	 */
	var $semiRand = null;

	/**
	 * attachment part of email
	 * @access public
	 * @var array
	 */
	var $attachments = array();
	
	/**
	 * Create a random string used in the MIME boundry
	 * @access public
	 * @return null
	 */		
	function Email() {
		$this->semiRand = md5(time());
	}

	/**
	 * Set the destination e-mail address
	 * @access public
	 * @param string $value
	 * @return bool
	 */
	function setTo($value) {
		if (is_array($value)) {
			foreach ($value as $address) {
				if (!Validation::emailAddress($address, true)) {
					trigger_error("To e-mail address '$address' is not valid");
					return false;
				}
			}
			$value = implode(",", $value);
		} else {
			if (!Validation::emailAddress($value, true)) {
				trigger_error("To e-mail address '$value' is not valid");
				return false;
			}
		}
		$this->to = $value;
		return true;
	}
	
	/**
	 * Set the "Copy To" e-mail address 
	 * @access public
	 * @param string $value
	 * @return bool
	 */
	function setCc($value) {
		if (is_array($value)) {
			foreach ($value as $address) {
				if (!Validation::emailAddress($address, true)) {
					trigger_error("To e-mail address '$address' is not valid");
					return false;
				}
			}
			$value = implode(",", $value);
		} else {
			if (!Validation::emailAddress($value, true)) {
			trigger_error("To e-mail address '$value' is not valid");
			return false;
		}
		}
		$this->cc = $value;
		return true;
	}
	
	/**
	 * Get the destination address (if set)
	 * @access public
	 * @return string
	 */
	function getTo() {
		return $this->to;
	}
	
	/**
	 * Set the subject of the e-mail
	 * @access public
	 * @param string $value
	 * @return null
	 */
	function setSubject($value) {
		$this->subject = $value;
	}
	
	/**
	 * Get the e-mail subject (if set)
	 * @access public
	 * @return string
	 */
	function getSubject() {
		return $this->subject;
	}

	/**
	 * Set the from address
	 * @access public
	 * @param string $value
	 * @param string $name
	 * @return bool
	 */
	function setFrom($value, $name = null) {
		if (!Validation::emailAddress($value, true)) {
			trigger_error("From e-mail address '$value' is not valid");
			return false;
		}
		$this->from = $value;
		return true;
	}

	/**
	 * Get the from address (if set)
	 * @access public
	 * @return string
	 */
	function getFrom() {
		return $this->from;
	}

	/**
	 * Get the destination address (if set)
	 * @access public
	 * @param string $value
	 * @param bool $createPlainBody
	 * @return string
	 */
	function setBody($value, $createPlainBody = true) {
		if ($createPlainBody) {
			$this->plainBody = HtmlControl::convertToText($value);
		}
		$this->body = $value;
		$this->needsConstruction = true;
	}

	/**
	 * Set the body of the e-mail (plain text)
	 * @access private
	 * @param string $value
	 * @return null
	 */
	function setPlainBody($value,  $createHtmlBody = true) {
		$this->plainBody = $value;
		if ($createHtmlBody) {
			$this->body = nl2br($value);
		}
		$this->needsConstruction = true;
	}

	/**
	 * Get the e-mail body
	 * @access public
	 * @return string
	 */
	function getBody() {
		return $this->body;
	}

	/**
	 * Get the e-mail body (plain text)
	 * @access public
	 * @return string
	 */
	function getPlainBody() {
		return $this->plainBody;
	}

	/**
	 * Set the e-mail priority
	 * @access public
	 * @param int $value
	 * @return null
	 */
	function setPriority($value) {
		$this->priority = $value;
	}

	/**
	 * Gets the e-mail priority
	 * @access public
	 * @return string
	 */
	function getPriority() {
		return $this->priority;
	}

	/**
	 * Set the e-mail headers
	 * @access public
	 * @param string $value
	 * @return null
	 */
	function setHeaders($value) {
		$this->headers = $value;
		$this->needsConstruction = true;
	}

	/**
	 * Get the e-mail headers
	 * @access public
	 * @return string
	 */
	function getHeaders() {
		$headers = "";
		
		if ($this->cc) {
			$headers .= "CC: {$this->cc}\r\n";
		}
		$headers .= "From: " . $this->from . "\r\n";
		$headers .= "X-Mailer: Clock - www.clock.co.uk \r\n";
		$headers .= $this->headers;
		return $headers;
	}

	/**
	 * Parse a Clock mail template and set as the e-mail body 
	 * @access public
	 * @param string $mailTemplate
	 * @return null
	 */
	function parseTemplate($mailTemplate) {
		$output = file_get_contents($mailTemplate);
		$this->setBody(preg_replace("/\{BODY\}/", $this->body, $output));
		$this->needsConstruction = true;
	}

	/**
	 * Parse a regular file as set as the e-mail body
	 * @access public
	 * @param string $file
	 * @return string
	 */
	function parseFile($file) {
		$this->setBody(file_get_contents($file));
	}

	/**
	 * Send the e-mail in plain text
	 * @access private
	 * @return null
	 */
	function sendPlainMail() {
		if (is_array($this->to)) {
			foreach ($this->to as $address) {
				mail($address, $this->subject, $this->plainBody, $this->getHeaders());
			}
		} else {
			mail($this->to, $this->subject, $this->plainBody, $this->getHeaders());
		}
	}
	
	/**
	 * Reads a file into the buffer and returns the contents in Base64. 
	 * @access private
	 * @param string $filename
	 * @return string
	 */
	function getBase64($filename) {
		$return = null;
		if (file_exists($filename)) {
			$size = filesize($filename);
			$handle = fopen($filename, "r");
			$content = fread($handle, $size);
			fclose($handle);
			$return = chunk_split(base64_encode($content), 68, "\n");
			return $return;
		} else {
			trigger_error("Could not find file: $filename");
		}
	}

	/**
 	 * Adds an attachment to the $this->attachments array.
	 * @access Public
	 * @param string $file
	 * @param string $name
	 * @param string $contentType
	 * @param string $encoding
	 * @return null
	 */
	function addAttachment($file, $name = null, $contentType = "application/octect-stream", $encoding = "base64") {
		if ($name == null) {
			$splitPath = explode("/", $file);
			$length = count($splitPath);
			$name = $splitPath[$length - 1];
		}
		$this->attachments[] = array(
			"body" => $file,
			"name" => $name,
			"contentType" => $contentType,
			"encoding" => $encoding);
		}

	/**
	 * Send the e-mail
	 * @deprecated Use send instead
	 * @see send
	 * @access public
	 * @return null
	 */
	function sendMail() {
		if ($this->needsConstruction) {
			$this->constructMail();
		}
		$this->sendMultiPartMail();
	}
	
	/**
	 * Send the e-mail 
	 * @access public
	 * @return null
	 */
	function send() {
		return $this->sendMail();
	}		
	
	/**
	 * Construct the MIME encoded e-mail 
	 * @access public
	 * @return null
	 */				
	function constructMail() {

		// Create a unique string $mime_boundary, this defines our first MIME "layer"
		$mime_boundary_top = "==MULTIPART_BOUNDARY_" . md5(uniqid(time()));
		$mime_boundary_header = chr(34) . $mime_boundary_top . chr(34);
		
		// Write the headers.
		// "multipart/related" lets us embed the attached content into the body of a mail.
		$this->finalHeaders = ""; 
		$this->finalHeaders .= $this->getHeaders();
		$this->finalHeaders .= "X-Priority: " . $this->getPriority() . "\r\n";
		$this->finalHeaders .= "MIME-Version: 1.0\r\n";
		// Changed to try and resolve spam issue with Rob - 2008-10-30
		//$this->finalHeaders .= "Content-Type: multipart/related;\r\n";
		//$this->finalHeaders .= "     boundary= " . $mime_boundary_header . "\r\n";

		$this->finalHeaders .= "Content-Type: multipart/related; boundary=" . $mime_boundary_header . "\r\n";
		
		// Create a second unique string to deal with the "multipart/alternative"
		// which allows us to support both HTML and plain text e-mail 
		$mime_boundary = "==MULTIPART_BOUNDARY_" . $this->semiRand;

		// Create our second MIME "layer" in which we put the body of the e-mail.
		// Changed to try and resolve spam issue with Rob - 2008-10-30
		// $declareBoundary = "Content-Type: multipart/alternative;\r\n";
		// $declareBoundary .= "     boundary=\"$mime_boundary\"";
		$declareBoundary = "Content-Type: multipart/alternative; boundary=\"$mime_boundary\"";
		$this->finalHtmlBody = "\r\n\r\n--$mime_boundary_top\n$declareBoundary\n\n";
		$this->finalHtmlBody .= "--$mime_boundary\nContent-Type: text/plain; charset=utf-8\nContent-Transfer-Encoding:";
		$this->finalHtmlBody .= "8bit\n\n{$this->plainBody}\n\n--{$mime_boundary}\nContent-Type: text/html; charset=utf-8\nContent-Transfer-Encoding:";
		$this->finalHtmlBody .= "8bit\n\n{$this->body}\n\n";
		 
		// Close our second MIME "layer"
		$this->finalHtmlBody .= "--{$mime_boundary}--\n\n";
					
		// Loop through the attachment array, Base64 the files, and then attach to the mail.			
		if (count($this->attachments) > 0) {
			$i = 0;
			while ($i < count($this->attachments)) {
				$base64 = $this->getBase64($this->attachments[$i]["body"]);
				$name = $this->attachments[$i]["name"];
				$contentType = $this->attachments[$i]["contentType"];
				$encoding = $this->attachments[$i]["encoding"];
				$MIMEPart = "--$mime_boundary_top\r\n" . 
							"Content-Type: $contentType; name=\"".$name."\"\r\n" . 
							"Content-ID: <$name>\r\n" .
							"Content-Transfer-Encoding: $encoding\r\n" .							
							"Content-Disposition: attachment; filename=\"".$name."\"\r\n\r\n" .							
							"$base64\r\n\r\n";
				$this->finalHtmlBody .= $MIMEPart . "\r\n";
				$i++;
				}
			}

		// Close our first MIME "layer"						
		$this->finalHtmlBody .= "--$mime_boundary_top--";			
		$this->needsConstruction = false;
	}

	/**
	 * Finally send the now MIME encoded e-mail.
	 * @access private
	 * @return null
	 */
	function sendMultiPartMail() {
		if (is_array($this->to)) {
			foreach ($this->to as $address) {
				mail($address, $this->subject, $this->finalHtmlBody, $this->finalHeaders);
			}
		} else {
			mail($this->to, $this->subject, $this->finalHtmlBody, $this->finalHeaders);
		}
	}
}