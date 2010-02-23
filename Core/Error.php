<?php
/**
 * @copyright Clock Limited 2007
 * @version 3.0 - $Revision$ - $Date$
 * @package Core
 */

/**
 * Global Error Handler
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2007
 * @version 3.0 - $Revision$ - $Date$
 * @package Core
 */
class ErrorControl {

	/**
	 * @see setCause
	 * @access private
	 * @var String
	 */
	var $cause = null;

	/**
	 * List of error details
	 * @access private
	 * @var Array
	 */
	var $errorList;

	/**
	 * Has there been errors
	 * @access private
	 * @var Boolean
	 */
	var $hasErrors = false;

	/**
	 * Creates a clear ErrorControl object.
	 * @return void
	 */
	function ErrorControl() {
		$this->clear();
	}

	/**
	 * Add error details to the internal list of errors rasied since 'clear' was called.
	 * @param String $value Details of the error
	 * @param String $key What element the error was on
	 * @return void
	 */
	function addError($value, $key = null) {
		$this->hasErrors = true;
		if ($key) {
			$this->errorList[$key] = $value;	
		} else {
			$this->errorList[] = $value;				
		}
	}

	/**
	 * Clears any errors that may have been set
	 * @return void
	 */
	function clear() {
		$this->cause = null;
		$this->errorList = array();
		$this->hasErrors = false;
	}

	/**
	 * Reports if there have been any errors since the object was cleared
	 * @return Boolean True if there have been errors
	 */
	function hasErrors() {
		return $this->hasErrors;
	}

	/**
	 * Returns an array filled with errors that have been set 
	 * and clears the list of errors. 
	 * @return Array Error Descriptions
	 */
	function getErrors($reset = true) {
		$errors = $this->errorList;
		if ($reset) {
			$this->clear();
		}
		return $errors;
	}

	/**
	 * Returns true if there is an error with the given name.
	 * @return Boolean
	 */
	function isErrorOn($errorName) {
		return isset($this->errorList[$errorName]);
	}


	/**
	 * Returns the last error added to the array
	 * @return String error
	 */
	function getLastError() {
		return array_pop($this->errorList);
	}

	/**
	 * Returns the cause of this set of errors
	 * @return String The cause of the errors
	 */
	function getCause() {
		return $this->cause;
	}

	/**
	 * Sets the cause of the set of errors
	 * @param String $value The cause of the error
	 * @return void
	 */
	function setCause($value) {
		$this->cause = $value;
	}
}