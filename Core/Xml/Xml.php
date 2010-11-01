<?php

/**
 * @package Core
 * @subpackage Xml
 * @copyright Clock Limited 2010
 * @version 3.2
 */

/**
 * Parser XML into more workable datastructures
 *
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2
 * @package Core
 * @subpackage Xml
 */
class XmlParser {

	var $object = array();
	var $resParser;
	var $strXmlData;

	/**
	 * Converts a SimpleXMLElement object to an Array
	 * @author Dom Udall {@link mailto:dom.udall@clock.co.uk dom.udall@clock.co.uk }
	 * @param Object $xmlObject The SimpleXMLElement object to be converted to an Array
	 * @return Array $xmlArray SimpleXMLElement as an Array
	 */
	function simplexmlToArray($xmlObject) {
		if (get_class($xmlObject) == 'SimpleXMLElement') {
			$originalXmlObject = $xmlObject;
			$xmlObject = get_object_vars($xmlObject);
		}
		if (is_array($xmlObject)) {
			if (count($xmlObject) == 0) return (string) $originalXmlObject; // for CDATA
			foreach($xmlObject as $key => $value) {
				$xmlArray[$key] = $this->simplexmlToArray($value);
				if (($xmlObject[$key] instanceof SimpleXMLElement) && (count($xmlObject[$key]->attributes())) > 0) {
					foreach($xmlObject[$key]->attributes() as $attributeKey => $value) {
						$xmlArray[$key]["attributes"][$attributeKey] = (string)$value;
					}
				}
			}
		} else {
			$xmlArray = (string) $xmlObject;
		}
		return $xmlArray;
	}

	/**
	 * Parses a XML file into an Array
	 * @param String $path The path of the file to parse
	 * @return Array An array containing the data from the XML
	 */
	function fileToArray($path) {
		if (file_exists($path)) {
			return $this->toArray(file_get_contents($path));
		} else {
			trigger_error("Unable to open XML file '{$path}', file not found.");
		}
		return false;
	}

	/**
	 * Parses a XML string into an Array
	 * @param String $inputXml XML packet to be parsed into an array
	 * @return Array An array containing the data from the XML
	 */
	function toArray($inputXml) {
		$this->parent = &$this->out;
		$this->resParser = xml_parser_create ();
		xml_set_object($this->resParser, $this);
		xml_set_element_handler($this->resParser, "tagOpen", "tagClosed");

		xml_set_character_data_handler($this->resParser, "tagData");

		$this->strXmlData = xml_parse($this->resParser, $inputXml);
		if(!$this->strXmlData) {
			die(sprintf("XML error: %s at line %d",
			xml_error_string(xml_get_error_code($this->resParser)),
			xml_get_current_line_number($this->resParser)));
		}
		xml_parser_free($this->resParser);
		return $this->object;
	}

	/**
	 * Internal Callback used by the XML parser when a tag is opened
	 */
	function tagOpen($parser, $name, $attrs) {

		$tag = null;
		if (sizeof($attrs) <= 0) {
			$attrs = null;
		} else {
			$tag["Attributes"] = $attrs;
		}
		$name = mb_strtolower($name);
		$this->object["$name"][] = &$tag;
		$this->tmp[] = &$this->object;
		$this->object = &$this->object["$name"][sizeof($this->object["$name"]) - 1];
	}

	/**
	 * Internal Callback used by the XML parser for handling the data
	 */
	function tagData($parser, $tagData) {
		if (isset($this->object["Data"])) {
			$this->object["Data"] .= "$tagData";
		} else {
			$this->object["Data"] = $tagData;
		}
	}

	/**
	 * Internal Callback used by the XML parser when a tag is closed
	 */
	function tagClosed($parser, $name) {
		$this->object = &$this->tmp[sizeof($this->tmp) - 1];
		array_pop($this->tmp);
	}

	/**
	 * Converts a multi-dimensioal array into XML
	 * @param Array $array Array to be converted
	 * @param String $elementName The XML element name
	 * @param Integer $depth Current depth of the recursion
	 * @return String XML repersentation of the Array
	 */
	function arrayToXml($array, $elementName = "data", $depth = 0) {
		$return = "";
		$elementName = mb_strtolower($elementName);
		foreach ($array as $key => $value) {
			if (is_array($value)) {
				$return .= "<{$elementName} entity=\"{$key}\">" . $this->arrayToXml($value, $elementName, ++$depth) . "</{$elementName}>";
			} else {
				$return .= "<{$elementName} entity=\"{$key}\">{$value}</{$elementName}>";
			}
		}
		return $return;
	}
}
