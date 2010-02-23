<?php
	/**
	 * @package Base
   * @subpackage Default
   * @copyright Clock Limited 2007
	 * @version 3.0 - $Revision$ - $Date$
	 */

	/**
	 * Include Data.php so that DataControl can be extended.
	 */
	require_once("Atrox/3.0/Core/Data/Data.php");

	/**
	 *
	 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
	 * @copyright Clock Limited 2007
	 * @version 3.0 - $Revision$ - $Date$
	 * @package Base
   * @subpackage Default
	 */
	class TemporaryStoreControl extends DataControl {

		var $table = "TemporaryStore";
		var $key = "Id";
		var $sequence = "TemporaryStore_Id_seq";
		var $defaultOrder = "Id";

		function init() {

			$this->fieldMeta["Id"] = new FieldMeta(
				"Id", "", FM_TYPE_INTEGER, null, FM_STORE_NEVER, false);

			$this->fieldMeta["Uid"] = new FieldMeta(
				"Uid", "", FM_TYPE_STRING, 40, FM_STORE_ALWAYS, false);

			$this->fieldMeta["DataEntityName"] = new FieldMeta(
				"Data Entity Name", "", FM_TYPE_STRING, 500, FM_STORE_ALWAYS, false);

			$this->fieldMeta["Key"] = new FieldMeta(
				"Key", "", FM_TYPE_STRING, 500, FM_STORE_ALWAYS, false);

			$this->fieldMeta["Value"] = new FieldMeta(
				"Value", "", FM_TYPE_STRING, 5000, FM_STORE_ALWAYS, false);

			$this->fieldMeta["DateCreated"] = new FieldMeta(
				"Date Created", "", FM_TYPE_DATE, 1, FM_STORE_NEVER, false);

			$this->fieldMeta["DateCreated"]->setFormatter(CoreFactory::getDateTimeFieldFormatter());
			
			$this->fieldMeta["IsFile"] = new FieldMeta(
				"IsFile", "", FM_TYPE_BOOLEAN, null, FM_STORE_ALWAYS, false);			
		}
		
		function getSessionUid($name, $expiryLength = 31536000) {
			if (!isset($_COOKIE["TemporaryStore-{$name}"])) {
				setcookie("Atrox-TemporaryStore-{$name}", $uid = sha1(uniqid(rand(), true)),
					time() + $expiryLength);
			}	else {
				$uid = $_COOKIE["Atrox-TemporaryStore-{$name}"];
			}
			return $uid;
		}
				
		function &fillArray($uid, $dataEntityName, &$arrayData) {
			$this->initControl();
			$filter = CoreFactory::getFilter();
			$filter->addConditional($this->table, "Uid", $uid);
			$filter->addConditional($this->table, "DataEntityName", $dataEntityName);		
			$this->setFilter($filter);
			$this->retrieveAll();
			while ($data = $this->getNext()) {
				$arrayData[$data->get("Key")] = $data->get("Value");
			}
		}
		
		function &mapToDataEntity($uid, $dataControl) {
			$this->initControl();
			$filter = CoreFactory::getFilter();
			$filter->addConditional($this->table, "Uid", $uid);
			$filter->addConditional($this->table, "DataEntityName", $dataControl->table);			
			$this->setFilter($filter);
			$this->retrieveAll();
			$dataEntity = $dataControl->makeNew();
			while ($data = $this->getNext()) {
				if ($dataControl->isField($data->get("Key"))) {
					$dataEntity->set($data->get("Key"), $data->get("Value"));
				}
			}
			return $dataEntity;
		}
		
		function clear($uid, $dataEntity = null) {
			$this->initControl();
			$filter = CoreFactory::getFilter();
			$filter->addConditional($this->table, "Uid", $uid);
			if ($dataEntity != null) {
				$filter->addConditional($this->table, "DataEntityName", $dataEntity);
			}
			$this->setFilter($filter);
			$this->retrieveAll();
			while ($data = $this->getNext()) {
				if (($data->get("IsFile") == "t") && (file_exists($data->get("Value")))) {
					unlink($data->get("Value"));
				}
				$this->delete($data->get("Id"));
			}			
			return true;
		}
		
		function store($uid, $dataEntity) {
			foreach($dataEntity->control->fieldMeta as $k => $v) {
				$filter = CoreFactory::getFilter();
				$filter->addConditional($this->table, "Uid", $uid);
				$filter->addConditional($this->table, "DataEntityName", $dataEntity->control->table);
				$filter->addConditional($this->table, "Key", $k);
				$this->setFilter($filter);
				$this->deleteAll();
				$this->addData($uid, $dataEntity->control->table, $k, $dataEntity->get($k));
			}
		}
		
		function storeSelection($uid, $dataEntity, $selection) {
			foreach($selection as $v) {
				if ($dataEntity->control->isField($v)) {
					$filter = CoreFactory::getFilter();
					$filter->addConditional($this->table, "Uid", $uid);
					$filter->addConditional($this->table, "DataEntityName", $dataEntity->control->table);
					$filter->addConditional($this->table, "Key", $v);
					$this->setFilter($filter);
					$this->deleteAll();
					$this->addData($uid, $dataEntity->control->table, $v, $dataEntity->get($v));
				}
			}
		}		
		
		function storeArray($uid, $dataEntityName, $array) {
			foreach($array as $k => $v) {
				$filter = CoreFactory::getFilter();
				$filter->addConditional($this->table, "Uid", $uid);
				$filter->addConditional($this->table, "DataEntityName", $dataEntityName);
				$filter->addConditional($this->table, "Key", $k);
				$this->setFilter($filter);
				$this->deleteAll();
				$this->addData($uid, $dataEntityName, $k, $v);
			}
		}	

		function addFile($uid, $dataEntityName, $key, $filename) {
			if ($filename == "") {
				return false;
			}
			$data = $this->makeNew();
			$newTempFilename = tempnam("/tmp", "CLK-TS");
			copy($filename, $newTempFilename);			
			$this->addData($uid, $dataEntityName, $key, $newTempFilename, true);
			return true;
		}	
		
		function addData($uid, $dataEntityName, $key, $value, $isFile = false) {
			$filter = CoreFactory::getFilter();
			$filter->addConditional($this->table, "Uid", $uid);
			$filter->addConditional($this->table, "DataEntityName", $dataEntityName);
			$filter->addConditional($this->table, "Key", $key);
			$this->setFilter($filter);
			$this->deleteAll();			
			$data = $this->makeNew();
			$data->set("Uid", $uid);
			$data->set("DataEntityName", $dataEntityName);
			$data->set("Key", $key);
			$data->set("Value", $value);
			$data->set("IsFile", $isFile ? "t" : "f");
			$this->quickAdd($data);
		}		
	}
