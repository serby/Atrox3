<?php
/**
 * @package Core
 * @subpackage Data
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 */

/**
 * Include Data.php so that DataControl can be extended.
 */
require_once("Atrox/Core/Data/Data.php");

/**
 * Handles all mod10 validation (used for Credit Card validation)
 *
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Core
 * @subpackage Data
 */
class Mod10Validation extends CustomValidation {

	/**
	 * Validate a credit card field using the Mod10 validation
	 * It will add an error to the global Applications ErrorControl if $value
	 * is not a mod10 valid String
	 * @param String $value The value to validate
	 * @param FieldMeta $fieldMeta The fieldmeta of the correspoding field
	 * @return void
	 */
	function validate(&$value, $fieldMeta) {
		if (!@Validation::mod10($value)) {
			$application = &CoreFactory::getApplication();
			$application->errorControl->addError(
				"'" . $fieldMeta->description.
				"' is not a valid card number.");
		}
	}
}

/**
 * Handles all validation where a User must agree to a given statement/condition
 *
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Core
 * @subpackage Validation
 */
class MustAgreeValidation extends CustomValidation {

	/**
	 * Validate whether a User has agreed to the required statement/condition (i.e. terms and conditions)
	 * It will add an error to the global Applications ErrorControl if $value
	 * is not 't'
	 * @param String $value The value to validate
	 * @param FieldMeta $fieldMeta The fieldmeta of the corresponding field
	 * @return void
	 */
	function validate(&$value, $fieldMeta) {
		if ($value != "t") {
   		$application = &CoreFactory::getApplication();
			$application->errorControl->addError(
				"You must agree to the '" . $fieldMeta->description.
				"'");
		}
	}
}

/**
 * Handles all validation where input must not contain a given phrase
 *
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Core
 * @subpackage Validation
 */
class NotContainValidation extends CustomValidation {

	/**
	 * The phrase that must not appear in the given value
	 * @var String
	 */
	var $phrase;

	/**
	 * @see $phrase
	 * @param String $phrase
	 * @return NotContainValidation
	 */
	function NotContainValidation($phrase) {
		$this->phrase = $phrase;
	}

	/**
	 * Validate whether a User has agreed to the required statement/condition (i.e. terms and conditions)
	 * @param String $value The value to validate
	 * @param FieldMeta $fieldMeta The fieldmeta of the corresponding field
	 * @return void
	 */
	function validate(&$value, $fieldMeta) {
		if ($fieldMeta->allowNull && $value == null) {
			return true;
		}
		if (mb_strpos($value, $this->phrase)) {
			$phrase = $this->phrase;
			if ($this->phrase == " ") {
				$phrase = "&lt;Spaces&gt;";
			}
   		$application = &CoreFactory::getApplication();
			$application->errorControl->addError(
				"'" . $fieldMeta->description .
				"' must not contain '" . $phrase . "'");
		}
	}
}

/**
 * Handles all password validation
 *
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Core
 * @subpackage Validation
 */
class StrongPasswordValidation extends CustomValidation {

	/**
	 * Validate passwords making sure they are at least 8 characters and a mixture of characters and digits
	 * If the password fails validation it will add an error to the global Applications ErrorControl
	 * If the password passes validation, it is hashed
	 * @param String $value The value to validate
	 * @param FieldMeta $fieldMeta The fieldmeta of the corresponding field
	 * @return void
	 * @see Validation::isStrong
	 */
	function validate(&$value, $fieldMeta) {
		if ($fieldMeta->allowNull && $value == null) {
			return true;
		}
		if (!@Validation::isStrongPassword($value, 8)) {
			$application = &CoreFactory::getApplication();
			$application->errorControl->addError(
				"'" . $fieldMeta->description.
				"' must be a valid password with at least 8	characters and a mixture of characters and digits.", "Password");
		} else {
			$passwordEncoder = CoreFactory::getPasswordEncoder();
			$value = $passwordEncoder->format($value);
		}
	}
}

/**
 * Handles all password validation
 *
 * @author Tom Smith {@link mailto:thomas.smith@clock.co.uk thomas.smith@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Core
 * @subpackage Validation
 */
class WeakPasswordValidation extends CustomValidation {

	/**
	 * Validate passwords making sure they are at least 6 characters and a mixture of characters and digits
	 * If the password fails validation it will add an error to the global Applications ErrorControl
	 * If the password passes validation, it is hashed
	 * @param String $value The value to validate
	 * @param FieldMeta $fieldMeta The fieldmeta of the corresponding field
	 * @return void
	 * @see Validation::isWeakPassword
	 */
	function validate(&$value, $fieldMeta) {
		if ($fieldMeta->allowNull && $value == null) {
			return true;
		}
		if (!@Validation::isWeakPassword($value, 6)) {
			$application = &CoreFactory::getApplication();
			$application->errorControl->addError(
				"'" . $fieldMeta->description.
				"' must be a valid password with at least 6	characters", "Password");
		} else {
			$passwordEncoder = CoreFactory::getPasswordEncoder();
			$value = $passwordEncoder->format($value);
		}
	}
}

