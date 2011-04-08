<?php
/**
 * @package Core
 * @subpackage Data
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 */

/**
 * Data Class core of the Clock PHP Framework
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Core
 * @subpackage Data
 */

/**
 * These Type Constants are the basic builtin types that the framework
 * uses
 */

/**
 * Basic String Type
 * Will encoder HTML characters
 */
define("FM_TYPE_STRING", 1);

/**
 * @see FM_TYPE_STRING
 * In addition to FM_TYPE_STRING the string will be converted to uppercase
 */
define("FM_TYPE_STRING_UPPER", 2);

/**
 * @see FM_TYPE_STRING
 * In addition to FM_TYPE_STRING the string will be converted to lowercase
 */
define("FM_TYPE_STRING_LOWER", 3);

/**
 * @see FM_TYPE_STRING
 * In addition to FM_TYPE_STRING the string will be converted to title case
 * ie 'paUl sErBy' will become 'Paul Serby'
 */
define("FM_TYPE_STRING_TITLE", 4);

/**
 * Integer Number Type
 */
define("FM_TYPE_INTEGER", 5);

/**
 * Float Number Type for Real numbers
 */
define("FM_TYPE_FLOAT", 6);

/**
 * Date and Time type
 */
define("FM_TYPE_DATE", 7);

/**
 * Boolean Type
 * will accept: true, false, t, f
 */
define("FM_TYPE_BOOLEAN", 8);

/**
 * E-mail Type
 * Will automaticly check it is a valid e-mail address
 */
define("FM_TYPE_EMAILADDRESS", 9);

/**
 * HTML String Type
 * For string that contain html that shouldn't be encoded
 */
define("FM_TYPE_HTML", 10);
/**
 * Password Type
 * Will ensure password is strong (More than 8 characters and contains both
 * letters and digits
 * also encodes to sha1
 */
define("FM_TYPE_PASSWORD", 11);

/**
 * Phone Number Type
 */
define("FM_TYPE_PHONENUMBER", 12);

/**
 * UK Postcode Type
 * Checks that postcode is in a valid UK form
 */
define("FM_TYPE_UKPOSTCODE", 13);

/**
 * Relation Type
 * A field of this type relates to another dataEntity
 * @see setRelationControl
 */
define("FM_TYPE_RELATION", 15);

/**
 * Unique Type
 * Autodata field that will be site unquie
 */
define("FM_TYPE_UID", 17);

/**
 * Global Unique Type
 * Autodata field that will be globaly unquie
 */
define("FM_TYPE_GUID", 18);

/**
 * Binary Type
 * For storing binary large objects
 */
define("FM_TYPE_BINARY", 19);

/**
 * Currency Type
 */
define("FM_TYPE_CURRENCY", 20);

/**
 * Safe HTML String Type
 */
define("FM_TYPE_SAFEHTML", 21);

/**
 * URL Type
	 * For storing linkable addresses
 */
define("FM_TYPE_URL", 22);

/**
 * IP Address Type
 */
define("FM_TYPE_IP", 23);

/**
 * Tag Type
 */
define("FM_TYPE_TAG", 24);

/**
 * Serialized Type
 */
define("FM_TYPE_SERIALIZED", 25);

//
define("FM_STORE_NEVER", 1);
define("FM_STORE_ADD", 2);
define("FM_STORE_UPDATE", 4);
define("FM_STORE_ALWAYS", 6);
define("FM_STORE_DONTSTORE", 7);
define("FM_STORE_REPORT", 8);
define("FM_OPTIONS_UNIQUE", 1);

/**
 * Field Meta data
 */
class FieldMeta {
	var $description = null;
	var $default = null;
	var $type = null;
	var $length = null;
	var $required = FM_STORE_NEVER;
	var $allowNull = false;
	var $options = null;
	var $control = null;
	var $inputFormatter = null;
	var $outputFormatter = null;
	var $autoData = null;
	var $validation = null;
	var $relationField = null;

	function FieldMeta($description, $default, $type, $length = null,
	$required, $allowNull, $options = null) {

		$this->description = $description;
		$this->default = $default;
		$this->mapDefault = $default;
		$this->type = $type;
		$this->length = $length;
		$this->required = $required;
		$this->allowNull = $allowNull;
		$this->options = $options;

		$this->outputFormatter = new FieldFormatter();

		switch($this->type) {
			case FM_TYPE_INTEGER:
				$this->inputFormatter = CoreFactory::getIntegerEncoder();
				$this->outputFormatter = CoreFactory::getIntegerFormatter();
				break;
			case FM_TYPE_FLOAT:
				$this->inputFormatter = CoreFactory::getRealEncoder();
				$this->outputFormatter = CoreFactory::getRealFormatter();
				break;
			case FM_TYPE_HTML:
				$this->inputFormatter = CoreFactory::getStringEncoder();
				$this->outputFormatter = CoreFactory::getHtmlEncoder();
				break;
			case FM_TYPE_SAFEHTML:
				$this->inputFormatter = CoreFactory::getSafeHtmlEncoder();
				$this->outputFormatter = CoreFactory::getHtmlEncoder();
				break;
			case FM_TYPE_STRING:
				$this->inputFormatter = CoreFactory::getStringEncoder();
				$this->outputFormatter = CoreFactory::getStringFormatter();
				break;
			case FM_TYPE_STRING_UPPER:
				$this->inputFormatter = CoreFactory::getStringUpperEncoder();
				$this->outputFormatter = CoreFactory::getStringFormatter();
				break;
			case FM_TYPE_STRING_LOWER:
				$this->inputFormatter = CoreFactory::getStringLowerEncoder();
				$this->outputFormatter = CoreFactory::getStringFormatter();
				break;
			case FM_TYPE_PHONENUMBER:
				break;
			case FM_TYPE_UKPOSTCODE:
				$this->inputFormatter = CoreFactory::getStringUpperEncoder();
				break;
			case FM_TYPE_DATE:
				//TODO: Add Date Validation
				$this->inputFormatter = CoreFactory::getDateTimeFieldEncoder();
				//$this->inputFormatter = new FieldFormatter();
				break;
			case FM_TYPE_BOOLEAN:
				// This set blank booleans set to false
				$this->mapDefault = "f";
				$this->inputFormatter = CoreFactory::getBooleanEncoder();
				$this->outputFormatter = CoreFactory::getBooleanFormatter();
				break;
			case FM_TYPE_EMAILADDRESS:
				$this->inputFormatter = CoreFactory::getStringLowerEncoder();
				$this->outputFormatter = CoreFactory::getStringFormatter();
				break;
			case FM_TYPE_UID:
				$uid = CoreFactory::getUid();
				$this->default = $uid->get();
				$this->inputFormatter = new FieldFormatter();
				$this->setAutoData($uid);
				break;
			case FM_TYPE_GUID:
				$this->inputFormatter = new FieldFormatter();
				$this->default = sha1(mt_rand());
				$this->setAutoData(CoreFactory::getGuid());
				break;
			case FM_TYPE_IP:
				$this->inputFormatter = new FieldFormatter();
				$this->setAutoData(CoreFactory::getIpAddress());
				break;
			case FM_TYPE_URL:
				$this->inputFormatter = CoreFactory::getUrlFieldEncoder();
				$this->outputFormatter = CoreFactory::getStringFormatter();
				break;
			case FM_TYPE_TAG:
				$this->inputFormatter = CoreFactory::getTaggedDataEncoder();
				$this->outputFormatter = CoreFactory::getTaggedDataFormatter();
				break;
			case FM_TYPE_CURRENCY:
				$this->inputFormatter = CoreFactory::getCurrencyEncoder();
				$this->outputFormatter = CoreFactory::getCurrencyFormatter();
				break;
			case FM_TYPE_BINARY:
				$this->setRelationControl(CoreFactory::getBinaryControl());
				$this->outputFormatter = new FieldFormatter();
				$this->inputFormatter = new FieldFormatter();
				$this->validation = new CustomValidation();
				//$this->allowNull = true;
				break;
			case FM_TYPE_SERIALIZED:
				$this->inputFormatter = CoreFactory::getJsonEncoder();
				$this->outputFormatter = CoreFactory::getJsonDecoder();
				break;
			case FM_TYPE_PASSWORD:
				$this->validation = CoreFactory::getStrongPasswordValidation();
			default:
				$this->inputFormatter = new FieldFormatter();
		}
	}

