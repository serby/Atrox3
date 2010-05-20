<?php

/**
 * @package Core
 * @subpackage Data
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 */

	require_once("Atrox/Core/Exception/DataEntityException.php");

/**
 * Data Entity Class
 *
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Core
 * @subpackage Data
 */
class DataEntity {

	/**
	 * @var DataControl
	 */
	var $control = null;
	var $data = null;
	var $binaries = null;
	var $formattedData = null;

	/**
	 * This will be true if the dataentity has not been stored
	 * @access private
	 * @var Boolean
	 */
	var $new = true;

	/**
	 * Has this field been changed
	 * @access private
	 * @var Boolean
	 */
	var $dirty = false;

	function DataEntity(&$control) {
		$this->control = &$control;
	}

	function add($name, $value) {
		$this->data[$name] = $value;
		$this->dirty[$name] = false;
	}

	function getControl() {
		return $this->control;
	}

	/**
		* Returns the value contained in $fieldName, if it exists
		* @param String $fieldName Name of the field from which to return the value
		* @return String Value contained in $fieldName
		*/
	function get($fieldName) {

		if (array_key_exists($fieldName, $this->data)) {
			return $this->data[$fieldName];
		} else {
			trigger_error("Bad Field Name '$fieldName'\n / " . $this->toString(), E_USER_ERROR);
		}
	}

	/**
		* Returns the value contained in $fieldName with the appropriate output
		* formatter applied, if it exists
		* @param String $fieldName Name of the field from which to return the value
		* @return String Formatted value contained in $fieldName
		*/
	function getFormatted($fieldName) {
		if (array_key_exists($fieldName, $this->data)) {
			return $this->control->fieldMeta[$fieldName]->outputFormatter->format(
			$this->data[$fieldName]);
		} else {
			trigger_error("Bad Field Name '$fieldName'\n / " . $this->toString(), E_USER_ERROR);
		}
	}

	/**
		* Returns true if the field in $fieldName is null or empty
		* formatter applied, if it exists
		* @param String $fieldName Name of the field from which to return the value
		* @return Boolean True on null
		*/
	function isNull($fieldName) {
		if (array_key_exists($fieldName, $this->data)) {
			return empty($this->data[$fieldName]);
		} else {
			trigger_error("Bad Field Name '$fieldName'\n / " . $this->toString(), E_USER_ERROR);
		}
	}

	function getLocalised($fieldName) {
		if (array_key_exists($fieldName, $this->data)) {
			switch ($this->control->fieldMeta[$fieldName]->type) {
				case FM_TYPE_DATE:
					$application = &CoreFactory::getApplication();
					return $application->localiseDateTime($this->data[$fieldName], "");
				default:
					return $this->data[$fieldName];
			}
		} else {
			trigger_error("Bad Field Name '$fieldName'\n / " .  $this->toString(), E_USER_ERROR);
		}
	}

	/**
	 * Return an array containing all the data in a key value array
	 */
	function getData() {
		return $this->data;
	}

	/**
	 * Return an array containing all the data in a key formatted value array
	 */
	function getObject() {
		$object = array();
		foreach ($this->control->fieldMeta as $k => $v) {
			$object[$k]->formatted = $this->getFormatted($k);
			$object[$k]->data = $this->data[$k];
		}
		return (object)$object;
	}

	/**
	 * Clears all the data
	 */
	function clear() {
		foreach($this->control->fieldMeta as $k => $v) {

			// Reset dirty status. - Ed
			$this->dirty[$k] = false;

			$value = $v->default;
			// Autodata
			if (isset($this->control->fieldMeta[$k]->autoData)) {
				$value = $this->control->fieldMeta[$k]->autoData->get();
			}
			$this->data[$k] = $value;
		}
	}

	function toShortTitle($customFormat = null) {
		$fieldData = array();
		foreach($this->control->shortTitleFields as $fieldName) {
			if ($this->control->isField($fieldName)) {
				$fieldData[] = $this->getFormatted($fieldName);
			}
		}
		if ($customFormat) {
			return vsprintf($customFormat, $fieldData);
		} else {
			return vsprintf($this->control->shortTitleFormat, $fieldData);
		}
	}

