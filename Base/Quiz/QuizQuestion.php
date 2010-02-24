<?php
/**
 * @package Base
 * @subpackage Quiz
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision: 456 $ - $Date: 2008-03-16 21:09:59 +0000 (Sun, 16 Mar 2008) $
 */

/**
 * Include Data.php so that DataControl can be extended.
 * Include DataEntity.php so that DataEntity can be extended.
 */
require_once("Atrox/Core/Data/Data.php");
require_once("Atrox/Core/Data/DataEntity.php");

/**
 *
 * @author Tom Smith {@link mailto:thomas.smith@clock.co.uk thomas.smith@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision: 456 $ - $Date: 2008-03-16 21:09:59 +0000 (Sun, 16 Mar 2008) $
 * @package Base
 * @subpackage Quiz
 */
class QuizQuestionControl extends DataControl {
	var $table = "QuizQuestion";
	var $key = "Id";
	var $sequence = "QuizQuestion_Id_seq";
	var $defaultOrder = "QuestionId";		
	var $searchFields = array("Id", "Answer");
	
	function init() {
		$this->fieldMeta["Id"] = new FieldMeta(
			"Id", "", FM_TYPE_INTEGER, null, FM_STORE_NEVER, false);
		
		$this->fieldMeta["QuizId"] = new FieldMeta(
			"Quiz Id", "", FM_TYPE_RELATION, null, FM_STORE_ALWAYS, false);

		$this->fieldMeta["QuizId"]->setRelationControl(BaseFactory::getQuizControl());
					
		$this->fieldMeta["Question"] = new FieldMeta(
			"Question", "", FM_TYPE_STRING, null, FM_STORE_ALWAYS, false);
	}
	
	function retrieveForQuiz($quiz) {
		$filter = CoreFactory::getFilter();
		$filter->addConditional($this->table, "QuizId", $quiz->get("Id"));
		$this->setFilter($filter);			
	}
	
	function afterInsert(&$dataEntity) {
		$quiz = $dataEntity->getRelation("QuizId", false);
		$quiz->set("NumberOfQuestions", $quiz->get("NumberOfQuestions") + 1);
		$quiz->save();	
	}
	
	function afterDelete(&$dataEntity) {
		$quiz = $dataEntity->getRelation("QuizId", false);
		$quiz->set("NumberOfQuestions", $quiz->get("NumberOfQuestions") - 1);
		$quiz->save();
	}
	
	function getDataEntity() {
		return new QuizQuestionDataEntity($this);
	}
}

class QuizQuestionDataEntity extends DataEntity {
	function getCorrectAnswer() {
		$quizAnswerControl = BaseFactory::getQuizAnswerControl();
		$quizAnswerControl->retrieveCorrectAnswerForQuestion($this);
		if ($quizAnswer = $quizAnswerControl->getNext()) {
			return $quizAnswer;
		} else {
			return false;
		} 
	}
}