	function setFormatter(&$object) {
		$this->outputFormatter = $object;
		return true;
	}

	function setEncoder(&$object) {
		$this->inputFormatter = $object;
		return true;
	}

	function setRelationControl(&$control, $relationField = "Id") {
		$this->control = $control;
		$this->relationField = $relationField;
	}

	function setAutoData(&$autoDataObject) {
		$this->autoData = $autoDataObject;
	}

	// Paul Serby 2006-12-14 - Removed pass by reference to
	// allow you clear a validator by passing null
	//TODO: This may cause problems!!!
	function setValidation($validationObject) {
		$this->validation = $validationObject;
		return true;
	}
}

/**
 * Main data control class that all data classes will inherit from
 */
class DataControl {
	var $key = null;
	var $table = null;
	var $results = null;

	/**
	 * @var DatabaseControl
	 */
	var $databaseControl = null;
	var $dataObject = null;
	var $fieldMeta = null;
	var $fullTable = null;
	var $fullKey = null;
	var $relationControls = null;

	/**
	 * @var Filter
	 */
	var $filter = null;
	var $numRows = null;
	var $recordPointer = null;
	var $pageLength = null;
	var $currentPage = null;
	var $pageCount = null;
	var $defaultOrder = null;
	var $defaultOrderDesc = false;
	var $errorControl = null;
	var $cacheData = null;
	var $fullTextIndex = null;
	/**
	 * @var Application
	 */
	var $application = null;
	var $shortTitleFields = null;
	var $shortTitleFormat = "%s %s";
	var $longTitleFields = null;
	var $longTitleFormat = "%s %s";

	function DataControl() {
		// Stop this being instantiated as an object
		//trigger_error("DataControl can not be instantiated", E_USER_ERROR);
		$this->application = &CoreFactory::getApplication();
		$this->errorControl = &$this->application->errorControl;
	}

	function init() {}

	function initControl() {
		if ($this->fieldMeta != null) {
			return false;
		}

		$this->init();
		$this->initTable();

		foreach($this->fieldMeta as $k => $v) {
			if ((($v->type == FM_TYPE_RELATION) || ($v->type == FM_TYPE_BINARY)) && ($v->control != null)) {
				$this->relationControls[$k] = $v->control;
			}
		}
	}

	function initTable() {
		if ($this->databaseControl != null) {
			return false;
		}
		$this->application = &CoreFactory::getApplication();
		$this->errorControl = &$this->application->errorControl;
		$this->databaseControl = &$this->application->databaseControl;
		$this->fullTable = $this->databaseControl->parseTable($this->table);
		$this->fullKey = $this->databaseControl->parseField($this->key);
		$filter = CoreFactory::getFilter();
		if (!is_array($this->defaultOrder)) {
			$this->defaultOrder = array(array("Field"=>$this->defaultOrder, "Desc"=>false));
		}
		foreach($this->defaultOrder as $orderField) {
			$filter->addOrder($orderField["Field"], $orderField["Desc"]);
		}

		$this->filter = $filter;
	}

	function getErrorControl() {
		return $this->errorControl;
	}

	function getDefaultOrder() {
		$this->initTable();
		return $this->defaultOrder[0]["Field"];
	}

	function setFilter(&$filter) {
		$this->initTable();
		$this->filter = $filter;
	}

	/**
	 *
	 * @return Filter
	 */
	function getFilter() {
		$this->initTable();
		return $this->filter;
	}

	function isField($fieldName) {
		$this->initControl();
		return isset($this->fieldMeta[$fieldName]);
	}

	/**
	 *
	 * @param DataEntity $dataEntity
	 * @return unknown_type
	 */
	function add(&$dataEntity) {
		$this->initControl();
		$this->beforeInsert($dataEntity);
		if ($this->quickAdd($dataEntity)) {
			if ($this->sequence != null) {
				$dataEntity->set($this->key, $this->databaseControl->getLastId($this));
			}
			$this->afterInsert($dataEntity);
			return $dataEntity->get($this->key);
		} else {
			return false;
		}
	}

	function quickAdd(&$dataEntity) {
		$fields = "";
		$values = "";

		foreach($this->fieldMeta as $k => $v) {
			if (($v->required != FM_STORE_DONTSTORE) &&
			(($v->required & FM_STORE_ADD) == FM_STORE_ADD)) {
				$fields .= $this->databaseControl->parseField("$k"). ", ";

				$value = $dataEntity->get($k);
				if (($value === "") || ($value === null)) {
					$values .= "null, ";
				} else {
					$values .= $this->databaseControl->parseValue($value). ", ";
				}
			}
		}
		// Crop the last comma
		$fields = mb_substr($fields, 0,-2);
		$values = mb_substr($values, 0,-2);
		$sql = "INSERT INTO $this->fullTable ($fields) VALUES ($values);";
		if ($this->databaseControl->query($sql)) {
			return true;
		} else {
			return false;
		}
	}

