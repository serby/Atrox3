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
class CssResourceAggregator implements IResourceAggregatorDelegate {

	/**
	 * Generates the HTML to include the resources on page.
	 *
	 * @author Dom Udall <dom.udall@clock.co.uk>
	 *
	 * @return string The file extension
	 */
	public function getFileExtension() {
		return "css";
	}

	/**
	 * Validates the options object passed to it, against an optional current group.
	 *
	 * @author Dom Udall <dom.udall@clock.co.uk>
	 *
	 * @param stdClass $options
	 * @param stdClass $currentGroup
	 *
	 * @throws Exception When trying to change a media type of an existing group.
	 *
	 * @return stdClass The original options combined with the mapped defaults.
	 */
	public function validateOptions(stdClass $options = null, stdClass $currentGroup = null) {
		if (!$options) {
			$options = new stdClass();
		}

		$defaultOptions = new stdClass();
		$defaultOptions->media = "all";
		$defaultOptions->group = "default";

		foreach ($defaultOptions as $key => $value) {
			if (!array_key_exists($key, $options)) {
				$options->$key = $value;
			}
		}

		if (isset($currentGroup->media) && ($options->media != $currentGroup->media)) {
			throw new Exception("Unable to change the media type of a existing group");
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
<link rel="stylesheet" type="text/css" href="{$filename}" media="{$options->media}" />
TEXT;
	}
}