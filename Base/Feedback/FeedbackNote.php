<?php
/**
 * @package Base
 * @subpackage Feedback
 */

/**
 * Include Data.php so that DataControl can be extended.
 */
require_once("Atrox/Core/Data/Data.php");

/**
 *
 * @author Robert Arnold (Clock Ltd) {@link mailto:robert.arnold@clock.co.uk robert.arnold@clock.co.uk }
 * @copyright Clock Limited 2007
 * @version 3.0 - $Revision$ - $Date$
 * @package Base
 * @subpackage Feedback
 */
class FeedbackNoteControl extends DataControl {
	var $table = "FeedbackNote";
	var $key = "Id";
	var $sequence = "FeedbackNote_Id_seq";
	var $defaultOrder = "Id";

	var $searchFields = array("Id", "FeedbackId");

	function init() {

		$this->fieldMeta["Id"] = new FieldMeta(
			"Id", "", FM_TYPE_INTEGER, null, FM_STORE_NEVER, false);

		$this->fieldMeta["FeedbackId"] = new FieldMeta(
			"Feedback Id", "", FM_TYPE_RELATION, null, FM_STORE_ALWAYS, false);

		$this->fieldMeta["FeedbackId"]->setRelationControl(BaseFactory::getFeedbackControl());

		$this->fieldMeta["DateCreated"] = new FieldMeta(
			"DateCreated", "", FM_TYPE_DATE, null, FM_STORE_NEVER, false);

		$this->fieldMeta["DateCreated"]->setFormatter(CoreFactory::getDateTimeFieldFormatter());

		$this->fieldMeta["Body"] = new FieldMeta(
			"Body", "", FM_TYPE_STRING, 5000, FM_STORE_ALWAYS, false);

		$this->fieldMeta["Body"]->setFormatter(CoreFactory::getBodyTextFormatter());

		$this->fieldMeta["MemberId"] = new FieldMeta(
			"Member Id", "", FM_TYPE_RELATION, null, FM_STORE_ALWAYS, true);

		$this->fieldMeta["MemberId"]->setRelationControl(BaseFactory::getMemberControl());
		$this->fieldMeta["MemberId"]->setAutoData(CoreFactory::getCurrentMember());

		$this->fieldMeta["Status"] = new FieldMeta(
			"Status", "", FM_TYPE_INTEGER, null, FM_STORE_ALWAYS, false);
		
		$feedBackControl = BaseFactory::getFeedbackControl();
		
		$this->fieldMeta["Status"]->setFormatter(
			CoreFactory::getArrayRelationFormatter($feedBackControl->status, FBK_NEW));
	}

	function retrieveForFeedback($feedbackId) {
		$filter = $this->getFilter();
		if ($filter == null) {
			$filter = CoreFactory::getFilter();
		}
		$filter->addOrder("DateCreated");
		$filter->addConditional($this->table, "FeedbackId", $feedbackId);
		$this->setFilter($filter);
		$this->retrieveAll();
	}
}
