<?php
/**
 * @package Core
 * @subpackage Internet
 * @copyright Clock Limited 2007
 * @version 3.0 - $Revision$ - $Date$
 */

	define("MRK_PLAIN", 1);
	define("MRK_OL", 2);
	define("MRK_UL", 3);
	define("MRK_DIV", 4);
	
/**
 * @author Paul Serby (Clock Ltd) {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2007
 * @version 3.0 - $Revision$ - $Date$
 * @package Base
 * @subpackage Internet
 */	
class DocumentMarkupParser {
	
	function parseDocument($text) {
		$output = str_replace("\r", "", $text);
		$contentSectionText = "";
		
		$output = htmlentities($output, ENT_QUOTES, "UTF-8");

		// Make the table of contents and parse headings and actions points
		$contentSection = $this->makeContents($output);
		
		// Parse line markup. i.e. * #
		$output = $this->parseLineMarkup($output);
	
		// Parse links, images
		
		$lastLevel = 1;
		if (sizeof($contentSection) > 4) {
			foreach ($contentSection as $k => $v) {
				if ($lastLevel < $v["Level"]) {
					$contentSectionText .= "<ul>\n";
				}
				while ($lastLevel -- > $v["Level"]) {
					$contentSectionText .= "\t</ul>\n";
				}
				$contentSectionText .= "\t\t<li>$k. " . $v["Text"] . "</li>\n";
				$lastLevel = $v["Level"];
			}
			while ($lastLevel -- > 1) {
				$contentSectionText .= "\t</ul>\n";
			}
			$contentSectionText = "<div id=\"document_contents\"><div id=\"advanced\" onclick=\"return toggleVisibility('extra')\" title=\"Click to display of contents table\"><h2><span>Contents</span></h2></div><div id=\"extra\">$contentSectionText</div><div class=\"footer\"><span>&nbsp;</span></div></div>\n";
		}
		return "$contentSectionText\n$output";
	}

	function parseMarkup($text) {
		$searchArray = array (
			"'\'\'\'(.*?)\'\'\''is",
			"'\'\'(.*?)\'\''is",
			"'\[\[image:([^|]*?)((\|(frame))|(\|(left|right))){0,2}\]\]'is",
			"'\[\[image:([^|]*?)((\|(frame))|(\|(left|right))){0,2}(\|(.*?))?\]\]'is",
			"'\[\[(http(s?)://.*?)(\|)(.*?)\]\]'i",
			"'\[\[(http(s?)://\S*?)\]\]'i",
			"'\[\[(mailto:.*?)(\|)(.*?)\]\]'i",
			"'\[\[(mailto:(\S*?))\]\]'i",
			"'\[\[((contact|doc|document|project|company):(.*?))(\|)(.*?)\]\]'i",
			"'\[\[((contact|doc|document|project|company):(.*?))]\]'i"
		);

		$replaceArray = array (
			"<em>$1</em>", 
			"<strong>$1</strong>",
			//"1-$1\n2-$2\n3-$3\n4-$4\n5-$5\n6-$6\n7-$7\n8-$8\n9-$9\n",
			"<img src=\"$1\" class=\"image_type$4$6\" title=\"Linked Image (\"$1\")\" alt=\"Linked Image (\"$1\")\" />",
			"<img src=\"$1\" class=\"image_type$4$6\" title=\"$8\" alt=\"$8\" />",		
			"<a href=\"$1\" target=\"_blank\" class=\"link_external\">$4</a>",
			"<a href=\"$1\" target=\"_blank\" class=\"link_external\">$1</a>",
			"<a href=\"$1\" target=\"_blank\" class=\"link_mail\">$3</a>",
			"<a href=\"$1\" target=\"_blank\" class=\"link_mail\">$2</a>",				
			"<a href=\"/search.php?Type=$2&amp;Search=$3\" class=\"link_$2\">$5</a>",
			"<a href=\"/search.php?Type=$2&amp;Search=$3\" class=\"link_$2\">$3</a>");
		$text = preg_replace($searchArray, $replaceArray, $text);
			
		return $text;
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
					case " " :
						if ($this->checkStack($stack) != MRK_PLAIN) {
							$currentListLevel = 0;
							$output .= $this->popEntireStack($stack);
							$output .= "<pre>\n";
							$this->pushStack($stack, MRK_PLAIN);
						}
						$output .= mb_substr($lines[$i], 1) . "\n";
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
					case "@" :
					case "=" :
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
	
	function makeContents(& $text) {
		$output = "";
		$this->contents(null, null, true);
		$text = preg_replace_callback("'^(=+)(#?)(.*?)(=+)\s*$'m", array ("DocumentMarkupParser", "headings"), $text);
		$text = preg_replace_callback("'^@{1,3}\s*(.*?)$'m", array ("DocumentMarkupParser", "actions"), $text);
		return $this->contents(null);
	}

	function contents($text, $level = null, $number = null, $clear = false) {
		static $contents;
		if ($clear) {
			$contents = array ();
		}
		if ($text) {
			$contents[$number]["Text"] = $text;
			$contents[$number]["Level"] = $level;
		}
		return $contents;
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
				$anchor .= $l["Count"].".";
			}
		}
		$anchor = mb_substr($anchor, 0, -1);
		$text = $matches[3];
		if ($matches[2] == "#") {
			$text = $anchor.". ".$matches[3];
		}
		DocumentMarkupParser::contents("<a href=\"#Heading_$anchor\">{$matches[3]}</a>", $i, $anchor);
		return "<h$i>$text<a name=\"Heading_$anchor\"></a></h$i>";
	}

	function actions($matches) {
		static $actionNumber;
		$anchor = "Action_".$actionNumber ++;

		if ($matches[1][1] == "!") {
			$actionNumber = 1;
			$text = mb_substr($matches[1], 3);
		}
		
		$firstSpacePosition = mb_strpos($matches[1], " ");
		$text = mb_substr($matches[1], $firstSpacePosition + 1);
		$actionToken = mb_substr($matches[1], 1, $firstSpacePosition);
		$styleClass = "actionpoint_new";
		$status = "";			
		switch ($matches[1][0]) {
			case "~" :
				$styleClass = "actionpoint_complete";
				$status = " <span class=\"status\">(Complete)</span>";
				break;
			case "*" :
				$styleClass = "actionpoint_incomplete";
				$status = " <span class=\"status\">(Incomplete)</span>";
				break;
			case "^" :
				$styleClass = "actionpoint_onhold";
				$status = " <span class=\"status\">(On-hold)</span>";
				break;					
			case "?" :
				$styleClass = "actionpoint_cancelled";
				$status = " <span class=\"status\">(Cancelled)</span>";
				break;
			case "%" :
				$styleClass = "actionpoint_partial";
				$status = " <span class=\"status\">(Partially Completed - {$actionToken}%)</span>";
				break;
			case "!":
				$actionNumber = 1;
				break;					
			default :
				$text = $matches[1];
				$status = "";
				break;
		}
		$text = "<strong>Action ". $actionNumber . ".</strong> " . $text . $status;

		return "<p class=\"$styleClass\">$text<a name=\"$anchor\"></a></p>";
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