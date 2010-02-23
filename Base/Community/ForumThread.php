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
require_once("Atrox/Core/Data/Data.php");
require_once("Atrox/Core/Data/DataEntity.php");  


define("FT_TYPE_NORMAL", 0);
define("FT_TYPE_LOCKED", -1);
define("FT_TYPE_STICKEY", 2);
define("FT_TYPE_ANNOUNCEMENT", 3);

/**
 *
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2007
 * @version 3.0 - $Revision$ - $Date$
 * @package Base
 * @subpackage Community
 */
class ForumThreadControl extends DataControl {
	var $table = "ForumThread";
	var $key = "Id";
	var $sequence = "ForumThread_Id_seq";
	var $defaultOrder = "TopPriority";

	var $types = array(
			FT_TYPE_NORMAL => "Normal",
			FT_TYPE_LOCKED => "Locked",
			FT_TYPE_STICKEY => "Stickey",
			FT_TYPE_ANNOUNCEMENT => "Announcement");

	function init() {

		$this->fieldMeta["Id"] = new FieldMeta(
			"Id", "", FM_TYPE_INTEGER, null, FM_STORE_NEVER, false);

		$this->fieldMeta["ForumId"] = new FieldMeta(
			"Forum Id", "", FM_TYPE_RELATION, null, FM_STORE_ALWAYS, false);

		$this->fieldMeta["ForumId"]->setRelationControl(BaseFactory::getForumControl());

		$this->fieldMeta["Subject"] = new FieldMeta(
			"Subject", "", FM_TYPE_HTML, 100, FM_STORE_ALWAYS, false);

		$this->fieldMeta["Subject"]->setFormatter(CoreFactory::getBodyTextFormatter());
		$this->fieldMeta["Subject"]->setValidation(BaseFactory::getProfanityValidation("Subject"));

		$this->fieldMeta["Body"] = new FieldMeta(
			"Body", "", FM_TYPE_HTML, 5000, FM_STORE_ALWAYS, false);

		$this->fieldMeta["Body"]->setFormatter(CoreFactory::getBodyTextFormatter());
		$this->fieldMeta["Body"]->setValidation(BaseFactory::getProfanityValidation("Body"));

		$this->fieldMeta["AuthorId"] = new FieldMeta(
			"Author", "", FM_TYPE_RELATION, null, FM_STORE_ALWAYS, false);

		$this->fieldMeta["AuthorId"]->setRelationControl(BaseFactory::getMemberControl());
		$this->fieldMeta["AuthorId"]->setAutoData(CoreFactory::getCurrentMember());

		$this->fieldMeta["ImageId"] = new FieldMeta(
			"ImageId", "", FM_TYPE_BINARY, null, FM_STORE_NEVER, false);

		$this->fieldMeta["Anonymous"] = new FieldMeta(
			"Anonymous", "", FM_TYPE_BOOLEAN, null, FM_STORE_ALWAYS, false);

		$this->fieldMeta["Replies"] = new FieldMeta(
			"Replies", "", FM_TYPE_INTEGER, null, FM_STORE_NEVER, false);

		$this->fieldMeta["DateCreated"] = new FieldMeta(
			"DateCreated", "", FM_TYPE_STRING, null, FM_STORE_NEVER, false);

		$this->fieldMeta["DateCreated"]->setFormatter(CoreFactory::getDateTimeFieldFormatter());

		$this->fieldMeta["LastPost"] = new FieldMeta(
			"LastPost", "", FM_TYPE_DATE, null, FM_STORE_NEVER, false);

		$this->fieldMeta["LastPost"]->setFormatter(CoreFactory::getDateTimeFieldFormatter());

		$this->fieldMeta["LastPostId"] = new FieldMeta(
			"LastPostId", "", FM_TYPE_RELATION, null, FM_STORE_NEVER, false);

		$this->fieldMeta["LastPostId"]->setRelationControl(BaseFactory::getForumPostControl());

		$this->fieldMeta["LastPostById"] = new FieldMeta(
			"LastPostById", "", FM_TYPE_RELATION, null, FM_STORE_NEVER, false);

		$this->fieldMeta["LastPostById"]->setRelationControl(BaseFactory::getMemberControl());

		$this->fieldMeta["TopPriority"] = new FieldMeta(
			"Top Priority", "", FM_TYPE_INTEGER, null, FM_STORE_ADD, false);

		$this->fieldMeta["Type"] = new FieldMeta(
			"Type", "", FM_TYPE_INTEGER, null, FM_STORE_ADD, false);

		$this->fieldMeta["Type"]->setFormatter(CoreFactory::getArrayRelationFormatter($this->types, FT_TYPE_NORMAL));

		$this->fieldMeta["AgeRestriction"] = new FieldMeta(
			"Age Restriction", "", FM_TYPE_INTEGER, null, FM_STORE_NEVER, false);

		$this->fieldMeta["Views"] = new FieldMeta(
			"Views", "", FM_TYPE_INTEGER, null, FM_STORE_NEVER, false);

	}

