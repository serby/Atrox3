<?php
/**
 * @package Core
 * @subpackage Internet
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 */

/**
 * Html Class for generating Html elements dynamically.
 *
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Core
 * @subpackage Internet
 */

// Define File Sizing Units
define("FS_KB", 1000);
define("FS_MB", 1000000);
define("FS_KIB", 1024);
define("FS_MIB", 1048576);

class HtmlControl {

	function getRandomElement($elements, $id = "1", $siteWide = false, $keepForSession = false, $new = false) {
		if ((!isset($elements)) || (sizeof($elements) <= 0)) {
			return "";
		}

		if ($siteWide) {
			$uid = "site_$id";
		} else {
			$uid = $_SERVER["REQUEST_URI"] . "_" . $id;
		}

		if ((!$new) && ($keepForSession) && (isset($_SESSION["__RandomElementID_$uid"]))) {
			$keys = array_keys($elements);
			return $elements[$keys[$_SESSION["__RandomElementID_$uid"]]];
		}
		$count = sizeof($elements) - 1;
		if (!isset($_SESSION["__RandomElementID_$uid"])) {
			$_SESSION["__RandomElementID_$uid"] = "";
		}
		$rnd = rand(0, $count);
		while (($rnd = rand(0, $count)) == $_SESSION["__RandomElementID_$uid"]) {}
		$_SESSION["__RandomElementID_$uid"] = $rnd;
		$keys = array_keys($elements);
		return $elements[$keys[$rnd]];
	}

	/*
	 * Retuns the html for a img with a random image taken from an array of images.
	 */
	function createRandomImage(&$images, $id = "1", $class = "") {
		$imageData = $this->getRandomElement($images, $id);
		return "<img id=\"$id\" class=\"$class\" src=\"$imageData[Source]\" title=\"$imageData[Description]\" alt=\"$imageData[Description]\" />";
	}

	/**
       * Create a hidden form value with a unique number. This can then be
	 * used to ensure that the same form is not submitted twice.
	 * @return A string Containing the HTML control
	 */
	function insertDoubleSubmitField() {
		return "<input name=\"_DoubleSubmissionCheck\" type=\"hidden\" value=\"" .uniqid(rand()). "\" />";
	}

	/**
	 * @return array An array of the current Path split on '/'
	 */
	function getPath($path, $part = false) {
		$this->path = $path;
		$this->pathParts = explode("/", trim($path, "/"));
		if (is_int($part)) {
			if ($part >= 0) {
				$position = $part;
			} else {
				$position = count($this->pathParts) + $part;
			}
			if(isset($this->pathParts[$position])) {
				return $this->pathParts[$position];
			} else {
				return false;
			}
		}
		return $this->pathParts;
	}

	/**
     * Construct a string containing list items from the passed in array. For a list
	 * with different values i.e. value is different from output uses a multidimesional array.
	 * @param $list The array to take the elements from
	 * @param $selected the value selected in the list
	 * @param $compareElement the element in the array to check
	 * @param $pattern a pattern for the list content
	 * @param $class the class for a selected item
	 * @return A string with the list items in
	 */
	function makeList($list, $selected = null, $compareElement = 0, $pattern = "%s", $class = "activeitem") {
		$newList = "";
		if (!is_array($list)) {
			return false;
		}
		if ((isset($list[0])) && (is_array($list[0]))) {
			foreach ($list as $value) {
				if ($value[$compareElement] == $selected) {
					$newList .= "<li class=\"{$class}\">" . vsprintf($pattern, $value) . "</li>\n";
				} else {
					$newList .= "<li>" . vsprintf($pattern, $value) . "</li>\n";
				}
			}
		} else {
			foreach ($list as $value) {
				if ($value == $selected) {
					$newList .= "<li class=\"{$class}\">" . $value . "</li>\n";
				} else {
					$newList .= "<li>" . $value . "</li>\n";
				}
			}
		}
		return "$newList\n";
	}

	function encodeEntities($string, $quote_style = ENT_COMPAT) {
		 $trans = get_html_translation_table(HTML_ENTITIES, $quote_style);
		 foreach ($trans as $key => $value)
				$trans[$key] = "&#" . ord($key) . ";";
		 return strtr($string, $trans);
	}

	/**
	 * Returns an image representing the boolean value of the expression
	 * supplied.
	 * @param $exp Expression to evaluate. t,T, 1,yes, or a true valueYear
	 * will return a true image everything else will return the false image.
	 * @return Html for the image.
	 */
	function booleanImage($exp) {
		if (($exp =="t") || ($exp =="T")  || ($exp =="1") || ($exp) ||
				($exp =="yes")) {
			return "<img src=\"/image/icon/yes.gif\" title=\"Yes\" alt=\"Yes\" \>";
		} else {
			return "<img src=\"/image/icon/no.gif\" title=\"No\" alt=\"No\" \>";
		}
	}

	/**
	 * Returns an image representing the current sort state
	 * @param $exp Expression to evaluate.
	 * @return Html for the image.
	 */
	function getSortStateImage($stat) {
		switch($stat) {
			case 1:
				return "<img src=\"/image/default/up.gif\" title=\"Sorted " .
				"Ascending\" alt=\"Sorted Ascending\" \>";
			case 2:
				return "<img src=\"/image/default/down.gif\" title=\"Sorted " .
				"Descending\" alt=\"Sorted Descending\" \>";
		}
		return "";
	}


	function inPath($path, $return) {
		$pathLen = mb_strlen($path);
		if (mb_substr($_SERVER["REQUEST_URI"], 0, $pathLen) == $path) {
			return $return;
		}
		return "";
	}

	function arrayToHtml($array, $level=1, $maxLevel=3) {
		$level++;
		$output  = "<table width=\"100%\" border=\"0\" cellpadding=\"3\"
				cellspacing=\"1\" bgcolor=\"#000000\" bordercolor=\"#000000\">\n";
		foreach($array as $key=>$value) {
			if ($key != "GLOBALS") {
				$output  .= "<tr bgcolor=\"#FFFFCC\"><td valign=\"top\" width=\"200\">
						<font size=\"2\" face=\"Arial, Helvetica, sans-serif\">$$key
						</font></td><td>";
				if ((is_array($value)) && ($level <= $maxLevel)) {
							$output .= $this->arrayToHtml($value, $level, $maxLevel);
				} else if (is_object($value)) {
						$value = print_r($value, true);
						$output .= "<font size=\"2\" face=\"Arial, Helvetica,
								sans-serif\">" .wordwrap(nl2br(htmlentities("$value")), 75,
								"<br/>\n", true). "</font>";
				} else {
						$output .= "<font size=\"2\" face=\"Arial, Helvetica,
								sans-serif\">" .wordwrap(htmlentities("$value"), 75, "<br/>\n",
								true). "</font>";
				}
				$output .= "</td></tr>\n";
			}
		}
		$output .= "</table>\n";
		return $output;
	}

	function formatCurrency($value) {
		return number_format($value, 2);
	}

	function formatDate($date, $offset=0, $format="%d-%b-%Y") {
		if ($date == "") {
			return "Not Set";
		}

		if (($date == "0000-00-00") || ($date == "")) {
			return;
		} else if (!is_int($date)) {
			$timestamp = $this->dateToInt($date);
		}
		$timestamp += $offset;
		return strftime($format, $timestamp);
	}

	//All binary functions need to be redone to work with hash value

/**
 * Function to perform various operations to binary images
 * Adds extra functionaility to existing function
 * @param unknown_type $binary
 * @param int $width
 * @param int $height
 * @param boolean $withSiteAdress
 * @param boolean $crop
 * @param boolean $watermark
 * @return string
 */
	function getImageBinaryLocation($binary, $width = null, $height = null, $withSiteAdress = false, $crop = true,
		$watermark = false, $wOffset = null, $hOffset = null, $srcWidth = null, $srcHeight = null, $includeModTime = false) {
		if ($binary) {
			$application = CoreFactory::getApplication();
			$dimensions = $width . "x" . $height . "_";

			$imageName = $dimensions . $binary->get("Filename");

			$siteFriendlyImageName = htmlentities($imageName);

			$imagePath = $application->registry->get("Cache/Binary/Path") . "/" . $binary->get("HashValue") . "/" . $imageName;
			if (!is_file($imagePath)) {
				@mkdir($application->registry->get("Cache/Binary/Path") . "/" . $binary->get("HashValue"));
 				$binaryControl = CoreFactory::getBinaryControl();
 				$imageControl = CoreFactory::getImageControl();
				if ($wOffset !== null || $hOffset !== null) {
					if ($binaryControl->outputToFile($binary, $imagePath)) {
						$imageControl->cropXY($imagePath, $imagePath, $width, $height, $wOffset, $hOffset, $srcWidth, $srcHeight);
					}
				}
				else if ($binaryControl->outputToFile($binary, $imagePath)) {
					$imageControl->resizeAndCrop($imagePath, $imagePath, $width, $height, false, !$crop);
				}
				if ($watermark) {
					$imageControl->watermark($imagePath, $imagePath);
				}

			}
			$path = "/get-binary.php?Binary=" . $binary->get("HashValue") . "{$imageName}";

			if ($binary->get("IsPublic") == "t") {
				$path = $application->registry->get("Cache/Binary/Path/Web", "/resource/binary/cache/") . $binary->get("HashValue") . "/{$siteFriendlyImageName}";
			}

			if ($includeModTime) {
				$path .= "?m=" . filemtime($imagePath);
			}

			if ($withSiteAdress) {
				return $application->registry->get("Site/Address") . $path;
			} else {
				return $path;
			}
		}
	}

