<?php
/**
 *
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Core
 */

/**
 * Useful constants for the entire application, for example seconds
 * in set time periods.
 *
 * @author Dom Udall (Clock Ltd) {@link mailto:dom.udall@clock.co.uk dom.udall@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Core
 */
define("SECONDS_IN_MINUTE", 60);
define("SECONDS_IN_HOUR", 3600);
define("SECONDS_IN_DAY", 86400);
define("SECONDS_IN_WEEK", 604800);

/**
 * All Web application will have an instance of this class to help
 * handle elements such as Errors, Session and Form submissions
 *
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Core
 */
class Application {

	/**
	 * Is the application in debug mode
	 * @var Boolean
	 * @see isDebug, setDebug
	 */
	var $debug = false;

	/**
	 * Where Application level setting are stored
	 * @var Registry
	 */
	var $registry;

	/**
	 * Global Error Control
	 * @var ErrorControl;
	 */
	var $errorControl;

	/**
	 * Default TimeZone.
	 * This should be set to the timezone that the server is set to
	 */
	var $timeZoneId = "GB";

	/**
	 * How should Dates be formatted
	 */
	var $dateFormat = "%d %b %Y";

	/**
	 * How should Times be formatted
	 */
	var $timeFormat = "%R %Z";

	/**
	 * Default Currency Symbol
	 */
	var $currencySymbol = "&pound;";

	/**
	 * Default Exchange Rate
	 */
	var $exchangeRate = 1;

	/**
	 * Default Country
	 */
	var $currentCountryId = 1;

	/**
	 * var SecurityControl
	 */
	var $securityControl = null;

	/**
	 * The default content type for output.
	 * Changing this to text/plain will make errors output in plain text.
	 */
	var $contentType = "text/html";


	/**
	 * @var DatabaseControl
	 */
	var $databaseControl;

	function Application() {

		set_error_handler(array("Application", "customErrorHandler"));
		@session_start();
		$this->registry = CoreFactory::getRegistry();
		$this->errorControl = CoreFactory::getErrorControl();

		$this->securityControl = CoreFactory::getSecurityControl();
		$this->databaseControl = CoreFactory::getDatabaseControl();

		if (isset($_SESSION["Application"])) {
			$this->currencySymbol = $_SESSION["Application"]->currencySymbol;
			$this->exchangeRate = $_SESSION["Application"]->exchangeRate;
			$this->currentCountryId = $_SESSION["Application"]->currentCountryId;
		}
		// Make the server work in UTC
		putenv("TZ=UTC");
	}

	/**
	 * Content Loaded
	 */
	function isContextLoaded() {
		return false;
	}

	/**
	 * Saves the current application state
	 */
	function save() {
		$_SESSION["Application"]->currencySymbol = $this->currencySymbol;
		$_SESSION["Application"]->exchangeRate = $this->exchangeRate;
		$_SESSION["Application"]->currentCountryId = $this->currentCountryId;
	}

	/**
	 * Sets a session or member setting. If the setting exists then it will
	 * be overwritten with the new value, otherwise a new setting will be
	 * created.
	 * @param String $name Unquie name for the setting.
	 * @param String $setting The value of the setting.
	 */
	function setSetting($name, $setting) {
		$_SESSION["Application"]->settings[$name] = $setting;
		$securityControl->setSetting($name, $setting);
	}

	/**
	 * Gets a session or member setting. If the setting doesn't exists then null
	 * will be returned.
	 * @param String $name Unique name for the setting.
	 * @return String The value of setting $name.
	 */
	function getSetting($name) {
		if (!isset($_SESSION["Application"]->settings[$name])) {
			$_SESSION["Application"]->settings[$name] = $securityControl->getSetting($name);
			return $_SESSION["Application"]->settings[$name];
		}
		return "";
	}

	/**
	 * Sets the Application's Content Type, and sends modified headers

	 * @param String $contentType The new content type
	 * @return void
	 */
	function setContentType($contentType) {
		$this->contentType = $contentType;
		// Add a HTTP header if we can
		@header("Content-type:" . $contentType);
	}

