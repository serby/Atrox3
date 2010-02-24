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
class QuizEntryControl extends DataControl {
	var $table = "QuizEntry";
	var $key = "Id";
	var $sequence = "QuizEntry_Id_seq";
	var $defaultOrder = "QuizId";		
	var $searchFields = array("Id", "MemberId");
	
	function init() {
		$this->fieldMeta["Id"] = new FieldMeta(
			"Id", "", FM_TYPE_INTEGER, null, FM_STORE_NEVER, false);
		
		$this->fieldMeta["QuizId"] = new FieldMeta(
			"Quiz Id", "", FM_TYPE_RELATION, null, FM_STORE_ALWAYS, false);

		$this->fieldMeta["QuizId"]->setRelationControl(BaseFactory::getQuizControl());
		
		$this->fieldMeta["MemberId"] = new FieldMeta(
			"Quiz Id", "", FM_TYPE_RELATION, null, FM_STORE_ALWAYS, false);

		$this->fieldMeta["MemberId"]->setRelationControl(BaseFactory::getMemberControl());
		
		$this->fieldMeta["Score"] = new FieldMeta(
			"Score", 0, FM_TYPE_INTEGER, null, FM_STORE_ALWAYS, false);
		
		$this->fieldMeta["DateCreated"] = new FieldMeta(
			"Date Created", "", FM_TYPE_DATE, 1, FM_STORE_NEVER, false);

		$this->fieldMeta["DateCreated"]->setFormatter(CoreFactory::getDateTimeFieldFormatter());
	}
	
	function retrieveForQuiz($quiz) {
		$filter = CoreFactory::getFilter();
		$filter->addConditional($this->table, "QuizId", $quiz->get("Id"));
		$this->setFilter($filter);			
	}
	
	function retrieveEntryForMember($quiz) {
		$this->retrieveForQuiz($quiz);
		$filter = $this->getFilter();
		$filter->addConditional($this->table, "MemberId", $this->application->securityControl->getMemberId());
		$filter->addOrder("DateCreated", true);
		$this->setFilter($filter);
		return $this->getNext();
	}
	
	function submitEntry($quiz, $answers) {
		$count = 1;
		
		//Check Answers and create errors
		foreach($answers as $questionId => $answerId) {
			if (!$answerId) {
				$this->application->errorControl->addError("Please select an answer for question {$count}.");
			}
			$count++;
		}
		
		//Check that the member hasnt entered the quiz already
		$filter = CoreFactory::getFilter();
		$filter->addConditional($this->table, "QuizId", $quiz->get("Id"));
		$filter->addConditional($this->table, "MemberId", $this->application->securityControl->getMemberId());
		$this->setFilter($filter);
		
		if ($this->getNumRows() > 0) {
			$this->application->errorControl->addError("You can only enter the quiz once. Please come back when we have a new quiz.");
		}
		
		//If a question hasnt been answered then return.s
		if ($this->application->errorControl->hasErrors()) {
			return false;
		}
		
		$quizEntry = $this->makeNew();
		$quizEntry->set("QuizId", $quiz->get("Id"));
		$quizEntry->set("MemberId", $this->application->securityControl->getMemberId());
		if ($quizEntry->save()) {
			$quizEntryAnswerControl = BaseFactory::getQuizEntryAnswerControl();
			foreach($answers as $questionId => $answerId) {
				$quizEntryAnswer = $quizEntryAnswerControl->makeNew();
				$quizEntryAnswer->set("QuizEntryId", $quizEntry->get("Id"));
				$quizEntryAnswer->set("QuizAnswerId", $answerId);
				$quizEntryAnswer->set("QuizQuestionId", $questionId);
				$quizEntryAnswer->save();
			}
			return $quizEntry;
		} else {
			return false;
		}
	}
}