<?php
/**
 * @copyright Clock Limited
 * @version 3.2
 * @package Core
 * @subpackage Utility
 */

/**
 * Abstract class for Observing an Obserable object
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited
 * @version 3.2
 * @package Core
 * @subpackage Utility
 */
class Observer {


	/**
	 * Called when an event you want to be watched occures
	 * @param Stirng $event Name of the event that occured
	 * @param mixed $data   
	 */	
	function observe($event, $data) {
	}

}