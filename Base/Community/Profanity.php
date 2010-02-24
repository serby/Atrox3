<?php
/**
 * @package Base
 * @subpackage Community
 * @copyright Clock Limited 2010
 * @version 3.2
 */

/**
 * Include Data.php so that DataControl can be extended.
 */
require_once("Atrox/Core/Data/Data.php");


/**
 * Profanity Types
 */
define("PROF_OBSCENITY", 1);
define("PROF_WORRYWORD", 2);

/**
 *
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2
 * @package Base
 * @subpackage Community
 */
class ProfanityControl extends DataControl {
	var $table = "Profanity";
	var $key = "Id";
	var $sequence = "Profanity_Id_seq";
	var $defaultOrder = "Word";
	var $defaultOrderDesc = true;
	var $searchFields = array("Word");

	var $types = array(
			PROF_OBSCENITY => "Obscenity",
			PROF_WORRYWORD => "Worry Phrase");

	function init() {

		$this->fieldMeta["Id"] = new FieldMeta(
			"Id", "", FM_TYPE_INTEGER, null, FM_STORE_NEVER, false);

		$this->fieldMeta["Word"] = new FieldMeta(
			"Word", "", FM_TYPE_STRING, 50, FM_STORE_ALWAYS, false, FM_OPTIONS_UNIQUE);

		$this->fieldMeta["Type"] = new FieldMeta(
			"Type", "", FM_TYPE_INTEGER, null, FM_STORE_ALWAYS, false);

		$this->fieldMeta["Type"]->setFormatter(
			CoreFactory::getArrayRelationFormatter($this->types, PROF_OBSCENITY));
	}

	function retrieveType($type) {
		$filter = $this->getFilter();
		if ($filter == null) {
			$filter = CoreFactory::getFilter();
		}
		$filter->addConditional($this->table, "Type", $type);
		$this->setFilter($filter);
		$this->retrieveAll();
	}

	function getTypes() {
		return $this->types;
	}

	function afterInsert(&$dataEntity) {
		$cacheControl = &CoreFactory::getCacheControl();
		$cacheControl->deleteListCache("Profanities");
		$cacheControl->deleteListCache("WorryWords");			
	}

	function afterUpdate(&$dataEntity) {
		$this->afterInsert($dataEntity);
	}

	function afterDelete(&$dataEntity) {
		$this->afterInsert($dataEntity);
	}
}

/**
 *
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2
 * @package Base
 * @subpackage Community
 */
class ProfanityValidation extends CustomValidation {

	var $field;
	var $callback = null;

	function ProfanityValidation($field = "text") {
		$this->field = $field;
	}

	function setCallBack(&$callBack) {
		$this->callBack = $callBack;
	}

	function validate(&$value) {
		$cacheControl = &CoreFactory::getCacheControl();

		$profanityControl = BaseFactory::getProfanityControl();
		$profanityControl->retrieveType(PROF_OBSCENITY);
		$obscenityList = $cacheControl->getCacheList("Profanities", $profanityControl , "Word");

		$profanityControl->clearFilter();
		$profanityControl->retrieveType(PROF_WORRYWORD);
		$worryWordList = $cacheControl->getCacheList("WorryWords", $profanityControl , "Word");

		$copy = " " . mb_strtoupper(str_replace(array("\r\n", "\n", "-", ".", ",", "!", "?"), " ", $value)) . " ";
		$output = "";

		if (is_array($obscenityList)) {
			foreach ($obscenityList as $word) {
				$upperWord = " " . mb_strtoupper($word) . " ";
				if (mb_strpos($copy, $upperWord) !== false) {
					$output .= "'$word', ";
				}
			}
			if ($output != "") {
				$output = mb_substr($output, 0, -2);
				$this->dataEntity->control->errorControl->addError(
					"Your $this->field contained the following word(s) or phrase(s) which are not allowed by the system ($output). Please try and rephrase your text so that it is not considered offensive.");
			}
		}

		if (isset($this->callBack)) {

			if (is_array($worryWordList)) {				
				$worryWords = array();
				foreach ($worryWordList as $word) {
					$upperWord = " " . mb_strtoupper($word) . " ";
					if (mb_strpos($copy, $upperWord) !== false) {
						$worryWords[] =  $word;
					}
				};	
				if (sizeof($worryWords) > 0) {
					$this->callBack->run($worryWords, $this->dataEntity);	
				}
			}
		}
		return true;
	}
}