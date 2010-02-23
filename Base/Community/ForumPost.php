<?php
/**
 * @package Base
 * @subpackage Community
 * @copyright Clock Limited 2007
 * @version 3.0 - $Revision$ - $Date$
 */

/**
 * Include Data.php so that DataControl can be extended.
 * Include DataEntity.php so that DataControl can be extended.
 */

require_once("Atrox/3.0/Core/Data/Data.php");
require_once("Atrox/3.0/Core/Data/DataEntity.php");  

/**
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2007
 * @version 3.0 - $Revision$ - $Date$
 * @package Base
 * @subpackage Community
 */
class ForumPostControl extends DataControl {
	var $table = "ForumPost";
	var $key = "Id";
	var $sequence = "ForumPost_Id_seq";
	var $defaultOrder = "Id";
	var $fullTextIndex = "TextIndex";

	function init() {

		$this->fieldMeta["Id"] = new FieldMeta(
			"Id", "", FM_TYPE_INTEGER, null, FM_STORE_NEVER, false);

		$this->fieldMeta["ForumId"] = new FieldMeta(
			"Forum Id", "", FM_TYPE_RELATION, null, FM_STORE_ALWAYS, false);

		$this->fieldMeta["ForumId"]->setRelationControl(BaseFactory::getForumControl());
		$this->fieldMeta["ForumId"]->setValidation(new ForumValidation());

		$this->fieldMeta["ForumThreadId"] = new FieldMeta(
			"Forum Thread Id", "", FM_TYPE_RELATION, null, FM_STORE_ALWAYS, false);
		$this->fieldMeta["ForumThreadId"]->setValidation(new ForumThreadValidation());

		$this->fieldMeta["ForumThreadId"]->setRelationControl(BaseFactory::getForumThreadControl());

		$this->fieldMeta["Subject"] = new FieldMeta(
			"Subject", "", FM_TYPE_HTML, 100, FM_STORE_ALWAYS, false);

		$subjectProfanityValidation = BaseFactory::getProfanityValidation("Subject");
		$subjectProfanityValidation->setCallBack(new WorryWordCallBack);

		$this->fieldMeta["Subject"]->setFormatter(CoreFactory::getBodyTextFormatter());
		$this->fieldMeta["Subject"]->setValidation($subjectProfanityValidation);

		$this->fieldMeta["Body"] = new FieldMeta(
			"Body", "", FM_TYPE_HTML, 5000, FM_STORE_ALWAYS, false);

		$bodyProfanityValidation = BaseFactory::getProfanityValidation("Body");
		$bodyProfanityValidation->setCallBack(new WorryWordCallBack);

		$this->fieldMeta["Body"]->setFormatter(CoreFactory::getBodyTextFormatter());
		$this->fieldMeta["Body"]->setValidation($bodyProfanityValidation);

		$this->fieldMeta["AuthorId"] = new FieldMeta(
			"Author Id", "", FM_TYPE_RELATION, null, FM_STORE_ADD, false);

		$this->fieldMeta["AuthorId"]->setRelationControl(BaseFactory::getMemberControl());
		$this->fieldMeta["AuthorId"]->setAutoData(CoreFactory::getCurrentMember());

		$this->fieldMeta["Anonymous"] = new FieldMeta(
			"Anonymous", "", FM_TYPE_BOOLEAN, 1, FM_STORE_ALWAYS, false);

		$this->fieldMeta["DateCreated"] = new FieldMeta(
			"DateCreated", "", FM_TYPE_DATE, null, FM_STORE_NEVER, false);

		$this->fieldMeta["DateCreated"]->setFormatter(CoreFactory::getDateTimeFieldFormatter());

		$this->fieldMeta["CanEdit"] = new FieldMeta(
			"Can Edit", "", FM_TYPE_BOOLEAN, 1, FM_STORE_NEVER, false);

		$this->fieldMeta["ReplyToId"] = new FieldMeta(
			"Reply To Id", "", FM_TYPE_INTEGER, null, FM_STORE_ADD, true);

		$this->fieldMeta["ReplyToId"]->setRelationControl($this);

		$ipAddressControl = CoreFactory::getIpAddress();

		$this->fieldMeta["IpAddress"] = new FieldMeta(
			"Ip Address", $ipAddressControl->get(), FM_TYPE_STRING, 15, FM_STORE_ALWAYS, false);
	}