	function toLongTitle() {
		$fieldData = array();
		foreach($this->control->longTitleFields as $fieldName) {
			if ($this->control->isField($fieldName)) {
				$fieldData[] = $this->getFormatted($fieldName);
			}
		}
		return vprintf($this->control->longTitleFormat, $fieldData);
	}

	/**
		* Returns the maximum length for $fieldName
		* @param String $fieldName Name of the field for which to return maximum length
		* @return Int Maximum length of $fieldName
		*/
	function getMaxLength($fieldName) {
		if (array_key_exists($fieldName, $this->data)) {
			return $this->control->fieldMeta[$fieldName]->length;
		} else {
			trigger_error("Bad Field Name '$fieldName'", E_USER_ERROR);
		}
	}

	function getRelationControl($fieldName) {
		if (!($this->control->relationControls[$fieldName] instanceof	DataControl)) {
			trigger_error("Invalid class. Expected DataControl",E_USER_ERROR);
		}
		if (isset($this->control->relationControls[$fieldName])) {
			$this->control->relationControls[$fieldName]->reset();
			return $this->control->relationControls[$fieldName];
		} else {
			return false;
		}
	}

	/**
	 *
	 * @param String $fieldName
	 * @param Boolean $cached
	 * @return DataEntity|false
	 */
	function getRelation($fieldName, $cached = true) {
		if (isset($this->control->relationControls[$fieldName])) {
			return $this->control->relationControls[$fieldName]->itemByField(
			$this->data[$fieldName], $this->control->fieldMeta[$fieldName]->relationField,"", false, $cached);
		} else {
			return false;
		}
	}

	function getRelationValue($fieldName, $value) {
		if ($relation = $this->getRelation($fieldName)) {
			return $relation->get($value);
		} else {
			return false;
		}
	}

	function setRelation($fieldName, &$dataEntity) {
		$this->set($fieldName, $dataEntity->get($dataEntity->control->key));
	}

	function getOneToManyControl($relationName) {
		if (!isset($this->control->oneToMany[$relationName])) {
			trigger_error("One to many relation does not exist.", E_USER_ERROR);
			return false;
		}
		// TODO: Make this detect multiple calls, and only construct
		// TODO: the filter once
		$relation = $this->control->oneToMany[$relationName];
		$filter = CoreFactory::getFilter();

		$filter->addConditional($relation->linkTable, $relation->fKey, $this->data[$this->control->key]);

		$relation->control->setFilter($filter);
		$relation->control->reset();
		return $relation->control;
	}

	function getManyToManyControl($relationName) {
		if (!isset($this->control->manyToMany[$relationName])) {
			trigger_error("Many to many relation does not exist.", E_USER_ERROR);
			return false;
		}
		// TODO: Make this detect multiple calls, and only construct
		// TODO: the filter once
		$relation = $this->control->manyToMany[$relationName];
		$filter = CoreFactory::getFilter();

		$filter->addJoin($relation->control->table, $relation->control->key,
		$relation->linkTable, $relation->fKey);

		$filter->addConditional($relation->linkTable, $relation->pKey,
		$this->data[$relation->control->key]);

		$relation->control->setFilter($filter);
		$relation->control->reset();
		return $relation->control;
	}

	function addManyRelation($relationName, $fKey) {
		if (!isset($this->control->manyToMany[$relationName])) {
			trigger_error("Many to many relation does not exist. ",E_USER_ERROR);
			return false;
		}
		$relation = $this->control->manyToMany[$relationName];

		if (!is_array($fKey)) {
			$fKey = array($fKey);
		}

		foreach($fKey as $fk) {
			$sql = "INSERT INTO \"$relation->linkTable\" (\"$relation->pKey\",
						\"$relation->fKey\") VALUES (" .
			$this->data[$relation->control->key] . ", $fk)";
			$this->control->databaseControl->query($sql);
		}
	}

	function removeManyRelation($relationName, $fKey) {
		if (!isset($this->control->manyToMany[$relationName])) {
			trigger_error("Many to many relation does not exist. ",E_USER_ERROR);
			return false;
		}

		$relation = $this->control->manyToMany[$relationName];

		if (!is_array($fKey)) {
			$fKey = array($fKey);
		}
		foreach($fKey as $fk) {
			$sql = "DELETE FROM \"$relation->linkTable\" WHERE
						\"$relation->pKey\"='" . $this->data[$relation->control->key] . "'
						AND \"$relation->fKey\" = '$fk'";

			$this->control->databaseControl->query($sql);

		}
	}