	/**
	 * Used to get the image dimensions of a specified image input
	 * @author Tom Gallacher <tom.gallacher@clock.co.uk>
	 * @param unknown_type $binary
	 * @return Array <width, height>
	 */
	public function getImageDimensions($binary) {

		$application = CoreFactory::getApplication();
		$imageName = $binary->get("Filename");
		$imagePath = $application->registry->get("Binary/Path") . "/" . $binary->get("HashValue") . "/" . $imageName;
		$size = getimagesize($imagePath);
		$imageDimension = array("width" => $size[0],"height" => $size[1]);

		return $imageDimension;
	}

	function showImageBinary($binary, $alt = null, $width = null, $height = null, $class = null, $crop = true,
		$watermark = false, $wOffset = null, $hOffset = null, $srcWidth = null, $srcHeight = null) {

		if ($binary) {

			$imageLocation = $this->getImageBinaryLocation($binary, $width, $height, false, $crop, $watermark, $wOffset,
			$hOffset, $srcWidth, $srcHeight, true);

			return "<img" . ($class != null ? " class=\"$class\"":"") . ($width != null ? " width=\"$width\"":"") .
			($height != null ? " height=\"$height\"":"") . " src=\"" . $imageLocation . "\" alt=\"$alt\" title=\"$alt\" />";
		}
	}

	/**
	 * Used to clear the binary image cache, this is needed to allow images that are being cropped
	 * to be updated in the cache.
	 * @author Tom Gallacher <tom.gallacher@clock.co.uk>
	 * @param unknown_type $binary
	 * @param int $height
	 * @param int $width
	 * @return null
	 */
	public function clearImageBinaryCache($binary, $height, $width)	{

		$application = CoreFactory::getApplication();
		$dimensions = $width . "x" . $height . "_";
		$imageName = $dimensions . $binary->get("Filename");

		$siteFriendlyImageName = htmlentities($imageName);
		$imagePath = $application->registry->get("Cache/Binary/Path") . "/" . $binary->get("HashValue") . "/" . $imageName;
		if (is_file($imagePath)) {
			unlink($imagePath);
		}
	}

	/**
	 * Take a Binary DataEntity and returns the site path of the file
	 *
	 * @param DataEntity $binary
	 * @return string The site path of the file
	 */
	function getBinaryLocation($binary) {
		if ($binary) {
			$application = CoreFactory::getApplication();
			$filePath = $application->registry->get("Binary/Path/Web", "/resource/binary/binary/");
			return $filePath . "/" . $binary->get("HashValue") . "/" . $binary->get("Filename") ;
		}
	}

	function createFileDownloadLink($filePath, $sitePath, $description = "", $sizeIn = FS_MB, $class = "attachment",
		$showFileName = true, $showFileSize = true, $showMimeType = true, $fileNameOverride = null) {

		if (!file_exists($filePath)) {
			return;
		}

		$fileSize = filesize($filePath);

		$application = CoreFactory::getApplication();
		if ($fileNameOverride) {
			$fileName = $fileNameOverride;
		} else {
			$fileName = basename($filePath);
		}
		// Default variables to nothing
		$unitAbbreviation = "";
		switch ($sizeIn) {
			case FS_KB:
				$unitAbbreviation = "kB";
				break;
			case FS_MB:
				$unitAbbreviation = "MB";
				break;
			case FS_KIB:
				$unitAbbreviation = "kiB";
				break;
			case FS_MIB:
				$unitAbbreviation = "MiB";
				break;
		}

		if ($showFileSize) {
			$actualFileSize = $fileSize / $sizeIn;
			if (round($actualFileSize, 2)	<= 0) {
				$actualFileSize = $fileSize;
				$unitAbbreviation = "bytes";
			}
			$fileSize = rtrim(rtrim(number_format($actualFileSize, 2), "0"), ".") . " " . $unitAbbreviation;
		}

		$iconType = str_replace(".", "", strrchr($fileName, "."));

		switch ($iconType) {
			case "xls":
				$mimeInfo = "Microsoft Excel Spreadsheet";
				break;
			case "zip":
				$mimeInfo = "Compressed File";
				break;
			case "wmv":
				$mimeInfo = "Windows Media Video";
				break;
			case "wma":
				$mimeInfo = "Windows Media Audio";
				break;
			case "wav":
				$mimeInfo = "Wave Audio File";
				break;
			case "txt":
				$mimeInfo = "Text Document";
				break;
			case "tiff":
				$mimeInfo = "TIFF Image";
				break;
			case "rar":
				$mimeInfo = "RAR Compressed File";
				break;
			case "psd":
				$mimeInfo = "Adobe Photoshop Document";
				break;
			case "ppt":
				$mimeInfo = "Microsoft Power Point Presentations";
				break;
			case "png":
				$mimeInfo = "PNG Image";
				break;
			case "pdf":
				$mimeInfo = "Adobe PDF File";
				break;
			case "mpeg":
				$mimeInfo = "MPEG Video File";
				break;
			case "flv":
				$mimeInfo = "FLV Video File";
				break;
			case "mp3":
				$mimeInfo = "MP3 Audio File";
				break;
			case "mov":
				$mimeInfo = "Quicktime Movie";
				break;
			case "jpeg":
			case "jpg":
				$mimeInfo = "JPG Image";
				break;
			case "html":
				$mimeInfo = "HTML Document";
				break;
			case "gif":
				$mimeInfo = "GIF Image";
				break;
			case "doc":
				$mimeInfo = "Microsoft Word Document";
				break;
			case "ai":
				$mimeInfo = "Adobe Illustrator Document";
				break;
			case "csv":
				$mimeInfo = "Comma Seperated Values File";
				break;
			default:
				$mimeInfo = "Unknown File Type";
				$iconType = "default";
		}

		$output = "<p ";
		if ($class != "") {
			$output .= "class=\"{$class} ";
		}
		$output .= "download-{$iconType}\"><a href=\"" . $sitePath . "\">" . $fileName . "";
		if ($showFileSize) {
			$output .= " <em>(" . $fileSize . ")</em>";
		}
		$output .= "</a>";
		if ($showMimeType) {
			$output .= " <span>" . $mimeInfo . "</span>";
		}
		$output .= "</p>";

		return $output;
	}


	function createBinaryDownloadLink($binary, $description = "", $sizeIn = FS_MB, $class = "download",
		$showFileName = true, $showFileSize = true, $showMimeType = true, $fileNameOverride = null) {
		if ($binary) {
			$application = CoreFactory::getApplication();
			$filePath = $application->registry->get("Binary/Path") . "/" . $binary->get("HashValue") . "/" . $binary->get("Filename");
			$sitePath = $application->registry->get("Binary/Path/Web") . "/" . $binary->get("HashValue") . "/" . $binary->get("Filename");

			return $this->createFileDownloadLink($filePath, $sitePath, $description,
				$sizeIn, $class, $showFileName, $showFileSize, $showMimeType, $fileNameOverride);
		}
	}

	/**
	 *
	 * @deprecated Use createBinaryDownloadLink Instead
	 * @see createBinaryDownloadLink
	 */
	function showAttachmentLinkWithIcon($binary, $description = "", $sizeIn = FS_MB, $class = "download", $showFileName = true, $showFileSize = true, $showMimeType = true) {
		return $this->createBinaryDownloadLink($binary, $description,
				$sizeIn, $class, $showFileName, $showFileSize, $showMimeType);
	}

	function dateToInt($time) {
		$dateArray = mb_split("[- :\+]", $time);
		$min = isset($dateArray[4]) ? (int)$dateArray[4] : 0;
		$hour = isset($dateArray[3]) ? (int)$dateArray[3] : 0;
		$day = isset($dateArray[2]) ? (int)$dateArray[2] : 0;
		$month = isset($dateArray[1]) ? (int)$dateArray[1] : 0;
		$year = isset($dateArray[0]) ? (int)$dateArray[0] : 0;
		return mktime($hour, $min, 0, $month, $day, $year);
	}

	function formatDateTime($date, $offset=0) {
		return $this->formatDate($date, $offset, "%d-%b-%Y @ %H:%M");
	}

	function formatLongDate($date, $offset=0) {
		return $this->formatDate($date, $offset, "%A %d, %B %Y");
	}

	function formatLongDateTime($date, $offset=0) {
		return $this->formatDate($date, $offset, "%A %d %B %Y @ %H:%M");
	}

	function formatTime($date, $offset=0) {
		return $this->formatDate($date, $offset, "%H:%M");
	}