	function addForumPost(&$forumPost) {
		if ($forumPost->save()) {
			if ($forumPost->get("ReplyToId") == "") {
				$this->updateField($forumPost, "ReplyToId", $forumPost->get("Id"));
			}
		} else {
			return false;
		}
		return true;
	}

	function retrieveThread($forumThreadId, $order = "DateCreated", $desc = true) {
		$filter = CoreFactory::getFilter();
		$filter->addConditional($this->table, "ForumThreadId", $forumThreadId);
		if ($this->isField($order)) {
		$filter->addOrder($order, $desc);
		} else if ($order == "ReverseDateCreated") {
			$filter->addOrder("DateCreated", true);					
		}
		$this->setFilter($filter);
		$this->retrieveAll();
	}

	function afterInsert(&$dataEntity) {
		$member = $dataEntity->getRelation("AuthorId");
		$memberControl = $member->getControl();
		$memberControl->incrementField($member, "Posts");
		
		$this->afterUpdate($dataEntity);
	}

	function afterUpdate(&$dataEntity) {
		$cacheControl = &CoreFactory::getCacheControl();
		$cacheControl->deleteWebPageCache("ForumThread" . $dataEntity->get("ForumThreadId"));
		$cacheControl->deleteWebPageCache("Forum" . $dataEntity->get("ForumId"));
		$cacheControl->deleteWebPageCache("ForumHome");
		$cacheControl->deleteWebPageCache("Forum-Summary");			
	}

	function afterDelete(&$dataEntity) {
		$member = $dataEntity->getRelation("AuthorId");
		$memberControl = $member->getControl();
		$memberControl->incrementField($member, "Posts", -1);
		$this->afterUpdate($dataEntity);
	}
	
	function getDataEntity() {
		return new ForumPostEntity($this);
	}		
}

class ForumPostEntity extends DataEntity {
	function isMemberAllowedToModify() {
		$application = &CoreFactory::getApplication();
		if (($this->get("CanEdit") == "t") &&	($this->get("AuthorId") == $application->securityControl->getMemberId())) {
			return true;
		} else {
			$forum = $this->getRelation("ForumId");
			return $application->securityControl->isAllowed($forum->getRelationValue("ModifySecurityResourceId", "Name"), false);
		}
	}
}

// TODO: Make some form of super class and proper callback mech
// TODO: Make Proper template based e-mail
/**
 *
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2007
 * @version 3.0 - $Revision$ - $Date$
 * @package Base
 * @subpackage Community
 */
class WorryWordCallBack {
	function run($worryWords, $dataEntity) {
		mail(SITE_ALERT_EMAIL, SITE_NAME . " - Worry Word Detected",
		"Worry Words Detected: " . implode(",", $worryWords) . "\n" .
		"Forum Subject: " . $dataEntity->getFormatted("Subject") . "\n" .
		"Description: " . $dataEntity->getFormatted("Body") . "\n" .
		"Author: " . $dataEntity->getRelationValue("AuthorId", "Alias") . "\n" .
		"Link: " . SITE_ADDRESS . SITE_FORUM_PATH . "/viewthread.php?Id=" .
			$dataEntity->get("ForumThreadId") . "\n", "From: " . SITE_GENERAL_EMAIL . "\n");
	}
}

/**
 *
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2007
 * @version 3.0 - $Revision$ - $Date$
 * @package Base
 * @subpackage Community
 */
class ForumValidation extends CustomValidation {
	function validate(&$value, $fieldMeta) {
		// First validate the thread and make sure it can exist
		if (!$forum = $this->dataEntity->getRelation("ForumId")) {
			$this->dataEntity->control->errorControl->addError("Invalid Forum");
			return false;
		}
	}
}

/**
 *
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2007
 * @version 3.0 - $Revision$ - $Date$
 * @package Base
 * @subpackage Community
 */
class ForumThreadValidation extends CustomValidation {
	function validate(&$value, $fieldMeta) {
		if (!$forumThread = $this->dataEntity->getRelation("ForumThreadId")) {
			$this->dataEntity->control->errorControl->addError("Invalid Thread :" . $this->dataEntity->get("ForumThreadId"));
			return false;
		}
		if ($forumThread->get("ForumId") != $this->dataEntity->get("ForumId")) {
			$this->dataEntity->control->errorControl->addError("Thread :" . $this->dataEntity->get("ForumThreadId") .
				" does not belong to Forum : " . $forumThread->get("ForumId"));
			return false;
		}
		return true;
	}
}