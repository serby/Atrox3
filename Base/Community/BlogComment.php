<?php
/**
 * @package Base
 * @subpackage Community
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 */

/**
 * Include Data.php so that DataControl can be extended.
 */
require_once("Atrox/Core/Data/Data.php");


/**
 *
 * @author Adam Forster (Clock Ltd) {@link mailto:adam.forster@clock.co.uk adam.forster@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Base
 * @subpackage Community
 */
class BlogCommentControl extends DataControl {
	var $table = "BlogComment";
	var $key = "Id";
	var $sequence = "BlogComment_Id_seq";
	var $defaultOrder = "Id";

	function init() {

		$this->fieldMeta["Id"] = new FieldMeta(
			"Id", "", FM_TYPE_INTEGER, null, FM_STORE_NEVER, false);

		$this->fieldMeta["BlogEntryId"] = new FieldMeta(
			"Blog Thread Id", "", FM_TYPE_RELATION, null, FM_STORE_ALWAYS, false);
		$this->fieldMeta["BlogEntryId"]->setValidation(new BlogEntryValidation());

		$this->fieldMeta["BlogEntryId"]->setRelationControl(BaseFactory::getBlogEntryControl());

		$this->fieldMeta["Subject"] = new FieldMeta(
			"Subject", "", FM_TYPE_HTML, 100, FM_STORE_ALWAYS, false);

		$subjectProfanityValidation = BaseFactory::getProfanityValidation("Subject");
		$subjectProfanityValidation->setCallBack(new BlogCommentWorryWordCallBack);

		$this->fieldMeta["Subject"]->setFormatter(CoreFactory::getBodyTextFormatter());
		$this->fieldMeta["Subject"]->setValidation($subjectProfanityValidation);

		$this->fieldMeta["Body"] = new FieldMeta(
			"Body", "", FM_TYPE_HTML, 5000, FM_STORE_ALWAYS, false);

		$bodyProfanityValidation = BaseFactory::getProfanityValidation("Body");
		$bodyProfanityValidation->setCallBack(new BlogCommentWorryWordCallBack);

		$this->fieldMeta["Body"]->setFormatter(CoreFactory::getBodyTextFormatter());
		$this->fieldMeta["Body"]->setValidation($bodyProfanityValidation);

		$this->fieldMeta["AuthorId"] = new FieldMeta(
			"Author Id", "", FM_TYPE_RELATION, null, FM_STORE_ALWAYS, false);

		$this->fieldMeta["AuthorId"]->setRelationControl(BaseFactory::getMemberControl());
		$this->fieldMeta["AuthorId"]->setAutoData(CoreFactory::getCurrentMember());

		$this->fieldMeta["Anonymous"] = new FieldMeta(
			"Anonymous", "", FM_TYPE_BOOLEAN, 1, FM_STORE_ALWAYS, false);

		$this->fieldMeta["DateCreated"] = new FieldMeta(
			"DateCreated", "", FM_TYPE_DATE, null, FM_STORE_NEVER, true);

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

	function addBlogComment(&$blogEntry) {
		if ($blogEntry->save()) {
			if ($blogEntry->get("ReplyToId") == "") {
				$this->updateField($blogEntry, "ReplyToId", $blogEntry->get("Id"));
			}
			return true;
		}
		return false;
	}

	function retrieveForBlogEntry(&$blogEntry) {
		$filter = $this->getFilter();
		if ($filter == null) {
			$filter = CoreFactory::getFilter();
		}
		$filter->addConditional($this->table, "BlogEntryId", $blogEntry->get("Id"));
		$this->setFilter($filter);
	}

	function afterInsert(&$dataEntity) {
		$blogEntry = $dataEntity->getRelation("BlogEntryId");
		$blogEntryControl = $blogEntry->getControl();
		$blogEntryControl->incrementField($blogEntry, "Comments");
		$this->afterUpdate($dataEntity);
	}

	function afterDelete(&$dataEntity) {
		$blogEntry = $dataEntity->getRelation("BlogEntryId");
		$blogEntryControl = $blogEntry->getControl();
		$blogEntryControl->incrementField($blogEntry, "Comments", -1);
		$this->afterUpdate($dataEntity);
	}
	
	function getMemberBlogComments($member) {
		$filter = $this->getFilter();
		if ($filter == null) {
			$filter = CoreFactory::getFilter();
		}
		$filter->addConditional($this->table, "AuthorId", $member->get("Id"));
		$this->setFilter($filter);
		$this->retrieveAll();
		return $this->getNumRows();
	}
}

// TODO: Make some form of super class and proper callback mech
// TODO: Make Proper template based e-mail
/**
 *
 * @author Adam Forster (Clock Ltd) {@link mailto:adam.forster@clock.co.uk adam.forster@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Base
 * @subpackage Community
 */
class BlogCommentWorryWordCallBack {
	function run($worryWords, $dataEntity) {
		mail(SITE_ALERT_EMAIL, SITE_NAME . " - Worry Word Detected",
		"Worry Words Detected: " . implode(",", $worryWords) . "\n" .
		"Blog Subject: " . $dataEntity->getFormatted("Subject") . "\n" .
		"Description: " . $dataEntity->getFormatted("Body") . "\n" .
		"Author: " . $dataEntity->getRelationValue("AuthorId", "Alias") . "\n" .
		"Link: " . SITE_ADDRESS . SITE_FORUM_PATH . "/view-thread.php?Id=" .
			$dataEntity->get("BlogEntryId") . "\n", "From: " . SITE_GENERAL_EMAIL . "\n");
	}
}

/**
 *
 * @author Adam Forster (Clock Ltd) {@link mailto:adam.forster@clock.co.uk adam.forster@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Base
 * @subpackage Community
 */
class BlogEntryValidation extends CustomValidation {
	function validate(&$value, $fieldMeta) {
		// First validate the blog entry and make sure it can exist
		if (!$forum = $this->dataEntity->getRelation("BlogEntryId")) {
			$this->dataEntity->control->errorControl->addError("Invalid Blog Entry");
			return false;
		}
	}
}

/**
 *
 * @author Adam Forster (Clock Ltd) {@link mailto:adam.forster@clock.co.uk adam.forster@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Base
 * @subpackage Community
 */
class BlogCommentValidation extends CustomValidation {
	function validate(&$value, $fieldMeta) {
		if (!$forumThread = $this->dataEntity->getRelation("BlogEntryId")) {
			$this->dataEntity->control->errorControl->addError("Invalid Thread :" . $this->dataEntity->get("BlogEntryId"));
			return false;
		}
		if ($forumThread->get("BlogId") != $this->dataEntity->get("BlogId")) {
			$this->dataEntity->control->errorControl->addError("Thread :" . $this->dataEntity->get("BlogEntryId") .
				" does not belong to Blog : " . $forumThread->get("BlogId"));
			return false;
		}
		return true;
	}
}