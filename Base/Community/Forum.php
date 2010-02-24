<?php
/**
 * @package Base
 * @subpackage Community
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 */

/**
 * Include Data.php so that DataControl can be extended.
 * Include DataEntity.php so that DataControl can be extended.
 */
require_once("Atrox/Core/Data/Data.php");
require_once("Atrox/Core/Data/DataEntity.php");


/**
 *
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Base
 * @subpackage Community
 */
class ForumControl extends DataControl {
	var $table = "Forum";
	var $key = "Id";
	var $sequence = "Forum_Id_seq";
	var $defaultOrder = "SectionId";
	var $searchFields = array("Subject");

	function init() {

		$this->fieldMeta["Id"] = new FieldMeta(
			"Id", "", FM_TYPE_INTEGER, null, FM_STORE_NEVER, false);

		$this->fieldMeta["SectionId"] = new FieldMeta(
			"Section Id", "", FM_TYPE_RELATION, null, FM_STORE_ALWAYS, false);

		$this->fieldMeta["SectionId"]->setRelationControl(BaseFactory::getSectionControl());

		$this->fieldMeta["Subject"] = new FieldMeta(
			"Subject", "", FM_TYPE_STRING, 255, FM_STORE_ALWAYS, false);

		$this->fieldMeta["Body"] = new FieldMeta(
			"Body", "", FM_TYPE_STRING, 1000, FM_STORE_ALWAYS, false);

		$this->fieldMeta["Body"]->setFormatter(CoreFactory::getBodyTextFormatter());

		$this->fieldMeta["AuthorId"] = new FieldMeta(
			"Author", "", FM_TYPE_RELATION, null, FM_STORE_ALWAYS, false);

		$this->fieldMeta["AuthorId"]->setRelationControl(BaseFactory::getMemberControl());
		$this->fieldMeta["AuthorId"]->setAutoData(CoreFactory::getCurrentMember());

		$this->fieldMeta["ImageId"] = new FieldMeta(
			"Image Id", "", FM_TYPE_BINARY, null, FM_STORE_ALWAYS, true);

		$this->fieldMeta["Anonymous"] = new FieldMeta(
			"Anonymous", "", FM_TYPE_BOOLEAN, null, FM_STORE_ALWAYS, false);

		$this->fieldMeta["Threads"] = new FieldMeta(
			"Threads", "", FM_TYPE_INTEGER, null, FM_STORE_NEVER, true);

		$this->fieldMeta["Posts"] = new FieldMeta(
			"Posts", "", FM_TYPE_INTEGER, null, FM_STORE_NEVER, true);

		$this->fieldMeta["DateCreated"] = new FieldMeta(
			"Date Created", "", FM_TYPE_DATE, null, FM_STORE_NEVER, false);

		$this->fieldMeta["DateCreated"]->setFormatter(CoreFactory::getDateTimeFieldFormatter());

		$this->fieldMeta["Updated"] = new FieldMeta(
			"Updated", "", FM_TYPE_DATE, null, FM_STORE_NEVER, true);

		$this->fieldMeta["LastPost"] = new FieldMeta(
			"Last Post", "", FM_TYPE_DATE, null, FM_STORE_NEVER, true);

		$this->fieldMeta["LastPost"]->setFormatter(CoreFactory::getDateTimeFieldFormatter("No Posts"));

		$this->fieldMeta["LastPostId"] = new FieldMeta(
			"Last Post Id", "", FM_TYPE_RELATION, null, FM_STORE_NEVER, true);

		$this->fieldMeta["LastPostId"]->setRelationControl(BaseFactory::getForumPostControl());
		
		$this->fieldMeta["LastPostById"] = new FieldMeta(
			"Last Post By Id", "", FM_TYPE_INTEGER, null, FM_STORE_NEVER, true);

		$this->fieldMeta["AgeRestriction"] = new FieldMeta(
			"Age Restriction", "", FM_TYPE_INTEGER, null, FM_STORE_ALWAYS, true);
			
		$this->fieldMeta["AddSecurityResourceId"] = new FieldMeta(
			"Security Resource needed for adding threads", SEC_RES_FORUM, FM_TYPE_RELATION, null, FM_STORE_ALWAYS, true);		
		
		$this->fieldMeta["AddSecurityResourceId"]->setRelationControl(BaseFactory::getSecurityResourceControl());
		
		$this->fieldMeta["ModifySecurityResourceId"] = new FieldMeta(
			"Security Resource needed for modifying threads", SEC_RES_FORUMADMIN, FM_TYPE_RELATION, null, FM_STORE_ALWAYS, true);		
		
		$this->fieldMeta["ModifySecurityResourceId"]->setRelationControl(BaseFactory::getSecurityResourceControl());			
							
	}

    function retrieveForSection($sectionId) {
      $filter = $this->getFilter();
      if ($filter == null) {
        $filter = CoreFactory::getFilter();
      }
      $filter->addConditional($this->table, "SectionId", $sectionId);
      $this->setFilter($filter);
      $this->retrieveAll();
    }

	function afterInsert(&$dataEntity) {
		$cacheControl = &CoreFactory::getCacheControl();
		$cacheControl->deleteWebPageCache("ForumHome");
		$cacheControl->deleteWebPageCache("Forum-Summary");
	}

	function afterUpdate(&$dataEntity) {
		$this->afterInsert($dataEntity);
	}

	function afterDelete(&$dataEntity) {
		$this->afterInsert($dataEntity);
	}
	
	function getDataEntity() {
		return new ForumDataEntity($this);
	}		
}

class ForumDataEntity extends DataEntity {
	function isMemberAllowedToAdd() {
		$application = &CoreFactory::getApplication();
		return $application->securityControl->isAllowed($this->getRelationValue("AddSecurityResourceId", "Name"), false);
	}
	function isMemberAllowedToModify() {
		$application = &CoreFactory::getApplication();
		return $application->securityControl->isAllowed($this->getRelationValue("ModifySecurityResourceId", "Name"), false);
	}		
}