	function removeAllManyRelations($relationName) {
		if (!isset($this->control->manyToMany[$relationName])) {
			trigger_error("Many to many relation does not exist. ",E_USER_ERROR);
			return false;
		}

		$relation = $this->control->manyToMany[$relationName];

		$sql = "DELETE FROM \"$relation->linkTable\" WHERE
				\"$relation->pKey\"='" . $this->data[$relation->control->key] . "'";

		$this->control->databaseControl->query($sql);
	}

	function set($fieldName, $value) {
		if (array_key_exists($fieldName, $this->data)) {

			// Field has been changed, set to dirty - Ed
			$this->dirty[$fieldName] = true;

			// Input Formatting
			$this->data[$fieldName] =
			$this->control->fieldMeta[$fieldName]->inputFormatter->format($value);

		} else {
			trigger_error("Unable to set value '$value' to non existant	field '$fieldName'");
			return false;
		}
		return $value;
	}

	function setWithoutFormatting($fieldName, $value) {
		if (array_key_exists($fieldName, $this->data)) {

			// Field has been changed, set to dirty - Ed
			$this->dirty[$fieldName] = true;

			// Input Formatting
			$this->data[$fieldName] = $value;

		} else {
			trigger_error("Unable to set value '$value' to non existant field '$fieldName'");
			return false;
		}
		return $value;
	}

	function setBinary($fieldName, $fieldValue, $fileInfo) {

		$removeOldFile = $fileInfo["Remove"];
		$isNewFile = false;
		if ($fileInfo["Size"] > 0){
			$isNewFile = true;
		}

		$binaryControl = CoreFactory::getBinaryControl();
		if ($removeOldFile && !$isNewFile){

			$binaryControl->delete($binaryControl->item($fieldValue));

			$this->binaries[$fieldName] = null;
			$this->data[$fieldName] = null;
			return true;
		}

		// Only if it was uploaded
		if ($isNewFile) {
			// First delete the existing binary
			if ($this->binaries[$fieldName] = $binaryControl->mapBinary($fieldValue, $fileInfo)) {
				if ($this->control->fieldMeta[$fieldName]->validation != null) {
					$this->control->fieldMeta[$fieldName]->validation->setExtraInfo(
					$this->binaries[$fieldName]->get("TempFilename"));
				}
			}
		} else {
			if ((!$this->control->fieldMeta[$fieldName]->allowNull) && ($fieldValue == null)) {
				switch ($fileInfo["Error"]) {
					case UPLOAD_ERR_NO_FILE:
						break;
					case UPLOAD_ERR_INI_SIZE:
					case UPLOAD_ERR_FORM_SIZE:
						$this->control->errorControl->addError("'" .
						$this->control->fieldMeta[$fieldName]->description
						. "' is larger that the allowed upload size");
						break;
					case UPLOAD_ERR_PARTIAL:
						$this->control->errorControl->addError("'" .
						$this->control->fieldMeta[$fieldName]->description
						. "' was only partially uploaded.");
						break;
					default:
						$this->control->errorControl->addError("'" .
						$this->control->fieldMeta[$fieldName]->description
						. "' was not uploaded.");
						break;
				}
			}
			return false;
		}
		return true;
	}

	function validate($type = FM_STORE_ADD) {
		return $this->control->validate($this, $type);
	}

	function isDirty($fieldName) {
		if (array_key_exists($fieldName, $this->data)) {
			return $this->dirty[$fieldName];
		} else {
			trigger_error("Bad Field Name '$fieldName'\n / " . $this->toString(), E_USER_ERROR);
		}
	}

	function save() {
		$key = $this->control->key;
		if ($existingItem = $this->control->item($this->data[$key])) {
			if ($this->control->validate($this, FM_STORE_UPDATE)) {
				if ($this->binaries != null) {
					foreach ($this->binaries as $k => $v) {
						if ($v == null) {
							$this->data[$k] = null;
						} else {
							if ($binaryId = $v->save()) {
								$this->data[$k] = $binaryId;
							}
						}
					}
				}
				if ($result = $this->control->update($this)) {
					foreach($this->control->fieldMeta as $k => $v) {
						// Reset dirty status. - Ed
						$this->dirty[$k] = false;
					}
				}
				return $result;
			}
		} else {

			if ($this->control->validate($this, FM_STORE_ADD)) {
				if ($this->binaries != null) {
					foreach ($this->binaries as $k => $v) {
						if ($v == null) {
							$this->data[$k] = null;
						} else {
							if ($binaryId = $v->save()) {
								$this->data[$k] = $binaryId;
							}
						}
					}
				}

				$this->data[$key] = $this->control->add($this);
				return $this->data[$key];
			}
		}
		return false;
	}

