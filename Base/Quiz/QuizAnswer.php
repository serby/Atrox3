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
class QuizAnswerControl extends DataControl {
	var $table = "QuizAnswer";
	var $key = "Id";
	var $sequence = "QuizAnswer_Id_seq";
	var $defaultOrder = "QuestionId";		
	var $searchFields = array("Id", "Answer");
	
	function init() {
		$this->fieldMeta["Id"] = new FieldMeta(
			"Id", "", FM_TYPE_INTEGER, null, FM_STORE_NEVER, false);
		
		$this->fieldMeta["QuestionId"] = new FieldMeta(
			"Question Id", "", FM_TYPE_RELATION, null, FM_STORE_ALWAYS, false);

		$this->fieldMeta["QuestionId"]->setRelationControl(BaseFactory::getQuizQuestionControl());
					
		$this->fieldMeta["Answer"] = new FieldMeta(
			"Answer", "", FM_TYPE_STRING, null, FM_STORE_ALWAYS, false);
		
		$this->fieldMeta["Correct"] = new FieldMeta(
			"Correct", "f", FM_TYPE_BOOLEAN, null, FM_STORE_ALWAYS, false);
	}
	
	function retrieveForQuestion($question) {
		$this->clearFilter();
		$filter = CoreFactory::getFilter();
		$filter->addConditional($this->table, "QuestionId", $question->get("Id"));
		$this->setFilter($filter);			
	}
	
	function retrieveCorrectAnswerForQuestion($quizQuestion) {
		$filter = CoreFactory::getFilter();
		$filter->addConditional($this->table, "QuestionId", $quizQuestion->get("Id"));
		$filter->addConditional($this->table, "Correct", "t");
		$this->setFilter($filter);
	}
}