	function update(&$dataEntity, $triggerCallback = true) {
		$this->initControl();

		if ($triggerCallback) {
			$this->beforeUpdate($dataEntity);
		}

		$sql = "";
		foreach($this->fieldMeta as $k => $v) {
			if (($v->required != FM_STORE_DONTSTORE) &&
			(($v->required & FM_STORE_UPDATE) == FM_STORE_UPDATE)) {
				$sql .= $this->databaseControl->parseField("$k"). "=";
				$value = $dataEntity->get($k);
				if (($value === "") || ($value === null)) {
					$sql .= "null, ";
				} else {
					$sql .= $this->databaseControl->parseValue($value). ", ";
				}
			}
		}

		// Crop the last comma
		$sql = mb_substr($sql, 0,-2);
		$id = $dataEntity->get($this->key);

		$sql = "UPDATE $this->fullTable SET $sql WHERE $this->fullKey = $id;";

		$this->cacheData[$id] = $dataEntity;

		if ($this->databaseControl->query($sql)) {
			if ($triggerCallback) {
				$this->afterUpdate($dataEntity);
			}
			return $id;
		} else {
			return false;
		}
	}

	function updateField(&$dataEntity, $fieldName, $value, $triggerCallback = true) {

		//		Commented out by Tom (11th April 2006)
		//			if (($value === "") || ($value === null)) {
		//				return false;
		//			}
		if (($fieldName === "") || ($fieldName === null)) {
			return false;
		}

		$this->initControl();

		if ($triggerCallback) {
			$this->beforeUpdate($dataEntity);
		}

		if ($value === null) {
			$sql = $this->databaseControl->parseField("$fieldName") . " = NULL";
		} else {
			$sql = $this->databaseControl->parseField("$fieldName") . " = " . $this->databaseControl->parseValue($value);
		}

		$id = $dataEntity->get($this->key);

		$sql = "UPDATE $this->fullTable SET $sql WHERE $this->fullKey = $id;";

		if ($this->databaseControl->query($sql)) {
			if ($triggerCallback) {
				$this->afterUpdate($dataEntity);
			}
			$dataEntity->set($fieldName, $value);
			return $id;
		} else {
			return false;
		}
	}

	function incrementField(&$dataEntity, $fieldName, $value = 1, $triggerCallback = true) {

		if (($value === "") || ($value === null)) {
			return false;
		}
		if (($fieldName === "") || ($fieldName === null)) {
			return false;
		}

		$this->initControl();
		if ($triggerCallback) {
			$this->beforeUpdate($dataEntity);
		}

		$sql = $this->databaseControl->parseField("$fieldName");
		$sql .= " = " . $sql . " + " . $value;

		$id = $dataEntity->get($this->key);

		$sql = "UPDATE $this->fullTable SET $sql WHERE $this->fullKey = $id;";

		if ($this->databaseControl->query($sql)) {
			if ($triggerCallback) {
				$this->afterUpdate($dataEntity);
			}
			return $id;
		} else {
			return false;
		}
	}

	/**
	 * Updates a dataentity setting the fields in the array $fields to the
	 * values in the array $values.
	 * @param DataEntity $dataEntity The dataentity to update
	 * @param Array $fieldNames Field names to update
	 * @param Array $values Value to set to
	 * @return Boolean False if failed and the id of the dataentity if successfull
	 */
	function updateFields(&$dataEntity, $fieldNames, $values, $triggerCallback = true) {

		if (!is_array($values)) {
			trigger_error("Values not array");
			return false;
		}
		if (!is_array($fieldNames)) {
			trigger_error("Fields not array");
			return false;
		}

		if (($arrayLen = sizeof($fieldNames)) != sizeof($values)) {
			trigger_error("Field to Value count mismatch");
			return false;
		}

		if ($arrayLen <= 0) {
			trigger_error("No values to set");
			return false;
		}

		$this->initControl();
		if ($triggerCallback) {
			$this->beforeUpdate($dataEntity);
		}

		$sql = "";
		for ($i = 0; $i < $arrayLen; $i++) {
			$sql .= $this->databaseControl->parseField($fieldNames[$i]). " = " . $this->databaseControl->parseValue($values[$i]) . ", ";
			$dataEntity->set($fieldNames[$i], $values[$i]);
		}
		$sql = mb_substr($sql, 0,-2);
		$id = $dataEntity->get($this->key);

		$sql = "UPDATE $this->fullTable SET $sql WHERE $this->fullKey = $id;";

		if ($this->databaseControl->query($sql)) {
			if ($triggerCallback) {
				$this->afterUpdate($dataEntity);
			}
			return $id;
		} else {
			return false;
		}
	}

	function updateFieldWithFormat(&$dataEntity, $fieldName, $value) {
		return $this->updateField($dataEntity, $fieldName, $this->fieldMeta[$fieldName]->inputFormatter->format($value));
	}

	function validate(&$dataEntity, $required = FM_STORE_ALWAYS) {
		$this->initControl();

		$id = $dataEntity->get($this->key);
		//$this->errorControl->clear();

		foreach($this->fieldMeta as $k => $v) {
			$this->validateField($dataEntity, $k, $required);
		}
		return !$this->errorControl->hasErrors();
	}


	function validateSelection($selection, $dataEntity, $required = FM_STORE_ALWAYS) {
		$this->initControl();

		foreach($selection as $v) {
			$this->validateField($dataEntity, $v, $required);
		}

		return !$this->errorControl->hasErrors();
	}


