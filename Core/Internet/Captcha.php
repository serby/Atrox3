<?php
/**
 * @package Core
 * @subpackage Internet
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision: 823 $ - $Date: 2008-12-10 13:00:57 +0000 (Wed, 10 Dec 2008) $
 */

/**
 * CAPTCHA (Completely Automated Public Turing test to tell Computers and Humans Apart)
 * Generator and validator to stop bots spaming blogs forums etc.
 *
 * @author Kapil Gohil (Clock Ltd) {@link mailto:kapil.gohil@clock.co.uk kapil.gohil@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Base
 * @subpackage Internet
 */
class Captcha {

	/**
	 * Characters that are used in a Captcha phrase
	 * @var String
	 */
	protected $availableCharacters = "ABDEFGHJLMNQRTYabdefghijmnqrty23456789";

	/**
	 * Set the available characters to be used in a Captcha phrase
	 * @param string $availableCharacters
	 */
	public function setAvailableCharacters($availableCharacters) {
		if (!ctype_alnum($availableCharacters)) {
			throw Exception("Only alpha numeric characters are to be used with the Captcha");
		}

		$this->availableCharacters = $availableCharacters;
	}

	/**
	 * This function randomly generates a code to check aganst
	 * @param int $length Length of the code to generate.
	 */
	function generateCode($length = 5) {
		$characterLength = strlen($this->availableCharacters) - 1;
		$captcha = "";
		for ($i = 0 ; $i < $length ; $i++){
			$captcha .= $this->availableCharacters[rand(0, $characterLength)];
		}
		return $_SESSION["CaptchaCode"] = $captcha;
	}

	/**
	 * Checks that $code is the last generated CAPTCHA
	 *
	 * @param string $code Code to check generated code aganst
	 * @return boolean True if the codes match
	 */
	function check($code) {
		return isset($_SESSION["CaptchaCode"]) && $_SESSION["CaptchaCode"] == $code;
	}

	/**
	 * Generate a CAPTCHA image
	 *
	 * @param int $width
	 * @param int $height
	 * @param string $code
	 * @return void
	 */
	function generateCaptchaImage($width, $height, $code = null) {
		if ($code == null) {
			$this->generateCode();
			$code = $_SESSION["CaptchaCode"];
		}

		$application = &CoreFactory::getApplication();
		$font = "../font/arial.ttf";

		$fontSize = $height * 0.60;
		$image = imagecreatetruecolor($width, $height);

		$rgb->red = rand(100, 255);
		$rgb->green = rand(100, 255);
		$rgb->blue = rand(100, 255);

		// Define colours
		$backgroundColor = imagecolorallocate($image, $rgb->red, $rgb->green, $rgb->blue);

		imagefilledrectangle($image, 0, 0, $width, $height, $backgroundColor);

		$textColor = imagecolorallocate($image,
			$rgb->red - 50,
			$rgb->green - 50,
			$rgb->blue - 50);

		$noiseColor = $textColor;


		$textbox = imagettfbbox($fontSize, 0, $font, "m");
		// Add the text
		$x = $textbox[4];
		$wWidth = $textbox[4];
		$y = ($height - $textbox[5]) / 2;

		for ($i = 0; $i < strlen($code); $i++) {
			imagettftext($image, $fontSize + rand(-2, 2), rand(-15, 15), 10 + ($wWidth * $i), $y + rand(-5, 5), $textColor, $font, $code[$i]);
		}

		// Add more noise
		for($i = 0; $i < ($width * $height) * 0.20; $i++ ) {
			imageline($image, $lineX = mt_rand(0, $width), $lineY = mt_rand(0, $height), $lineX, $lineY, $noiseColor);
		}

		// Output captcha image to browser
		header("Content-Type: image/gif");
		imagegif($image);
		imagedestroy($image);
	}
}