	function createMediaVaultControl($controlId) {
		$mediaVaultControl = BaseFactory::getMediaVaultControl();
		$mediaVaultItemControl = BaseFactory::getMediaVaultItemControl();
?>
		<div class="mediavault_toolbar">
			<h3 class="title">Add Promotion Item</h3>
			<p class="title">Click on any Promotion Items to insert it into your BODY text</p>
			<div class="mediavault-items">

<?php
		while ($mediaVault = $mediaVaultControl->getNext()) {
			$selected = $mediaVault->get("MediaVaultItemId");
?>
				<p class="description" title="Insert Tag" onclick="doCustomTag('<?php echo $controlId; ?>', '[MEDIA|LEFT|<?php echo $mediaVault->get("Title"); ?>]', '');"><?php echo $mediaVault->get("Title");?> (Active Item)</p>
				<ul>
<?php
			$mediaVaultItemControl->retrieveForMediaVault($mediaVault);
			while ($mediaVaultItem = $mediaVaultItemControl->getNext()) {
?>
				<li title="Insert Tag" onclick="doCustomTag('<?php echo $controlId; ?>', '[MEDIA|LEFT|<?php echo $mediaVault->get("Title"); ?>/<?php echo $mediaVaultItem->get("Title"); ?>]', '');">
					<span class="preview">
						<?php echo $this->showImageBinary($mediaVaultItem->get("MediaId"), null, 50, null, null, false); ?>
					</span>
					<span class="title">
						<?php echo $mediaVaultItem->get("Title");?>
<?php
				if ($mediaVaultItem->get("Id") == $selected) {
?>
						<strong>(Active Item)</strong>
<?php
					}
?>
					</span>
				</li>
<?php
				}
?>
				</ul>
<?php
			}
?>
			</div>
		</div>
<?php
		}

		function createPromotionControl($controlId) {
			$promotionControl = BaseFactory::getPromotionControl();
			$promotionItemControl = BaseFactory::getPromotionItemControl();
?>
		<div class="mediavault-toolbar">
			<h3 class="title">Add Promotion Item</h3>
			<p class="title">Click on any Promotion Items to insert it into your BODY text</p>
			<div class="mediavault-items">

<?php
		while ($promotion = $promotionControl->getNext()) {
			$selected = $promotion->get("PromotionItemId");
?>
				<p class="description" title="Insert Tag" onclick="doCustomTag('<?php echo $controlId; ?>', '[MEDIA|LEFT|<?php echo $promotion->get("Title"); ?>]', '');"><?php echo $promotion->get("Title");?> (Active Item)</p>
				<ul>
<?php
			$promotionItemControl->retrieveForPromotion($promotion);
			while ($promotionItem = $promotionItemControl->getNext()) {
?>
				<li title="Insert Tag" onclick="doCustomTag('<?php echo $controlId; ?>', '[MEDIA|LEFT|<?php echo $promotion->get("Title"); ?>/<?php echo $promotionItem->get("Title"); ?>]', '');">
					<span class="preview">
						<?php echo $this->showImageBinary($promotionItem->get("BinaryId"), null, 50, null, null, false); ?>
					</span>
					<span class="title">
						<?php echo $promotionItem->get("Title");?>
<?php
				if ($promotionItem->get("Id") == $selected) {
?>
						<strong>(Active Item)</strong>
<?php
					}
?>
					</span>
				</li>
<?php
				}
?>
				</ul>
<?php
			}
?>
			</div>
		</div>
<?php
		}

		function createFormattingToolbar($controlId, $returnAsString = false) {
			if (!$returnAsString) {
?>
		<div class="format-toolbar">
			<a class="bold-selection" href="javascript:;"
				onclick="RumbleUI.Form.Textarea.wrapSelection('<?php echo $controlId; ?>','[b]','[/b]');"
				title="Makes the selected text bold">
				<span>Bold</span>
			</a>
			<a class="italic-selection" href="javascript:;" onclick="
					RumbleUI.Form.Textarea.wrapSelection('<?php echo $controlId; ?>','[i]','[/i]')"
					title="Makes the selected text italic">
				<span>Italic</span>
			</a>
			<a class="underline-selection" href="javascript:;" onclick="
					RumbleUI.Form.Textarea.wrapSelection('<?php echo $controlId; ?>','[u]','[/u]')"
					title="Makes the selected text underlined">
				<span>Undelined</span>
			</a>
			<a class="big-selection" href="javascript:;"
				onclick="RumbleUI.Form.Textarea.wrapSelection('<?php echo $controlId; ?>','[big]','[/big]');"
				title="Makes the selected text larger">
				<span>Larger</span>
			</a>
			<a class="colorred-selection" href="javascript:;" onclick="
					RumbleUI.Form.Textarea.wrapSelection('<?php echo $controlId; ?>','[color=&#034;#FF0000&#034;]','[/color]')"
					title="Makes the selected text Red">
				<span>Red</span>
			</a>
			<a class="colorbrown-selection" href="javascript:;" onclick="
					RumbleUI.Form.Textarea.wrapSelection('<?php echo $controlId; ?>','[color=&#034;#603913&#034;]','[/color]')"
					title="Makes the selected text brown">
				<span>Brown</span>
			</a>
			<a class="colororange-selection" href="javascript:;" onclick="
					RumbleUI.Form.Textarea.wrapSelection('<?php echo $controlId; ?>','[color=&#034;#F26522&#034;]','[/color]')"
					title="Makes the selected text orange">
				<span>Orange</span>
			</a>
			<a class="coloryellow-selection" href="javascript:;" onclick="
					RumbleUI.Form.Textarea.wrapSelection('<?php echo $controlId; ?>','[color=&#034;#FFF20D&#034;]','[/color]')"
					title="Makes the selected text Yellow">
				<span>Yellow</span>
			</a>
			<a class="colorgreen-selection" href="javascript:;" onclick="
					RumbleUI.Form.Textarea.wrapSelection('<?php echo $controlId; ?>','[color=&#034;#0DA651&#034;]','[/color]')"
					title="Makes the selected text green">
				<span>Green</span>
			</a>
			<a class="colorblue-selection" href="javascript:;" onclick="
					RumbleUI.Form.Textarea.wrapSelection('<?php echo $controlId; ?>','[color=&#034;#0054A6&#034;]','[/color]')"
					title="Makes the selected text blue">
				<span>Blue</span>
			</a>
			<a class="colorpurple-selection" href="javascript:;" onclick="
					RumbleUI.Form.Textarea.wrapSelection('<?php echo $controlId; ?>','[color=&#034;#92278F&#034;]','[/color]')"
					title="Makes the selected text purple">
				<span>Purple</span>
			</a>
			<a class="colorpink-selection" href="javascript:;" onclick="
					RumbleUI.Form.Textarea.wrapSelection('<?php echo $controlId; ?>','[color=&#034;#EC0D8C&#034;]','[/color]')"
					title="Makes the selected text pink">
				<span>Pink</span>
			</a>
			<a class="colordarkgrey-selection" href="javascript:;" onclick="
					RumbleUI.Form.Textarea.wrapSelection('<?php echo $controlId; ?>','[color=&#034;#3D3D3D&#034;]','[/color]')"
					title="Makes the selected text dark grey">
				<span>Dark Grey</span>
			</a>
			<a class="colorlightgrey-selection" href="javascript:;" onclick="
					RumbleUI.Form.Textarea.wrapSelection('<?php echo $controlId; ?>','[color=&#034;#818181&#034;]','[/color]')"
					title="Makes the selected text grey">
				<span>Light Grey</span>
			</a>
			<a class="img-selection" href="javascript:;" onclick="
					RumbleUI.Form.Textarea.wrapSelection('<?php echo $controlId; ?>','[img src=&#034;','&#034;/]')"
					title="Link to an image. Usage: [img]&lt;url&gt;[/img] or [img src=&#034;&lt;url&gt;&#034; /]">
				<span>Image</span>
			</a>
			<a class="link-selection" href="javascript:;" onclick="
					RumbleUI.Form.Textarea.wrapSelection('<?php echo $controlId; ?>','[url]', '[/url]')"
					title="Link to a Web page. Usage: [url]&lt;url&gt;[/url] or [url=&#034;&lt;url&gt;&#034;]&lt;Link Text&gt;[/url]">
				<span>Link</span>
			</a>
			<a class="link-selection" href="javascript:;" onclick="
					RumbleUI.Form.Textarea.wrapSelection('<?php echo $controlId; ?>','[link]', '[/link]')"
					title="Link to a Web page. Usage: [link]&lt;url&gt;[/link] or [link=&#034;&lt;url&gt;&#034;]&lt;Link Text&gt;[/link]">
				<span>Link</span>
			</a>
		</div>
<?php
		} else {
			$formattedToolbarString = "";
			$formattedToolbarString .= "<div class=\"format_toolbar\">";
			$formattedToolbarString .= "<a class=\"bold-selection\" href=\"javascript:;\" onclick=\"RumbleUI.Form.Textarea.wrapSelection('" . $controlId . "','[b]','[/b]');\" title=\"Makes the selected text bold\"><span>Bold</span></a>";
			$formattedToolbarString .= "<a class=\"italic-selection\" href=\"javascript:;\" onclick=\"RumbleUI.Form.Textarea.wrapSelection('" . $controlId . "','[i]','[/i]');\" title=\"Makes the selected text italic\"><span>Italic</span></a>";
			$formattedToolbarString .= "<a class=\"underline-selection\" href=\"javascript:;\" onclick=\"RumbleUI.Form.Textarea.wrapSelection('" . $controlId . "','[u]','[/u]');\" title=\"Makes the selected text underlined\"><span>Underlined</span></a>";
			$formattedToolbarString .= "<a class=\"big-selection\" href=\"javascript:;\" onclick=\"RumbleUI.Form.Textarea.wrapSelection('" . $controlId . "','[big]','[/big]');\" title=\"Makes the selected text larger\"><span>Larger</span></a>";
			$formattedToolbarString .= "<a class=\"colorred-selection\" href=\"javascript:;\" onclick=\"RumbleUI.Form.Textarea.wrapSelection('" . $controlId . "','[color=&#034;#FF0000&#034;]','[/color]');\" title=\"Makes the selected text red\"><span>Red</span></a>";
			$formattedToolbarString .= "<a class=\"colorbrown-selection\" href=\"javascript:;\" onclick=\"RumbleUI.Form.Textarea.wrapSelection('" . $controlId . "','[color=&#034;#603913&#034;]','[/color]');\" title=\"Makes the selected text brown\"><span>Brown</span></a>";
			$formattedToolbarString .= "<a class=\"colororange-selection\" href=\"javascript:;\" onclick=\"RumbleUI.Form.Textarea.wrapSelection('" . $controlId . "','[color=&#034;#F26522&#034;]','[/color]');\" title=\"Makes the selected text orange\"><span>Orange</span></a>";
			$formattedToolbarString .= "<a class=\"coloryellow-selection\" href=\"javascript:;\" onclick=\"RumbleUI.Form.Textarea.wrapSelection('" . $controlId . "','[color=&#034;#FFF20D&#034;]','[/color]');\" title=\"Makes the selected text yellow\"><span>Yellow</span></a>";
			$formattedToolbarString .= "<a class=\"colorgreen-selection\" href=\"javascript:;\" onclick=\"RumbleUI.Form.Textarea.wrapSelection('" . $controlId . "','[color=&#034;#0DA651&#034;]','[/color]');\" title=\"Makes the selected text yellow\"><span>Yellow</span></a>";
			$formattedToolbarString .= "<a class=\"colorblue-selection\" href=\"javascript:;\" onclick=\"RumbleUI.Form.Textarea.wrapSelection('" . $controlId . "','[color=&#034;#0054A6&#034;]','[/color]');\" title=\"Makes the selected text yellow\"><span>Yellow</span></a>";
			$formattedToolbarString .= "<a class=\"colorpurple-selection\" href=\"javascript:;\" onclick=\"RumbleUI.Form.Textarea.wrapSelection('" . $controlId . "','[color=&#034;#92278F&#034;]','[/color]');\" title=\"Makes the selected text yellow\"><span>Yellow</span></a>";
			$formattedToolbarString .= "<a class=\"colorpink-selection\" href=\"javascript:;\" onclick=\"RumbleUI.Form.Textarea.wrapSelection('" . $controlId . "','[color=&#034;#EC0D8C&#034;]','[/color]');\" title=\"Makes the selected text yellow\"><span>Yellow</span></a>";
			$formattedToolbarString .= "<a class=\"colordarkgrey-selection\" href=\"javascript:;\" onclick=\"RumbleUI.Form.Textarea.wrapSelection('" . $controlId . "','[color=&#034;#3D3D3D&#034;]','[/color]');\" title=\"Makes the selected text yellow\"><span>Yellow</span></a>";
			$formattedToolbarString .= "<a class=\"colorlightgrey-selection\" href=\"javascript:;\" onclick=\"RumbleUI.Form.Textarea.wrapSelection('" . $controlId . "','[color=&#034;#818181&#034;]','[/color]');\" title=\"Makes the selected text yellow\"><span>Yellow</span></a>";
			$formattedToolbarString .= "<a class=\"img-selection\" href=\"javascript:;\" onclick=\"RumbleUI.Form.Textarea.wrapSelection('" . $controlId . "','[img src=&#034;','&#034;/]');\" title=\"Link to an image. Usage: [img]&lt;url&gt;[/img] or [img src=&#034;&lt;url&gt;&#034; /]\"><span>Yellow</span></a>";
			$formattedToolbarString .= "<a class=\"link-selection\" href=\"javascript:;\" onclick=\"RumbleUI.Form.Textarea.wrapSelection('" . $controlId . "','[url]','[/url]');\" title=\"Link to a Web page. Usage: [url]&lt;url&gt;[/url] or [url=&#034;&lt;url&gt;&#034;]&lt;Link Text&gt;[/url]\"><span>Yellow</span></a>";
			$formattedToolbarString .= "<a class=\"link-selection\" href=\"javascript:;\" onclick=\"RumbleUI.Form.Textarea.wrapSelection('" . $controlId . "','[link]','[/link]');\" title=\"Link to a Web page. Usage: [link]&lt;url&gt;[/link] or [link=&#034;&lt;url&gt;&#034;]&lt;Link Text&gt;[/link]\"><span>Yellow</span></a>";

			$formattedToolbarString .= "</div>";
			return $formattedToolbarString;
		}
	}
	function createLinks($string) {
		$search = array (
			"'(\s)((http|ftp|https)://[^\s]*)'is",
			"'^((http|ftp|https)://[^\s]*)'is",
			"'(\s)(www\.[^\s]*)'is",
			"'^(www\..[^\s]*)'is",
			"'[a-z0-9\._-]+@+[a-z0-9\._-]+\.+[a-z]{2, 3}'is");
		$replace = array (
			"$1<a href=\"$2\" target=\"_blank\" class=\"FlaggedLink\" title=\"Click
					here to open link in new window.\">$2</a>",
			"<a href=\"$1\" target=\"_blank\" class=\"FlaggedLink\" title=\"Click
					here to open link in new window.\">$1</a>",
			"$1<a href=\"http://$2\" target=\"_blank\" class=\"FlaggedLink\"
					title=\"Click here to open link in new window.\">$2</a>",
			"<a href=\"http://$1\" target=\"_blank\" class=\"FlaggedLink\"
					title=\"Click here to open link in new window.\">$1</a>",
			"<a href=\"mailto:$0\" target=\"_blank\" class=\"FlaggedLink\"
				title=\"Click here to create a new mail to $0.\">$0</a>");
		return preg_replace($search, $replace, $string. " ");
	}