/**
 * Handles all ISBN validation
 *
 * @author Robert Arnold (Clock Ltd) {@link mailto:robert.arnold@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Core
 * @subpackage Validation
 */
class IsbnValidation extends CustomValidation {

	/**
	 * Validate a ISBN ( International Standard Book Number ) making sure they are at least 10 digits and
	 * contains the country of origin or language code, the publisher, the item number, and a checksum character.
	 * If the validation fails, it will add an error to the global Applications ErrorControl
	 * If the password passes validation, true is returned
	 * @param String $value The ISBN to validate
	 * @param FieldMeta $fieldMeta The fieldmeta of the corresponding field
	 * @return boolean
	 */
	function validate(&$value, $fieldMeta) {
		if ($fieldMeta->allowNull && $value == null) {
			return true;
		}
		$stripped = preg_replace("/[\s-]/", "", $value);

		if (preg_match("/[^\d][x]/", $stripped)) {
			$application = &CoreFactory::getApplication();
			$application->errorControl->addError(
				"'" . $fieldMeta->description.
				"' must be a valid, it must only contain digits.");
		}

		if (mb_strlen($stripped) != 10) {
			$application = &CoreFactory::getApplication();
			$application->errorControl->addError(
				"'" . $fieldMeta->description.
				"' must be exactly 10	digits");
		}

		$check = "";

		for ($i = 0; $i < 10; $i++) {
			$c = mb_substr($stripped, $i, 1);
			$check = $check + (10-$i) * ((($c == "X") || ($c == "x")) ? 10 : $c);
		}
		$check = $check % 11;

		if ($check == 0) {
			return true;
		} else {
			$application = &CoreFactory::getApplication();
			$application->errorControl->addError(
				"'" . $fieldMeta->description.
				"' must be valid.");
		}
	}
}

/**
 * Handles all mobile phone originator and recipient validation
 *
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @author Robert Arnold (Clock Ltd) {@link mailto:robert.arnold@clock.co.uk robert.arnold@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Core
 * @subpackage Validation
 */
class MobileValidation extends CustomValidation {

	/**
	 * Is the value a mobile number
	 * @access private
	 * @var Boolean
	 */
	var $mobileOnly = false;

	/**
	 * Check to see if the parameter is a mobile number (whether it needs strict mobile number validation)
	 * Set $mobileOnly to passed in parameters value (set to false by default)
	 * @param Boolean $mobileOnly The value to set mobileOnly to
	 * @return void
	 */
	function MobileValidation($mobileOnly = false) {
		$this->mobileOnly = $mobileOnly;
	}

	/**
	 * Validate mobile originator or recipient against its corresponding fieldMeta data
	 * If the number/name fails validation it will add an error to the global Applications ErrorControl
	 * @param String $value The value to validate
	 * @param FieldMeta $fieldMeta The fieldmeta of the corresponding field
	 * @return void
	 */
	function validate(&$value, $fieldMeta) {
		if ($fieldMeta->allowNull && $value == null) {
			return true;
		}
		if (is_numeric($value)) {
			$isMobile = $this->validateMobileNumber($value);
		} else {
			$isMobile =	false;
		}
		if ($this->mobileOnly) {
			if(!$isMobile) {
				$application = &CoreFactory::getApplication();
				$application->errorControl->addError("Please ensure that '" . $fieldMeta->description . "' is a valid mobile number");
			}
			if ((mb_strlen($value) > 14) || (mb_strlen($value) < 10)) {
				$application = &CoreFactory::getApplication();
				$application->errorControl->addError("Please ensure that '" . $fieldMeta->description . "' is a valid mobile number");
			}
		} else if ($value == "") {
			$application = &CoreFactory::getApplication();
			$application->errorControl->addError("Please enter a valid '" . $fieldMeta->description . "'");
		} else {
			if (!$isMobile) {
				if (mb_strlen($value) > 11) {
					$application = &CoreFactory::getApplication();
					$application->errorControl->addError("Please ensure that '" . $fieldMeta->description . "' is no longer than 11 characters/digits");
				}
				if (preg_match("/[^a-z0-9\s]/i", $value) > 0) {
					$application = &CoreFactory::getApplication();
					$application->errorControl->addError("Please ensure that '" . $fieldMeta->description . "' is alphanumeric (and doesn't contain special characters)");
				}
			} else if ((mb_strlen($value) != 11) && (mb_substr($value,0,2) != "44")) {
				$application = &CoreFactory::getApplication();
				$application->errorControl->addError("Please ensure that '" . $fieldMeta->description . "' is a valid mobile number");
			}
		}
	}