	/**
	 * Store history of calls to this function
	 * @param String $type The type of history to store
	 * @param String $value The value to store
	 * @param String $size The maxiumn number of history steps to store
	 * @return Array An Array full of the historic steps
	 */
	function storeHistory($type, &$value, $size = 10) {
		//TODO: This should be removed from Application and make part of the user section
		$null = null;
		if (!isset($value)) {
			return false;
		}
		$history = $this->securityControl->getSetting(
			"History::$type", $null);

		$history = explode(",", $history);

		$originalTopElement = $history[0];

		array_unshift($history, $_GET["Id"]);
		$history = array_unique($history);
		$newTopElement = $history[0];

		if ($newTopElement != $originalTopElement) {
			$cacheControl = &CoreFactory::getCacheControl();
			$cacheControl->deleteWebPageCache("History-$type");
		}

		if (sizeof($history) > $size) {
			$history = array_slice($history, 0, $size);
		}

		$history = $this->securityControl->setSetting(
			"History::$type", implode(",", $history));

		return $history;
	}

	/**
	 * Get an array of all the history items for a given type
	 * @param String $type The type of history to store
	 * @return Array An Array full of the historic steps of false if there are is no history to remove
	 */
	function getHistory($type) {
		$null = null;
		$history = $this->securityControl->getSetting(
			"History::$type", $null);
		if ($history != null) {
			$history = explode(",", $history);
			return $history;
		}
		return false;
	}

	function clearHistory($type) {
		$history = $this->securityControl->setSetting("History::$type", "");
	}

	/**
	 * Sets the country ID for this session
	 * @param String $id The country ID
	 * @return null
	 */
	function setCountryId($id) {
		$this->currentCountry = $id;
		$this->save();
	}

	function getCountryId() {
		return $this->currentCountryId;
	}

	function setExchangeRate($rate) {
		$this->exchangeRate = $rate;
		$this->save();
	}

	function getExchangeRate() {
		return $this->exchangeRate;
	}

	function setCurrencySymbol($currencySymbol) {
		$this->currencySymbol = $currencySymbol;
		$this->save();
	}

	/**
	 * If on development server, message is displayed inside pre tags
	 * @param Mixed $message The message to be displayed inside <pre> tag
	 * @return void
	 */
	function debugEcho($message) {
		if (($_SERVER["REMOTE_ADDR"] == "195.173.111.2") || ($_SERVER["REMOTE_ADDR"] == "85.189.107.190")) {
			echo "<pre>$message</pre>";
		}
	}

	/**
	 * Sets the database connection string
	 * @param $connectionString The connection string for the relivate database
	 * @return Returns true on success
	 */
	function setConnectionString($connectionString) {
		if ($connectionString == null) {
			trigger_error("Connection string can not be null");
			return false;
		}
		$this->connectionString = $connectionString;
		return true;
	}

	/**
	 * Gets the current database connection string
	 * @return string The current database connection string;
	 */
	function getConnectionString() {
		return $this->connectionString;
	}

	/**
	 * Gets the Ip address of the current connection
	 *
	 * @return string IP Address
	 */
	function getRemoteIpAddress() {
		if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
			$ipAddress = $_SERVER["HTTP_X_FORWARDED_FOR"];
		} else {
			$ipAddress = $_SERVER["REMOTE_ADDR"];
		}

		$ipAddress = $this->trimIpPrimaryAddress($ipAddress);

