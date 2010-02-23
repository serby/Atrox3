<?php
/**
 * @package Core
 * @subpackage Internet
 * @subpackage Internet
 * @copyright Clock Limited 2007
 * @version 3.0 - $Revision$ - $Date$
 */

/**
 * Include Validation.php so that ValidationControl can be extended.
 * Include Html.php so that HtmlControl can be extended.
 */
require_once("Atrox/Core/Data/Validation.php");
require_once("Atrox/Core/Internet/Html.php");

/**
 * HtmlForm class. Performs functions specific to html forms such as map file uploads
 *
 * @author Thomas Smith (Clock Ltd) {@link mailto:thomas.smith@clock.co.uk thomas.smith@clock.co.uk }
 * @copyright 2007 Clock Ltd
 * @version 0.l
 * @package Core
 * @subpackage Internet
 */

class HtmlFormControl {
	
	/**
	 * Maps the file parameters into the post variables and return the
	 * updated post variables
	 * @param Array $post Post variables from form submission
	 * @param Array $file File information from form submission
	 * @return unknown
	 */
	function mapFormSubmission($post, $files = false) {
		if ($files) {
			foreach($files as $filename => $file) {
				if (!is_array($file["error"])) {
					if ($file["error"] == 0) {
						$filename = explode("_", $filename);
						$post[$filename[0]] = array("CurrentId" => $post[$filename[0]]["Current"],
							"FileInfo"=> array(
								"Remove"=>isset($post[$filename[0]]["Remove"]),
								"Filename" => $file["name"],
								"Type" => $file["type"],
								"TempName" => $file["tmp_name"],
								"Error" => $file["error"],
								"Size" => $file["size"]
							)
						);
					} else {
						$filename = explode("_", $filename);
						$post[$filename[0]] = array("CurrentId" => $post[$filename[0]]["Current"],
							"FileInfo"=> array(
								"Remove"=>isset($post[$filename[0]]["Remove"]),
								"Filename" => "",
								"Type" => "",
								"TempName" => "",
								"Error" => "",
								"Size" => 0
							)
						);
					}
				}
			}
		}
		return $post;
	}
}