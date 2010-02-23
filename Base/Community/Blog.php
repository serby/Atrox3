<?php
/**
 * @package Base
 * @subpackage Community
 * @copyright Clock Limited 2007
 * @version 3.0 - $Revision$ - $Date$
 */

/**
 * Include TaggedData.php so that DataControl can be extended.
 */
require_once("Atrox/Core/Data/TaggedData.php");

/**
 *
 * @author Adam Forster (Clock Ltd) {@link mailto:adam.forster@clock.co.uk adam.forster@clock.co.uk }
 * @copyright Clock Limited 2007
 * @version 3.0 - $Revision$ - $Date$
 * @package Base
 * @subpackage Community
 */
class BlogControl extends TaggedDataControl {
	var $table = "Blog";
	var $key = "Id";
	var $sequence = "Blog_Id_seq";
	var $defaultOrder = "Title";
	var $searchFields = array("Title");
	var $imageSize = array("MinWidth" => 50, "MinHeight" => 50, "MaxWidth" => 100, "MaxHeight" => 100);

	function init() {

		$this->fieldMeta["Id"] = new FieldMeta(
			"Id", "", FM_TYPE_INTEGER, null, FM_STORE_NEVER, false);

		$this->fieldMeta["Title"] = new FieldMeta(
			"Title", "", FM_TYPE_STRING, 500, FM_STORE_ALWAYS, false);

		$this->fieldMeta["Description"] = new FieldMeta(
			"Description", "", FM_TYPE_STRING, 10000, FM_STORE_ALWAYS, false);

		$this->fieldMeta["Description"]->setFormatter(CoreFactory::getBodyTextFormatter());

		$this->fieldMeta["AuthorId"] = new FieldMeta(
			"Author", "", FM_TYPE_RELATION, null, FM_STORE_ALWAYS, false);

		$this->fieldMeta["AuthorId"]->setRelationControl(BaseFactory::getMemberControl());
		$this->fieldMeta["AuthorId"]->setAutoData(CoreFactory::getCurrentMember());

		$this->fieldMeta["Entries"] = new FieldMeta(
			"Entries", "", FM_TYPE_INTEGER, null, FM_STORE_NEVER, true);

		$this->fieldMeta["LastEntry"] = new FieldMeta(
			"Last Entry", "", FM_TYPE_DATE, null, FM_STORE_NEVER, true);

		$this->fieldMeta["LastEntry"]->setFormatter(CoreFactory::getDateTimeFieldFormatter("No Entries"));

		$this->fieldMeta["ImageId"] = new FieldMeta(
			"Image", "", FM_TYPE_BINARY, null, FM_STORE_ALWAYS, true);

		$this->fieldMeta["ImageId"]->setValidation(CoreFactory::getImageValidation(
				$this->imageSize["MinWidth"],
				$this->imageSize["MinHeight"],
				$this->imageSize["MaxWidth"],
				$this->imageSize["MaxHeight"]));

		$this->fieldMeta["DateCreated"] = new FieldMeta(
			"Date Created", "", FM_TYPE_DATE, null, FM_STORE_NEVER, false);

		$this->fieldMeta["DateCreated"]->setFormatter(CoreFactory::getDateTimeFieldFormatter());
		
		$this->fieldMeta["Tags"] = new FieldMeta(
			"Tags", "", FM_TYPE_TAG, null, FM_STORE_ALWAYS, true);
	}

	function retrieveLatest($limit){
		$filter = $this->getFilter();
      if ($filter == null) {
        $filter = CoreFactory::getFilter();
      }      
		$filter->addLimit($limit);
      $this->setFilter($filter);
      return $this->retrieveAll();
	}

	function afterInsert(&$dataEntity) {
		$cacheControl = &CoreFactory::getCacheControl();
		$cacheControl->deleteWebPageCache("BlogHome");
		$cacheControl->deleteWebPageCache("Static");			
	}

	function afterUpdate(&$dataEntity) {
		$this->afterInsert($dataEntity);
	}

	function afterDelete(&$dataEntity) {
		$this->afterInsert($dataEntity);
	}

	function delete(&$blogIds) {
		$application = &CoreFactory::getApplication();
		if ($application->securityControl->isAllowed("Blog Admin", false)) {
			parent::delete($blogIds);
		} else {
			$ids[] = null;
			$blogIds = explode(",", $blogIds);
			foreach ($blogIds as $blogId) {
				$blog = $this->item($blogId);
				if ($blog->get("AuthorId") == $application->securityControl->memberId) {
					parent::delete($blog->get("Id"));
				}
			}
		}
	}
}