	function validateField(&$dataEntity, $fieldName, $required = FM_STORE_ALWAYS) {

		$id = $dataEntity->get($this->key);
		$value = $dataEntity->get($fieldName);

		if (($this->fieldMeta[$fieldName]->required & $required) != $required) {
			return true;
		}

		// Paul Serby swapped these two ifs around - 2008-02-11
		// If custom validation is provided then use that
		if ($this->fieldMeta[$fieldName]->validation != null) {
			$this->fieldMeta[$fieldName]->validation->setDataEntity($dataEntity);
			$this->fieldMeta[$fieldName]->validation->validate($value,
				$this->fieldMeta[$fieldName]);
		} else if (($value === "") || ($value === null)) {
			if ($this->fieldMeta[$fieldName]->allowNull) {
				return true;
			} else {
				$this->errorControl->addError("'" .
				$this->fieldMeta[$fieldName]->description.
				"' must not be empty", $fieldName);
				return false;
			}
		} else {
			switch($this->fieldMeta[$fieldName]->type) {
				case FM_TYPE_URL:
					if (!preg_match('/^(http|https):\/\/[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,5}((:[0-9]{1,5})?\/.*)?$/i' , $value)) {
					$this->errorControl->addError("'" . $value . "' is not a valid URL", $fieldName);
					}
					break;
				case FM_TYPE_STRING:
					$this->fieldMeta[$fieldName]->length != null &&
					(mb_strlen($value) > $this->fieldMeta[$fieldName]->length) &&
					$this->errorControl->addError("'" .
					$this->fieldMeta[$fieldName]->description.
					"' too long. The Maximum length is " .
					$this->fieldMeta[$fieldName]->length. " characters.", $fieldName);
					break;
				case FM_TYPE_PHONENUMBER:
					if (!preg_match("/(\d|\+|\s)+/", $value)) {
						$this->errorControl->addError("Invalid phone number.", $fieldName);
					}
					$this->fieldMeta[$fieldName]->length != null &&
					(mb_strlen($value) > $this->fieldMeta[$fieldName]->length) &&
					$this->errorControl->addError("'" .
					$this->fieldMeta[$fieldName]->description.
					"' too long. The Maximum length is " .
					$this->fieldMeta[$fieldName]->length. " characters.", $fieldName);
					break;
				case FM_TYPE_UKPOSTCODE:
					if (preg_match("/[0-9] .{2}$/", $value)) {
						$value = preg_replace("/([^\s])([0-9] .{2})$/", "$1 $2", $value);
					}
					if (!preg_match("/^[A-Z] .{1, 3}\s[0-9] .{2}$/", $value)) {
						$this->errorControl->addError("Invalid UK Postcode.", $fieldName);
					}
					break;
				case FM_TYPE_INTEGER:
					if (!is_numeric($value) || ($value != intval($value))) {
						$this->errorControl->addError("'" .
						$this->fieldMeta[$fieldName]->description .
						"' must be an whole number.", $fieldName);
					}
					break;
				case FM_TYPE_FLOAT:
					if (!is_numeric($value)) {
						$this->errorControl->addError("'" .
						$this->fieldMeta[$fieldName]->description .
						"' must be an real number.", $fieldName);
					}
					break;
				case FM_TYPE_DATE:
					if ($value != null) {
						require_once("Date.php");
						if (is_array($value)) {
							//$date = new Date(sprintf("%04d-%02d-%02d", $value["Year"], $value["Month"], $value["Day"]));
							$value = sprintf("%04d-%02d-%02d", $value["Year"], $value["Month"], $value["Day"]);
						}
						$date = new Date($value);
						if (!checkdate($date->getMonth(), $date->getDay(), $date->getYear())) {
							$this->errorControl->addError("'" . $this->fieldMeta[$fieldName]->description
							. "' must be a valid date.", $fieldName);
						}
					}
					break;
				case FM_TYPE_BOOLEAN:
					break;
				case FM_TYPE_EMAILADDRESS:
					if (!mb_eregi("^[_a-z0-9-]+([\.\'][_a-z0-9-]+)*@[a-z0-9][a-z0-9-]+(\.[a-z0-9][a-z0-9-]*)*(\.[a-z]{2,4})$", $value)) {
						$this->errorControl->addError("'" .
						$this->fieldMeta[$fieldName]->description
						. "' must be a valid e-mail address.", $fieldName);
					}
					break;
				case FM_TYPE_PASSWORD:
					$encoder = CoreFactory::getPasswordEncoder();
					$dataEntity->data[$fieldName] = $encoder->format($value);
					break;
				case FM_TYPE_UID:
					break;
				case FM_TYPE_GUID:
					break;
				case FM_TYPE_CURRENCY:
					break;
				case FM_TYPE_SERIALIZED:
					$encoder = CoreFactory::getJsonEncoder();
					$dataEntity->data[$fieldName] = $encoder->format($value);
					break;
			}
		}
		// Do a unique field check
		if ($this->fieldMeta[$fieldName]->options &
		FM_OPTIONS_UNIQUE == FM_OPTIONS_UNIQUE) {

			$sql = "SELECT * FROM $this->fullTable WHERE " .
			$this->databaseControl->parseField($fieldName). "=" .
			$this->databaseControl->parseValue($value). " ";
			if ($id != null) {
				$sql .= "AND $this->fullKey != $id";
			}
			if ($this->databaseControl->numRows($this->databaseControl->query($sql)) > 0) {
				$this->errorControl->addError(
				"'" . $this->fieldMeta[$fieldName]->description . "'" .
				" is already in use. Please try a different '" .
				$this->fieldMeta[$fieldName]->description . "'", $fieldName);
			}
		}
		if ($this->errorControl->hasErrors()) {
			return false;
		} else {
			$dataEntity->data[$fieldName] = $value;
			return true;
		}
	}

	/**
	 * Resets the result pointer
	 * @return The number of rows returned by the query.
	 */
	function reset() {
		$this->results = null;
	}

	function clearFilter() {
		$this->filter = CoreFactory::getFilter();
		$this->reset();
	}

	/**
	 * Adds a filter for the value of $value on each of the fields set in $searchFields
	 * @param String $value The value to search for
	 * @return void
	 */
	function search($value, $freeText = false, $exclude = false, $splitSearchPhrase = false) {
		$this->initTable();
		if ($freeText) {
			if (($value == null) || (!isset($this->fullTextIndex))) {
				return false;
			}
			if (!$this->filter->addFreeTextCondition($this->table, $this->fullTextIndex, $value)) {
				return false;
			}
		} else {
			if (($value == null) || (!isset($this->searchFields)) || (!is_array($this->searchFields))) {
				return false;
			}
			if ($splitSearchPhrase) {
				$values = explode(" ", $value);
			} else {
				$values[] = $value;
			}
			$conditions = array();
			foreach ($this->searchFields as $fieldName) {
				if ($exclude) {
					$operator = "NOT ILIKE";
					$logicalOperator = "AND";
				} else {
					$operator = "ILIKE";
					$logicalOperator = "OR";
				}
				foreach ($values as $value) {
					$conditions[] = $this->filter->makeConditional($this->table,
					$fieldName, "%" . $value . "%", $operator, $logicalOperator);
				}
			}
			$this->filter->addConditionalGroup($conditions);
		}
		return true;
	}

