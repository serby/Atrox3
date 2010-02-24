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
class QuizEntryAnswerControl extends DataControl {
	var $table = "QuizEntryAnswer";
	var $key = "Id";
	var $sequence = "QuizEntryAnswer_Id_seq";
	var $defaultOrder = "QuizEntryId";		
	var $searchFields = array("Id");
	
	function init() {
		$this->fieldMeta["Id"] = new FieldMeta(
			"Id", "", FM_TYPE_INTEGER, null, FM_STORE_NEVER, false);
		
		$this->fieldMeta["QuizEntryId"] = new FieldMeta(
			"Quiz Entry Id", "", FM_TYPE_RELATION, null, FM_STORE_ALWAYS, false);

		$this->fieldMeta["QuizEntryId"]->setRelationControl(BaseFactory::getQuizEntryControl());
		
		$this->fieldMeta["QuizAnswerId"] = new FieldMeta(
			"Quiz Answer Id", "", FM_TYPE_RELATION, null, FM_STORE_ALWAYS, false);

		$this->fieldMeta["QuizAnswerId"]->setRelationControl(BaseFactory::getQuizAnswerControl());
		
		$this->fieldMeta["QuizQuestionId"] = new FieldMeta(
			"Quiz Question Id", "", FM_TYPE_RELATION, null, FM_STORE_ALWAYS, false);

		$this->fieldMeta["QuizQuestionId"]->setRelationControl(BaseFactory::getQuizQuestionControl());
	}
	
	function retrieveForQuizEntry($quizEntry) {
		$filter = CoreFactory::getFilter();
		$filter->addConditional($this->table, "QuizEntryId", $quizEntry->get("Id"));
		$this->setFilter($filter);			
	}
	
	function afterInsert(&$dataEntity) {
		$quizAnswer = $dataEntity->getRelation("QuizAnswerId");
		if ($quizAnswer->getFormatted("Correct") == "Yes") {
			$quizEntry = $dataEntity->getRelation("QuizEntryId", false);
			$quizEntry->set("Score", $quizEntry->get("Score") + 1);
			$quizEntry->save();
		}
	}
}