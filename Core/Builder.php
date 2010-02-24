<?php
/**
 * @package Core
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 */

/**
 * Include Data.php so that DataControl can be extended.
 */
require_once("Atrox/Core/Data/Data.php");

/**
 * Generates the required SQL schema to for a data entity using the data control.
 *
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Core
 */
class Builder {

	var $dataControl = null;

	function Builder(&$dataControl) {
		$this->dataControl = $dataControl;
	}

	function getType($k, $v) {
		$return = "";

		return $return;
	}

	function getCreateSql() {
		$this->dataControl->initControl();
		$this->indexes = array();
		if (is_array($this->dataControl->searchFields)) {
			$this->indexes = $this->dataControl->searchFields;
		}
		$output = "CREATE TABLE \"" . $this->dataControl->table . "\" (\n";
		foreach ($this->dataControl->fieldMeta as $k => $v) {
			$output .= "\t\"$k\" ";
			$default = "";
			switch ($v->type) {
				case FM_TYPE_RELATION:
				case FM_TYPE_INTEGER:
				case FM_TYPE_BINARY:					
					if ($this->dataControl->key == $k) {
						$output .= "serial";
						$v->allowNull = true;
					} else {
						if (mb_substr($k, -2) == "Id") {
							$this->indexes[] = $k;
						}
						$output .= "int4";
					}
					break;
				case FM_TYPE_DATE:
					$output .= "timestamp";
					if ($k == "DateCreated") {
						$default .= " DEFAULT now()";
					}
					$this->indexes[] = $k;
					break;
				case FM_TYPE_CURRENCY:
				case FM_TYPE_FLOAT:
					$output .= "float4";
					break;
				case FM_TYPE_BOOLEAN:
					$output .= "bool";
					break;
				case FM_TYPE_IP:
				case FM_TYPE_URL:
				case FM_TYPE_STRING:
				case FM_TYPE_TAG:
				case FM_TYPE_EMAILADDRESS:
				case FM_TYPE_SERIALIZED:
				default:
					$output .= "text";
					break;					
			}

			if (!$v->allowNull) {
				$output .= " NOT NULL";
			}
			$output .= "$default,\n";
		}
		$output .= "\tCONSTRAINT \"" . $this->dataControl->table . "_pkey\" PRIMARY KEY (\"" . $this->dataControl->key . "\")\n";
		$output .= ") WITHOUT OIDS;\n\n";
		$output .= "GRANT ALL ON TABLE \"" . $this->dataControl->table . "\" TO postgres WITH GRANT OPTION;\n";
		$output .= "GRANT ALL ON TABLE \"" . $this->dataControl->table . "\" TO GROUP \"WebUserGroup\";\n";
		$output .= "GRANT ALL ON TABLE \"" . $this->dataControl->table . "_" . $this->dataControl->key . "_seq\" TO GROUP \"WebUserGroup\";\n\n";
		$this->indexes = array_unique($this->indexes);
		foreach ($this->indexes as $index) {
			$output .= "CREATE INDEX \"{$this->dataControl->table}_{$index}_idx\" ON \"{$this->dataControl->table}\" USING btree(\"{$index}\");\n";
		}
		
		return $output;
	}
}