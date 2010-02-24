<?php
/**
 * @package
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 */

/**
 *
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package
 */
class Mime {

	/**
	 * Constants for different Content-Transfer-Encoding
	 *
	 */
	const ENCODING_UNSPECIFIED = 0;
	const ENCODING_7BIT = 1;
	const ENCODING_8BIT = 2;
	const ENCODING_BASE64 = 3;
	const ENCODING_BINARY = 4;

	private $contentTransferEncodingTypes = array(
		Mime::ENCODING_7BIT => "7bit",
		Mime::ENCODING_8BIT => "8bit",
		Mime::ENCODING_BASE64 => "base64",
		Mime::ENCODING_BINARY => "binary"
	);

	private $parts = array();
	private $boundary = null;

 	function __construct($boundary = null) {
 		$this->boundary = $boundary;
 		if ($this->boundary == null) {
 			$this->boundary = "boundary-" . md5(uniqid(rand(), true));
 		}
	}

	/**
	 * @return string the boundary
	 */
	function getBoundary() {
		return $this->boundary;
	}

	function addPart($content, $contentType, $contentTransferEncoding = Mime::ENCODING_UNSPECIFIED, $additionaHeaders = null) {
		$part->headers[] = "Content-type: " . $contentType;
		if ($contentTransferEncoding != Mime::ENCODING_UNSPECIFIED) {
			$part->headers[] = "Content-Transfer-Encoding: " . $this->contentTransferEncodingTypes[$contentTransferEncoding];
		}
		if (is_array($additionaHeaders)) {
			$part->header += $additionaHeaders;
		}
		$part->body = $contentTransferEncoding == Mime::ENCODING_BASE64 ?  base64_encode($content) : $content;
		$this->parts[] = $part;
	}

	function toString() {
		$output = "";
		if (sizeof($this->parts) <= 0) {
			return "";
		}
		foreach($this->parts as $part) {
			$output .= "--" . $this->boundary . "\r\n";
			$output .=  implode("\r\n", $part->headers) . "\r\n\r\n";
			$output .= $part->body . "\r\n";
		}
		$output .= "--" . $this->boundary . "--";
		return $output;
	}
}