	function parseCustomTagging($string, $hideImages = false) {
		$search = array (
			"'\[img\](.*?)\[/img\]'is",
			"'\[h1\](.*?)\[/h1\]'is",
			"'\[h2\](.*?)\[/h2\]'is",
			"'\[h3\](.*?)\[/h3\]'is",
			"'\[big\](.*?)\[/big\]'is",
			"'\[b\](.*?)\[/b\]'is",
			"'\[i\](.*?)\[/i\]'is",
			"'\[u\](.*?)\[/u\]'is",
			"'\[code\](.*?)\[/code\]'is",
			"'\[color=&quot;(.*?)&quot;\](.*?)\[/color\]'is",
			"'\[size=&quot;(.*?)&quot;\](.*?)\[/size\]'is",
			"'\[email\](.*?)\[/email\]'is",
			"'\[url\](.*?)\[/url\]'is",
			"'\[url=&quot;(.*?)&quot;\](.*?)\[/url\]'is",
			"'\[link\](.*?)\[/link\]'is",
			"'\[link=&quot;(.*?)&quot;\](.*?)\[/link\]'is",
			"'\[quote\]'is",
			"'\[/quote\]'is",
			"'\[quote from=&quot;(.*?)&quot;\]'is",
			"':-\)'is",
			"':-\('is",
			"';-\)'is",
			"':-D'is",
			"':-\|'is",
			"':\'\)'is"
			);
			$images = $hideImages ? "" : "<a href=\"$1\"><img src=\"$1\" width=\"100\" title=\"Linked image (&quot;$1&quot;)\" alt=\"Linked Image (&quot;$1&quot;)\" /></a>";
		$replace = array (
			$images,
			"<h1 class=\"customtag\">$1</h1>",
			"<h2 class=\"customtag\">$1</h2>",
			"<h3 class=\"customtag\">$1</h3>",
			"<big>$1</big>",
			"<b>$1</b>",
			"<span style=\"font-style: italic\">$1</span>",
			"<span style=\"text-decoration: underline\">$1</span>",
			"<code class=\"customtag\">$1</code>",
			"<font color=\"$1\">$2</font>",
			"<span style=\"font-size: $1px\">$2</span>",
			"<a href=\"mailto:$1\" target=\"_blank\">$1</a>",
			"<a href=\"$1\" target=\"_blank\">$1</a>",
			"<a href=\"$1\" target=\"_blank\">$2</a>",
			"<a href=\"$1\">[External Link]</a>",
			"<a href=\"$1\">$2</a>",
			"<blockquote>",
			"</blockquote>",
			"<blockquote><strong>Quote from '$1'</strong>",
			"<img src=\"/image/main/emoticons/happy.gif\" alt=\"Happy :-)\" title=\"Happy :-)\" width=\"18\" height=\"18\" />",
			"<img src=\"/image/main/emoticons/sad.gif\" alt=\"Sad :-(\" title=\"Sad :-(\" width=\"18\" height=\"18\" />",
			"<img src=\"/image/main/emoticons/wink.gif\" alt=\"Wink ;-)\" title=\"Wink ;-)\" width=\"18\" height=\"18\" />",
			"<img src=\"/image/main/emoticons/veryhappy.gif\" alt=\"Very happy :-d\" title=\"Very Happy :-D\" width=\"18\" height=\"18\" />",
			"<img src=\"/image/main/emoticons/unimpressed.gif\" alt=\"Unimpressed :-|\" title=\"Unimpressed :-|\" width=\"18\" height=\"18\" />",
			"<img src=\"/image/main/emoticons/cry.gif\" alt=\"Crying :')\" title=\"Crying :')\" width=\"18\" height=\"18\" />"
			);
		return preg_replace($search, $replace, $string . " ");
	}

