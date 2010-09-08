<?php
/**
 * @package Core
 * @subpackage Data
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 */
	require_once("Atrox/Core/Data/Data.php");

/**
 * Controls the input and output of binary large objects to the database.
 *
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Core
 * @subpackage Data
 */
class BinaryControl extends DataControl {
	var $table = "Binary";
	var $key = "Id";
	var $sequence = "Binary_Id_seq";
	var $defaultOrder = "Created";

	function init() {

		$this->fieldMeta["Id"] = new FieldMeta(
			"Id", "", FM_TYPE_INTEGER, null, FM_STORE_NEVER, false);

		$this->fieldMeta["Filename"] = new FieldMeta(
			"Filename", "", FM_TYPE_STRING, null, FM_STORE_ALWAYS, false);

		$this->fieldMeta["Size"] = new FieldMeta(
			"Size", "", FM_TYPE_INTEGER, null, FM_STORE_ALWAYS, false);

		$this->fieldMeta["MimeType"] = new FieldMeta(
			"Mime Type", "", FM_TYPE_STRING, null, FM_STORE_ALWAYS, false);

		$this->fieldMeta["Created"] = new FieldMeta(
			"Created", "", FM_TYPE_DATE, null, FM_STORE_NEVER, false);

		$this->fieldMeta["HashValue"] = new FieldMeta(
			"Hash Value", "", FM_TYPE_STRING, null, FM_STORE_ALWAYS, false);

		$this->fieldMeta["IsPublic"] = new FieldMeta(
			"Is Binary Public", true, FM_TYPE_BOOLEAN, null, FM_STORE_ALWAYS, true);
	}

	function mapBinary($id, $fileInfo) {
		// Create a new binary if it doesn't already exist

		if (($id == null) || (!$d = $this->item($id))) {
			$d = $this->makeNew();
		}
		$d->add("Filename", stripslashes($fileInfo["Filename"]));
		$d->add("MimeType", $fileInfo["Type"]);
		$d->add("Size", $fileInfo["Size"]);
		$d->add("TempFilename", $fileInfo["TempName"]);

		//Add extra security to hashvalue by using salt
		$salt = $fileInfo["Filename"] . "AtroxBinaryImage";
		$d->add("HashValue", md5(file_get_contents($fileInfo["TempName"]) . $salt));
		return $d;
	}

	function add(&$binary) {
		if ($return = parent::add($binary)) {
			$application = CoreFactory::getApplication();
			$filename = $application->registry->get("Binary/Path") . "/" . $binary->get("HashValue") . "/" . $binary->get("Filename");
			@mkdir($application->registry->get("Binary/Path") . "/" . $binary->get("HashValue"));
			//If the file already exists dont add it
			if (!file_exists($filename)) {
				copy($binary->get("TempFilename"), $filename);
			}
		}
		return $return;
	}

	function update(&$binary) {
		$cacheControl = CoreFactory::getCacheControl();
		$cacheControl->clearBinaries($binary);

		$application = CoreFactory::getApplication();

		if ($oldBinary = $this->item($binary->get("Id"), false)) {
			$filename = $application->registry->get("Binary/Path") . "/" . $oldBinary->get("HashValue") . "/" . $oldBinary->get("Filename");
			//Check if another binary is using the file if not delete it
			$filter = CoreFactory::getFilter();
			$filter->addConditional($this->table, "HashValue", $oldBinary->get("HashValue"));
			$filter->addConditional($this->table, "Filename", $oldBinary->get("Filename"));
			$this->setFilter($filter);

			if ($this->getNumRows() < 2) {
				@unlink($filename);
				@rmdir($application->registry->get("Binary/Path") . "/" . $oldBinary->get("HashValue"));
			}
		}
		$filename = $application->registry->get("Binary/Path") . "/" . $binary->get("HashValue") . "/" . $binary->get("Filename");
		@mkdir($application->registry->get("Binary/Path") . "/" . $binary->get("HashValue"));
		@copy($binary->get("TempFilename"), $filename);
		return parent::update($binary);
	}

	function delete(&$binary) {
		if ($binary) {
			$cacheControl = CoreFactory::getCacheControl();
			$cacheControl->clearBinaries($binary);
			$application = CoreFactory::getApplication();
			$filename = $application->registry->get("Binary/Path") . "/" . $binary->get("HashValue") . "/" . $binary->get("Filename");
			$filter = CoreFactory::getFilter();
			$filter->addConditional($this->table, "HashValue", $binary->get("HashValue"));
							$filter->addConditional($this->table, "Filename", $binary->get("Filename"));
			$this->setFilter($filter);
			if ($this->getNumRows() < 2) {
				@unlink($filename);
				@rmdir($application->registry->get("Binary/Path") . "/" . $binary->get("HashValue"));
			}
			return parent::quickDelete($binary->get("Id"));
		} else {
			return false;
		}
	}

	function outputToFile($binary, $filename, $createMetaFile = true) {
		if ($binary) {
			$application = CoreFactory::getApplication();
			$binaryFilename = $application->registry->get("Binary/Path") . "/" . $binary->get("HashValue") . "/" . $binary->get("Filename");
			@mkdir($application->registry->get("Cache/Binary/Path") . "/" . $binary->get("HashValue"));
			if (@copy($binaryFilename, $filename)) {
				return $binary;
			} else {
				return false;
			}
		}
		return false;
	}

	function getBinaryFullPath(&$binary) {
		return $this->application->registry->get("Binary/Path") . "/" . $binary->get("HashValue") . "/" . $binary->get("Filename");
	}

	function isImage($binary) {
		switch ($binary->get("MimeType")) {
			case "image/jpeg":
			case "image/pjpeg":
			case "image/gif":
			case "image/png":
				return true;
			default:
				return false;
		}
	}

	function isAudio($binary) {

		//TODO: List is incomplete

		switch ($binary->get("MimeType")) {
			case "audio/mpeg3":
			case "audio/mpeg":
				return true;
			default:
				return false;
		}
	}
}