	/**
	 * Returns all the rows from the table defined for this class, except thoses
	 * filtered out by the '$filter' object.
	 * @return The number of rows returned by the query.
	 */
	function retrieveAll() {
		$this->initTable();
		if ($this->filter != null) {
			$distinct = $this->filter->getDistinctSql();
			$joins = $this->filter->getJoinSql();
			$conditions = $this->filter->getConditionSql();
			$order = $this->filter->getOrderSql();
			$limit = $this->filter->getLimitSql();
			$offset = $this->filter->getOffsetSql();
			$sql = "SELECT $distinct $this->fullTable.* FROM $this->fullTable $joins $conditions $order $limit $offset";
		} else {
			$sql = "SELECT $this->fullTable.* FROM $this->fullTable";
		}
		$this->numRows = -1;
		if ($this->results = $this->databaseControl->query($sql)) {
			$this->numRows = $this->databaseControl->numRows($this->results);
		} else {
			trigger_error($this->databaseControl->getLastError() . " SQL: {$sql}");
		}
		return $this->numRows;
	}

	/**
	 * Returns the count of rows from the table defined for this class, except
	 * thoses filtered out by the '$filter' object.
	 * @return The number of rows counted.
	 */
	function count() {
		$this->initTable();
		if ($this->filter != null) {
			$distinct = $this->filter->getDistinctSql();
			$joins = $this->filter->getJoinSql();
			$conditions = $this->filter->getConditionSql();
			$order = $this->filter->getOrderSql();
			$limit = $this->filter->getLimitSql();
			$offset = $this->filter->getOffsetSql();
			$sql = "SELECT COUNT(*) FROM $this->fullTable $joins $conditions $limit $offset";
		} else {
			$sql = "SELECT COUNT(*) FROM $this->fullTable";
		}

		$data = $this->databaseControl->fetchRow($this->databaseControl->query($sql));
		return $data[0] == null ? 0 : $data[0];
	}

	/**
	 *
	 * @return The number of rows counted.
	 */
	function duplicate(&$dataEntity) {
		$newDataEntity = $this->makeNew();

		// Copy the data
		$newDataEntity->data = $dataEntity->data;
		$newDataEntity->set($this->key, "");

		$binaryControl = CoreFactory::getBinaryControl();
		// If there are any binaries copy them
		foreach ($this->fieldMeta as $key => $field) {
			if (($field->type == FM_TYPE_BINARY) && ($binary = $dataEntity->getRelation($key))) {
				$newDataEntity->data[$key] = $binaryControl->duplicate($binary);
			}
		}

		return $newDataEntity;
	}

	function runQuery(&$sql) {
		$this->initTable();
		$this->results = $this->databaseControl->query($sql);
		$this->numRows = $this->databaseControl->numRows($this->results);
		return $this->numRows;
	}


	/**
	 * Abstraction of the logic from the various different aggregate function methods as they were all identical.
	 * Takes in the field name and the type of aggreagate function and returns the corresponding result (SUM, AVG etc).
	 * @author Adam Duncan <adam.duncan@clock.co.uk>
	 * @param String $fieldName
	 * @param String $type
	 * @return Mixed
	 */
	protected function aggregateFunctionGenerator($fieldName, $type) {
		$this->initTable();
		if ($this->filter != null) {
			$joins = $this->filter->getJoinSql();
			$conditions = $this->filter->getConditionSql();

			$sql = "SELECT " . $type . "(" . $this->databaseControl->parseField($fieldName)
			. ") FROM $this->fullTable $joins $conditions";
		} else {
			$sql = "SELECT " . $type . "(" . $this->databaseControl->parseField($fieldName)
			. ") FROM $this->fullTable $joins $conditions";
		}

		$data = $this->databaseControl->fetchRow($this->databaseControl->query($sql));
		return $data[0] == null ? 0 : $data[0];
	}

	/**
	 * Returns the max of the column '$field' from the table defined
	 * for this class, except thoses filtered out by the '$filter' object.
	 * @return The max of the column returned by the query.
	 */
	public function maxField($fieldName) {
		return $this->aggregateFunctionGenerator($fieldName, "MAX");
	}

	/**
	 * Returns the min of the column '$field' from the table defined
	 * for this class, except thoses filtered out by the '$filter' object.
	 * @return The max of the column returned by the query.
	 */
	public function minField($fieldName) {
		return $this->aggregateFunctionGenerator($fieldName, "MIN");
	}

	/**
	 * Returns the average of the column '$field' from the table defined
	 * for this class, except thoses filtered out by the '$filter' object.
	 * @return The sum of the column returned by the query.
	 */
	public function avgField($fieldName) {
		return $this->aggregateFunctionGenerator($fieldName, "AVG");
	}

	/**
	 * Returns the sum of the column '$field' from the table defined
	 * for this class, except thoses filtered out by the '$filter' object.
	 * @return The sum of the column returned by the query.
	 */
	public function sumField($fieldName) {
		return $this->aggregateFunctionGenerator($fieldName, "SUM");
	}

	/**
	 * Deletes all the rows from the table defined for this class, except thoses
	 * Use with care!!!!
	 * filtered out by the '$filter' object.
	 * @return The number of rows removed by the query.
	 */
	function deleteAll() {
		$this->initTable();
		if ($this->filter != null) {
			$joins = $this->filter->getJoinSql();
			$conditions = $this->filter->getConditionSql();
			$order = $this->filter->getOrderSql();
			$sql = "DELETE FROM $this->fullTable $joins
						$conditions";
		} else {
			$sql = "DELETE FROM $this->fullTable";
		}

		$this->databaseControl->query($sql);
	}

	/**
	 * Deletes all the rows from the table defined for this class, except thoses
	 * Use with care!!!!
	 * Filtered using the '$filter' object.
	 * @return The number of rows updated by the query.
	 */
	function updateAll($fieldValuePair) {

		$this->initTable();
		$updateSql = "";

		foreach ($fieldValuePair as $fieldName => $value) {
			if ($this->isField($fieldName)) {
				$updateSql .= $this->databaseControl->parseField($fieldName) . " = " .
					$this->databaseControl->parseValue($value) . ",";
			}
		}
		if (strlen($updateSql) <= 0) {
			return false;
		}

		$updateSql = substr($updateSql, 0, -1);

		if ($this->filter != null) {
			$conditions = $this->filter->getConditionSql();

			$sql = "UPDATE $this->fullTable SET $updateSql $conditions";
		} else {
			$sql = "UPDATE $this->fullTable SET $updateSql";
		}

		$result = $this->databaseControl->query($sql);
		return $this->databaseControl->numRows($result);
	}

	/**
	 * Returns the next Record in the dataset as a DataEntity
	 * @return DataEntity
	 */
	function getNext() {
		if ($this->results === null) {
			$this->retrieveAll();
		}
		return $this->outputMap($this->databaseControl->fetchRow($this->results));
	}