	/**toRow formats a data entity into a tab seperated format by default, but when true is passed in
	 * the output is in html table row format.
	 * @param $asHtml bool default false
	 * @return $output String
		**/
	function toRow($asHtml = false) {
		$output = "";
		if ($asHtml) {
			$output .= "<tr>\n";
			foreach($this->data as $v) {
				$output .= "\t<td>".$v ."</td>\n";
			}
			$output .= "</tr>\n";
		} else {
			foreach($this->data as $v) {
				$output .= $v ."\t";
			}
			$output .= "\n";
		}
		return $output;
	}

	/**
	 * toString formats a data entity to a new line seperated format of <fieldName>: <fieldValue>\n
	 * if true is passed in then the \n is replaced by a html break
	 * @param $asHtml bool default false
	 * @return $output String
	 **/
	function toString($asHtml = false) {
		$output = "";
		foreach ($this->data as $k => $v) {
			if (isset($this->control->fieldMeta[$k])) {
				$output .= $this->control->fieldMeta[$k]->description. ": $v\n";
			}
		}
		if ($asHtml) {
			return nl2br($output);
		}
		return $output;
	}

	/**
	 * Returns a JSON string representing this data entity.
	 * @return $output String
	 **/
	function toJson($extraJson = null) {
		$output = "";
		foreach ($this->data as $k => $v) {
			switch($this->control->fieldMeta[$k]->type) {
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

	/**
	 * Returns a JSON string representing this data entity.
	 * @return $output String
	 **/
	function toObject($includeFormatted = true) {
		$array = array();
		foreach ($this->data as $k => $v) {
			switch($this->control->fieldMeta[$k]->type) {
				case FM_TYPE_BOOLEAN:
					$v = $v == "t" || $v === true || $v == "true";
					break;
			}
			$array[$k]->Data = $v;
			if ($includeFormatted) {
				$array[$k]->Formatted = $this->getFormatted($k);
			}
		}

		return (object)$array;
	}

	/**
	 * toStringInclude formats selected fields from a data entity to a new line seperated format
	 * of <fieldName>: <fieldValue>\n unless true is passed in in which case \n is replaced by a html break
	 * @param $fields array of fields to use
	 * @param $asHtml bool default false
	 * @return $output String
	 **/
	function toStringInclude($fields, $asHtml = false) {
		$output = "";
		foreach($this->data as $k => $v) {
			if (in_array($k, $fields)) {
				if (isset($this->control->fieldMeta[$k])) {
					$output .= $this->control->fieldMeta[$k]->description. ": $v\n";
				}
			}
		}
		if ($asHtml) {
			return nl2br($output);
		}
		return $output;
	}

	/**
	 * If the DataEntity has a tag with the given prefix then return remaining part of the tag
	 * @author Paul Serby
	 * @param String $tagPrefix The starting part of the tag to match
	 * @param Boolean $returnIdOnMatch Should the ID part of the matching tag be returned
	 * @param String $tagField The name of tags column if different.
	 * @param Boolean $exactMatch
	 * @return Mixed The remaining tag or false
	 */
	function hasTag($tagPrefix, $returnIdOnMatch = false, $tagField = "Tags", $exactMatch = false) {
		if ($tags = $this->get($tagField)) {
			$tags = explode("\n", $tags);
			$prefixLength = mb_strlen($tagPrefix);
			foreach ($tags as $tag) {
				if ($tagPrefix == mb_substr($tag, 0, $prefixLength)) {
					if ($returnIdOnMatch && !$exactMatch) {
						return mb_substr($tag, $prefixLength);
					} else if ($exactMatch) {
						if (preg_match("/^$tagPrefix$/", $tag)) {
							return true;
						}
					} else {
						return true;
					}
				}
			}
		}
		return false;
	}
}