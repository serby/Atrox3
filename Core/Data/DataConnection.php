<?php
/**
 * @package Core
 * @subpackage Data
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 */

/**
 * Database Control
 *
 * Acts as a layer between the DataControl classes and database
 * Currently a Postgres specific class, also the Filter class has a little
 * postgres specific code in to do full text searches.
 *
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Core
 * @subpackage Data
 */
class DatabaseControl {

	/**
	 * @var DatabaseConnection
	 */	
	var $dbConnection;
	
	/**
	 * We ensure that we only ever have one instances of this object.
	 */
	function DatabaseControl() {
		// Create the connection.
		return $this;
	}
	function connect() {
		$application = &CoreFactory::getApplication();
		$this->dbConnection = pg_pconnect($application->getConnectionString());
	}
	
	function close() {
		pg_close($this->dbConnection);
	}		
	
	function parseTable($tableName) {
		return "\"$tableName\"";
	}

	function parseField($fieldName) {
		return "\"$fieldName\"";
	}

	function parseValue($value) {
		return "'" . addslashes($value) . "'";
	}

	function fetchRow($result, $recordOffset=-1) {
		if ($recordOffset == -1) {
			return pg_fetch_array($result);
		} else {
			return pg_fetch_array($result, $recordOffset);
		}
	}

	function query($sql) {
		
		$application = &CoreFactory::getApplication();
		$application->log(": " . $sql . "\n\n", $type = "sql");
		$result = @pg_query($this->dbConnection, $sql);
		
		if ($error = pg_last_error($this->dbConnection)) {
			$application->log(":\n\t" . $error . "\n\n", $type = "sql");
			$application->errorControl->addError("Certain constraints have prevented your operation: $error");
		}
		return $result;
	}

	function getValueFromQuery($sql) {
		$data = pg_fetch_row($this->query($sql));
		return $data[0];
	}

	function getLastId($controlObject) {
		$sql = "select currval('\"" . $controlObject->sequence . "\"'::text)";
		$data = $this->fetchRow($this->query($sql));
		return $data[0];
	}

	function numRows($result) {
		return pg_num_rows($result);
	}

	function affectedRows($results) {
		return pg_affected_rows($results);
	}

	function getLastError() {
		return pg_last_error($this->dbConnection);
	}
	
	function removeBinary($oid) {
		pg_query($this->dbConnection, "BEGIN");
		@pg_lo_unlink($this->dbConnection, $oid);
		pg_query($this->dbConnection, "COMMIT");
		return true;
	}

	function insertBinary(&$filename) {
		pg_query($this->dbConnection, "BEGIN");
		$oid = pg_lo_import($this->dbConnection, $filename);
		pg_query($this->dbConnection, "COMMIT");
		return $oid;
	}

	function dumpBinary($oid) {
		pg_query($this->dbConnection, "BEGIN");
		$fd = pg_lo_open($this->dbConnection, $oid, "r");
		pg_lo_read_all($fd);
		pg_lo_close($fd);
		pg_query($this->dbConnection, "COMMIT");
	}

	function binaryToFile($oid, $filename) {
		pg_query($this->dbConnection, "BEGIN");
		if ($fd = @pg_lo_open($this->dbConnection, $oid, "r")) {
			$handle = fopen($filename, "w+");
			while ($data = pg_lo_read($fd)) {
				fwrite($handle, $data);
			}
			fclose($handle);
			pg_lo_close($fd);
		}
		pg_query($this->dbConnection, "COMMIT");
	}

	function getNow() {
		return "NOW()";
	}

	function getCurrentDate() {
		return gmdate("Y-m-d");
	}

	function getCurrentDateTime() {
		return gmdate("Y-m-d H:i:s");
	}
	
	function generateCsv(&$controlObject, $fields, $sql = null) {
		$output = "";
		
		$length = count($fields);
		for ($i = 0; $i < $length - 1; $i++) {
			$output .= $fields[$i] . ", ";
		}
		$output .= $fields[$length - 1] . "\n";
		
		if ($sql != null) {
			$controlObject->runQuery($sql);
			while ($data = $this->fetchRow($controlObject->results)) {
				for ($i = 0; $i < $length - 1; $i++) {
					$output .= $data[$fields[$i]] . ", ";
				}
				$output .= $data[$fields[$length - 1]] . "\n";
			}
		}
		
		while ($data = $controlObject->getNext()) {
			for ($i = 0; $i < $length - 1; $i++) {
			$output .= $data->get($fields[$i]) . ", ";
			}
			$output .= $data->get($fields[$length - 1]) . "\n";
		}
		
		return $output;
	}
}