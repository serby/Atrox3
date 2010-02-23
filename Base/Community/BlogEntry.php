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
require_once("Date.php");

/**
 *
 * @author Adam Forster (Clock Ltd) {@link mailto:adam.forster@clock.co.uk adam.forster@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Base
 * @subpackage Community
 */
class BlogEntryControl extends DataControl {
	var $table = "BlogEntry";
	var $key = "Id";
	var $sequence = "BlogEntry_Id_seq";
	var $defaultOrder = "DateCreated";
	var $searchFields = array("Subject", "Body");

	function init() {

		$this->fieldMeta["Id"] = new FieldMeta(
			"Id", "", FM_TYPE_INTEGER, null, FM_STORE_NEVER, false);

		$this->fieldMeta["BlogId"] = new FieldMeta(
			"Blog Id", "", FM_TYPE_RELATION, null, FM_STORE_ADD, false);

		$this->fieldMeta["BlogId"]->setRelationControl(BaseFactory::getBlogControl());
		$this->fieldMeta["BlogId"]->setValidation(new BlogValidation());

		$this->fieldMeta["Subject"] = new FieldMeta(
			"Subject", "", FM_TYPE_HTML, 100, FM_STORE_ALWAYS, false);

		$subjectProfanityValidation = BaseFactory::getProfanityValidation("Subject");
		$subjectProfanityValidation->setCallBack(new BlogWorryWordCallBack);

		$this->fieldMeta["Subject"]->setFormatter(CoreFactory::getBodyTextFormatter());
		$this->fieldMeta["Subject"]->setValidation($subjectProfanityValidation);

		$this->fieldMeta["Body"] = new FieldMeta(
			"Body", "", FM_TYPE_HTML, 5000, FM_STORE_ALWAYS, false);

		$bodyProfanityValidation = BaseFactory::getProfanityValidation("Body");
		$bodyProfanityValidation->setCallBack(new BlogWorryWordCallBack);

		$this->fieldMeta["Body"]->setFormatter(CoreFactory::getBodyTextFormatter());
		$this->fieldMeta["Body"]->setValidation($bodyProfanityValidation);

		$this->fieldMeta["DateCreated"] = new FieldMeta(
			"DateCreated", "", FM_TYPE_DATE, null, FM_STORE_NEVER, true);

		$this->fieldMeta["DateCreated"]->setFormatter(CoreFactory::getDateTimeFieldFormatter());

		$this->fieldMeta["Comments"] = new FieldMeta(
			"Comments", "", FM_TYPE_INTEGER, null, FM_STORE_NEVER, true);
	}

	function afterInsert(&$dataEntity) {
		$blog = $dataEntity->getRelation("BlogId");
		$blogControl = $blog->getControl();
		$blogControl->incrementField($blog, "Posts");
		$blogControl->updateField($blog, "LastPost", $this->databaseControl->getCurrentDateTime());
		$this->afterUpdate($dataEntity);
	}

	function afterDelete(&$dataEntity) {
		$blog = $dataEntity->getRelation("BlogId");
		$blogControl = $blog->getControl();
		$blogControl->incrementField($blog, "Posts", -1);
		$this->afterUpdate($dataEntity);
	}

	function retrieveForBlog(&$blog) {
		$filter = $this->getFilter();
		if ($filter == null) {
			$filter = CoreFactory::getFilter();
		}
		$filter->addConditional($this->table, "BlogId", $blog->get("Id"));
		$this->setFilter($filter);
	}

	function retrieveFirstPageForBlog(&$blog, $limit = 5) {
		$filter = $this->getFilter();
		if ($filter == null) {
			$filter = CoreFactory::getFilter();
		}
		$filter->addOrder("DateCreated", "True");
		$filter->addLimit($limit);
		$this->setFilter($filter);
		$this->retrieveForBlog($blog);
	}

	function retrieveLatestEntriesForBlog(&$blog, $limit = 10) {
		$filter = $this->getFilter();
		if ($filter == null) {
			$filter = CoreFactory::getFilter();
		}
		$filter->addOrder("DateCreated", "True");
		$filter->addLimit($limit);
		$this->setFilter($filter);
		$this->retrieveForBlog($blog);
	}

	function retrieveFromArchive(&$blog, $startDate, $endDate) {
		$filter = $this->getFilter();
		if ($filter == null) {
			$filter = CoreFactory::getFilter();
		}
		$filter->addOrder("DateCreated", "True");
		$filter->addConditional($this->table, "DateCreated", $startDate, ">=");
		$filter->addConditional($this->table, "DateCreated", $endDate, "<");
		$this->setFilter($filter);
		$this->retrieveForBlog($blog);
	}

	function generateArchiveByYear(&$blog) {
		$oldestEntry = $this->getOldest($blog);
		$newestEntry = $this->getNewest($blog);

		if (!$oldestEntry || !$newestEntry) {
			return false;
		}

		$endDate = new Date($newestEntry->get("DateCreated"));
		$startDate = new Date($oldestEntry->get("DateCreated"));
		$finalYear = $endDate->getYear();
		$firstYear = $startDate->getYear();

		$archive[][] = "";

		$i = 0;

		for ($year = $finalYear; $year >= $firstYear; $year--) {
			$archive[$i]["Year"] = $year;
			$archive[$i]["Start"] = "{$year}-01-01";
			$yearPlus = $year + 1;
			$archive[$i]["End"] = "{$yearPlus}-01-01";
			$i++;
		}

		return $archive;
	}

	function getNewest(&$blog) {
		$this->reset();
		$filter = CoreFactory::getFilter();
		$filter->addOrder("DateCreated", "True");
		$filter->addLimit(1);
		$this->setFilter($filter);
		$this->retrieveForBlog($blog);
		return $this->getNext();
	}

	function getOldest(&$blog) {
		$this->reset();
		$filter = CoreFactory::getFilter();
		$filter->addOrder("DateCreated");
		$filter->addLimit(1);
		$this->setFilter($filter);
		$this->retrieveForBlog($blog);
		return $this->getNext();
	}

	function delete(&$blogEntryIds) {
		$application = &CoreFactory::getApplication();
		if ($application->securityControl->isAllowed("Blog Admin", false)) {
			parent::delete($blogEntryIds);
		} else {
			$ids[] = null;
			$blogEntryIds = explode(",", $blogEntryIds);
			foreach ($blogEntryIds as $blogEntryId) {
				$blogEntry = $this->item($blogEntryId);
				if ($blogEntry->getRelationValue("BlogId", "AuthorId") == $application->securityControl->memberId) {
					parent::delete($blogEntry->get("Id"));
				}
			}
		}
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
class BlogWorryWordCallBack {
	function run($worryWords, $dataEntity) {
		mail(SITE_ALERT_EMAIL, SITE_NAME . " - Worry Word Detected",
		"Worry Words Detected: " . implode(",", $worryWords) . "\n" .
		"Blog Subject: " . $dataEntity->getFormatted("Subject") . "\n" .
		"Description: " . $dataEntity->getFormatted("Body") . "\n" .
		"Author: " . $dataEntity->getRelationValue("AuthorId", "Alias") . "\n",
		"From: " . SITE_GENERAL_EMAIL . "\n");
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
class BlogValidation extends CustomValidation {
	function validate(&$value, $fieldMeta) {
		// First validate the thread and make sure it can exist
		if (!$forum = $this->dataEntity->getRelation("BlogId")) {
			$this->dataEntity->control->errorControl->addError("Invalid Blog");
			return false;
		}
	}
}
