<?php
/**
 * @package Core
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 */

/**
 * Include Data.php so that DataControl can be extended.
 */
require_once("Atrox/Core/Data/Data.php");

/**
 * @author Paul Serby (Clock Ltd) {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Core
 */
class DirectAdministrationControl {

	var $controllers = array();
		
	function addHandler($dataControl, $url) {
		$this->controllers[$dataControl->table] = $url;
	}
	
	
	function createEditController($dataEntity) {
		if (isset($this->controllers[$dataEntity->control->table])) {
			return str_replace("{ID}", $dataEntity->get($dataEntity->control->key), $this->controllers[$dataEntity->control->table]);		
		}
	}	
		
}