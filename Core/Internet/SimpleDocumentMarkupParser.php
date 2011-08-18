<?php
/**
 * @package Core
 * @subpackage Internet
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision: 751 $ - $Date: 2008-09-29 17:49:53 +0100 (Mon, 29 Sep 2008) $
 */

	define("MRK_PLAIN", 1);
	define("MRK_OL", 2);
	define("MRK_UL", 3);
	define("MRK_DIV", 4);

/**
 * @author Paul Serby (Clock Ltd) {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision: 751 $ - $Date: 2008-09-29 17:49:53 +0100 (Mon, 29 Sep 2008) $
 * @package Base
 * @subpackage Internet
 */
class SimpleDocumentMarkupParser {

	function __construct() {
		$this->uid = md5(uniqid(mt_rand(), true));
	}

	function parseDocument($text) {
		$output = str_replace("\r", "", $text);
		$contentSectionText = "";

		$output = htmlentities($output, ENT_COMPAT, "UTF-8");

		// Parse line markup. i.e. * #
		$output = $this->parseLineMarkup($output);

		// Make the table of contents and parse headings and actions points
		$output = $this->makeContents($output);

		return trim($output);
	}

	function parseMarkup($text) {
		
		$text = preg_replace_callback("'\[\[image:([^|]+)(?:(\|(frame))?(\|(left|centre|right))?(\|([^]]*))?)\]\]'is",
			array($this, 'parseStaticImageMarkup'), $text);
		
		$text = preg_replace_callback("'\[\[dynamic-image:([^|]*)(?:\|([0-9]+)\|([0-9]+))(?:\|(left|right|centre|none))(?:\|([^]]*))?\]\]'is",
			array($this, 'parseDynamicImageMarkup'), $text);

		$searchArray = array (
			"'\'\'\'\'\'(.*?)\'\'\'\'\''is",
			"'\'\'\'(.*?)\'\'\''is",
			"'\'\'(.*?)\'\''is",

			"'\[\[\[(http(s?)://.*?)(\s)(.*?)\]\]\]'i",
			"'\[\[\[(http(s?)://\S*?)\]\]\]'i",
			"'\[\[\[(.*?)(\s)(.*?)\]\]\]'i",
			"'\[\[\[(.*?)\]\]\]'i",

			"'\[\[(http(s?)://.*?)(\s)(.*?)\]\]'i",
			"'\[\[(http(s?)://\S*?)\]\]'i",
			"'\[\[(.*?)(\s)(.*?)\]\]'i",
			"'\[\[(.*?)\]\]'i",

			"'\[\[(mailto:.*?)(\s)(.*?)\]\]'i",
			"'\[\[(mailto:(\S*?))\]\]'i",
			"'\{\{(.*?)(\|)(.*?)\}\}'i"
		);



		$replaceArray = array (
			"<em><strong>$1</strong></em>",
			"<em>$1</em>",
			"<strong>$1</strong>",

			"<a href=\"$1\" target=\"_blank\" class=\"link-external\">$4</a>",
			"<a href=\"$1\" target=\"_blank\" class=\"link-external\">$1</a>",
			"<a href=\"$1\" target=\"_blank\" class=\"link-external\">$3</a>",
			"<a href=\"$1\" target=\"_blank\" class=\"link-external\">$1</a>",

			"<a href=\"$1\" class=\"link-internal\">$4</a>",
			"<a href=\"$1\" class=\"link-internal\">$1</a>",
			"<a href=\"$1\" class=\"link-internal\">$3</a>",
			"<a href=\"$1\" class=\"link-internal\">$1</a>",
			"<a href=\"$1\" class=\"link-mail\">$3</a>",
			"<a href=\"$1\" class=\"link-mail\">$2</a>",
			"<span class=\"$1\">$3</span>"
		);

		$text = preg_replace($searchArray, $replaceArray, $text);

		// This could go in to make html valid. But needs testing.
//		if ($text === "") {
//			$text = "&nbsp;";
//		}
		return trim($text);
	}
	
	/**
	 * Parses image markup and generates an HTML image tag.
	 *
	 * The following optional parameters may be passed, in this order:
	 * frame
	 * 	If set, draws a border around the image.
	 * alignment (left|right|centre)
	 * 	If set, floats the image to the horizontal alignment specified.
	 * altText
	 * 	If set, replaces the default alt text of "" with what has been supplied.
	 * 	All characters except a right square brace may be used.
	 *
	 * @param array Array containing regex matches (@see this#parseMarkup)
	 */
	protected function parseStaticImageMarkup($matches) {
		$src = $matches[1];
		$hasFrame = ((isset($matches[3]) && $matches[3] !== "") ? true : null);
		$alignment = ((isset($matches[5]) && $matches[5] !== "") ? $matches[5] : null);
		$altText = ((isset($matches[7]) && $matches[7] !== "") ? $matches[7] : "");
		
		$cssClass = "";
		if ($hasFrame || $alignment) {
			$cssClass = "class=\"";
			if ($hasFrame) {
				$cssClass .= "image-type-frame ";
			}
			if ($alignment) {
				$cssClass .= "image-type-" . $alignment;
			}
			$cssClass .= "\"";
		}
		return "<img src=\"" . $src . "\"" . $cssClass . " title=\"" . $altText . "\" alt=\"" . $altText . "\" />";
	}