		return $ipAddress;
	}

	/**
	 * Trims IP Address to only return the primary IP address and not include any others, such as from a load balancer
	 * @example "192.168.50.1, 45.56.3.12" returns "192.168.50.1"
	 *
	 * @param string IP Address
	 *
	 * @return string IP Address
	 */
	protected function trimIpPrimaryAddress($ipAddress) {
		$position = mb_strpos($ipAddress, ",");
		if ($position !== false) {
			return mb_substr($ipAddress, 0, $position);
		}

		return $ipAddress;
	}

	function required(&$value) {
		if ((!isset($value)) || ($value == null)) {
			Application::doNotStorePage();
			Application::gotoLastPage();
		}
	}

	/**
	 * This is intended to show if a page has been viewed this session.
	 * An Id is passed in then the function returns True if that Id has already be passed
	 * in during this browser session.
	 * @param Integer $id Unique Id
	 * @return Boolean True if the Id has
	 */
	function visited($id) {
		if (isset($_SESSION["Visited"][$id])) {
			return true;
		} else {
			$_SESSION["Visited"][$id] = true;
		}
	}

	/**
	 * Used to parse page actions
	 * @return String Contains the page action
	 */
	function parseSubmit() {
		//TODO: This should be in a web package
		// IE has a very annoying feature where by when a form is
		// submitted by pressing enter whilst in a text control, the
		// form is submitted yet the Submit value is passed through.
		// Therefore this will make sure that at lease Submit is returned
		if (sizeof($_POST) > 0) {
			$submit = "__Submit";
			if (isset($_POST["Submit"])) {
				if (is_array($_POST["Submit"])) {
					$keys = array_keys($_POST["Submit"]);
					$submit = $keys[0];
				} else {
					$submit = $_POST["Submit"];
				}
			} else if (isset($_GET["Submit"])) {
				$submit = $_GET["Submit"];
			}
			return $submit;
		} else if (mb_strlen($_SERVER["QUERY_STRING"]) > 0) {
			$submit = "__Submit";
			if (isset($_GET["Submit"])) {
				$submit = $_GET["Submit"];
			}
			return $submit;
		}
		return false;
	}

  /**
	 * Sets the application into debug mode.
	 * Will set a Debugging Error Handler with a stack trace and
	 * variable dump.
	 * @see isDebug
	 * @return void
	 */
	function setDebug($on = true) {
		if (!defined("DEBUG_MODE")) {
			define("DEBUG_MODE", $on);
		}
		$this->debug = $on;
}

	/**
	 * Is the application currently in debug mode.
	 * @see setDebug
	 * @return Boolean True if currently in debug mode
	 */
	function isDebug() {
		return $this->debug;
	}

	/**
	 * Redirects if page isn't secure, with $redirect = false it will return the ssl state (boolean)
	 * @author Dom Udall (Clock Ltd) {@link mailto:dom.udall@clock.co.uk}
	 * @date 2008-02-19
	 * @param Boolean $sslRequired Should this page be on SSL
	 * @param Boolean $redirect Should the application redirect if the URL is not $sslRequired
	 * @return Boolean True if currently in SSL (if $set === false)
	 */
	function isSecure($sslRequired = true, $redirect = true) {
			//TODO: This should be in a web package
		if ($this->registry->get("Site/HasSsl", true)) {

			$isSecure = !empty($_SERVER["HTTPS"]) || (!empty($_SERVER["HTTP_X_FORWARDED_PORT"]) && ($_SERVER["HTTP_X_FORWARDED_PORT"] == 443));

			if ($isSecure == $sslRequired) {
				return true;
			} else {
				if ($redirect) {
					$this->redirect($this->createUrl(null, $sslRequired));
				}
			}
			return false;
		}
	}

	/**
	 * Create a Non-SSL/SSL absolute URL from a relative path.
	 * @author Edward Pearson (Clock Ltd) {@link mailto:edward.pearson@clock.co.uk}
	 * @date 2008-02-19
	 * @example:
	 *  $this->registry->set("Site/HasSsl", true)
	 *  echo $application->createUrl("/store/", true);
	 *  // Would print
	 *  https://<siteaddress>/store/
	 * @param String $relativePath The relative path to be used in the creation of an absolute URL
	 * @param Boolean $ssl Should this path be on SSL
	 * @return String Absolute URL with protocal depending on $ssl
	 */
	function createUrl($relativePath = null, $ssl = true) {
			//TODO: This should be in a web package
		if ($this->registry->get("Site/HasSsl", true)) {
			if ($relativePath == null) {
				$relativePath = $_SERVER["REQUEST_URI"];
			}
			if ($ssl) {
				$prefix = $this->registry->get("Site/Ssl/Address");
			} else {
				$prefix = $this->registry->get("Site/Address");
			}
			$result = !preg_match("/^http(s)?:\/\//", $relativePath);
			if ($relativePath[0] != "/" && $result) {
				$relativePath = $prefix . "/" . dirname($_SERVER["REQUEST_URI"]) . $relativePath;
			} else if ($relativePath[0] == "/" && $result) {
				$relativePath = $prefix . $relativePath;
			}
			return $relativePath;
		} else {
			return $relativePath;
		}
	}

	/**
	 * Goto given page
	 * @param $url
	 */
	function redirect($url = "") {
		//TODO: This should be in a web package
		if ($url == "") {
			if ((isset($_SESSION["GotoPage"])) && ($_SESSION["GotoPage"] != "")) {
				$url = $_SESSION["GotoPage"];
			} else {
				Application::gotoLastPage();
			}
		}
		header("Location: $url");
		$_SESSION["GotoPage"] = $url;
		exit;
	}

	function displayErrorPage($path, $code = 404) {
		header("HTTP/1.1 {$code}");
		header("Status: {$code}");
		$application = $this;
		include $path;
		exit;
	}

	/**
	 * Reloads the current page
	 */
	function reload() {
			//TODO: This should be in a web package
		header("Location: " . $_SERVER["PHP_SELF"]);
		exit;
	}

	/**
	 * Gets the current URL
	 * @return String The current URL
	 */
	function getCurrentUrl() {
		//TODO: This should be in a web package
		$host = isset($_SERVER["HTTP_HOST"]) ? $_SERVER["HTTP_HOST"] : $_SERVER["SERVER_ADDR"];
		return (isset($_SERVER["HTTPS"])?"https://":"http://") . $host . $_SERVER["REQUEST_URI"];
	}

	/**
	 * Stores the current and last pages so
	 */
	function storePage() {
			//TODO: This should be in a web package
		if (!isset($_SERVER["SERVER_ADDR"])) {
			return;
		}

		$currentURL = $this->getCurrentUrl();
		if ((isset($_SESSION["CurrentPage"])) && ($currentURL == $_SESSION["CurrentPage"])) {

		} else {
			if (isset($_SESSION["CurrentPage"])) {
				$_SESSION["LastPage"] = $_SESSION["CurrentPage"];
			}
			$_SESSION["CurrentPage"] = $currentURL;
		}
	}

	function isRequestFromSelf() {
		if ((isset($_SERVER["HTTP_HOST"])) && (isset($_SERVER["HTTP_REFERER"]))) {
			$urlParts = parse_url($_SERVER["HTTP_REFERER"]);
			$host = $_SERVER["HTTP_HOST"];
			if ($pos = strpos($_SERVER["HTTP_HOST"], ":")) {
				$host = substr($host, 0, $pos);
			}
			return $host == $urlParts["host"];
		}
		return false;
	}

	/**
	 * Used on non returnable pages
	 */
	function doNotStorePage() {
			//TODO: This should be in a web package
		$_SESSION["CurrentPage"] = isset($_SESSION["LastPage"]) ? $_SESSION["LastPage"] : "";
		$_SESSION["GotoPage"] = "";
	}

	/**
	 * Goto last stored page
	 */
	function goToLastPage($loadStoredRequests = false, $additional = "", $defaultUrl = "/") {
		//TODO: This should be in a web package
		if ((isset($_SESSION["LastPage"])) && ($_SESSION["LastPage"] != "")) {
			$_SESSION["LoadRequestData"] = $loadStoredRequests;
			if (!empty($_REQUEST["ReturnUrl"])) {
				header("Location: {$_REQUEST["ReturnUrl"]}$additional");
			} else {
				header("Location: $_SESSION[LastPage]$additional");
			}
			exit;
		}
		header("Location: $defaultUrl");
		exit;
	}

	/**
	 * Returns the Last Page
	 * @return String The last page visited
	 */
	function getLastPage($returnUrl = null) {
		//TODO: This should be in a web package
		if (isset($_SESSION["LastPage"])) {
			return urldecode($_SESSION["LastPage"]);
		} else if (!empty($returnUrl)) {
			return $returnUrl;
		} else {
			return "/";
		}
	}

	function loadRequestData() {
			//TODO: This should be in a web package
		if ($_SESSION["LoadRequestData"]) {
			$_POST = $_SESSION["StoredPost"];
			$_GET = $_SESSION["StoredGet"];
			unset($_SESSION["StoredPost"]);
			unset($_SESSION["StoredGet"]);
			unset($_SESSION["LoadRequestData"]);
		}
	}

	function storeRequestData()	{
		//TODO: This should be in a web package
		$_SESSION["StoredPost"] = $_POST;
		$_SESSION["StoredGet"] = $_GET;
	}

	function defaultValue(&$value, $default = "") {
		if (isset($value)) {
			return $value;
		} else {
			return $default;
		}
	}

	function log($string, $type) {
		if ($this->isDebug() && $this->registry->get("Log/Path")) {
			if (strpos(php_sapi_name(), "cli") === false) {
				@include_once("FirePHPCore/FirePHP.class.php");
				if (class_exists("FirePHP", false)) {
					$firephp = FirePHP::getInstance(true);
					$firephp->log($string, $type);
				}
			}

			if (!(file_exists($this->registry->get("Log/Path")))) {
				mkdir($this->registry->get("Log/Path"), null, true);
			}
			error_log(date("Y-m-d H:i:s") . (isset($_SESSION["Alias"]) ?  " - " . $_SESSION["Alias"] : "") . $string, 3, $this->registry->get("Log/Path") . "/" . $type . ".log");
		}
	}

	function formatCurrency($amount, $decimalPlaces = 2) {
		if ($amount === null || $amount === "") {
			return;
		}
		return $this->currencySymbol . number_format(((float)$amount) * $this->exchangeRate, $decimalPlaces);
	}

	function encodeCurrency($amount) {
		static $called = 1;
		return round(((float)$amount) / $this->exchangeRate, 2);
	}

	function localiseTime($time) {
		require_once("Date.php");
		$date = new Date($time);
		$date->setTZbyID("UTC");
		$date->convertTZbyID($this->timeZoneId);
		return $date->getTime();
	}

	function formatMonthYear($dateValue, $nullValue = "Not Set") {
		if ($dateValue == null) {
			return $nullValue;
		}
		require_once("Date.php");
		$date = new Date($dateValue);
		$date->setTZbyID("UTC");
		$date->convertTZbyID($this->timeZoneId);
		return $date->format("%b %Y");
	}

	function formatDate($dateValue, $nullValue = "Not Set") {
		if ($dateValue == null) {
			return $nullValue;
		}
		require_once("Date.php");
		$date = new Date($dateValue);
		$date->setTZbyID("UTC");
		$date->convertTZbyID($this->timeZoneId);
		return $date->format($this->dateFormat);
	}

	function formatLongDate($dateValue, $nullValue = "Not Set") {
		if ($dateValue == null) {
			return $nullValue;
		}
		require_once("Date.php");
		$date = new Date($dateValue);
		$date->setTZbyID("UTC");
		$date->convertTZbyID($this->timeZoneId);
		return $date->format("%A, %d %B %Y");
	}

	function formatTime($dateValue, $nullValue = "Not Set") {
		if ($dateValue == null) {
			return $nullValue;
		}
		require_once("Date.php");
		$date = new Date($dateValue);
		$date->setTZbyID("UTC");
		$date->convertTZbyID($this->timeZoneId);
		return $date->format($this->timeFormat);
	}

	function formatDecimalTime($decimalTime, $nullValue = "Not Set") {
		if ($decimalTime == null) {
			return $nullValue;
		}
		return floor($decimalTime) . "h "
			. sprintf("%02d", round(60 * ($decimalTime - floor($decimalTime)))) . "m";
	}

	function formatDateTime($dateValue, $nullValue = "Not Set") {
		if ($dateValue == null) {
			return $nullValue;
		}
		require_once("Date.php");
		$date = new Date($dateValue);
		$date->setTZbyID("UTC");
		$date->convertTZbyID($this->timeZoneId);
		return $date->format($this->dateFormat . " - " . $this->timeFormat);
	}

	function localiseDateTime($dateValue, $nullValue = "Not Set") {
		if ($dateValue == null) {
			return $nullValue;
		}
		require_once("Date.php");
		$date = new Date($dateValue);
		$date->setTZbyID("UTC");
		$date->convertTZbyID($this->timeZoneId);
		return $date->getDate();
	}

	function formatDBDateTime($dateValue, $nullValue = "Not Set", $offset = 0) {
		if ($dateValue == null) {
			return $nullValue;
		}
		require_once("Date.php");
		$date = new Date($dateValue);
		$date->setTZbyID($this->timeZoneId);
		$date->convertTZbyID("UTC");
		if ($offset < 0) {
			$date->subtractSeconds($offset * -1);
		} else {
			$date->addSeconds($offset);
		}
		return $date->format("%Y-%m-%d %H:%M:%S");
	}

	function customFormatDateTime($dateValue, $format = "%d %b %Y", $nullValue = "Not Set") {
		if ($dateValue == null) {
			return $nullValue;
		}
		require_once("Date.php");
		$date = new Date($dateValue);
		$date->setTZbyID("UTC");
		$date->convertTZbyID($this->timeZoneId);
		return $date->format($format);
	}

	function getCurrentTime($offset = 0) {
		require_once("Date.php");
   	$date = new Date();
		$date->convertTZbyID($this->timeZoneId);
		if ($offset < 0) {
			$date->subtractSeconds($offset * -1);
		} else {
			$date->addSeconds($offset);
		}
		return $date->format("%R:%S");
	}

	function getCurrentDateObject() {
		require_once("Date.php");
		$date = new Date();
		$date->convertTZbyID($this->timeZoneId);
		return $date;
	}

	function getCurrentDate($offset = 0) {
		require_once("Date.php");
		$date = new Date();
		$date->convertTZbyID($this->timeZoneId);
		if ($offset < 0) {
			$date->subtractSeconds($offset * -1);
		} else {
			$date->addSeconds($offset);
		}
		return $date->format("%Y-%m-%d");
	}

	function getCurrentDateTime($offset = 0) {
		require_once("Date.php");
		$date = new Date();
		$date->convertTZbyID($this->timeZoneId);
		if ($offset < 0) {
			$date->subtractSeconds($offset * -1);
		} else {
			$date->addSeconds($offset);
		}
		return $date->format("%Y-%m-%d %H:%M:%S");
	}

	function getCurrentUtcDateTime() {
		return $this->formatDBDateTime($this->getCurrentDateTime());
	}

	function getFormattedCurrentDate() {
		require_once("Date.php");
   	$date = new Date();
		$date->convertTZbyID($this->timeZoneId);
		return $date->format($this->dateFormat);
	}

	function getFormattedCurrentDateTime() {
		require_once("Date.php");
  	$date = new Date();
		$date->convertTZbyID($this->timeZoneId);
		return $date->format($this->dateFormat . " - " . $this->timeFormat);
	}

	function getDateFormat() {
		return $this->dateFormat;
	}

	function setDateFormat($format) {
		$this->dateFormat = $format;
	}

	function getTimeFormat() {
		return $this->timeFormat;
	}

	function setTimeFormat($format) {
		$this->timeFormat = $format;
	}

	function getCurrentTimeZoneId() {
		return $this->timeZoneId;
	}

	function setTimeZoneId($timeZoneId) {
		$this->timeZoneId = $timeZoneId;
	}

	/*
	 * Returns the Textual State/Sequence of a day. e.g. Morning
	 * @return String State/Sequence of a day
	 */
	function getTextualDaySequence($time = false) {
		if (!$time) {
			$time = time();
		}
		$now = $this->customFormatDateTime($time, "%H:%M");

		if ($now <= "11:59") {
			return "Morning";
		}	else if (($now > "11:59") && ($now < "16:59")) {
			return "Afternoon";
		}	else if ($now >= "17:00") {
			return "Evening";
		}
	}

	/**
	 * Takes an array and inserts it as a row in a CSV.
	 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
	 * @param String $filePath The file path of the CSV file. If the file doesn't exist then it will be created
	 * @param Array $array The data to insert into the array. Key values will be used as column when the file is created
	 * @param Array $Fields A list of fields that are required.
	 * @return Mixed If all the required fields are entered and the data is written to the file then True is returns
	 */
	function arrayToCsv($filePath, $array, $requiredFields = null) {
			//TODO: This should be in a data package

		if (!isset($array) || (sizeof($array) <= 0)) {
			return true;
		}

		$headerRow = "";
		$dataRow = "";
		$errors = null;
		foreach($requiredFields as $value) {
			$valueUnderscored = str_replace(" ", "_", $value);
			if ((!isset($array[$valueUnderscored])) || ($array[$valueUnderscored] == null)) {
				$errors[] = "'$value' is a required value.";
			}
		}

		if ($errors !== null) {
			return $errors;
		}

		foreach($array as $key => $value) {
			switch ($value) {
				case "Submit":
					continue;
			}
			$keyUnUnderscored = str_replace("_", " ", $key);
			$headerRow .= "$keyUnUnderscored,";
			$dataRow .= "\"$value\",";
		}

		$headerRow = mb_substr($headerRow, 0, -1) . "\n";
		$dataRow = mb_substr($dataRow, 0, -1) . "\n";

		if (file_exists($filePath)) {
			$headerRow = "";
		}
		$handle = fopen($filePath, "a");
		fwrite($handle, $headerRow);
		fwrite($handle, $dataRow);
		fclose($handle);
		return false;
	}

	static function dumpVariables($variables, $depth = 2, $currentDepth = 1) {
		$variableList = "";
		foreach ($variables as $variable) {
			$type = gettype($variable);
			switch($type) {
				case "object":
				case "array":
					if ($currentDepth < $depth) {
						$variableList .= Application::dumpVariables($variable, $depth, $currentDepth + 1);
					} else {
						$variableList = str_repeat("\t", $currentDepth) . gettype($variable) . ": ...\n";
					}
					break;
				default:
					$variableList = str_repeat("\t", $currentDepth) . gettype($variable) . ": $variable\n";
			}
		}
		return $variableList;
	}

	/**
	 * Debug defined error handling function
	 */
	static function customErrorHandler ($errNumber, $errMessage, $filename, $linenum, $variables) {

		if ((!error_reporting()) || ($errNumber == 2048)) {
			return false;
		}

		if ($errNumber == 2048) {
			return false;
		}

		$application = &CoreFactory::getApplication();

		// define an assoc array of error string
		// in reality the only entries we should
		// consider are 2, 8, 256, 512 and 1024
		$errorType = array (
					1   =>  "Error",
					2   =>  "Warning",
					4   =>  "Parsing Error",
					8   =>  "Notice",
					16  =>  "Core Error",
					32  =>  "Core Warning",
					64  =>  "Compile Error",
					128 =>  "Compile Warning",
					256 =>  "User Error",
					512 =>  "User Warning",
					1024=>  "User Notice",
					2048=>  "Extra"
					);

		// Create a new Template
		$template = CoreFactory::getTemplate();

		if ($application->contentType == "text/html") {
			$template->setTemplateFile(dirname(__FILE__) ."/Template/phperror.tpl");
		} else {
			$template->setTemplateFile(dirname(__FILE__) ."/Template/phperror-plain.tpl");
		}

		$template->set("SITE_SUPPORT_EMAIL", $application->registry->get("EmailAddress/Developer"));

		$template->set("DATE", date("Y-m-d H:i:s "));
		$template->set("ERROR_NUMBER", $errNumber);
		$template->set("ERROR_MESSAGE", $errMessage);
		$template->set("ERROR_TYPE", $errorType[$errNumber]);
		$template->set("ERROR_FILENAME", $filename);
		$template->set("ERROR_LINENUMBER", $linenum);
		$template->set("ERROR_VARIABLES", Application::dumpVariables($variables));

		// Show Stack Trace
		$a = debug_backtrace();

		$stackTrace = "";
		$depth = 0;
		for($e = sizeof($a) - 1; $e > 1; $e--) {
			if ($application->contentType == "text/html") {
				$stackTrace .= "<p>";
			}
			$value = $a[$e];
			if (!isset($value["file"])) {
				continue;
			}
			$args = "";
			if (isset($value["args"])) {
				foreach ($value["args"] as $k => $arg) {
					if (is_array($arg)) {
						$args .= implode(",", $arg);
					} else if (is_int($arg)) {
						$args .= "$arg, ";
					} else if (is_string($arg)) {
						$args .= "\"$arg\", ";
					}
				}
			}
			$args = mb_substr($args, 0, -2);
			$stackTrace .= $value["file"] . "\n";
			$stackTrace .= "Line: " . $value["line"] . "\n";
			if (isset($value["class"])) {
				$stackTrace .= "Class:  " . $value["class"] . "\n";
			}
			$stackTrace .= "Function: " . $value["function"] . "($args)\n";

			$depth++;
			if ($application->contentType == "text/html") {
				$stackTrace .= "</p>\n";
			}
		}

		$template->set("STACKTRACE", $stackTrace);


		$memoryDump = "Server:\n" . @print_r($_SERVER, true) .
				"\nSession:\n" . @print_r($_SESSION, true);

		if ($application->contentType == "text/html") {
			$memoryDump = nl2br($memoryDump);
		}

		$template->set("MEMORYDUMP", $memoryDump);

		// Show bad line
		$a = file($filename);

		$start = $linenum-10;
		$end = $linenum+10;

		if ($start < 1) {
			$start = 1;
		}

		if ($end >= sizeof($a)) {
			$end = sizeof($a) - 1;
		}

		$output = "";
		$fileDetails = "";
		for ($i = $start; $i <= $end; $i++) {
			if ($i == $linenum) {
				$fileDetails .= "\t" .sprintf("% 4d", $i). " -->>" . $a[$i-1] . "";
			} else {
				$fileDetails .= "\t" .sprintf("% 4d", $i). "  " . $a[$i-1] . "";
			}
		}

		/*$fileDetails = highlight_string("<?php\n$fileDetails?>", true);*/
		$fileDetails = $fileDetails;


		if ($application->contentType == "text/html") {
			$fileDetails = htmlentities($fileDetails);
		}

		$template->set("CODE", $fileDetails);

		if ($application->isDebug()) {
			while (@ob_end_clean());
			header("Content-type: " . $application->contentType);
			echo $template->parseTemplate();
			exit;
		} else {
			$template->write($application->registry->get("Log/Error/Path", $application->registry->get("Log/Path")) . "/" .
				str_replace(array(" ", ":"), "", $application->registry->get("Name") . "-" .
				$application->getCurrentDate() . "-" . $application->getCurrentTime()) .
				".html");

			while (@ob_end_clean());

			header("HTTP/1.1 500 Internal Server Error");
			header("Status: 500 Internal Server Error");

			if (file_exists($application->registry->get("Site/ErrorPage"))) {
				include($application->registry->get("Site/ErrorPage"));
			} else {
				echo "Sorry there has been problem. Please try again later";
			}
			exit;
		}
	}
}
