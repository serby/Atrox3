<?php
	/**
	 * @package Base
	 * @subpackage Member
	 * @copyright Clock Limited 2007
	 * @version 3.0 - $Revision$ - $Date$
	 */

	/**
	 * Include Data.php so that DataControl can be extended.
	 */
	require_once("Atrox/Core/Data/Data.php");
	
	define("MU_SUBMITTED", 1);
	define("MU_VIEWED", 2);
	define("MU_DECLINED", 3);
	define("MU_ACCPETED", 4);

	/**
	 *
	 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
	 * @copyright Clock Limited 2007
	 * @version 3.0 - $Revision$ - $Date$
	 * @package Base
	 * @subpackage Member
	 */
	class MemberUploadControl extends DataControl {
		var $table = "MemberUpload";
		var $key = "Id";
		var $sequence = "MemberUpload_Id_seq";
		var $defaultOrder = "DateCreated";
		var $searchFields = array("Title", "Description");

		var $status = array(
			MU_SUBMITTED => "Submitted",
			MU_VIEWED => "Viewed",
			MU_DECLINED => "Declined",
			MU_ACCPETED => "Accepted");

		function init() {
			$lists = CoreFactory::getLists();

			$this->fieldMeta["Id"] = new  FieldMeta(
				"Id", "", FM_TYPE_INTEGER, null, FM_STORE_NEVER, false);

			$this->fieldMeta["Title"] = new FieldMeta(
				"Title", "", FM_TYPE_STRING, 255, FM_STORE_ALWAYS, false);

			$this->fieldMeta["Creator"] = new FieldMeta(
				"Creator", "", FM_TYPE_STRING, 255, FM_STORE_ALWAYS, false);

			$this->fieldMeta["Description"] = new FieldMeta(
				"Description", "", FM_TYPE_STRING, 5000, FM_STORE_ALWAYS, false);

			$this->fieldMeta["BinaryId"] = new FieldMeta(
				"Upload", "", FM_TYPE_BINARY, null, FM_STORE_ALWAYS, false);

			$this->fieldMeta["Status"] = new FieldMeta(
				"Status", MU_SUBMITTED, FM_TYPE_INTEGER, null, FM_STORE_ALWAYS, true);

			$this->fieldMeta["Status"]->setFormatter(
				CoreFactory::getArrayRelationFormatter($this->status, MU_SUBMITTED));
			
			$this->fieldMeta["DateCreated"] = new FieldMeta(
				"DateCreated", "", FM_TYPE_DATE, null, FM_STORE_NEVER, true);

			$this->fieldMeta["DateCreated"]->setFormatter(CoreFactory::getDateTimeFieldFormatter());
			
			$this->fieldMeta["MemberId"] = new FieldMeta(
				"Member Id", "", FM_TYPE_RELATION, null, FM_STORE_ALWAYS, true);

			$this->fieldMeta["MemberId"]->setRelationControl(BaseFactory::getMemberControl());			

		}
		
		function retrieveForMember($member) {
			$filter = CoreFactory::getFilter();
			$filter->addConditional($this->table, "MemberId", $member->get("Id"));
			$this->setFilter($filter);
			$this->retrieveAll();
		}
	}