	function formatTaggedText(&$text) {
	 return nl2br($this->parseCustomTagging($text));
	}

	function select($exp) {
		return $this->evaluate($exp, "selected=\"selected\"");
	}

	function check($exp) {
		return $this->evaluate($exp, "checked=\"checked\"");
	}

	function disable($exp) {
		return $this->evaluate($exp, "disabled=\"disabled\"");
	}

	function evaluateToBoolean($exp) {
		return $this->evaluate($exp, true, false);
	}

	function evaluate($exp, $trueValue, $falseValue = "") {
		if (!isset($exp)) {
			return $falseValue;
		}
		if ($exp == "t") {
			return $trueValue;
		}
		if ($exp == "true") {
			return $trueValue;
		}
		if ($exp == 1) {
			return $trueValue;
		}
		return $falseValue;
	}

	/**
	* Shortens a string to the word boundary.
	* @param String $text The string to shorten.
	* @param Integer $length The string to shorten.
	* @return String shortened to the last space before $length number of
	*	charaters.
	*/
	function shortenText($text, $length) {
		$returnValue = mb_substr($text, 0, $length-mb_strlen(strrchr(
				mb_substr($text, 0, $length), " ")));
		if (mb_strlen($text) != mb_strlen($returnValue))
			$returnValue .= " ... ";
		else
			$returnValue .= " ";
		return $returnValue;
	}

	function createRppListValues($default) {
		$returnValue = "";
		for ($i = 4; $i <= 64; $i *= 2) {
			$returnValue .= "<option " . (($default==$i)?"selected=\"selected\"":"")
					. ">$i</option>\n";
		}
		return $returnValue;
	}

	function createNumberListValues($default, $start = 1, $stop = 10, $step = 1) {
		$returnValue = "";
		if ($step == 0) {
			return "";
		} else if ($step > 0) {
			for ($i = $start; $i <= $stop; $i += $step) {
				$returnValue .= "<option value=\"$i\" " . (($default == $i)?"selected=\"selected\"":"")
						. ">$i</option>\n";
			}
		} else {
			for ($i = $start; $i >= $stop; $i += $step) {
				$returnValue .= "<option value=\"$i\" " . (($default == $i)?"selected=\"selected\"":"")
						. ">$i</option>\n";
			}
		}
		return $returnValue;
	}

	function createArrayListValues($default, $array, $noValues = false) {
		$returnValue = "";
		if ($noValues) {
			foreach ($array as $text) {
				$returnValue .= "<option" . (($default == $text)?" selected=\"selected\"":"")
					. ">$text</option>\n";
			}
		} else {
			foreach ($array as $value => $text) {
				$returnValue .= "<option" . (($default == $value)?" selected=\"selected\"":"")
					. " value=\"$value\">$text</option>\n";
			}
		}
		return $returnValue;
	}
//
	function createTaggedArray($tag, $array) {
		$returnValue = "";
		foreach ($array as $value) {
			$returnValue .= "<$tag>$value</$tag>\n";
		}
		return $returnValue;
	}

	function createPaddedNumberListValues($default, $start = 1, $length = 10, $step = 1, $blank = "--") {
		$returnValue = "<option value=\"\">$blank</option>\n";
		if ($default === "") {
			$default = "NULL";
		} else {
			$default = (int)$default;
		}
		if (!($length < 0)) {
			for ($i = (int)$start; $i < $start + $length; $i += $step) {
				$returnValue .= "<option " . (($default === $i) ? "selected=\"selected\"" : "")
					. ">" . sprintf("%02d", $i) . "</option>\n";
			}
		} else {
			for ($i = $start + $length; $i <= $start; $i += $step) {
				if ($i < 0) {
					$tmp = $i + 100;
				} else {
					$tmp = $i;
				}
				$returnValue .= "<option " . (($default === $tmp) ? "selected=\"selected\"" : "")
						. ">" . sprintf("%02d", $tmp) . "</option>\n";
			}
		}
		return $returnValue;
	}

	function createTimeListValues($default, $start = 0, $length = 15, $step = 0.25) {
		$returnValue = "";
		for ($i = $start; $i <= $length; $i += $step) {
			$returnValue .= "<option " . (($default == $i) ? "selected=\"selected\"" : "") .
			">" . sprintf("%.02f", $i) . "</option>\n";
		}
		return $returnValue;
	}

	function createFormattedTimeListValues($default, $start = 0, $length = 15, $step = 0.25) {
		$returnValue = "";
		for ($i = $start; $i <= $length; $i += $step) {
			//echo $default . " = " . $i . "<br />";
			$returnValue .= "<option value=\"" . sprintf("%f", $i) . "\" " . (($default == sprintf("%f", $i)) ? "selected=\"selected\"" : "") .
			">" . floor($i) . "h " . sprintf("%02d", round(60 * ($i - floor($i)))) . "m</option>\n";
		} //exit;
		return $returnValue;
	}


	function createYearListValues($default, $start = 1, $length = 10, $step = 1) {
		$returnValue = "<option value=\"\">--</option>\n";
		if ($step > 0) {
 			for ($i = $start+$length;$i >= $start; $i -= $step) {
				$returnValue .= "<option " . (($default==$i)?"selected=\"selected\"":"")
						. ">$i</option>\n";
			}
		} else {
			for ($i = $start; $i <= $start + $length; $i -= $step) {
				$returnValue .= "<option " . (($default==$i)?"selected=\"selected\"":"")
						. ">$i</option>\n";
			}
		}
		return $returnValue;
	}

	function createMonthListValues($default, $start = 1, $stop = 12, $step = 1) {
		$monthArray = array("1"=>"Jan", "2"=>"Feb", "3"=>"Mar", "4"=>"Apr",
			"5"=>"May", "6"=>"Jun", "7"=>"Jul", "8"=>"Aug", "9"=>"Sep", "10"=>"Oct",
			"11"=>"Nov", "12"=>"Dec");
		$returnValue = "<option value=\"\">--</option>\n";
		for ($i = $start; $i <= $stop;$i += $step) {
			$returnValue .= "<option value=\"$i\" title=\"" .date("m",mktime(0, 0, 0, $i+1, 0, 0)).
					"\" " . (($default == $i)?"selected=\"selected\"":"") . ">"
					.date("M", mktime(0, 0, 0, $i + 1, 0, 0)). "</option>\n";
		}
		return $returnValue;
	}

	//TODO: startYear could be changed to default as -1 in the next version.
	function createMonthYearControl($name, $default, $startYear = 0, $yearLength = 10,
		$class="", $monthBlank = "", $yearBlank = "", $monthNames = false) {
		$output = "";
		$month = "";
		$year = "";
		if ($default != "") {
			if (preg_match("/\d\d\/\d\d/", $default)) {
				$month = mb_substr($default, 0, 2);
				$year = mb_substr($default, 3, 2);
			}
		}
		if ($startYear == -1) {
			$dateArray = getdate();
			$startYear = mb_substr($dateArray["year"], -2);
		}
		 $output .= "<select name=\"$name" . "[Month]\" class=\"$class\">" .
    	$this->createPaddedNumberListValues($month, 1, 12, 1, $monthBlank). "</select>";
		$output .= "<select name=\"$name" . "[Year]\" class=\"$class\">" .
			$this->createPaddedNumberListValues($year, $startYear, $yearLength, 1, $yearBlank). "</select>";

		return $output;
	}


	// Updated to pass step into the makeYear List box so can count up rather than down - Tom 2008-01-11, May have broken not sure???
	//Also see the createYearListValues functions as this has been updated too!
	function createDateControl($name, $default, $tz = false, $class = "", $startYear = 1900, $forYears = 0, $showDay = true, &$tabIndex = null) {
		$tabIndexDay = $tabIndexMonth = $tabIndexYear = "";

		if ($tabIndex) {
			$tabIndexDay = " tabindex=\"" . $tabIndex++ . "\"";
			$tabIndexMonth = " tabindex=\"" . $tabIndex++ . "\"";
			$tabIndexYear = " tabindex=\"" . $tabIndex++ . "\"";
		}

		$day = -1;
		$month = -1;
		$year = -1;

		if ($default !=  null) {
			if (is_numeric($default)) {
				$dateArray = getdate($default);
				$day = $dateArray["mday"];
				$month = $dateArray["mon"];
				$year = $dateArray["year"];
			} else if (is_array($default)) {
				$year =  $default["Year"];
				$month = $default["Month"];
				$day = $default["Day"];
			} else {
				// Make a last try at make sense of the default date.
				if (sizeof($dateArray = mb_split("[- :\+]", $default)) > 2) {
					$day = $dateArray[2];
					$month = $dateArray[1];
					$year = $dateArray[0];
				}
			}
		}

		// -1 means start on this year
		if ($startYear <= 0) {
			$startYear = date("Y") + $startYear;
		}

		$application = &CoreFactory::getApplication();
		$step = 1;
		if ($forYears <= 0) {
			$date = $application->getCurrentDateObject();
			$forYears = $date->getYear() - $startYear + abs($forYears);
			$step = -1;
		}
		$output = "";
		$idName = str_replace(array("[", "]") , "", $name);
		if ($showDay) {
			$output = "<select id=\"{$idName}Day\" name=\"$name" . "[Day]\" class=\"$class\" title=\"Select Day\"$tabIndexDay>" .
				$this->createPaddedNumberListValues($day, 1, 31). "</select>";
		}
		$output .= "<select id=\"{$idName}Month\" name=\"$name" . "[Month]\" class=\"$class\" title=\"Select Month\"$tabIndexMonth>" .
						$this->createMonthListValues($month, 1, 12). "</select>";
		$output .= "<select id=\"{$idName}Year\" name=\"$name" . "[Year]\" class=\"$class\" title=\"Select Year\"$tabIndexYear>" .
						$this->createYearListValues($year, $startYear, $forYears, $step). "</select>";

		if (!$tz) {
		} else {
			$output .= "<input type=\"hidden\" name=\"$name" . "[timezone]\"
					value=\"" . $application->getCurrentTimezone().
					"\" />&nbsp;" .
			$application->getCurrentTimezone();
		}
		return $output;
	}