	function addForumThread(&$forumThread) {

		// Non admin will need the type setting.
		if ($forumThread->get("Type") == "") {
			$forumThread->set("Type", FT_TYPE_NORMAL); 
		}

		// Security check to ensure user can add a thread
		$forum = $forumThread->getRelation("ForumId");
		$allowModify = $forum->isMemberAllowedToModify();
		if ((!$forum->isMemberAllowedToAdd()) && (!$allowModify)) {
			$this->errorControl->addError("You do not have succient privlages to add a thread to this forum");
		}

		// Validate the thread and make sure it exists
		if (!$forum = $forumThread->getRelation("ForumId")) {
			$this->errorControl->addError("Invalid Forum");
			return false;
		}

		// Make sure the AgeRestrictions doesn't conflict with the forums Age Restriction
		if ((int)$forum->get("AgeRestriction") > (int)$forumThread->get("AgeRestriction")) {
			$this->errorControl->addError("Age Restriction can not be less than the restriction on the forum");
			return false;
		}

		// Security check to ensure they are mods of this forum
		if ((!$allowModify) && ($forumThread->get("Type") != FT_TYPE_NORMAL)) {
			$this->errorControl->addError("You do not have succient privlages to " . 
				"make a thread of type '" . $this->types[$forumThread->get("Type")] . "'");
			return false;
		}
		
		// If Age Restriction isn't set then add 
		if ($forum->get("AgeRestriction") == null) {
			$forumThread->set("AgeRestriction", $forum->get("AgeRestriction"));
		}

		// Make Sticky and Announcement thread stay at the top
		switch($forumThread->get("Type")) {
			case FT_TYPE_ANNOUNCEMENT:
			case FT_TYPE_STICKEY:
				$forumThread->set("TopPriority", $forumThread->get("Type"));
				break;
			default:
				$forumThread->set("TopPriority", 0);
		}

		if (!$forumThread->save()) {
			return false;
		}

		$forumPostControl = BaseFactory::getForumPostControl();
		$forumPost = $forumPostControl->makeNew();

		$forumPost->set("ForumThreadId", $forumThread->get("Id"));
		$forumPost->set("ForumId", $forumThread->get("ForumId"));
		$forumPost->set("Subject", $forumThread->get("Subject"));
		$forumPost->set("Body", $forumThread->get("Body"));
		$forumPost->set("AuthorId", $forumThread->get("AuthorId"));
		$forumPost->set("Anonymous", $forumThread->get("Anonymous"));

		return $forumPost->save();
	}

	function viewed($forumThread) {
		$this->incrementField($forumThread, "Views");
	}

	function retrieveForum($forumId, $order = null, $desc=false) {
		$filter = $this->getFilter();
		if ($filter == null) {
			$filter = CoreFactory::getFilter();
		}
		$filter->addConditional($this->table, "ForumId", $forumId);
		$filter->addOrder("TopPriority", true);
		if ($this->isField($order)) {
			$filter->addOrder($order, $desc);
		}
		$this->setFilter($filter);
		$this->retrieveAll();
	}

	function getTypes() {
		return $this->types;
	}

	function setType(&$dataEntity, $type = FT_TYPE_NORMAL) {
		if (!isset($this->types[$type])) {
			$this->errorControl->addError("Not a supported Forum Thread Type");
		} else {
			$this->updateField($dataEntity, "Type", $type);
			switch($type) {
				case FT_TYPE_ANNOUNCEMENT:
				case FT_TYPE_STICKEY:
				$this->updateField($dataEntity, "TopPriority", $type);
					break;
				default:
					$this->updateField($dataEntity, "TopPriority", 0);
			}
		}			
	}


	function getNextItem(&$dataEntity, $orderField) {
		$filter = $this->getFilter();
		if ($filter == null) {
			$filter = CoreFactory::getFilter();
		}
		$filter->addOrder($orderField, false);
		$filter->addConditional($this->table, "ForumId", $dataEntity->get("ForumId"));
		$this->setFilter($filter);
		return parent::getNextItem($dataEntity, $orderField);
	}

	function getPreviousItem(&$dataEntity, $orderField) {
		$filter = $this->getFilter();
		if ($filter == null) {
			$filter = CoreFactory::getFilter();
		}
		$filter->addOrder($orderField, true);
		$filter->addConditional($this->table, "ForumId", $dataEntity->get("ForumId"));
		$this->setFilter($filter);
		return parent::getPreviousItem($dataEntity, $orderField);
	}

	function retrieveLastThreads($memberId, $amount){
		$filter = $this->getFilter();
      if ($filter == null) {
        $filter = CoreFactory::getFilter();
      }
      $filter->addConditional($this->table, "AuthorId", $memberId);
		$filter->addLimit($amount);
      $this->setFilter($filter);
      return $this->retrieveAll();
	}
	
	function retrieveLatestThreads($limit){
		$filter = $this->getFilter();
      if ($filter == null) {
        $filter = CoreFactory::getFilter();
      }   
		$filter->addOrder("DateCreated", true);   
		$filter->addLimit($limit);
      $this->setFilter($filter);
      return $this->retrieveAll();
	}
	
	function getDataEntity() {
		return new ForumThreadEntity($this);
	}		
}

class ForumThreadEntity extends DataEntity {
	function isMemberAllowedToAdd() {
		$application = &CoreFactory::getApplication();
		if (($this->get("Type") == FT_TYPE_LOCKED) || ($this->get("Type") == FT_TYPE_ANNOUNCEMENT)) {
			$forum = $this->getRelation("ForumId");
			return $forum->isMemberAllowedToModify();
		} else {
			return true;
		}
	}
}