	/**
	 * Validate a mobile number making sure it is formatted correctly for sending SMS messages
	 * @param String $number The value to validate
	 * @return Boolean True/False
	 */
	function validateMobileNumber(&$number) {
		// Do the best to format number correctly
		$number = str_replace("(","",$number);
		$number = str_replace(")","",$number);
		$number = str_replace(" ","",$number);
		$number = str_replace("-","",$number);
		$number = str_replace("+","",$number);
		if (mb_substr($number,0,4)=="0044") {
			$number = "44".mb_substr($number,4,mb_strlen($number)-4);
		}
		if (mb_substr($number,0,2)=="00") {
			$number = mb_substr($number,2,mb_strlen($number)-2);
		}
		if(mb_substr($number,0,2)=="44") {

			// Validate number
			if (mb_strlen($number)>13) {
				return false;
			} else {
				return true;
			}
		} else {
		if((is_numeric($number)) && (mb_strlen($number)<=13)) {
			return true;
		}
		}
		return false;
	}
}

/**
 * Static Class used for validation
 * All basic validation functions should be defined here and then called
 * statically.
 *
 * @author Paul Serby { @link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Core
 * @subpackage Validation
 */
class Validation {

	/**
	 * Constructor for static class that triggers an error on instantiation and exits
	 */
	function Validation() {
		trigger_error("Unable to create instance of static class 'Validation'");
		exit;
	}

	/**
	 * Validate an e-mail address
	 * Example:
	 * <code>
	 * <?php
	 * 	if (Validation::emailAddress("Paul Serby <paul.serby@clock.co.uk>", true)) {
	 * 		echo "Good E-mail Address\n";
	 *	}
	 * 	if (!Validation::emailAddress("paul.serbyuk")) {
	 * 		echo "Bad E-mail Address\n";
	 *	}
	 * ?>
	 * </code>
	 * The above example will output
	 * <pre>Good E-mail Address
	 * Bad E-mail Address</pre>
	 * @param String $value The value to validate
	 * @param Boolean $allowName Whether or not to allow a name
	 * @return Boolean True/False
	 */
	static function emailAddress($value, $allowName = false) {
		// Unsure whether our regexp covers all addressed (as per the RFCs);
		// http://uk.php.net/eregi#52458

		$value = trim($value);
		$emailRegexp = "[_a-z0-9-]+([\.\'][_a-z0-9-]+)*@[a-z0-9][a-z0-9-]+(\.[a-z0-9][a-z0-9-]*)*(\.[a-z]{2,4})";
		if ($allowName) {
			return mb_eregi("^[^<>]*\s+<" .
				$emailRegexp . ">$", $value) ||
				mb_eregi("^" . $emailRegexp . "$", $value);
		}
		return mb_eregi("^" . $emailRegexp . "$", $value);
	}

	/**
	 * Validate a credit card number
	 * @param String $cardNumber The value to validate
	 * @return Boolean True if $cardNumber is a valid mod10
	 */
	static function mod10($cardNumber) {
		$cardNumber = trim($cardNumber);
		for ($sum = 0, $i = mb_strlen($cardNumber) - 1; $i >= 0; $i--) {
			if (!is_numeric($cardNumber)) {
				return false;
			}
			$sum += $cardNumber[$i];
			$doubdigit = "" . (2 * $cardNumber[--$i]);
			for ($j = mb_strlen($doubdigit) - 1; $j >= 0; $j--) {
				$sum += $doubdigit[$j];
			}
		}
		return ($sum % 10) == 0;
	}

	/**
	 * Validate $password making sure it is Strong
	 * A strong password is any password greater than $minLength which has at
	 * least 1 alphabet character and one number or symbol in it
	 * @param String $password The value to validate
	 * @param Integer $minLength The length to validate password against
	 * @return Boolean True/False
	 */
	static function isStrongPassword($password, $minLength = 6) {
		$password = trim($password);
		$length = mb_strlen($password);

		if ($length < $minLength) {
			return false;
		}

		$charFound = false;
		$digitFound = false;

		for ($i = 0; $i < $length; $i++) {
			$c = ord(mb_substr($password, $i, 1));
			if (($c >= 65) && ($c <= 90) ||
				($c >= 97) && ($c <= 122)) {
				$charFound = true;
			}

			if (($c >= 48) && ($c <= 57)) {
				$digitFound = true;
			}

			if ($digitFound && $charFound) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Validate $password making sure it meets min length
	 * @param String $password The value to validate
	 * @param Integer $minLength The length to validate password against
	 * @return Boolean True/False
	 */
	static function isWeakPassword($password, $minLength = 6) {
		$password = trim($password);
		$length = mb_strlen($password);

		if ($length < $minLength) {
			return false;
		}
		return true;
	}
}