	function createTimeControl($name, $default, $class="", $styleId="") {
		$output = "";
		if ($default !=  null) {
			if (is_numeric($default)) {
				$dateArray = getdate($default);
				$hour = $dateArray["hours"];
				$min = $dateArray["minutes"] - ($dateArray["minutes"] % 15);
			} else {
				$dateArray = mb_split("[- :\+]", $default);
				$min = $dateArray[4] - ($dateArray[4] % 15);
				$hour = $dateArray[3];
			}
		}
		$output .= "<select name=\"$name\" class=\"$class\" id=\"$styleId\">";
		for ($h = 0; $h < 24; $h++) {
			for ($m = 0; $m < 60; $m = $m + 15) {
				$output .= "<option value=\"" . sprintf("%02d", $h) . ":" . sprintf("%02d", $m) . "\"" . (((sprintf("%02d", $h) == $hour) && (sprintf("%02d", $m) == $min)) ? "selected=\"selected\"" : "") .  ">" . sprintf("%02d", $h) . ":" . sprintf("%02d", $m) . "</option>";
			}
		}
		$output.= "</select>";
		return $output;
	}

	function createSplitTimeControl($name, $default, $class="", $styleId="") {
		$output = "";
		if ($default !=  null) {
			if (is_numeric($default)) {
				$dateArray = getdate($default);
				$hour = $dateArray["hours"];
				$min = $dateArray["minutes"] - ($dateArray["minutes"] % 15);
			} else {
				$dateArray = mb_split("[- :\+]", $default);
				$min = $dateArray[4] - ($dateArray[4] % 15);
				$hour = $dateArray[3];
			}
		}
		$output .= "<select name=\"$name\" class=\"$class\" id=\"$styleId\">";
		$output = "<select name=\"$name" . "[Hour]\" class=\"$class\">" .
						$this->createPaddedNumberListValues($hour, 0, 24). "</select>";
		$output .= "<select name=\"$name" . "[Minute]\" class=\"$class\">" .
						$this->createPaddedNumberListValues($min, 0, 60, 5). "</select>&nbsp;";
		$output.= "</select>";
		return $output;
	}

	function createDateTimeControl($name, $default, $tz = false, $class="", $startYear = 1900, $forYears = 0) {

		$hour = -1;
		$min = -1;
		$day = -1;
		$month = -1;
		$year = -1;

		$application = &CoreFactory::getApplication();

		if ($default !=  null) {
			require_once("Date.php");

			//$default = $application->localiseTime($default);
			$date = new Date($default);

			$hour = $date->getHour();
			$min = $date->getMinute() - ($date->getMinute() % 5);
			$day = $date->getDay();
			$month = $date->getMonth();
			$year = $date->getYear();
		}

		if ($forYears <= 0) {
			$date = $application->getCurrentDateObject();
			// Added 5 years by Rob 2006-04-21
			$forYears = $date->getYear() + 5 - $startYear + abs($forYears);
		}

		$output = "<select name=\"$name" . "[Hour]\" class=\"$class\">" .
						$this->createPaddedNumberListValues($hour, 0, 24). "</select>";
		$output .= "<select name=\"$name" . "[Minute]\" class=\"$class\">" .
						$this->createPaddedNumberListValues($min, 0, 60, 5). "</select>&nbsp;";
		$output .= "<select name=\"$name" . "[Day]\" class=\"$class\">" .
						$this->createPaddedNumberListValues($day, 1, 31). "</select>";
		$output .= "<select name=\"$name" . "[Month]\" class=\"$class\">" .
						$this->createMonthListValues($month, 1, 12). "</select>";
		$output .= "<select name=\"$name" . "[Year]\" class=\"$class\">" .
						$this->createYearListValues($year, $startYear, $forYears). "</select>";

		if (!$tz) {
		} else {
			$output .= "<input type=\"hidden\" name=\"$name" . "[gmtoffset]\"
					value=\"" . $application->getGmtOffsetHours() .
					"\" />&nbsp;" .
			$application->getCurrentTimezone();
		}
		return $output;
	}

	function createRadioOptionControl($default, $name, $options, $useValue = true, $showDescription = true, $showValue = false) {
		$returnValue = "";

		foreach($options as $key => $option) {
			if (!isset($option["Description"])) {
				$option["Description"] = $option["Value"];
			}
			$returnValue .= "<div class=\"radioitem\">";
			$returnValue .= "<label>";

			if ($useValue) {
				$value = $option["Value"];
			} else {
				$value = $key;
			}

			if ($default == $value) {
				$checked = "checked=\"checked\"";
			} else {
				$checked = "";
			}

			$returnValue .= "<input type=\"radio\" name=\"$name\" value=\"{$value}\" $checked />";
			if (isset($option["Image"])) {
				$returnValue .= "<img src=\"{$option["Image"]}\" alt=\"{$option["Description"]}\" title=\"{$option["Description"]}\" />";
			}

			if ($showValue) {
				$returnValue .= "<h4>" . $option["Value"] . "</h4>";
			}

			if ($showDescription) {
				if ($showValue) {
					$returnValue .= "<p class=\"timelineitem_description\">" . nl2br($option["Description"]) . "</p>";
				} else {
					$returnValue .= "<strong>" . nl2br($option["Description"]) . "</strong>";
				}
			}

			$returnValue .= "</label>";
			$returnValue .= "<div class=\"footer\"></div></div>";
		}
		return $returnValue;
	}

	function createCheckBoxOptionControl($default, $name, $options, $useValue = true, $groupedBoxes = false, $selectAllbox = true) {
		$returnValue = "";
		if ($selectAllbox) {
			$returnValue .= "<label class=\"checkbox\">\n";
			$returnValue .= "<input name=\"SelectAll\" type=\"checkbox\" onclick=\"setAllCheckboxes(this)\" /> Assign To All";
			$returnValue .= "</label>\n";
		}
		if ($groupedBoxes) {
			foreach ($options as $dept => $department) {
				$returnValue .= "<div class=\"checkbox_group\" style=\"float:left;\">\n";
				foreach ($department as $key => $description) {
					$returnValue .= "<label class=\"checkbox\">\n";
					if ($useValue) {
						$value = $key;
					} else {
						$value = $description;
					}
					if (in_array($value, $default)) {
						$checked = "checked=\"checked\"";
					} else {
						$checked = "";
					}
					$returnValue .= "<input type=\"checkbox\" name=\"$name\" value=\"{$value}\" $checked /> " . $description . "\n";
					$returnValue .= "</label>\n";
				}
				$returnValue .= "</div>\n";
			}
		} else {
			foreach ($options as $key => $description) {
				$returnValue .= "<label class=\"checkbox\">\n";
				if ($useValue) {
					$value = $key;
				} else {
					$value = $description;
				}
				if (in_array($value, $default)) {
					$checked = "checked=\"checked\"";
				} else {
					$checked = "";
				}
				$returnValue .= "<input type=\"checkbox\" name=\"$name\" value=\"{$value}\" $checked /> " . $description . "\n";
				$returnValue .= "</label>\n";
			}
		}
		return $returnValue;
	}

	function insertEditRegion($name, $width, $height, $styleSheet = null) {

		$filename = dirname($_SERVER["SCRIPT_FILENAME"]) . "/." .
			basename($_SERVER["SCRIPT_FILENAME"]) . ".{$name}.content";

		if (isset($_POST["RegionBody_" . $name])) {
			$handle = fopen($filename, "w+");
			fwrite($handle, stripslashes($_POST["RegionBody_" . $name]));
			fclose($handle);
			$application = &CoreFactory::getApplication();
			$application->redirect(basename($_SERVER["SCRIPT_NAME"]));
		}

		$contents = @file_get_contents($filename);
		if (isset($_REQUEST["Edit_" . $name])) {

?>
					<form action="" method="post" onsubmit="return updateRTEs();">
						<script language="JavaScript" type="text/javascript">
							initRTE("/scripts/richtext/image/", "/scripts/richtext/", "<? echo $styleSheet; ?>", true);
						</script>
						<script language="JavaScript" type="text/javascript">
							writeRichText("RegionBody_<?php echo $name; ?>", '<?php echo $this->rteSafe($contents); ?>', <?php echo $width ; ?>, <?php echo $height; ?>, true, false);
						</script>
						<p><input type="submit" name="submit" value="Submit"></p>
					</form>
<?php
		} else {
			echo $contents;
		}
	}