	function getFromRecordPointer() {
		if ($this->results === null) {
			$this->retrieveAll();
		}
		return $this->outputMap($this->databaseControl->fetchRow($this->results,
		$this->recordPointer));
	}

	function getRandom() {
		$this->initTable();
		if ($this->filter != null) {
			$joins = $this->filter->getJoinSql();
			$conditions = $this->filter->getConditionSql();
		}
		$this->filter->addOrder(-1);
		$this->retrieveAll();
		return $this->outputMap($this->databaseControl->fetchRow($this->results));
	}

	/**
	 * Returns the net $length number of records from the result set.
	 * @param $length The number of records to return.
	 * @return DataEntity
	 */
	function getPage($page, $length = null) {
		if ($this->results === null) {
			$this->retrieveAll();
		}
		if (empty($length)) {
			$length = null;
		}
		$this->pageLength = $length;

		$this->setupPaging($page, $length);

		if ($this->recordPointer === null) {
			$start = ($this->currentPage - 1) * $length;
			$this->recordPointer = $start;
		}	else {
			$start = ($this->currentPage - 1) * $length;
			$this->recordPointer++;
		}

		if ((($length !== null) && ($this->recordPointer >= $start + $length))
		|| ($this->recordPointer >= $this->numRows)) {
			$this->recordPointer = null;
			return false;
		} else {
			return $this->outputMap($this->databaseControl->fetchRow(
			$this->results, $this->recordPointer));
		}
	}

	function setupPaging($page, $length) {
		if ($length == 0) {
			$this->pageCount = 1;
		} else {
			$this->pageCount = max(ceil($this->numRows / $length), 1);
		}
		if (($page > $this->pageCount) || ($page == -1)) {
			$page = $this->pageCount;
		}
		if ($page < 1) {
			$page = 1;
		}
		$this->currentPage = $page;
	}

	/**
	 * Returns the current page length
	 */
	function getPageLength() {
		return $this->pageLength;
	}

	/**
	 * Returns the item with the given Id
	 * @param Integer $id The id of the row to retrieve.
	 * If $id is null the return the first row of the table
	 * @return DataEntity|false The dataEntity on successful find otherwise false
	 */
	function item($id, $cached = true) {
		if (!is_numeric($id)) {
			return false;
		}
		$this->initControl();
		if (($cached) && (isset($this->cacheData[$id]))) {
			return $this->cacheData[$id];
		}
		$sql = "SELECT * FROM $this->fullTable WHERE $this->fullKey = $id LIMIT 1";
		$result = $this->databaseControl->query($sql);
		if ($result === null) {
			return false;
		} else {
			if ($rowData = $this->databaseControl->fetchRow($result)) {
				$this->cacheData[$id] = $this->outputMap($rowData);
				return $this->cacheData[$id];
			} else {
				return false;
			}
		}
	}

	/**
	 * Returns the item after the passed in dataEntity in a default order
	 * @param DataEntity $dataEntity The item you wish to find the next record from
	 * @return DataEntity|false The dataEntity on successful find otherwise false
	 */
	function getNextItem(&$dataEntity, $orderField) {
		$this->initTable();
		$id = $dataEntity->get($this->key);
		if ($this->filter != null) {

			$joins = $this->filter->getJoinSql();

			$this->filter->addConditional($this->table, $orderField,
			$dataEntity->get($orderField), ">");


			$conditions = $this->filter->getConditionSql();
			$order = $this->filter->getOrderSql();

			$sql = "SELECT $this->fullTable.* FROM $this->fullTable $joins" .
			" $conditions $order LIMIT 1";
		} else {
			return false;
		}

		$result = $this->databaseControl->query($sql);
		if ($result === null) {
			return false;
		} else {
			if ($rowData = $this->databaseControl->fetchRow($result)) {
				$this->cacheData[$id] = $this->outputMap($rowData);
				return $this->cacheData[$id];
			} else {
				return false;
			}
		}
	}

	/**
	 * Returns the item after the passed in dataEntity in a default order
	 * @param DataEntity $dataEntity The item you wish to find the next record from
	 * @return DataEntity|false The dataEntity on successful find otherwise false
	 */
	function getPreviousItem(&$dataEntity, $orderField) {
		$this->initTable();
		$id = $dataEntity->get($this->key);
		if ($this->filter != null) {

			$joins = $this->filter->getJoinSql();

			$this->filter->addConditional($this->table, $orderField,
			$dataEntity->get($orderField), "<");

			$conditions = $this->filter->getConditionSql();
			$order = $this->filter->getOrderSql();

			$sql = "SELECT $this->fullTable.* FROM $this->fullTable $joins" .
			" $conditions $order LIMIT 1";
		} else {
			return false;
		}

		$result = $this->databaseControl->query($sql);
		if ($result === null) {
			return false;
		} else {
			if ($rowData = $this->databaseControl->fetchRow($result)) {
				$this->cacheData[$id] = $this->outputMap($rowData);
				return $this->cacheData[$id];
			} else {
				return false;
			}
		}
	}

	/**
	 * @return DataEntity Returns a DataEntity set with the first row of the dataset
	 */
	function firstItem() {
		$this->initTable();
		if ($this->filter != null) {
			$joins = $this->filter->getJoinSql();
			$conditions = $this->filter->getConditionSql();
			$order = $this->filter->getOrderSql();
			$sql = "SELECT $this->fullTable.* FROM $this->fullTable $joins
						$conditions $order LIMIT 1";
		} else {
			$sql = "SELECT * FROM $this->fullTable LIMIT 1";
		}
		$result = $this->databaseControl->query($sql);
		if ($result === null) {
			return false;
		} else {
			if ($rowData = $this->databaseControl->fetchRow($result)) {
				return $this->outputMap($rowData);
			} else {
				return false;
			}
		}
	}

	/**
	 * Returns the first item with the field with the name $field name
	 * equal to $value
	 * @param $value The value to match $fieldName
	 * @param $fieldName The name of the field to check value agenst
	 * @param $order What should we order by
	 * @param $desc Should we order in descending order
	 * @return DataEntity|false
	 */
	function itemByField($value, $fieldName, $order = "", $desc = false, $cached = true) {

		$this->initTable();
		if ($value == null) {
			return false;
		}
		if ($fieldName == null) {
			return false;
		}

		if (($cached) && (isset($this->cacheData[$fieldName][$value]))) {
			return $this->cacheData[$fieldName][$value];
		}

		$sql = "SELECT * FROM $this->fullTable WHERE " .
		$this->databaseControl->parseField($fieldName) . " = " .
		$this->databaseControl->parseValue($value);

		if ($order) {
			$sql .= " ORDER BY " . $this->databaseControl->parseField($order);
		}

		if ($desc) {
			$sql .= " DESC";
		}

		$sql .= " LIMIT 1";

		$result = $this->databaseControl->query($sql);

		if ($result === null) {
			return false;
		} else {
			if ($rowData = $this->databaseControl->fetchRow($result)) {
				return $this->cacheData[$fieldName][$value] = $this->outputMap($rowData);
			} else {
				return false;
			}
		}
	}

