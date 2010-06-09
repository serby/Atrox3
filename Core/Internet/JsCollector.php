<?php
require_once "ResourceCollector.php";
/**
 * Collects text based resources and minifies it.
 *
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision: 1480 $ - $Date: 2010-05-19 14:51:14 +0100 (Wed, 19 May 2010) $
 * @package Core
 * @subpackage Internet
 */
class JsCollector extends ResourceCollector {

	public function getFileExtention() {
		return "js";
	}


	public function makeHtml($filename, $other) {
		return <<<TEXT
<script src="{$filename}" type="text/javascript"></script>
TEXT;
	}

	/**
	 *
	 * @param string $source
	 * @return string
	 */
	protected function minify($source) {
		$v = trim($source);
	  return $v;
	}
}