	/**
	 * This is used to parse content passed back by the Rich text control
	 */
	function rteSafe($strText) {
		//returns safe code for preloading in the RTE
		$tmpString = $strText;

		//convert all types of single quotes
		$tmpString = str_replace(chr(145), chr(39), $tmpString);
		$tmpString = str_replace(chr(146), chr(39), $tmpString);
		$tmpString = str_replace("'", "&#39;", $tmpString);

		//convert all types of double quotes
		$tmpString = str_replace(chr(147), chr(34), $tmpString);
		$tmpString = str_replace(chr(148), chr(34), $tmpString);
	//	$tmpString = str_replace("\"", "\"", $tmpString);

		//replace carriage returns & line feeds
		$tmpString = str_replace(chr(10), " ", $tmpString);
		$tmpString = str_replace(chr(13), " ", $tmpString);

		return $tmpString;
	}


	function createPageListValues($default, $pageCount) {
		return createNumberListValues($default, 1, $pageCount, 1);
	}

	function createCalControl($name, $dateValue, $onchange = "") {
		$output = "";
		$date = new Date($dateValue);
		$nextMonth = $date;
		$nextMonth->setMonth($date->getMonth() + 1);
		$daysInMonth = $date->getDaysInMonth();
		$daysInLastMonth = $nextMonth->getDaysInMonth();
		$date2 = $date;
		$date2->setDay(1);
		$dayCounter = -$date2->getDayOfWeek() ;
		$date2->subtractSeconds((int)abs($dayCounter) * 86400);
		$calTable = "<div class=\"CalControl\"><input name=\"$name\" id=\"$name\" type=\"hidden\" value=\"$dateValue\">";
		$calTable .= "<table><tr><td><table width=\"100%\" class=\"CalMonthYear\"><tr><td align=\"right\">" .
			"<select id=\"$name" . "Month\" name=\"$name" . "Month\" onchange=\"setDateMonthYear('Date', '$name" . "Month', '$name" . "Year'); $onchange\">" .
			$this->createMonthListValues($date->getMonth()) .
			"</select>" .
			"<select id=\"$name" . "Year\" name=\"$name" . "Year\"  onchange=\"setDateMonthYear('Date', '$name" . "Month', '$name" . "Year'); $onchange\">" .
			$this->createYearListValues($date->getYear(), 2004, 10) .
			"</select></td></tr><tr></table></td></tr><tr><td>";
		$calTable .= "<table class=\"CalTable\">";
		$calTable .= "<tr><th>Su</th><th>Mo</th><th>Tu</th><th>We</th><th>Th</th><th>Fr</th><th>Sa</th></tr>";

		for ($e = 0; $e < 6; $e++) {
			$calTable .= "<tr>";
			for ($i = 0; $i < 7; $i++) {
				$calTable .= "<td onclick=\"setValue('$name', '" . $date2->format("%Y-%m-%d") . "');$onchange;\"" .
				($date2->getDate() == $date->getDate() ? " class=\"CalSelectedDay\"" : ($i == 0 || $i == 6 ? " class=\"CalWeekend\"":"")) . ">" .
					$date2->getDay() . "</td>\n";
				$date2->addSeconds(86400);
			}
			$calTable .= "</tr>";
		}
		return $calTable . "</table></table></div>";
	}

	function displayDay($day, $daysInMonth, $daysInLastMonth) {
		if ($day < 1) {
			return $daysInLastMonth + $day;
		} else if ($day > $daysInMonth) {
			return -$daysInMonth + $day;
		}
		return $day;
	}

	/**
	 * Adds a prefix to a string, but make sure there is only one occurance.
   * @param String $value Text to prefix.
   * @param String $prefix Prefix to add to text.
   * @param Boolean $ignoreCase Should case be ignored when determining whether to
	 * add prefix
	 * @return String  $value with the prefix added if it wasn't prevoiusly there.
	 */
	function addPrefix($value, $prefix, $ignoreCase = true) {

		if ($ignoreCase) {
			if (mb_strtoupper(mb_substr($value, 0, mb_strlen($prefix))) ==	mb_strtoupper($prefix)) {
				return $value;
			} else {
				return $prefix . $value;
			}
		} else {
			if (mb_substr($value, 0, mb_strlen($prefix)) == $prefix) {
				return $value;
			} else {
				return $prefix . $value;
			}
		}
	}

	/**
	 * Remove any dangerous code that could cause cross site scripting
	 * @param Mixed $data Data to be
	 * @return Mixed Clean version of $data
	 */
	function sanitise($data) {
		if (is_array($data)) {
			$returnData = array();
			foreach($data as $key => $value) {
				$returnData[$key] = $this->sanitiseString($value);
			}
		} else if (is_string($data) || is_numeric($data)) {
			$returnData = $this->sanitiseString($data);
		}
		return $returnData;
	}

	/**
	 * Remove any dangerous code that could cause cross site scripting
	 * @param String $data Data to be sanitised
	 * @return String Clean version of $data
	 */
	function sanitiseString($data) {
		if (is_array($data)) { return $this->sanitiseArray($data); };

		$data = stripslashes($data);
		return str_replace(
			array("\"", "<", ">"),
			array("", "&lt;", "&gt;"), $data);
	}


	/**
	 * Remove any dangerous code that could cause cross site scripting
	 * @param Array $data Array to be sanitised
	 * @return Arrat Clean array
	 */
	function sanitiseArray($data) {
		foreach ($data as $key => $value) {
			$data[$key] = $this->sanitiseString($value);
		}
		return $data;
	}


	/**
	 * Taken from CI clean_xss
	 *
	 * @param string $str
	 * @param string $charset
	 * @return string
	 */
	function sanitiseHtml($str, $charset = "iso-8859-1") {
		//TODO: Reformat to Atrox standard
		//TODO: Fix so that it doesn't clear when a href's are sanitized over 3500 characters
		//TODO: When upgraded to PHP5 we can default $charset to 'utf-8'
		/*
		 * Remove Null Characters
		 *
		 * This prevents sandwiching null characters
		 * between ascii characters, like Java\0script.
		 *
		 */
		$str = preg_replace('/\0+/', '', $str);
		$str = preg_replace('/(\\\\0)+/', '', $str);

		/*
		 * Validate standard character entites
		 *
		 * Add a semicolon if missing.  We do this to enable
		 * the conversion of entities to ASCII later.
		 *
		 */
		$str = preg_replace('#(&\#*\w+)[\x00-\x20]+;#u',"\\1;",$str);

		/*
		 * Validate UTF16 two byte encodeing (x00)
		 *
		 * Just as above, adds a semicolon if missing.
		 *
		 */
		$str = preg_replace('#(&\#x*)([0-9A-F]+);*#iu',"\\1\\2;",$str);

		/*
		 * URL Decode
		 *
		 * Just in case stuff like this is submitted:
		 *
		 * <a href="http://%77%77%77%2E%67%6F%6F%67%6C%65%2E%63%6F%6D">Google</a>
		 *
		 * Note: Normally urldecode() would be easier but it removes plus signs
		 *
		 */
		$str = preg_replace("/%u0([a-z0-9]{3})/i", "&#x\\1;", $str);
		$str = preg_replace("/%([a-z0-9]{2})/i", "&#x\\1;", $str);

		/*
		 * Convert character entities to ASCII
		 *
		 * This permits our tests below to work reliably.
		 * We only convert entities that are within tags since
		 * these are the ones that will pose security problems.
		 *
		 */

		if (preg_match_all("/<(.+?)>/si", $str, $matches))
		{
			for ($i = 0; $i < count($matches['0']); $i++)
			{
				$str = str_replace($matches['1'][$i],
									html_entity_decode($matches['1'][$i], ENT_COMPAT, $charset),
									$str);
			}
		}

		/*
		 * Convert all tabs to spaces
		 *
		 * This prevents strings like this: ja    vascript
		 * Note: we deal with spaces between characters later.
		 *
		 */
		$str = preg_replace("#\t+#", " ", $str);

		/*
		 * Makes PHP tags safe
		 *
		 *  Note: XML tags are inadvertently replaced too:
		 *
		 *    <?xml
		 *
		 * But it doesn't seem to pose a problem.
		 *
		 */
		$str = str_replace(array('<?php', '<?PHP', '<?', '?>'),  array('&lt;?php', '&lt;?PHP', '&lt;?', '?&gt;'), $str);

		/*
		 * Compact any exploded words
		 *
		 * This corrects words like:  j a v a s c r i p t
		 * These words are compacted back to their correct state.
		 *
		 */
		$words = array('javascript', 'vbscript', 'script', 'applet', 'alert', 'document', 'write', 'cookie', 'window');
		foreach ($words as $word)
		{
			$temp = '';
			for ($i = 0; $i < mb_strlen($word); $i++)
			{
				$temp .= mb_substr($word, $i, 1)."\s*";
			}

			$temp = mb_substr($temp, 0, -3);
			$str = preg_replace('#'.$temp.'#s', $word, $str);
			$str = preg_replace('#'.ucfirst($temp).'#s', ucfirst($word), $str);
		}

		/*
		 * Remove disallowed Javascript in links or img tags
		 */

		/*
		 * This pretty much invalidates the whole sanitize function and is a terrible terrible hack.
		 * Put in by Dom Udall under the supervision of Paul Serby 16:57 16-09-2008
		 */
		 $str2 = preg_replace("#<a.+?href=.*?(alert\(|alert&\#40;|javascript\:|window\.|document\.|\.cookie|<script|<xss).*?\>.*?</a>#si", "", $str);
		if ($str2 != null) {
			$str = $str2;
		}

		 $str = preg_replace("#<img.+?src=.*?(alert\(|alert&\#40;|javascript\:|window\.|document\.|\.cookie|<script|<xss).*?\>#si", "", $str);
		 $str = preg_replace("#<(script|xss).*?\>#si", "", $str);
		/*
		 * Remove JavaScript Event Handlers
		 *
		 * Note: This code is a little blunt.  It removes
		 * the event handler and anything up to the closing >,
		 * but it's unlkely to be a problem.
		 *
		 */
		 $str = preg_replace('#(<[^>]*?)((onblur|onchange|onclick|onfocus|onload|onmouseover|onmouseup|onmousedown|onselect|onsubmit|onunload|onkeypress|onkeydown|onkeyup|onresize)=\".*?\"\s*)(.*)>#i',"$1>",$str);

		/*
		 * Sanitize naughty HTML elements
		 *
		 * If a tag containing any of the words in the list
		 * below is found, the tag gets converted to entities.
		 *
		 * So this: <blink>
		 * Becomes: &lt;blink&gt;
		 *
		 */
		$str = preg_replace('#<(/*\s*)(alert|applet|basefont|base|behavior|bgsound|blink|body|embed|expression|form|frameset|frame|head|html|ilayer|iframe|input|layer|link|meta|object|plaintext|style|script|textarea|title|xml|xss)([^>]*)>#is', "&lt;\\1\\2\\3&gt;", $str);

		/*
		 * Sanitize naughty scripting elements
		 *
		 * Similar to above, only instead of looking for
		 * tags it looks for PHP and JavaScript commands
		 * that are disallowed.  Rather than removing the
		 * code, it simply converts the parenthesis to entities
		 * rendering the code unexecutable.
		 *
		 * For example:    eval('some code')
		 * Becomes:        eval&#40;'some code'&#41;
		 *
		 */
		$str = preg_replace('#(alert|cmd|passthru|eval|exec|system|fopen|fsockopen|file|file_get_contents|readfile|unlink)(\s*)\((.*?)\)#si', "\\1\\2&#40;\\3&#41;", $str);

		/*
		 * Final clean up
		 *
		 * This adds a bit of extra precaution in case
		 * something got through the above filters
		 *
		 */
		$bad = array(
						'document.cookie'    => '',
						'document.write'    => '',
						'window.location'    => '',
						"javascript\s*:"    => '',
						"Redirect\s+302"    => '',
						'<!--'                => '&lt;!--',
						'-->'                => '--&gt;'
					);

		foreach ($bad as $key => $val) {
			$str = preg_replace("#".$key."#i", $val, $str);
		}

		return $str;
	}