	/**
	 * Returns a random item
	 * @param $min The min of the random range (usually 0)
	 * @param $max The max of the random range (usually num rows - 1)
	 * @return DataEntity on success, false on failure

	 * TODO: Needs to be discussed with serby (limit and -1 on max)
	 */
	function randomItem($min = "", $max = "") {

		$offset = rand($min, $max);

		$this->initTable();

		$joins = $this->filter->getJoinSql();
		$conditions = $this->filter->getConditionSql();
		$order = $this->filter->getOrderSql();

		$sql = "SELECT * FROM $this->fullTable $joins $conditions $order OFFSET $offset";

		$result = $this->databaseControl->query($sql);

		if ($result === null) {
			return false;
		} else {
			if ($rowData = $this->databaseControl->fetchRow($result)) {
				return $this->outputMap($rowData);
			} else {
				return false;
			}
		}
	}

	function outputMap(&$data) {
		if (!$data) {
			return false;
		}

		$this->initControl();

		$d = $this->getDataEntity();
		foreach($this->fieldMeta as $k => $v) {

			$value = null;

			if (isset($data[$k])) {
				if (is_array($data[$k])) {
					switch ($this->fieldMeta[$k]->type) {
						case FM_TYPE_STRING:
						case FM_TYPE_STRING_UPPER:
						case FM_TYPE_STRING_LOWER:
						case FM_TYPE_STRING_TITLE:
							$value = trim(implode(" ", $data[$k]));
							break;
						default:
							$value = $data[$k];
							break;
					}
				} else {
					$value = $data[$k];
				}
			} else {
				$value = $v->mapDefault;
			}
			$d->add($k, $value);
		}
		return $d;
	}

	function map(&$data) {
		if (!$data) {
			return false;
		}

		$this->initControl();
		$d = $this->getDataEntity();
		foreach($this->fieldMeta as $k => $v) {
			$value = null;
			if (isset($data[$k])) {
				if (is_array($data[$k])) {
					switch ($this->fieldMeta[$k]->type) {
						case FM_TYPE_STRING:
						case FM_TYPE_STRING_UPPER:
						case FM_TYPE_STRING_LOWER:
						case FM_TYPE_STRING_TITLE:
							$value = trim(implode(" ", $data[$k]));
							break;
						case FM_TYPE_BINARY:
							$this->fieldMeta[$k]->inputFormatter->format($data[$k]["FileInfo"]["TempName"]);
							// Set to -1 if CurrentId is null, to ensure the binary field gets validated
							// Paul Serby - 2008-02-11
							$value = $data[$k]["CurrentId"];
							$d->setBinary($k, $value, $data[$k]["FileInfo"]);

							break;
						default:
							$value = $data[$k];
							// Formatting
							$value = $this->fieldMeta[$k]->inputFormatter->format($value);
							break;
					}
				} else {
					$value = $data[$k];
				}
			} else {
				$value = $v->mapDefault;
			}

			// Autodata
			if (isset($this->fieldMeta[$k]->autoData)) {
				$value = $this->fieldMeta[$k]->autoData->get();
			}

			// Formatting
			if ($this->fieldMeta[$k]->type != FM_TYPE_BINARY) {
				$value = $this->fieldMeta[$k]->inputFormatter->format(stripslashes($value));
			}
			$d->add($k, $value);
		}
		return $d;
	}

	/**
	 * Takes two data entites and attempts to merge them with precidence of the
	 * primary DataEntity $primaryDataEntity
	 */
	function leftMergeDataEntities($primaryDataEntity, $secondryDataEntity) {
		foreach($primaryDataEntity->data as $k => $v)	{
			if (($v == "") && ($secondryDataEntity->data[$k] != "")) {
				$primaryDataEntity->set($k, $secondryDataEntity->data[$k]);
			}
		}
		return $primaryDataEntity;
	}

	/**
	 * Takes an Xml version of the DataEntity and returns a DataEntity
	 * structure with it's data.
	 */
	function parseXml(&$xml) {
		if (!$xml) {
			return false;
		}
		$this->initControl();
		$p = xml_parser_create();
		$vals = "";
		$index = "";
		xml_parse_into_struct($p, $xml, $vals, $index);
		xml_parser_free($p);

		$d = $this->makeNew();
		foreach($this->fieldMeta as $k => $v) {
			if (isset($index[mb_strtoupper($k)])) {
				$value = $vals[$index[mb_strtoupper($k)][0]]["value"];
				$d->set($k, $value);
			}
		}
		return $d;
	}

	function getDataEntity() {
		return CoreFactory::getDataEntity($this);
	}

	/**
	 *
	 * @return DataEntity
	 */
	function makeNew() {
		$this->initControl();
		$d = $this->getDataEntity();
		foreach($this->fieldMeta as $k => $v) {
			$value = $v->default;
			// Autodata
			if (isset($this->fieldMeta[$k]->autoData)) {
				$value = $this->fieldMeta[$k]->autoData->get();
			}
			$d->add($k, $value);
		}
		return $d;
	}

	/**
	 * Delete records that match supplied comma separated IDs
	 * @param $ids Comma separated list of Ids for the records to be deleted.
	 * @return Boolean Returns true if query is ran successfully
	 */
	function delete($ids, $fieldName = null) {
		$this->initControl();
		if ($ids == null) {
			trigger_error("Unable to delete records. ", E_USER_ERROR);
			$this->application->errorControl->addError("Unable to delete. Nothing selected");
			return false;
		}
		$this->initTable();
		$ids = explode(",", $ids);

		$binaryFields = array();
		foreach($this->fieldMeta as $k => $v) {
			if ($v->type == FM_TYPE_BINARY) {
				$binaryFields[] = $k;
			}
		}

		if ($fieldName == null) {
			$fieldName = $this->key;
		}

		$binaryControl = CoreFactory::getBinaryControl();

		// Store the status of each delete to return
		$returnStatus = array();
		foreach($ids as $v) {
			if ($dataEntity = $this->itemByField($v, $fieldName)) {
				$this->beforeDelete($dataEntity);
				foreach ($binaryFields as $binaryField) {
					$binary = $binaryControl->item($dataEntity->get($binaryField));
					if ($binary = $binaryControl->item($dataEntity->get($binaryField))) {
						$binaryControl->delete($binary);
					}
				}
				$sql = "DELETE FROM $this->fullTable WHERE " . $this->databaseControl->parseField($fieldName) . " = " .
				$this->databaseControl->parseValue($v);
				$returnStatus[$v] = "Successful";
				if (!$this->databaseControl->query($sql)) {
					$returnStatus[$v] = "Failed";
				}
				$this->afterDelete($dataEntity);
			}
		}
		if (sizeof($returnStatus) > 0) {
			return $returnStatus;
		} else {
			return false;
		}
	}

