<?php
/**
 * @package Core
 * @copyright Clock Limited 2007
 * @version 3.0 - $Revision$ - $Date$
 */

/**
 * Template Control takes a file and parses it replacing varibles with value
 * from this object
 *
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2007
 * @version 3.0 - $Revision$ - $Date$
 * @package Core
 */
class Template {
	
	/**
	 * List of field data set
	 * @var Array
	 */
	var $fieldData = null;
	
	/**
	 * Contents of the template requested
	 * @var String
	 */
	var $template = null;
	
	/**
	 * Output of the data
	 * @var String
	 */
	var $output = null;
	
	/**
	 * Sets the template file if filename is not null
	 * 
	 * @param String $filename to be set as the template.  
	 * @return void
	 */
	function Template($filename = null) {
		if ($filename != null) {
			$this->setTemplateFile($filename);
		}
	}

	/**
	 * Clears any fieldData set
	 * 
	 * @return void
	 */
	function clearData() {
		$this->fieldData = null;
	}

	/**
	 * Sets the name and value for fieldData
	 * 
	 * @param String $name for the key variable of fieldData
	 * @param String $value for the value of the fieldData
	 * @return void
	 */
	function set($name, $value) {
		$this->fieldData[mb_strtoupper($name)] = $value;
	}

	/**
	 * Passed through a Data Entity and sets each field as fieldData. 
	 * 
	 * @param Object $dataEntity
	 * @return void
	 */
	function setData($dataEntity) {
		$data = $dataEntity->getData();
		if ((isset($data)) && (is_array($data))) {
			foreach($data as $k => $v) {
				$this->set($k, $dataEntity->getFormatted($k));
			}
		}
	}

	/**
	 * Checks and sets the template file location passed
	 * 
	 * @param String $filename to be set at the Template File
	 * @return sets template or returns error if file not found.
	 */
	function setTemplateFile($filename) {
		$application = &CoreFactory::getApplication();
    if (file_exists($application->registry->get("Path") . $filename)) {
    	$this->setTemplate(file_get_contents($application->registry->get("Path") . $filename));
    } else if (file_exists($filename)) {
			$this->setTemplate(file_get_contents($filename));
		} else {
			trigger_error("Unable to open template file '" . realpath($filename) . "', file not found");
		}
	}

	/**
	 * Sets the template file requested
	 * 
	 * @param String $template location to be set as the template. 
	 * @return void
	 */
	function setTemplate($template) {
		$this->template = $template;
	}

	/**
	 * Parses the template where the fieldData is set
	 * 
	 * @return output of parsed data
	 */
	function parseTemplate() {
		$this->output = $this->template;
		if ((isset($this->fieldData)) && (is_array($this->fieldData))) {
      foreach($this->fieldData as $k => $v) {
        $this->output = preg_replace("/\{$k\}/", $v, $this->output);
      }
		}
		return $this->output;
	}

	/**
	 * Returns the template output
	 * 
	 * @return output
	 */
	function toString() {
		return $this->output;
	}

	/**
	 * Writes the parsed template to disk
	 * 
	 * @param String $filename Where to write the file to
	 * @return Boolean True if writing was a success
	 */
	function write($filename) {
		if ($handle = fopen($filename, "w+")) {
			fwrite($handle, $this->parseTemplate());
			fclose($handle);
			return true;
		}
		return false;
	}
}