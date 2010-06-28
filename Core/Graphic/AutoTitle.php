<?php
/**
 * @package Core
 * @subpackage Graphic
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 */

/**
 * Control for automatically creating graphical text, used in header etc.
 *
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Core
 * @subpackage Graphic
 */
class AutoTitleControl {

	/**
	 * Constructs a new AutoTitleControl with the given options
	 *
	 * @param object $options
	 * @return AutoTitleControl
	 */
	function AutoTitleControl($options = null) {

		$this->options = (object)array(
			"language" => "en",
			"maxWidth" => -1,
			"fontSize" => 12,
			"fontFile" => "arial.ttf?v=@VERSION-NUMBER@",
			"paddingTop" => 0,
			"paddingRight" => 0,
			"paddingBottom" => 0,
			"paddingLeft" => 0,
			"leading" => 10,
			"backgroundHexColor" => "000000",
			"foregroundHexColor" => "FFFFFF",
			"transparent" => false,
			"recreate" => false,
			"url" => ""

		);

		if (is_object($options)) {
			$this->setOptions($options);
		}
	}

	function setOptions($options) {
		foreach ($options as $key => $value) {
			if (isset($this->options->$key)) {
				$this->options->$key = $value;
			}
		}
	}

	function getFullPath() {

	}

	function getApplicationPath() {

	}

	function validateOptions(&$options) {
		$colorControl = CoreFactory::getColorControl();

		if (!$colorControl->isHexColor($options->backgroundHexColor)) {
			trigger_error("Invalid background hex color.");
			return false;
		}

		if (!$colorControl->isHexColor($options->foregroundHexColor)) {
			trigger_error("Invalid foreground hex color.");
			return false;
		}

		return true;
	}

	function getTitlePath($text, $functionOptions = null) {
		$options = $this->options;
		if (is_array($functionOptions)) {
			foreach ($functionOptions as $key => $value) {
				if (isset($options->$key)) {
					$options->$key = $value;
				}
			}

			if (!$this->validateOptions($options)) {
				trigger_error("Invalid options for creating an AutoTitle");
				return false;
			}
		}

		$application = &CoreFactory::getApplication();

		// TODO: Find out why spaces and special characters are replaced with nothing.
		// For example, will cause problems with "Dom sand" and "Doms and".
		$cachePath = $application->registry->get("Cache/AutoTitle/Path", $application->registry->get("Path") . "/resource/autotitle") .
			"/" . md5(print_r($options, true));

		if (!is_dir($cachePath)) {
			mkdir($cachePath);
		}

		$outputFile = $cachePath . "/" . preg_replace("'[^\w\d-_]'", "", $text) . ".png?v=@VERSION-NUMBER@";

		return $this->createTitle($text, $outputFile, $options);
	}

	function createTitle($text, $outputFile, $options) {
		$application = &CoreFactory::getApplication();
		$localFilename = $application->registry->get("Cache/AutoTitle/LocalPath") ?
			$application->registry->get("Cache/AutoTitle/LocalPath") .
			mb_substr($outputFile, mb_strlen($application->registry->get("Cache/AutoTitle/Path")))
			: mb_substr($outputFile, mb_strlen($application->registry->get("Path")));

		if ((!$options->recreate) && (is_file($outputFile))) {
			$size = getimagesize($outputFile);

			return (object)array("filename" => $outputFile, "localFilename" => $localFilename, "width" => $size[0], "height" => $size[1]);
		}

		if (!file_exists($options->fontFile)) {

			$application = &CoreFactory::getApplication();
			$options->fontFile = $application->registry->get("Path") . "/" . $options->fontFile;

			if (!file_exists($options->fontFile)) {
				trigger_error("Font file not found.");
				return false;
			}
		}
		$lineCount = 1;

		$textDimension = $this->getTitleDimensions($text, $options->fontSize, $options->fontFile);
		$textDimension->height = $textDimension->height + ($options->paddingTop + $options->paddingBottom);

		$maxTextDimension = $this->getTitleDimensions("abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890.,?!:;-", $options->fontSize, $options->fontFile);
		$maxTextDimension->height = $maxTextDimension->height + ($options->paddingTop + $options->paddingBottom);

		$totalMaximumHeight = $maxTextDimension->height;
		if (($options->maxWidth != -1) && ($textDimension->width +
			$options->paddingLeft + $options->paddingRight > $options->maxWidth)) {

			$spaceDimension = $this->getTitleDimensions(" ", $options->fontSize, $options->fontFile);
			$spaceWidth = $spaceDimension->width;
			$language = "";
			if ($language == "zh") {
				$outputText = str_split($text, 3);
			} else {
				$outputText = explode(" ", $text);
			}

			$totalWords = count($outputText);

			$text = "";
			$newLine = "";
			$space = "";

			for ($i = 0; $i < $totalWords; $i++) {
				$dimension = $this->getTitleDimensions($newLine . $space . $outputText[$i], $options->fontSize, $options->fontFile);
				$currentWidth = $dimension->width;
				if ($currentWidth + $options->paddingLeft + $options->paddingRight > $options->maxWidth) {
					$totalMaximumHeight = $totalMaximumHeight + $dimension->height;
					$newLine .= $i > 0 ? "\n" : "";
					$text .= $newLine;
					$newLine = $outputText[$i];
					$lineCount++;
				} else {
					$newLine .= $space . $outputText[$i];
					$space = " ";
				}
			}
			$text .= $newLine;
		}

		$size["width"] = $options->maxWidth == -1 ? $textDimension->width + $options->paddingLeft +
			$options->paddingRight : $options->maxWidth;

		$lineHeight = $maxTextDimension->height;
		if ($lineCount > 1) {
			$size["height"] = (($lineHeight + $options->leading) * $lineCount) +
			$options->paddingTop + $options->paddingBottom;
		} else {
			$size["height"] = $lineHeight;
		}

		$image = imagecreate($size["width"], $size["height"]);

		$colorControl = CoreFactory::getColorControl();
		$backgroundColor = $colorControl->getRgbFromHex($options->backgroundHexColor);
		$foregroundColor = $colorControl->getRgbFromHex($options->foregroundHexColor);
		$backgroundColor = imagecolorallocate($image, $backgroundColor->red, $backgroundColor->green, $backgroundColor->blue);
		$foregroundColor = imagecolorallocate($image, $foregroundColor->red, $foregroundColor->green, $foregroundColor->blue);

		if ($options->transparent) {
			imagecolortransparent($image, $backgroundColor);
		}

		imagettftext($image, $options->fontSize, 0, $options->paddingLeft, ($options->fontSize) + $options->paddingTop,
			$foregroundColor, $options->fontFile, $text);

		imagepng($image, $outputFile);
		imagedestroy($image);

		return (object)array("filename" => $outputFile, "localFilename" => $localFilename, "width" => $size["width"], "height" => $size["height"]);
	}

	function getTitleDimensions($text, $fontSize, $fontFile, $kerning = 0) {
		$textDimension = imagettfbbox($fontSize, 0, $fontFile, $text);
		$height = max($textDimension[1], $textDimension[3]) - min($textDimension[5], $textDimension[7]);
		$width = max($textDimension[2], $textDimension[4]) - min($textDimension[0], $textDimension[6]);
		return (object)array("width" => $width, "height" => $height);
	}
}