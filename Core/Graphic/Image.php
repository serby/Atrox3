<?php
/**
 * @package Core
 * @subpackage Graphic
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 */

/**
 * Defines
 */
define("IMG_QUALITY", 90);

/**
 * Include Data.php so that DataControl can be extended.
 */
require_once("Atrox/Core/Data/Data.php");

/**
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Core
 * @subpackage Graphic
 */
class ImageValidation extends CustomValidation {

	var $minWidth = null;
	var $minHeight = null;
	var $maxWidth = null;
	var $maxHeight = null;

	function ImageValidation ($minWidth = null, $minHeight = null, $maxWidth = null, $maxHeight = null, $widthAspect = null, $heightAspect = null) {
		$this->minWidth = $minWidth;
		$this->minHeight = $minHeight;
		$this->maxWidth = $maxWidth;
		$this->maxHeight = $maxHeight;
		$this->widthAspect = $widthAspect;
		$this->heightAspect = $heightAspect;
	}

	function validate(&$value, $fieldMeta) {

		// Validation can only take place if there an image has been uploaded
		// which will mean getExtraInfo will be set
		if ($this->getExtraInfo() == null) {
			return true;
		}
		$application = &CoreFactory::getApplication();

		// First check the binary exists
		clearstatcache();
		$size = getimagesize($this->getExtraInfo());
		if ($size) {
			if (($size[2] != 1) && ($size[2] != 2) && ($size[2] != 3)) {
				$application->errorControl->addError(
					"'" . $fieldMeta->description.
					"' is not a valid image. Must be a Jpeg, Gif or Png");
				return false;
			}
			if (($this->minWidth != null) && ($size[0] < $this->minWidth)) {
				$application->errorControl->addError(
					"'" . $fieldMeta->description.
					"' has a width of '$size[0]' less than the minimum width of '$this->minWidth'.");
			}
			if (($this->minHeight != null) && ($size[1] < $this->minHeight)) {
				$application->errorControl->addError(
					"'" . $fieldMeta->description.
					"' has a height of '$size[1]' less than the minimum height of '$this->minHeight'.");
			}
			if (($this->maxWidth != null) && ($size[0] > $this->maxWidth)) {
				$application->errorControl->addError(
					"'" . $fieldMeta->description.
					"' has a width of '$size[0]' greater than the maximum width of '$this->maxWidth' .");
			}
			if (($this->maxHeight != null) && ($size[1] > $this->maxHeight)) {
				$application->errorControl->addError(
					"'" . $fieldMeta->description.
					"' has a height of '$size[1]' greater than the maximum height of '$this->maxHeight'.");
			}
			if (($this->widthAspect != null) && ($this->heightAspect != null)) {
				if (($size[0] / $size[1] * $this->heightAspect) != $this->widthAspect) {
					$application->errorControl->addError(
						"'" . $fieldMeta->description.
						"' does not have the correct aspect ratio of $this->widthAspect:$this->heightAspect");
				}
			}
		} else {
			$application->errorControl->addError(
					"'" . $fieldMeta->description.
					"' is not a valid image. Must be a Jpeg, Gif or Png");
				return false;
		}
	}
}

/**
 * Image control
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Core
 * @subpackage Graphic
 */
class ImageControl {

	/**
	 * Return an RGB from a HEX value
	 */
	function colorFromHex(&$image, $colorHex) {
		if ($colorHex != null) {
			if ($colorHex[0] == "#") {
				$colorHex = mb_substr($colorHex, 1);
			}
			sscanf($colorHex, "%2x%2x%2x", $red, $green, $blue);
			return imagecolorallocate($image, $red, $green, $blue);
		} else {
			 return imagecolorallocate($image, 0, 0, 0);
		}
	}

	function isSize($inputFile, $width, $height) {
		$size = getimagesize($inputFile);
		return $size[0] == $width && $size[1] == $height;
	}

