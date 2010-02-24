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

class ContentPageControl extends DataControl {
	var $table = "ContentPage";
	var $key = "Id";
	var $sequence = "ContentPage_Id_seq";
	var $defaultOrder = "Id";

	function init() {

		$this->fieldMeta["Id"] = new FieldMeta(
			"Id", "", FM_TYPE_INTEGER, null, FM_STORE_NEVER, false);						
		
		$this->fieldMeta["Name"] = new FieldMeta(
			"Name", "", FM_TYPE_STRING, null, FM_STORE_ALWAYS, false);
			
		$this->fieldMeta["Title"] = new FieldMeta(
			"Title", "", FM_TYPE_STRING, null, FM_STORE_ALWAYS, false);	
			
		$this->fieldMeta["Summary"] = new FieldMeta(
			"Summary", "", FM_TYPE_STRING, 2000, FM_STORE_ALWAYS, true);

		$this->fieldMeta["Summary"]->setFormatter(CoreFactory::getBodyTextFormatter());
		
		$this->fieldMeta["Body"] = new FieldMeta(
			"Body", "", FM_TYPE_STRING, 5000, FM_STORE_ALWAYS, false);

		$this->fieldMeta["Body"]->setFormatter(CoreFactory::getBodyTextFormatter());
			
		$this->fieldMeta["AttachmentId"] = new FieldMeta(
			"Attachment", "", FM_TYPE_BINARY, null, FM_STORE_ALWAYS, true);
		
		$this->fieldMeta["AttachmentId"]->setRelationControl(CoreFactory::getBinaryControl());
			
		$this->fieldMeta["ImageId"] = new FieldMeta(
			"Image", "", FM_TYPE_BINARY, null, FM_STORE_ALWAYS, false);
		
		$this->fieldMeta["ImageId"]->setRelationControl(CoreFactory::getBinaryControl());

		$this->fieldMeta["DateCreated"] = new FieldMeta(
			"DateCreated", "", FM_TYPE_DATE, null, FM_STORE_NEVER, false);

		$this->fieldMeta["DateCreated"]->setFormatter(CoreFactory::getDateTimeFieldFormatter());
	}
	
	/**
	 * This function returns a service elements controller filtered for the service Id 
	 * passed in.
	 * @param Int Service Id filter is to be set for.
	 **/
	function getFilteredServiceElementsControl($id) {
		$serviceElementsControl = new ServiceElementControl();
		$filter = CoreFactory::getFilter();
		$filter->addConditional($serviceElementsControl->table, "ServiceId", $id);
		$serviceElementsControl->setFilter($filter);
		return $serviceElementsControl;
	}			
	
}