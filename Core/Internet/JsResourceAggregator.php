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

	/**
	 * Generates the HTML to include the resources on page.
	 *
	 * @author Dom Udall <dom.udall@clock.co.uk>
	 *
	 * @return string The file extension
	 */
	public function getFileExtension() {
		return "js";
	}

	/**
	 * Validates the options object passed to it, against an optional current group.
	 *
	 * @author Dom Udall <dom.udall@clock.co.uk>
	 *
	 * @param stdClass $options
	 * @param stdClass $currentGroup
	 *
	 * @return stdClass The original options combined with the mapped defaults.
	 */
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

	/**
	 * Generates the HTML to include the resources on page.
	 *
	 * @author Dom Udall <dom.udall@clock.co.uk>
	 *
	 * @param string $filename The name of the file to include
	 * @param stdClass $options An object of options, specific to the aggregator
	 *
	 * @return string The HTML to include the resources on page.
	 */
	public function makeHtml($filename, stdClass $options) {
		return <<<TEXT
<script src="{$filename}" type="text/javascript"></script>
TEXT;
	}
}