	function watermark($inputFile, $outputFile) {

		$watermarkPath = WATERMARK_LOCATION;

		if ($inputFile == "") {
			trigger_error("Input filename must not be blank");
			return false;
		}
		if (!is_file($inputFile)) {
			trigger_error("Input file '$inputFile' does not exist or it not a file");
			return false;
		}
		if ($outputFile == "") {
			trigger_error("Output filename must not be blank");
			return false;
		}

		clearstatcache();
		$size = getimagesize($inputFile);

		switch($size[2]) {
			case 1:
				$imageSrc = imagecreatefromgif($inputFile);
				break;
			case 2:
				$imageSrc = imagecreatefromjpeg($inputFile);
				break;
			case 3:
				$imageSrc = imagecreatefrompng($inputFile);
				break;
			default:
				$GLOBALS["application"]->errorControl->addError(
				"'$inputFile' is not a valid image. Image must be a Jpeg, Gif or Png");
				return false;
		}

		$w1 = imagesx($imageSrc);
		$h1 = imagesy($imageSrc);

		$watermark = imagecreatefrompng($watermarkPath);
		$watermarkwidth = imagesx($watermark);
		$watermarkheight = imagesy($watermark);
		$startwidth = (($w1 - $watermarkwidth) / 2);
		$startheight = (($h1 - $watermarkheight) / 2);

		$imageDst = imagecreatetruecolor($w1, $h1);
		imagecopy($imageDst, $imageSrc, 0, 0, 0, 0, $w1, $h1);
		imagecopyresampled($imageDst, $watermark, 0, 0, 0, 0, $w1, $h1, $watermarkwidth, $watermarkheight);

		imagejpeg($imageDst, $outputFile);
		imagedestroy($imageSrc);
		imagedestroy($imageDst);
		imagedestroy($watermark);
	}

	function resizeAndCrop($inputFile, $outputFile, &$width, &$height,
			$exactSize = false, $resizeOnly = false) {

		if ($inputFile == "") {
			trigger_error("Input filename must not be blank");
			return false;
		}
		if (!is_file($inputFile)) {
			trigger_error("Input file '$inputFile' does not exist or it not a file");
			return false;
		}
		if ($outputFile == "") {
			trigger_error("Output filename must not be blank");
			return false;
		}

		clearstatcache();
		$size = getimagesize($inputFile);

		if ((($width == null && $height == null)) || (($size[0] == $width) && ($size[1] == $height))) {
			if ($inputFile != $outputFile) {
				copy($inputFile, $outputFile);
			}
			return true;
		}

		switch($size[2]) {
			case 1:
				$imageSrc = imagecreatefromgif($inputFile);
				break;
			case 2:
				$imageSrc = imagecreatefromjpeg($inputFile);
				break;
			case 3:
				$imageSrc = imagecreatefrompng($inputFile);
				break;
			default:
				$application = &CoreFactory::getApplication();
				$application->errorControl->addError(
					"'$inputFile' is not a valid image. Image must be a Jpeg, Gif or Png");
				return false;
		}

		$w1 = imagesx($imageSrc);
		$h1 = imagesy($imageSrc);

		if ($width == "") {
			$width = $w1;
		}

		if ($height == "") {
			$height = $h1;
		}

		// Width and height aspect ratios
		$wa = $width / $w1;
		$ha = $height / $h1;

		// Only allow scaling up if asked to
		if (($wa >= 1) && ($ha >= 1) && (!$exactSize)) {
			//return true;
		}

		$wOffset = 0;
		$hOffset = 0;

		// Which dimesion to scale by
		if ($resizeOnly) {
			$w2 = $w1;
			$h2 = $h1;

			if ($wa > $ha) {
				// If width is bigger then
				$w1 = $w1 * $ha;
				$h1 = $h1 * $ha;
			} else {
				$w1 = $w1 * $wa;
				$h1 = $h1 * $wa;
			}
			$width = min($width, $w1);
			$height = min($height, $h1);
		} else {
			if ($wa > $ha) {
				$w2 = $w1;
				$h2 = ($height/$width)*$w1;
				$hOffset = ($h1 - $h2) / 2;
				$w1 = $width;
				$h1 = $height;
			} else {
				$w2 = ($width/$height) * $h1;
				$h2 = $h1;
				$wOffset = ($w1 - $w2) / 2;
				$w1 = $width;
				$h1 = $height;
			}
		}

		$imageDst = imagecreatetruecolor($width, $height);
		imagecopyresampled($imageDst, $imageSrc, 0, 0, $wOffset, $hOffset, $w1, $h1, $w2, $h2);
		clearstatcache();
		$size = getimagesize($inputFile);
		switch($size[2]) {
			case 1:
				imagegif ($imageDst, $outputFile, IMG_QUALITY);
				break;
			case 2:
				imagejpeg($imageDst, $outputFile, IMG_QUALITY);
				break;
			case 3:
				imagepng($imageDst, $outputFile);
				break;
		}
		return array($width, $height);
	}
}