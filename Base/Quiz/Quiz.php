<?php
/**
 * @package Base
 * @subpackage Quiz
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision: 456 $ - $Date: 2008-03-16 21:09:59 +0000 (Sun, 16 Mar 2008) $
 */

/**
 * Include Data.php so that DataControl can be extended.
 */
require_once("Atrox/Core/Data/Data.php");

/**
 *
 * @author Tom Smith {@link mailto:thomas.smith@clock.co.uk thomas.smith@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision: 456 $ - $Date: 2008-03-16 21:09:59 +0000 (Sun, 16 Mar 2008) $
 * @package Base
 * @subpackage Quiz
 */
class QuizControl extends DataControl {
	var $table = "Quiz";
	var $key = "Id";
	var $sequence = "Quiz_Id_seq";
	var $defaultOrder = "DateCreated";		
	var $searchFields = array("Id", "Name");
	
	function init() {
		$this->fieldMeta["Id"] = new FieldMeta(
			"Id", "", FM_TYPE_INTEGER, null, FM_STORE_NEVER, false);
		
		$this->fieldMeta["Name"] = new FieldMeta(
			"Name", "", FM_TYPE_STRING, null, FM_STORE_ALWAYS, false);
		
		$this->fieldMeta["NumberOfQuestions"] = new FieldMeta(
			"Number Of Question", 0, FM_TYPE_STRING, null, FM_STORE_ALWAYS, false);
		
		$this->fieldMeta["Live"] = new FieldMeta(
			"Live", "f", FM_TYPE_BOOLEAN, null, FM_STORE_ALWAYS, false);

		$this->fieldMeta["DateCreated"] = new FieldMeta(
			"Date Created", "", FM_TYPE_DATE, 1, FM_STORE_NEVER, false);

		$this->fieldMeta["DateCreated"]->setFormatter(CoreFactory::getDateTimeFieldFormatter());
	}
	
	function getLatestQuiz() {
		$filter = CoreFactory::getFilter();
		$filter->addConditional($this->table, "Live", "t");
		$filter->addLimit(1);
		$this->setFilter($filter);
		if ($quiz = $this->getNext()) {
			return $quiz;
		} else {
			return false;
		}
	}
}