	/**
	 * Strip tags from text and converts to plan text
	 * @param $value Text to convert
	 * @return plan version of the original html
	 */
	static function convertToText($value) {
		$search = array ("'<a href=\"(.*?)\" .*?>(.*?)</a>'smi",
										"'<img\s.*?(alt=\"(.*?)\"?)>'smi",
										"'<head[^>]*>.*?</head>'smi",
										"'<script[^>]*>.*?</script>'smi",
										"'<style[^>]*>.*?</style>'smi",
										"'\r|\n'smi",
										"'>\s*'sm",
										"'\s\s+'sm",
										"'<td.*?>'smi",
										"'</tr>|<br.*?/?>|</h1>|</h2>|</h3>|</h4>|</h5>|</h6>|</p>|</div>'smi");

		$replace = array ("$2 ($1)",
											"$1",
											"",
											"",
											"",
											"",
											">",
											" ",
											"$1\t",
											"$1\n");

		return html_entity_decode(strip_tags(preg_replace($search, $replace, $value)));
	}

	function getTitleUrl($text, $size = null, $backgroundColor = null, $foregroundColor = null, $font = null) {

		if ($text == "") {
			return false;
		}

		$application = &CoreFactory::getApplication();

		$size == null && $size = $application->registry->get("Site/Titles/Size", 20);
		$backgroundColor == null && $backgroundColor = $application->registry->get("Site/Titles/BackgroundColor", "FFFFFF");
		$foregroundColor == null && $foregroundColor = $application->registry->get("Site/Titles/ForegroundColor", "000000");
		$font == null && $font = $application->registry->get("Site/Titles/Font/Path", "") . "/" . $application->registry->get("Site/Titles/Font/Default", "arial.ttf");

		$fileHash = md5($text . $size . $backgroundColor . $foregroundColor . $font);
		$cacheFile = "title-{$fileHash}.gif?v=@VERSION-NUMBER@";
		$sitePath = $application->registry->get("Site/Cache/Public/WebPath", "/resource/cache") . "/" . $cacheFile;
		$cacheFile = $application->registry->get("Site/Cache/Private/Path", $application->registry->get("Path") . "/resource/cache") . "/" . $cacheFile;

		if (!file_exists($cacheFile)) {

			$imageControl = CoreFactory::getImageControl();
			$padding = 1;

			// The text to draw
			$textDimension = imagettfbbox($size, 0, $font, $text);

			$width = abs($textDimension[4] - $textDimension[0]) + $padding * 2;
			$height = abs($textDimension[5] - $textDimension[1]) + $padding * 2;

			// Create the image
			$image = imagecreate($width, $height);

			$backgroundColor = $imageControl->colorFromHex($image, $backgroundColor);
			$foregroundColor = $imageControl->colorFromHex($image, $foregroundColor);

			// Background
			imagefilledrectangle($image, 0, 0, $width, $height, $backgroundColor);

			// Add the text
			imagettftext($image, $size, 0, $padding, $size + $padding, $foregroundColor, $font, $text);

			// Using imagepng() results in clearer text compared with imagejpeg()
			imagegif($image, $cacheFile);
			imagedestroy($image);
		}

		return $sitePath;
	}

	/**
	 *
	 * @param $value
	 * @return
	 */
	function createImageControl($value, $max, $imageLocation, $blankImageLocation = "", $altText = "") {

		$output = "";
		$value = round($value);
		for ($i = 1; $i <= $value; $i++) {
			$output .= "<img src=\"$imageLocation\" alt=\"" . $altText . "\" title=\"" . $altText . "\" />";
		}
		$remainder = $max - ($i - 1);
		if (($blankImageLocation != "") && ($remainder > 0)) {
			for ($k = 1; $k <= $remainder; $k++) {
				$output .= "<img src=\"$blankImageLocation\" alt=\"" . $altText . "\" title=\"" . $altText . "\" />";
			}
		}
		return $output;
	}
	/**
	 * @param Integer Amount
	 */
	function getNounMultiple($amount, $singular, $plural, $returnAmount = false) {
		return ($returnAmount ? $amount . " " : "") . ($amount != 1 ? $plural : $singular);
	}
	/**
	 * Produces a report based on the sql passed to it and creates columns based on the fields entered.
	 * @return $output String containing the HTML and results to be displayed
	 */
	function produceReport($sql, $fields, $page, $resultsPerPage = 10) {
		$databaseControl = &CoreFactory::getDatabaseControl();
		$result = $databaseControl->query($sql);
		$noResults = $databaseControl->numRows($result);
		$output = "<h2>Report - " . $_POST["StartDate"] . " - " . $_POST["EndDate"] . "</h2>";
		$output .= "<table><tr>";
		foreach ($fields as $field) {
			$output .= "<th>" . $field . "</th>";
		}
		$output .= "</tr>";
		if ($page < 1) {
			$page = 1;
		}

		$i = ($page - 1) * $resultsPerPage;
		$lastRow = min($i + $resultsPerPage, $noResults);
		while (($i < $lastRow) && ($results = $databaseControl->fetchRow($result, $i))) {
			$output .= "<tr>";
			for ($count = 0; $count < (sizeof($results)/2); $count++) {
				$output .= "<td>" . $results[$count] . "</td>";
			}
			$output .= "</tr>";
			$i++;
		}
		$output .= "</table>\n";
		return $output;
	}

		/**
		 * This generates a time dropdown, with specified start and end times being optional
		 * @author Tom Gallacher {tom.gallacher@clock.co.uk
		 * @param unknown_type $name
		 * @param unknown_type $value
		 * @param unknown_type $tabIndex
		 * @param unknown_type $class
		 * @param unknown_type $id
		 * @param unknown_type $onChangeEvent
		 * @param unknown_type $startHour
		 * @param unknown_type $endHour
		 * @return string
		 */
		public function generateTimeDropdown($name, $value, $tabIndex = null, $class = null, $id = null, $onChangeEvent = null, $startHour = null, $endHour = null) {

		$startHour = (is_null($startHour)) ? 1 : $startHour;
		$endHour = (is_null($endHour)) ? 23 : $endHour;
		$hours = range($startHour, $endHour);

		$minutes = array("00", "15", "30", "45");

		$select = "<select name=\"$name\" id=\"$id\" class=\"$class\" tabindex=\"$tabIndex\" onChange=\"$onChangeEvent\">\n";
		foreach ($hours as $hour) {
			$hour = $hour < 10 ? "0" . $hour : $hour;
			foreach ($minutes as $min) {
				$time = $hour . ":" .$min;
				$select .= <<<HTML
<option value="{$time}"
HTML;
        $select .= ($time==$value) ? ' selected="selected"' : '';
        $select .= ">".$time."</option>\n";
			}
		}
		$select .= '</select>';
		return $select;

	}
}