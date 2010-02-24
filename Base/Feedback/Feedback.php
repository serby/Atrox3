<?php
/**
 * @package Base
 * @subpackage Feedback
 */

/**
 * Include Data.php so that DataControl can be extended.
 */
require_once("Atrox/Core/Data/Data.php");

define("FBK_NEW", 1);
define("FBK_READ", 2);
define("FBK_RESPONDED", 3);
define("FBK_COMPLAINT", 4);
define("FBK_COMPLAINT_RESPONDED", 5);
define("FBK_DISMISSED", 6);
define("FBK_RESPONSE", 7);
/**
 * Generic feedback references. Non-generic types may be defined by extending
 * this control.
 */
define("FBK_REF_OTHER", -1);
define("FBK_REF_SITE", 1);
define("FBK_REF_CUST_SERVICE", 2);

/**
 *
 * @author Robert Arnold (Clock Ltd) {@link mailto:robert.arnold@clock.co.uk robert.arnold@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Base
 * @subpackage Feedback
 */
class FeedbackControl extends DataControl {
	var $table = "Feedback";
	var $key = "Id";
	var $sequence = "Feedback_Id_seq";
	var $defaultOrder = "Id";
	var $hasNotes = false;

	var $status = array(
		FBK_NEW => "New unread feedback",
		FBK_READ => "Read unresponded",
		FBK_RESPONDED => "Responded",
		FBK_COMPLAINT => "Complaint",
		FBK_COMPLAINT_RESPONDED => "Complaint Responded",
		FBK_DISMISSED => "Dismissed",
		FBK_RESPONSE => "Response",);

	var $searchFields = array("Id", "FullName", "Subject", "EmailAddress");

	function init() {

		$this->fieldMeta["Id"] = new FieldMeta(
			"Id", "", FM_TYPE_INTEGER, null, FM_STORE_NEVER, false);

		$this->fieldMeta["FullName"] = new FieldMeta(
			"Full Name", "", FM_TYPE_STRING, 255, FM_STORE_ALWAYS, false);

		$this->fieldMeta["EmailAddress"] = new FieldMeta(
			"E-mail Address", "", FM_TYPE_EMAILADDRESS, 255, FM_STORE_ALWAYS, false);

		$this->fieldMeta["Reference"] = new FieldMeta(
			"Reference", "", FM_TYPE_STRING, 255, FM_STORE_ALWAYS, true);

		$this->fieldMeta["Subject"] = new FieldMeta(
			"Subject", "", FM_TYPE_STRING, 255, FM_STORE_ALWAYS, false);

		$this->fieldMeta["Body"] = new FieldMeta(
			"Body", "", FM_TYPE_HTML, 5000, FM_STORE_ALWAYS, false);

		$this->fieldMeta["IpAddress"] = new FieldMeta(
			"Ip Address", "", FM_TYPE_STRING, 15, FM_STORE_ALWAYS, false);

		$this->fieldMeta["IpAddress"]->setAutoData(CoreFactory::getIpAddress());

		$this->fieldMeta["DateCreated"] = new FieldMeta(
			"DateCreated", "", FM_TYPE_DATE, null, FM_STORE_NEVER, false);

		$this->fieldMeta["DateCreated"]->setFormatter(
			CoreFactory::getDateTimeFieldFormatter());

		$this->fieldMeta["Parent"] = new FieldMeta(
			"Parent", "", FM_TYPE_BOOLEAN, 1, FM_STORE_ALWAYS, true);
			
		$this->fieldMeta["Status"] = new FieldMeta(
			"Status", FBK_NEW, FM_TYPE_INTEGER, null, FM_STORE_ALWAYS, false);

		$this->fieldMeta["Status"]->setFormatter(
			CoreFactory::getArrayRelationFormatter($this->status, FBK_NEW));
	}

	function delete($ids) {
		$idsArray = explode(",", $ids);
		
		$feedbackChildControl = $this;

		foreach ($idsArray as $id) {
			if ($feedback = $this->item($id)) {
				$reference = $feedback->get("Reference");
				$feedbackChildFilter = CoreFactory::getFilter();
				$feedbackChildFilter->addConditional($feedbackChildControl->table, "Reference", $reference);
				$feedbackChildControl->clearFilter();
				$feedbackChildControl->setFilter($feedbackChildFilter);
				
				$feedbackNoteControl = BaseFactory::getFeedbackNoteControl();
				if ($this->hasNotes) {
					while ($feedbackChild = $feedbackChildControl->getNext()) {
						$feedbackNoteControl->delete($feedbackChild->get("Id"), "FeedbackId");
						parent::delete($feedbackChild->get("Id"));
					}
				}
				$feedbackNoteControl->delete($id, "FeedbackId");
			}
			
			parent::delete($ids);
		}
	}
	
	function getStatusArray(){
		return $this->status;
	}
}
