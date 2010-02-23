<?php
/**
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Core
 * @subpackage Utility
 */

/**
 * Abstract class for making a class Observable
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited
 * @version 3.2 - $Revision$ - $Date$
 * @package Core
 * @subpackage Utility
 */
class Observerable {

	var $observers;
	
	/**
	 * Add a Observer to watch what is going on.
	 * @param Observer $observer The observer that is going to watch this object
	 * @param Stirng $name Name to reference the Observer by  
	 */
	function addObserver($observer, $name = null) {
		if ($name == null) {
			$this->observers[] = $observer;
		} else {
			$this->observers[$name] = $observer;
		} 
	}

	/**
	 * Called when an event you want to be watched occures
	 * @param Stirng $event Name of the event that occured
	 * @param mixed $data   
	 */
	function observe($event, $data) {
		if ($this->observers) {
			foreach ($this->observers as $observer) {
				$observer->observe($event, $data);
			}
		}
	}

}