<?php
require_once "IResourceAggregatorDelegate.php";

/**
 * Collects CSS-based resources, aggregated them and minifies output to a file.
 *
 * @author Dom Udall <dom.udall@clock.co.uk>
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision: 1480 $ - $Date: 2010-05-19 14:51:14 +0100 (Wed, 19 May 2010) $
 * @package Core
 * @subpackage Internet
 */
class JsResourceAggregator implements IResourceAggregatorDelegate {

	public function getFileExtension() {
		return "js";
	}

	public function validateOptions(stdClass $options = null, stdClass $currentGroup = null) {
		if (!$options) {
			$options = new stdClass();
		}

		$defaultOptions = new stdClass();
		$defaultOptions->group = "default";

		foreach ($defaultOptions as $key => $value) {
			if (!array_key_exists($key, $options)) {
				$options->$key = $value;
			}
		}

		return $options;
	}

	public function makeHtml($filename, stdClass $options) {
		return <<<TEXT
<script src="{$filename}" type="text/javascript"></script>
TEXT;
	}
}