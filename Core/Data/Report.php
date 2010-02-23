<?php
/**
 * @package Core
 * @subpackage Data
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 */

/**
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Core
 * @subpackage Data
 */
class ReportControl {

	var $databaseControl = null;
	var $results = null;
	var $numRows = null;
	var $fieldMeta = null;

	function ReportControl() {
	}

	function init() {
		$this->databaseControl = CoreFactory::getDatabaseControl();
		$this->databaseControl->connect();
	}

	function getNext() {
		if (!$data = $this->databaseControl->fetchRow($this->results)) {
			return false;
		}
		$reportEntity = $this->getDataEntity();
		$reportEntity->control = $this;
		foreach ($data as $k => $v) {
			$reportEntity->add($k, $v);
		}
		return $reportEntity;

	}
	
	function getDataEntity() {
		return new ReportEntity();
	}

	function &getDBControl() {
		return $this->databaseControl;
	}

	function numRows() {
		return $this->databaseControl->numRows($this->results);
	}

	function getArray() {
		$return = null;
		while ($data = $data = $this->databaseControl->fetchRow($this->results)) {
			$return[$data[0]] = $data;
		}
		return $return;
	}

	function runQuery(&$sql) {
		$this->results = $this->databaseControl->query($sql);
		$this->numRows = $this->databaseControl->numRows($this->results);
		return $this->numRows;
	}
}

/**
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Core
 * @subpackage Image
 */
class ReportEntity {

	var $control = null;
	var $data = null;

	function ReportEntity() {
	}

	function add($name, $value) {
		$this->data[$name] = $value;
	}

	function &get($fieldName) {
		if (array_key_exists($fieldName, $this->data)) {
			return $this->data[$fieldName];
		} else {
			trigger_error("Bad Field Name '$fieldName'",E_USER_ERROR);
		}
	}

	function getFormatted($fieldName) {
		if (array_key_exists($fieldName, $this->data)) {
			return $this->control->fieldMeta[$fieldName]->outputFormatter->format(
					$this->data[$fieldName]);
		} else {
			trigger_error("Bad Field Name $fieldName'",E_USER_ERROR);
		}
	}

	function set($fieldName, $value) {

		$this->data[$fieldName] = $value;
		return $value;
	}

	function getRelation($fieldName) {
		if (isset($this->control->relationControls[$fieldName])) {
			return $this->control->relationControls[$fieldName]->item(
					$this->data[$fieldName]);
		} else {
			return false;
		}
	}

	function &getRelationValue($fieldName, $value) {
		if (isset($this->control->relationControls[$fieldName])) {
			$relation = $this->control->relationControls[$fieldName]->item(
				$this->data[$fieldName]);
			return $relation->get($value);
		} else {
			return false;
		}
	}
	
	function toJson($extraJson = null) {
		$output = "";
		foreach ($this->control->fieldMeta as $k => $meta) {
			$v = "";
			if (isset($this->data[$k])) {
				$v = $this->data[$k];
			}
			switch($meta->type) {
				case FM_TYPE_BOOLEAN:
					$v = $v == "t" || $v === true || $v == "true";
					break;
			}
			$output .= "\"{$k}\":{\"Data\":\"" . str_replace(array("\x00", "\x0a", "\x0d", "\x1a", "\x09"), array("\\0", "\\n", "\\r", "\\Z" , "\\t"), addslashes($v)) . "\",\"Formatted\":\"". str_replace(array("\x00", "\x0a", "\x0d", "\x1a", "\x09"), array("\\0", "\\n", "\\r", "\\Z" , "\\t"), addslashes($this->getFormatted($k))) ."\"},";
		}
		$output = mb_substr($output, 0, -1);
		if ($extraJson) {
			$extraJson = "," . $extraJson;
		}
		return "{{$output}{$extraJson}}";
	}
}