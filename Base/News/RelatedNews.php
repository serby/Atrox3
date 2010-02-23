<?php
/**
 * @package Base
 * @subpackage Related News
 * @copyright Clock Limited 2007
 * @version 3.0 - $Revision$ - $Date$
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
 * @subpackage Related News
 */
class RelatedNewsControl extends DataControl {

	var $table = "RelatedNews";
	var $key = "Id";
	var $sequence = "RelatedNews_Id_seq";
	var $defaultOrder = "Id";
	var $searchFields = array("NewsId", "RelatedNewsId");

	function init() {

		$this->fieldMeta["Id"] = new FieldMeta(
			"Id", "", FM_TYPE_INTEGER, null, FM_STORE_NEVER, false);

		$this->fieldMeta["NewsId"] = new FieldMeta(
			"News Id", "", FM_TYPE_RELATION, null, FM_STORE_ALWAYS, false);

		$this->fieldMeta["NewsId"]->setRelationControl(BaseFactory::getNewsControl());

		$this->fieldMeta["RelatedNewsId"] = new FieldMeta(
			"Related News Id", "", FM_TYPE_RELATION, null, FM_STORE_ALWAYS, false);

		$this->fieldMeta["RelatedNewsId"]->setRelationControl(BaseFactory::getNewsControl());
			
		$this->fieldMeta["DateCreated"] = new FieldMeta(
			"Date Created", "", FM_TYPE_DATE, 1, FM_STORE_NEVER, false);

		$this->fieldMeta["DateCreated"]->setFormatter(CoreFactory::getDateTimeFieldFormatter());
		
	}
	
	function retrieveForNews($news) {
		if ($news->isNull("Id")) {
			return false;
		}
		$filter = $this->getFilter();
		if ($filter == null) {
			$filter = CoreFactory::getFilter();
		}
		$filter->addConditional($this->table, "NewsId", $news->get("Id"));
		$this->setFilter($filter);
	}
}