	/**
		 * Deletes record upon passing an ID
		 * @param Integer $id ID of the record to deleted.
		 * @return Boolean Returns true if query ran successfully. False if $id is not numeric
	 */
	function quickDelete($id) {
		if (!is_numeric($id)) {
			return false;
		}
		$this->initTable();
		$sql = "DELETE FROM $this->fullTable WHERE $this->fullKey = '$id'";
		$this->databaseControl->query($sql);
		return true;
	}

	/**
	 * Delete a record where Field has Value
	 * @param $ids Comma seperated list of Ids for the records to be deleted.
	 * @return true;
	 */
	function deleteWhere($fieldName, $value) {
		$this->initTable();

		//Added by Tom (2007-07-27) to allow and array of fieldnames and values)
		if (!is_array($fieldName)) {
			$fieldName = array($fieldName);
		}
		if (!is_array($value)) {
			$value = array($value);
		}

		$sql = "DELETE FROM $this->fullTable WHERE ";

		foreach($fieldName as $i => $field) {
			if (isset($value[$i])) {
				$sql .= $this->databaseControl->parseField($field) . " = "
					. $this->databaseControl->parseValue($value[$i]) . " AND ";
			}
		}
		//Remove the final " AND " from the SQL.
		$sql = mb_substr($sql, 0, -5);

		$this->databaseControl->query($sql);
		//TODO: Run the delete callback - You will need to get the Dataentity
		// before you can do the call back
		//$this->afterDelete( ??$dataEntity?? );
		return true;
	}

		/**
		 * Returns true if results are retrieved, false otherwise
		 *
		 * @return Boolean Returns true if results are retrieved, false otherwise
		 */
	function isRetrieved() {
		return $this->results !== null;
	}

		/**
		 * The number of rows returned
		 *
		 * @return Integer The number of rows returned
		 */
	function getNumRows() {
		if ($this->results === null) {
			$this->retrieveAll();
		}
		return $this->numRows;
	}

		/**
		 * Returns the number of results for current page
		 *
		 * @return Integer The number of results for the current page
		 */
	function getCurrentPage() {
		return $this->currentPage;
	}

		/**
		 * Returns the page count
		 *
		 * @return Integer The number of results for the page
		 */
	function getPageCount() {
		return $this->pageCount;
	}

		/**
		 * Returns results as array
		 *
		 * @param String $key Name of the key field. By default, "Id"
		 * @return Array $returnArray Returns the results as an array
		 *
		 */
	function getResultsAsArray($key = "Id") {
		$returnArray = array();
		while ($data = $this->getNext()) {
			$returnArray[$data->get($key)] = $data;
		}
		return $returnArray;
	}

		/**
		 * Returns results for a given field ($fieldName) as array
		 *
		 * @param String $fieldName Name of field to produce the results for
		 * @param String $key Name of the key field. By default, "Id"
		 * @return Array $returnArray Returns the field of results as an array
		 */
	function getResultsAsFieldArray($fieldName, $key = "Id") {
		$returnArray = array();
		while ($data = $this->getNext()) {
			$returnArray[$data->get($key)] = $data->get($fieldName);
		}
		return $returnArray;
	}

	/**
	 * Override this function to perform actions before insertions
	 * @param DataEntity The DataEntity that has been inserted
	 * @return void
	 */
	function beforeInsert(&$dataEntity) {
	}

	/**
	 * Override this function to perform actions before updates
	 * @param DataEntity The DataEntity that has been inserted
	 * @return void
	 */
	function beforeUpdate(&$dataEntity) {
	}

	/**
	 * Override this function to perform actions before deletes
	 * @param DataEntity The DataEntity that has been inserted
	 * @return void
	 */
	function beforeDelete(&$dataEntity) {
	}

	/**
	 * Override this function to perform actions after insertions
	 * @param DataEntity The DataEntity that has been inserted
	 * @return void
	 */
	function afterInsert(&$dataEntity) {
	}

	/**
	 * Override this function to perform actions after updates
	 * @param DataEntity The DataEntity that has been inserted
	 * @return void
	 */
	function afterUpdate(&$dataEntity) {
	}

	/**
	 * Override this function to perform actions after deletes
	 * @param DataEntity The DataEntity that has been inserted
	 * @return void
	 */
	function afterDelete(&$dataEntity) {
	}

	/**
	 * This function returns a string of the field names stored in the controller
	 * if true is passed in these are formatted as table headers otherwise they are tab seperated
	 * @param asHtml bool default false (tab seperated return)
	 * @return output String
	 */
	function toRowHeader($asHtml = false) {
		$output = "";
		if ($asHtml) {
			$output .= "<tr>\n";
			foreach($this->fieldMeta as $k => $v) {
				$output .= "\t<th>".	$k ."</th>\n";
			}
			$output .= "</tr>\n";
		} else {
			foreach($this->fieldMeta as $k => $v) {
				$output .= $k ."\t";
			}
			$output .= "\n";
		}
		return $output;
	}
}


/**
 * Interface Definitions
 */

/**
 * The base FieldFormatter interface that should be overridded by custom
 * formating classes
 *
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Core
 */
class FieldFormatter {
	/**
	 * Formats the given value in a specific way.
	 * @param $value The value to be formatted
	 * @return The formatted value
	 */
	function format($value) {
		return $value;
	}
}

/**
 * Default interface for Custom validation
 *
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Core
 * @subpackage Validation
 */
class CustomValidation {
	var $dataEntity = null;
	var $extraInfo = null;

	function validate(&$value, $fieldMeta) {
		return true;
	}
	function setDataEntity(&$value) {
		$this->dataEntity = $value;
	}
	function setExtraInfo($value) {
		$this->extraInfo = $value;
	}
	function getExtraInfo() {
		return $this->extraInfo;
	}
	function onFail() {
	}
	function onPass() {
	}
}
