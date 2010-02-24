<?php
/**
 * @package Core
 * @subpackage Internet
 * @copyright 2004 Clock Ltd
 * @version 0.l
 */

/**
 * Creates a bsmtp control for batch mail outs
 *
 * @author Dave Folan (Clock Ltd) {@link mailto:dave.folan@clock.co.uk dave.folan@clock.co.uk }
 * @copyright 2004 Clock Ltd
 * @version 0.l
 * @package Core
 * @subpackage Internet
 */

class BsmtpControl {
	var $fileHandle;
	var $temporaryFileAddress;
	var $uploadDirectory;

	function BsmtpControl($uploadDirectory) {

		$application = &CoreFactory::getApplication();
		$temporaryDirectory = $application->registry->get("TemporaryDirectoryPath", "/tmp");		

		$this->uploadDirectory = $uploadDirectory;
		
		$filename = md5(uniqid(rand(), true)) . date("Y-m-d") . "T" . date("H:i:sO") . ".bsmtp";

		$this->temporaryFileAddress = "{$temporaryDirectory}/{$filename}";

		if (!$this->fileHandle = fopen($this->temporaryFileAddress, "w")) {
			$this->errorControl->addError("Unable to open the BSMTP file to send campaign.");
		}
	}

	function escapeMessageData($data) {
		$data = str_replace("\r\n", "\n", $data);
		$data = explode("\n", $data);
		$output = "";
		foreach($data as $line) {
			if (mb_substr($line, 0, 1) == ".") {
				$output .= ".";
			}
			$output .= $line . "\n";
		}
		return trim($output);
	}
	
	function addMailToQueue($to, $message, $envelopeSender = "no-reply@clock.co.uk") {
		$recipients = explode(",", $to);
		foreach ($recipients as $recipient) {
			$recipient = trim($recipient);
			if (!empty($recipient)) {
				$envelopeRecipientArray[] = $recipient;
			}
		}

		if (count($envelopeRecipientArray) > 0) {
			fwrite($this->fileHandle, "MAIL FROM: {$envelopeSender}\n");
			foreach ($envelopeRecipientArray as $recipient) {
				fwrite($this->fileHandle, "RCPT TO: {$recipient}\n");
			}
			fwrite($this->fileHandle, "DATA\n");
			fwrite ($this->fileHandle, $this->escapeMessageData($message) . "\n");
			fwrite($this->fileHandle, ".\n");
		} else {
			$this->errorControl->addError("No BSMTP recipients specified");
		}
	}

	function getFileContents() {
		return file_get_contents($this->temporaryFileAddress);			
	}

	function sendQueue() {
		fclose($this->fileHandle);
		if ($this->uploadDirectory) {
			rename($this->temporaryFileAddress, $this->uploadDirectory);
		} else {
			// If there is not an upload directory get this machine to send the mail
			$application = &CoreFactory::getApplication();
			system($application->registry->get("Exim/Path", "/usr/sbin/exim4") . " -bS < " . $this->temporaryFileAddress);
		}
	}
}