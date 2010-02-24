<?php
/**
 * @package Base
 * @subpackage Default
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 */

/**
 * Include Data.php so that DataControl can be extended.
 */
require_once("Atrox/Core/Data/Data.php");

define("NOTE_STATUS_ADDED", 1);  
define("NOTE_STATUS_EDITED", 2);
define("NOTE_STATUS_DELETED", 3);

/**
 *
 * @author Robert Arnold (Clock Ltd) {@link mailto:robert.arnold@clock.co.uk robert.arnold@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Base
 * @subpackage Default
 */
class NoteControl extends DataControl {
	var $table = "Note";
	var $key = "Id";
	var $sequence = "Note_Id_seq";
	var $defaultOrder = "Id";
	var $searchFields = array("Id", "DateCreated", "Description");
	var $status = array(
		NOTE_STATUS_ADDED => "Added",
		NOTE_STATUS_EDITED => "Updated",
		NOTE_STATUS_DELETED => "Deleted");

	function init() {
		$this->fieldMeta["Id"] = new FieldMeta(
			"Id", "", FM_TYPE_INTEGER, null, FM_STORE_NEVER, false);
			
		$this->fieldMeta["MemberId"] = new FieldMeta(
			"MemberId", "", FM_TYPE_RELATION, null, FM_STORE_ALWAYS, true);
			
		$this->fieldMeta["MemberId"]->setRelationControl(BaseFactory::getMemberControl());
		$this->fieldMeta["MemberId"]->setAutoData(CoreFactory::getCurrentMember());
		
		$this->fieldMeta["DateCreated"] = new FieldMeta(
			"DateCreated", "", FM_TYPE_DATE, null, FM_STORE_NEVER, false);

		$this->fieldMeta["DateCreated"]->setFormatter(CoreFactory::getDateTimeFieldFormatter());

		$this->fieldMeta["Description"] = new FieldMeta(
			"Description", "", FM_TYPE_STRING, 1000, FM_STORE_ALWAYS, false);
		
		$this->fieldMeta["CapturedId"] = new FieldMeta(
			"Captured Id", "", FM_TYPE_INTEGER, null, FM_STORE_ALWAYS, true);		
		
		$this->fieldMeta["IpAddress"] = new FieldMeta(
			"Ip Address", "", FM_TYPE_STRING, 15, FM_STORE_ALWAYS, false);

		$this->fieldMeta["IpAddress"]->setAutoData(CoreFactory::getIpAddress());
		
		$this->fieldMeta["Status"] = new FieldMeta(
			"Status", "", FM_TYPE_INTEGER, null, FM_STORE_ALWAYS, true);	
		
		$this->fieldMeta["Type"] = new FieldMeta(
			"Type", "", FM_TYPE_STRING, null, FM_STORE_ALWAYS, true);
		
		$this->fieldMeta["FolderLocation"] = new FieldMeta(
			"Folder Location", "", FM_TYPE_STRING, null, FM_STORE_ALWAYS, true);
		
	}	
		
	function addNote($description = null, $type = null, $capturedId = null, $status = 1, $folderLocation = null) {
		if (!$folderLocation) {
			$folderLocation	= mb_strtolower($type);
		}
		if ((!is_int($capturedId)) && (mb_strpos($capturedId, ","))) {
			$capturedIds = explode(",", $capturedId);
			foreach ($capturedIds as $value) {
				$note = $this->makeNew();
				$note->set("Description", $description);
				$note->set("CapturedId", $value);			
				$note->set("Type", $type);
				$note->set("Status", $status);
				$note->set("FolderLocation", $folderLocation);	
				$note->save();		
			}
		} else {	
			$note = $this->makeNew();
			$note->set("Description", $description);
			$note->set("CapturedId", $capturedId);			
			$note->set("Type", $type);
			$note->set("Status", $status);
			$note->set("FolderLocation", $folderLocation);		
			$note->save();	
		}		
	}			
	
}