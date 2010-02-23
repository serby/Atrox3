<?php
	/**
	 * @package Base
	 * @subpackage ContentManagement
	 * @copyright Clock Limited 2010
	 * @version 3.2 - $Revision$ - $Date$
	 */

	/**
	 * Include Data.php so that DataControl can be extended.
	 * Include Dataentity.php so that DataEntity can be extended
	 */
	require_once("Atrox/Core/Data/Data.php");
	require_once("Atrox/Core/Data/DataEntity.php");
	
	/**
	 *
	 * @author Tom Smith (Clock Ltd) {@link mailto:thomas.smith@clock.co.uk thomas.smith@clock.co.uk }
	 * @copyright Clock Limited 2010
	 * @version 3.2 - $Revision$ - $Date$
	 * @package Base
	 * @subpackage ContentManagement
	 */

	class ContentPageElementControl extends DataControl {
		var $table = "ContentPageElement";
		var $key = "Id";
		var $sequence = "ContentPageElement_Id_seq";
		var $defaultOrder = "Id";

		function init() {

			$this->fieldMeta["Id"] = new FieldMeta(
				"Id", "", FM_TYPE_INTEGER, null, FM_STORE_NEVER, false);						

			$this->fieldMeta["ContentPageId"] = new FieldMeta(
				"Content Page Id", "", FM_TYPE_RELATION, null, FM_STORE_ALWAYS, false);

			$this->fieldMeta["ContentPageId"]->setRelationControl(BaseFactory::getContentPageControl());
				
			$this->fieldMeta["Title"] = new FieldMeta(
				"Title", "", FM_TYPE_STRING, null, FM_STORE_ALWAYS, false);	

			$this->fieldMeta["Summary"] = new FieldMeta(
				"Summary", "", FM_TYPE_STRING, 2000, FM_STORE_ALWAYS, true);

			$this->fieldMeta["Summary"]->setFormatter(CoreFactory::getBodyTextFormatter());	
				
			$this->fieldMeta["Body"] = new FieldMeta(
				"Body", "", FM_TYPE_STRING, 5000, FM_STORE_ALWAYS, false);

			$this->fieldMeta["Body"]->setFormatter(CoreFactory::getBodyTextFormatter());

			$this->fieldMeta["TitleImageId"] = new FieldMeta(
				"Title Image", "", FM_TYPE_BINARY, null, FM_STORE_ALWAYS, true);
			
			$this->fieldMeta["TitleImageId"]->setRelationControl(CoreFactory::getBinaryControl());

			$this->fieldMeta["MainImageId"] = new FieldMeta(
				"Main Image", "", FM_TYPE_BINARY, null, FM_STORE_ALWAYS, true);
			
			$this->fieldMeta["MainImageId"]->setRelationControl(CoreFactory::getBinaryControl());
				
			$this->fieldMeta["DateCreated"] = new FieldMeta(
				"DateCreated", "", FM_TYPE_DATE, null, FM_STORE_NEVER, false);

			$this->fieldMeta["DateCreated"]->setFormatter(CoreFactory::getDateTimeFieldFormatter());
		}
		
		function retrieveForContentPage($contentPage, $excludeElementId = false) {
			$filter = CoreFactory::getFilter();
			$filter->addConditional($this->table, "ContentPageId", $contentPage->get("Id"));
			if ($excludeElementId) {
				$filter->addConditional($this->table, "Id", $excludeElementId, "!=");
			}
			$this->setFilter($filter);
		}
	}	
