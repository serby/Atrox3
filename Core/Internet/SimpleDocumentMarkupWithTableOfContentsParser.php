<?php
/**
 * @package Core
 * @subpackage Internet
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision: 751 $ - $Date: 2008-09-29 17:49:53 +0100 (Mon, 29 Sep 2008) $
 */

/**
 * @author Paul Serby (Clock Ltd) {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision: 751 $ - $Date: 2008-09-29 17:49:53 +0100 (Mon, 29 Sep 2008) $
 * @package Base
 * @subpackage Internet
 */

class SimpleDocumentMarkupWithTableOfContentsParser {

	const MRK_PLAIN = 1;
	const MRK_OL = 2;
	const MRK_UL = 3;
	const MRK_DIV = 4;

	private $contents = array();
	private $level = array();
	private static $instanceCount = 0;
	public $instance;


	function parseDocument($text) {
		self::$instanceCount++;
		$this->instance = self::$instanceCount;

		$output = str_replace("\r", "", $text);
		$contentSectionText = "";

		$output = htmlentities($output, ENT_COMPAT, "UTF-8");

		// Parse line markup. i.e. * #
		$output = $this->parseLineMarkup($output);

		// Make the table of contents and parse headings and actions points
		$output = $this->makeContents($output);


		$lastLevel = 1;
		$contentSectionText = "";
		if (sizeof($this->contents) > 1) {
			foreach ($this->contents as $k => $v) {
				if ($lastLevel < $v["Level"]) {
					$contentSectionText .= "<ul>\n";
				}
				while ($lastLevel -- > $v["Level"]) {
					$contentSectionText .= "\t</ul>\n";
				}
				$contentSectionText .= "\t\t<li>$k. " . $v["Text"] . "</li>\n";
				$lastLevel = $v["Level"];
			}
			while (--$lastLevel > 1) {
				$contentSectionText .= "\t</ul>\n";
			}
			$contentSectionText = "<div class=\"wiki-contents\">\n\t<h2><span>Contents</span></h2>\n\t$contentSectionText</div>\n";
		}
		return "$contentSectionText\n$output";

		return trim($output);
	}

	function parseMarkup($text) {
		$searchArray = array (
			"'\'\'\'\'\'(.*?)\'\'\'\'\''is",
			"'\'\'\'(.*?)\'\'\''is",
			"'\'\'(.*?)\'\''is",
			"'\[\[image:([^|]*?)((\|(frame))|(\|(left|right))){0,2}\]\]'is",
			"'\[\[image:([^|]*?)((\|(frame))|(\|(left|right))){0,2}(\|(.*?))?\]\]'is",
			"'\[\[(http(s?)://.*?)(\|)(.*?)\]\]'i",
			"'\[\[(http(s?)://\S*?)\]\]'i",
			"'\[(http(s?)://.*?)(\|)(.*?)\]'i",
			"'\[(http(s?)://\S*?)\]'i",
			"'\[\[(mailto:.*?)(\|)(.*?)\]\]'i",
			"'\[\[(mailto:(\S*?))\]\]'i",
			"'\[(.*?)(\|)(.*?)\]'i",
			"'\[(\S*?)\]'i"
		);

		$replaceArray = array (
			"<em><strong>$1</strong></em>",
			"<em>$1</em>",
			"<strong>$1</strong>",
			"<img src=\"$1\" class=\"image-type$4$6\" title=\"Linked Image (\"$1\")\" alt=\"Linked Image (\"$1\")\" />",
			"<img src=\"$1\" class=\"image-type$4$6\" title=\"$8\" alt=\"$8\" />",
			"<a href=\"$1\" target=\"_blank\" class=\"link-external\">$4</a>",
			"<a href=\"$1\" target=\"_blank\" class=\"link-external\">$1</a>",
			"<a href=\"$1\" class=\"link-internal\">$4</a>",
			"<a href=\"$1\" class=\"link-internal\">$1</a>",
			"<a href=\"$1\" class=\"link-mail\">$3</a>",
			"<a href=\"$1\" class=\"link-mail\">$2</a>",
			"<a href=\"$1\" class=\"link-internal\">$3</a>",
			"<a href=\"$1\" class=\"link-internal\">$1</a>"
		);
		$text = preg_replace($searchArray, $replaceArray, $text);
		return trim($text);
	}

	function popStack(& $stack) {
		$stackSymbol = array_pop($stack);
		switch ($stackSymbol) {
			case self::MRK_PLAIN :
				if ($this->checkStack($stack) != self::MRK_PLAIN) {
					return "</pre>\n";
				}
				break;
			case self::MRK_OL :
				return "</ol>\n";
			case self::MRK_UL :
				return "</ul>\n";
			case self::MRK_DIV :
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
						if ($this->checkStack($stack) != self::MRK_PLAIN) {
							$currentListLevel = 0;
							$output .= $this->popEntireStack($stack);
							$output .= "<pre>\n";
							$this->pushStack($stack, self::MRK_PLAIN);
						}
						$output .= mb_substr($lines[$i], 1) . "\n";
						break;
					case "*":
					case "#":
						$level = 1;
						if ($lines[$i][0] == "#") {
							$symbol = self::MRK_OL;
							$openingTag = "<ol>\n";
						} else if ($lines[$i][0] == "*") {
							$symbol = self::MRK_UL;
							$openingTag = "<ul>\n";
						}
						while ((isset($lines[$i][$level])) && (($lines[$i][$level] == "*") || ($lines[$i][$level] == "#"))) {
							if ($lines[$i][$level] == "#") {
								$symbol = self::MRK_OL;
								$openingTag = "<ol>\n";
							} else if ($lines[$i][$level] == "*") {
							 $symbol = self::MRK_UL;
								$openingTag = "<ul>\n";
							}
							$level ++;
						}
						if (($this->checkStack($stack) != self::MRK_OL) &&  ($this->checkStack($stack) != self::MRK_UL)) {
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
		return preg_replace_callback("'^(=+)(#?)(.*?)(=+)\s*$'m", array($this, "headings"), $text);
	}

	function addToContent($text, $level = null, $number = null, $clear = false) {
		if ($text) {
			$this->contents[$number]["Text"] = $text;
			$this->contents[$number]["Level"] = $level;
		}
	}

	function headings($matches) {

		$i = mb_strlen($matches[1]);
		if (isset($this->level[$i]["Count"])) {
			$this->level[$i]["Count"]++;
		} else {
			$this->level[$i]["Count"] = 1;
		}
		if ($this->level[$i]["Count"] != null) {
			$this->level[$i + 1]["Count"] = 0;
		}

		$anchor = "";

		foreach ($this->level as $l) {
			if ($l["Count"] >= 1) {
				$anchor .= $l["Count"] . ".";
			}
		}
		//TODO: This is a hack
		$anchor = trim($anchor, ".");
		$text = $matches[3];
//		if ($matches[2] == "#") {
//			$text = $anchor . "." . $matches[3];
	//	}
		$this->addToContent("<a href=\"#I{$this->instance}-H$anchor\">{$matches[3]}</a>", $i, $anchor);
		return "<h$i>$text<a name=\"I{$this->instance}-H$anchor\"></a></h$i>";
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