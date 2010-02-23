<?php
/**
 * @package Core
 */

/**
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Core
 */
class QueryString {

	var $toggle = null;
	var $stat = 0;

	function QueryString() {
		// Default Toggle
		$this->toggle["Name"] = "Desc";
		$this->toggle["Value"] = "True";
		$this->toggle["Linked"] = "Order";
	}

	function setValue($name, $value) {
		$this->queryValues["$name"] = $value;
	}

	function setToggle($name, $value, $linked = "Order") {
		$this->toggle["Name"] = $name;
		$this->toggle["Value"] = $value;
		$this->toggle["Linked"] = $linked;
	}

	function getStat() {
		return $this->stat;
	}

	function getValues($name = null, $value = null) {
		$this->stat = 0;
		$output = "";
		if (is_array($name)) {
			$names = $name;
		} else {
			$names[] = $name; 
		}
		foreach ($_GET as $k => $v) {
			if (($k != $this->toggle["Name"]) && (!in_array($k, $names))) {
				if (is_array($v)) {
					foreach ($v as $vKey => $vValue) {
						$output .= "$k" . "[$vKey]=$vValue&amp;";
					}
				} else {
					$output .= "$k=$v&amp;";
				}
			}
		}
		if (in_array($this->toggle["Linked"], $names)) {
			if ($_GET[$name] == $value) {
				if ($_GET[$this->toggle["Name"]] == $this->toggle["Value"]) {
					$output .= $this->toggle["Name"] . "=&amp;";
					$this->stat = 1;
				} else {
					$output .= $this->toggle["Name"] . "=" . $this->toggle["Value"] ."&amp;";
					$this->stat = 2;
				}
			} else {
				$output .= $this->toggle["Name"] . "=&amp;";
			}
		} else {
			$output .= $this->toggle["Name"] . "=" .
				(isset($_GET[$this->toggle["Name"]]) ?
				$_GET[$this->toggle["Name"]] : "") ."&amp;";
		}

		$output .= "{$names[0]}=$value&amp;";

		return mb_substr($output, 0, -5);
	}

	function getValuesSimple($name = null) {
		$this->stat = 0;
		$output = "";
		if (is_array($name)) {
			$names = $name;
		} else {
			$names[] = $name; 
		}
		foreach ($_GET as $k => $v) {
			if (!in_array($k, $names)) {
				if ($v) {
					$output .= "$k=$v&amp;";
				} else {
					$output .= "$k&amp;";
				}
			}
		}
		return mb_substr($output, 0, -5);
	}
	
	function getHiddenControls($dontShow) {
		$output = "";
		foreach($_GET as $k => $v) {
			if (!in_array($k, $dontShow)) {
				$output .= "<input name=\"$k\" value=\"$v\" type=\"hidden\" />\n";
			}
		}
		return $output;
	}
}