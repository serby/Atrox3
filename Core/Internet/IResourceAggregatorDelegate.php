<?php
/**
 * Interface for a resource aggregator delegator.
 *
 * @author Dom Udall <dom.udall@clock.co.uk>
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision: 1480 $ - $Date: 2010-05-19 14:51:14 +0100 (Wed, 19 May 2010) $
 * @package Core
 * @subpackage Internet
 */
interface IResourceAggregatorDelegate {

	/**
	 * Returns the file extension set by the aggregator.
	 *
	 * @author Dom Udall <dom.udall@clock.co.uk>
	 */
	public function getFileExtension();

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
	public function makeHtml($filename, stdClass $options);
}