	/**
	 * The dynamic image markup will generate html source by providing binary IDs with a specified width and height.
	 * @param array $matches
	 * @return string HTML Source
	 */
	 function parseDynamicImageMarkup($matches) {
		$binaryId = $matches[1];
		$width = ($matches[2] ? $matches[2] : null);
		$height = ($matches[3] ? $matches[3] : null);
		$float = ($matches[4] != "None") ? "class=\"image-type-" . strtolower($matches[4]) : "";
		$alt = $matches[5];

		$binaryControl = CoreFactory::getBinaryControl();
		if ($binary = $binaryControl->item($binaryId)) {
			$htmlControl = CoreFactory::getHtmlControl();
			$binaryLocation = $htmlControl->getImageBinaryLocation($binary, $width, $height);

			return $replaceString = "<img src=\"" . $binaryLocation . "\" " . $float . "\" title=\"" . $alt . "\" alt=\"" . $alt . "\" />";
		}
	}

	function popStack(& $stack) {
		$stackSymbol = array_pop($stack);
		switch ($stackSymbol) {
			case MRK_PLAIN :
				if ($this->checkStack($stack) != MRK_PLAIN) {
					return "</pre>\n";
				}
				break;
			case MRK_OL :
				return "</ol>\n";
			case MRK_UL :
				return "</ul>\n";
			case MRK_DIV :
				return "</div>\n";
		}
		return false;
	}

	function popEntireStack(& $stack) {
		$output = "";
		while ($pop = $this->popStack($stack)) {
			$output .= $pop;
		}
		return $output;
	}

	function pushStack(& $stack, $symbol) {
		array_push($stack, $symbol);
	}

	function checkStack(& $stack) {
		if (isset ($stack[sizeof($stack) - 1])) {
			return $stack[sizeof($stack) - 1];
		}
	}

	function parseLineMarkup($text) {
		$lines = explode("\n", $text);
		$stack = array ();
		$currentListLevel = 0;
		$output = "";
		for ($i = 0; $i < count($lines); $i ++) {
			if (isset ($lines[$i][0])) {
				switch ($lines[$i][0]) {
					case ":" :
						$currentListLevel = 0;
						$output .= $this->popEntireStack($stack);
						$output .= "<p>&nbsp;&nbsp;" . $this->parseMarkup(mb_substr($lines[$i], 1)) . "</p>\n";
						break;
					case "*":
					case "#":
						$level = 1;
						if ($lines[$i][0] == "#") {
							$symbol = MRK_OL;
							$openingTag = "<ol>\n";
						} else if ($lines[$i][0] == "*") {
							$symbol = MRK_UL;
							$openingTag = "<ul>\n";
						}
						while ((isset($lines[$i][$level])) && (($lines[$i][$level] == "*") || ($lines[$i][$level] == "#"))) {
							if ($lines[$i][$level] == "#") {
								$symbol = MRK_OL;
								$openingTag = "<ol>\n";
							} else if ($lines[$i][$level] == "*") {
							 $symbol = MRK_UL;
								$openingTag = "<ul>\n";
							}
							$level ++;
						}
						if (($this->checkStack($stack) != MRK_OL) &&  ($this->checkStack($stack) != MRK_UL)) {
							$currentListLevel = 0;
							$output .= $this->popEntireStack($stack);
						}
						if ($level > $currentListLevel) {
							$output .= $openingTag;
							$this->pushStack($stack, $symbol);
						} else
							if ($level < $currentListLevel) {
								$currentListLevel --;
								$output .= $this->popStack($stack);
							} else
								if ($this->checkStack($stack) != $symbol) {
									$output .= $this->popStack($stack);
									$output .= $openingTag;
									$this->pushStack($stack, $symbol);
								}
						$output .= "<li>" . $this->parseMarkup(mb_substr($lines[$i], $level)) . "</li>\n";
						$currentListLevel = $level;
						break;
					case "=":
						$currentListLevel = 0;
						$output .= $this->popEntireStack($stack);
						$output .= $this->parseMarkup($lines[$i]) . "\n";
						break;
					default:
						$currentListLevel = 0;
						$output .= $this->popEntireStack($stack);
						$output .= "<p>". $this->parseMarkup($lines[$i]) ."</p>\n";
				}
			}
		}
		$output .= $this->popEntireStack($stack);
		return $output;
	}

	function makeContents($text) {
		$output = "";
		return preg_replace_callback("'^(=+)(#?)(.*?)(=+)\s*$'m", array($this, "headings"), $text);
	}

	function headings($matches) {
		static $level;
		$i = mb_strlen($matches[1]);
		if (isset($level[$i]["Count"])) {
			$level[$i]["Count"]++;
		} else {
			$level[$i]["Count"] = 1;
		}
		if ($level[$i]["Count"] != null) {
			$level[$i + 1]["Count"] = 0;
		}

		$anchor = "";

		foreach ($level as $l) {
			if ($l["Count"] > 0) {
				$anchor .= $l["Count"] . ".";
			}
		}
		$text = $matches[3];
		if ($matches[2] == "#") {
			$text = $anchor . ". " . $matches[3];
		}
		return "<h$i>$text</h$i>";
	}
}

//			$text = "==Hello this is a test==\n" .
//			"==Plan==\n" .
//			" 123\n" .
//			" 456\n" .
//			" 789\n" .
//			"===# Lists===\n" .
//			"# [https://www.apple.com Apple]\n" .
//			"## Granny Smith\n" .
//			"## Brayburn\n" .
//			"# Pear\n" .
//			"# Carrot\n" .
//			"==New Thing==\n" .
//			"@ Hello\n" .
//			"===New Sub thing===\n";
//
//
//		echo "\n\nMarkup:\n\n\n";
//
//		$parser = new ClockDocumentParser();
//		echo $parser->parseDocument($text);
