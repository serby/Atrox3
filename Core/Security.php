<?php
/**
 * @package Core
 * @copyright Clock Limited 2007
 * @version 3.0 - $Revision$ - $Date$
 */

/**
 * Include DataConnection.php so that DataConnectionControl can be extended.
 */
require_once("Data/DataConnection.php");

/**
 * Handles System Security
 *
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2007
 * @version 3.0 - $Revision$ - $Date$
 * @package Core
 */
  class SecurityControl {

	var $memberId = null;
	var $authId = null;
	var $alias = null;
	var $securityGroups = null;
	var $acl = null;
	var $databaseControl = null;
	var $customFields = null;
	var $fields = null;

	var $logOnPage = "/members/logon.php";
	var $logOffPage = "/members/logon.php?LogOff";
	var $noAccessPage = "/members/noaccess.php";
	var $blockedPage = "/members/blocked.php";

	/**
	 * @param Array customFields
	 * @param Application $applcation
	 */
	function SecurityControl($customFields = null) {
		$this->customFields = $customFields;
	}

	/**
	 * Check if user is logged on, attempt Auto-logon and setup session enviroment
	 * @return void
	 */
	function setup() {
		if (isset($_SESSION["MemberId"])) {
			$this->setControlValues();
		}	else {
			if ((isset($_COOKIE["AutoLogonId"])) && ($_COOKIE["AutoLogonId"] != null)) {
				$this->logon("", "", $_COOKIE["AutoLogonId"]);
			}
		}
		if (isset($_SESSION["Acl"])) {
			$this->acl = $_SESSION["Acl"];
		}
	}

	function setControlValues() {
		$this->memberId = $_SESSION["MemberId"];
		$this->authId = $_SESSION["AuthId"];
		$this->alias = $_SESSION["Alias"];
		if ($this->customFields != null) {
			foreach ($this->customFields as $field) {
				$this->fields[$field] = isset($_SESSION[$field]) ? $_SESSION[$field] : null;
			}
		}
		if (isset($_SESSION["MemberSecurityGroups"])) {
			$this->securityGroups = $_SESSION["MemberSecurityGroups"];
		}
	}

	/**
	 * Check the members logon credentials
	 * @param $emailAddress
	 * @param $password
	 * @param $autoLogonId
	 * @return Member DataEntity of the
	 */
	function logon($emailAddress, $password, $autoLogonId = "") {

		$application = &CoreFactory::getApplication();

		if (sizeof($_COOKIE) <= 0) {
			$application->errorControl->addError("Your browser cannot accept Cookies. This will severely affect your use of the this site. Please enable Cookies or upgrade to a browser that supports them.", "Cookie");
			return false;
		}

		$this->memberId = null;

		// First  check AutoLogonId
		if ($autoLogonId != null) {

			$sql = "SELECT * FROM " .  $application->databaseControl->parseTable("Member") .
				" WHERE " .$application->databaseControl->parseField("AutoLogonId"). " = " .
					$application->databaseControl->parseValue($autoLogonId). ";";

			$result = $application->databaseControl->query($sql);

			if ($application->databaseControl->numRows($result) <= 0) {
				return false;
			}

			if ($data = $application->databaseControl->fetchRow($result)) {
				$this->memberId = $data["Id"];
			}

		} else {
			// If AutoLogonId check fails then check e-mail and password
			$sql = "SELECT * FROM " . $application->databaseControl->parseTable("Member") .
					" WHERE " . $application->databaseControl->parseField("EmailAddress"). " ILIKE " .
					$application->databaseControl->parseValue($emailAddress). ";";

			$result = $application->databaseControl->query($sql);

			if (!$data = $application->databaseControl->fetchRow($result)) {
				// If AutoLogonId check fails then check e-mail and password
				$sql = "SELECT * FROM " . $application->databaseControl->parseTable("Member") .
					" WHERE " . $application->databaseControl->parseField("Alias"). " ILIKE " .
					$application->databaseControl->parseValue($emailAddress). ";";
					$result = $application->databaseControl->query($sql);
					$data = $application->databaseControl->fetchRow($result);
			}

			if ($data) {
				if ($data["Password"] == sha1($password)) {
					$this->memberId = $data["Id"];
				} else {
					$application->errorControl->addError("Invalid Logon Details");
					return false;
				}
			}	else {
				$application->errorControl->addError("Invalid Logon Details");
				return false;
			}
		}

		$sql = "SELECT * FROM " . $application->databaseControl->parseTable("MemberToSecurityGroup") .
				" WHERE " . $application->databaseControl->parseField("MemberId"). " = " .
				$application->databaseControl->parseValue($this->memberId). ";";

		$securityGroups = "";

		if ($result = $application->databaseControl->query($sql)) {
			while ($securityGroupData = $application->databaseControl->fetchRow($result)) {
				$securityGroups[] = $securityGroupData["SecurityGroupId"];
			}
		}

		// Create a new AuthId. This may be used by Auth Web Service
		$authId = sha1(mt_rand());

		// Update Last Visit
		$sql = "UPDATE " . $application->databaseControl->parseTable("Member") .
				" SET " .
				$application->databaseControl->parseField("LastVisit") . " = " .
				$application->databaseControl->getNow() . ", " .
				$application->databaseControl->parseField("Visits") . " = " .
				$application->databaseControl->parseField("Visits") . " + 1, " .
				$application->databaseControl->parseField("AuthId") . " = " .
				$application->databaseControl->parseValue($authId) .
						" WHERE " . $application->databaseControl->parseField("Id") . " = " . $application->databaseControl->parseValue($this->memberId) . ";";

		$application->databaseControl->query($sql);

		// Make sure existing session is dead.
		$lastPage = $application->defaultValue($_SESSION["LastPage"], "/");

		// If it's desired to kill the session, also delete the session cookie.
		// Note: This will destroy the session, and not just the session data!
		//echo session_name();
		//exit;


		//session_unset();
		//session_destroy();
		// Unset all of the session variables.
		//$_SESSION = array();

		session_regenerate_id();
		//setcookie(session_name(), session_id(), ini_get("session.cookie_lifetime"), "/");

		if ($data["Blocked"] == "t") {
			$this->blocked();
		}
		//  Restore last page to redirected login can resume on the orginally linked page.
		$_SESSION["LastPage"] = $lastPage;
		$_SESSION["MemberId"] = $this->memberId;
		$_SESSION["AuthId"] = $authId;
		$_SESSION["Alias"] = $data["Alias"];
		$_SESSION["MemberSecurityGroups"] = $securityGroups;

		$this->setControlValues();

		setcookie("MemberId", $this->memberId, null, "/");
		setcookie("Alias", $data["Alias"], null, "/");
		if ($this->customFields != null) {
			foreach ($this->customFields as $field) {
				$_SESSION[$field] = $data[$field];
			}
		}
		return true;
	}

	/**
	 * Returns the MemberId of the currently loggged on Member
	 * @return String The ID of the currently logged on member
	 */
	function getMemberId() {
		return $this->memberId;
	}

	/**
	 * Returns the Current Member
	 */
	function getCurrentMember() {
		$memberControl = BaseFactory::getMemberControl();
		return $memberControl->item($this->getMemberId());
	}

	/**
	 * Returns the Alias of the currently loggged on Member
	 * @return String The Alias of the currently logged on member
	 */
	function getAlias() {
		return $this->alias;
	}

	/**
	 * Returns the value of custom field "$field" of the currently loggged on Member
	 * @return String The value of the custom field "$field"
	 */
	function getCustomField($field) {
		return $this->fields[$field];
	}

	function getAcl($resourceName) {

		$application = &CoreFactory::getApplication();

		// If AutoLogonId check fails then check e-mail and password
		$sql = "SELECT * FROM " . $application->databaseControl->parseTable("SecurityGroupToSecurityResource") .
				" WHERE " . $application->databaseControl->parseField("SecurityResourceName"). " = " .
				$application->databaseControl->parseValue($resourceName). ";";
		$result = $application->databaseControl->query($sql);

		$acl = array();

		while ($aclData = $application->databaseControl->fetchRow($result)) {
			$acl[] = $aclData["SecurityGroupId"];
		}
		return $acl;
	}

   /**
	 * Finds out if current user is allowed to access the given resource(s).
	 * @param Mixed $resourceName The name or array of names, of resources in question
	 * @param Boolean $redirect Whether the browser should redirect if member isn't
	 * allowed.
	 * @return Boolean Is the current member allowed to access the give security resource
	 */
   function isAllowed($resourceName, $redirect = true, $operator = "AND", $noAccessPageOverride = false) {
		// This'll let you pass $resourceName as an Array - Ed.
		if (is_array($resourceName)) {
			if ($operator == "AND") {
				$success = true;
				foreach ($resourceName as $value) {
					if (!$this->isAllowed($value, $redirect)) {
						$success = false;
						break;
					}
				}
			} else {
				$success = false;
				foreach ($resourceName as $value) {
					if ($this->isAllowed($value, $redirect)) {
						$success = true;
						break;
					}
				}
			}
			return $success;
		}


		if (!isset($this->acl[$resourceName])) {
			$this->acl[$resourceName] = $this->getAcl($resourceName);
			$_SESSION["Acl"] = $this->acl;
		}


		// Find the security groups that are allowed to access this resource
		$securityGroups = $this->acl[$resourceName];
		if (is_array($this->securityGroups)) {
			if (!is_array($securityGroups)) {
				if (in_array($securityGroups, $this->securityGroups)) {
					return true;
				} else {
					return false;
				}
			}

			foreach ($securityGroups as $value) {
				if (in_array($value, $this->securityGroups)) {
					return true;
				}
			}
		}

      if ($redirect) {
			$application = &CoreFactory::getApplication();
			if ($this->memberId == null) {
				$application->storePage();
				$application->redirect($this->logOnPage);
			} else {
				if (!$noAccessPageOverride) {
					$noAccessPageOverride = $this->noAccessPage;
				}
				$application->redirect($noAccessPageOverride);
			}
		} else {
			return false;
		}
    }
/**
 * Is the member logged in
 * @return
 */
function isLoggedOn() {
	return $this->memberId != null;
}

    function createAutoLogon() {

		$application = &CoreFactory::getApplication();
		$uid = sha1(mt_rand());

		// Update Last Visit
		$sql = "UPDATE " . $application->databaseControl->parseTable("Member") .
				" SET " . $application->databaseControl->parseField("AutoLogonId") . " = " .
				$application->databaseControl->parseValue($uid) .
						" WHERE " . $application->databaseControl->parseField("Id") . " = " .
						$application->databaseControl->parseValue($this->memberId) . ";";

		$application->databaseControl->query($sql);

		// Set cookie to expire after a long time
		setcookie ("AutoLogonId", $uid, time() + (3600 * 24 * 1000), "/");
	}

	function logOff($redirectAfterLogoff = true) {

		$application = &CoreFactory::getApplication();
		// Update Last Visit
		$sql = "UPDATE " . $application->databaseControl->parseTable("Member") .
				" SET " .
				$application->databaseControl->parseField("AuthId") . " = " .
				$application->databaseControl->parseValue("") .
						" WHERE " . $application->databaseControl->parseField("Id") . " = " .
						$application->databaseControl->parseValue($this->memberId) . ";";

		@$application->databaseControl->query($sql);

		// If it's desired to kill the session, also delete the session cookie.
		// Note: This will destroy the session, and not just the session data!
		if (isset($_COOKIE[session_name()])) {
		   setcookie(session_name(), "", time() - 42000, "/");
		}
		@session_unset();
		@session_destroy();
		$_SESSION = array();

		setcookie("AutoLogonId", false, false, "/");
		setcookie("MemberId", false, false, "/");
		setcookie("Alias", false, false, "/");

		$this->memberId = false;

		if ($redirectAfterLogoff) {
			$application->redirect($this->logOffPage);
		}
	}

	/**
	 * What to do if a member is blocked
	 */
	function blocked() {
		$application = &CoreFactory::getApplication();
		session_unset();
		session_destroy();
		$application->redirect($this->blockedPage);
	}

	function getSetting($name, &$override, $default = "") {
		if (isset($override)) {
			$this->setSetting($name, $override);
			return $override;
		}
		if (isset($_SESSION["Settings"][$name])) {
			return $_SESSION["Settings"][$name];
		} else if (isset($this->memberId)) {
			$application = &CoreFactory::getApplication();
			$sql = "SELECT * FROM  " . $application->databaseControl->parseTable("Setting") .
			" WHERE " . $application->databaseControl->parseField("MemberId") . " = " .
			$application->databaseControl->parseValue($this->memberId) . " AND " .
			$application->databaseControl->parseField("Name") . " = " .
			$application->databaseControl->parseValue($name);

			$result = $application->databaseControl->query($sql);

			if ($data = $application->databaseControl->fetchRow($result)) {
				$_SESSION["Settings"][$name] = $data["Value"];
				return $data["Value"];
			} else {
				$_SESSION["Settings"][$name] = $default;
				return $default;
			}
		} else {
			if (isset($_COOKIE["Settings"][$name])) {
				return $_COOKIE["Settings"][$name];
			} else {
				return $default;
			}
		}
	}

	function deleteSetting($name) {
		$application = &CoreFactory::getApplication();
		$sql = "DELETE FROM  " . $application->databaseControl->parseTable("Setting") .
			" WHERE " . $application->databaseControl->parseField("MemberId") . " = " .
			$application->databaseControl->parseValue($this->memberId) . " AND " .
			$application->databaseControl->parseField("Name") . " = " . $application->databaseControl->parseValue($name);
		$_SESSION["Settings"][$name] = "";
		$application->databaseControl->query($sql);
	}

	function setSetting($name, $value) {
		$application = &CoreFactory::getApplication();
		if (isset($this->memberId)) {
			$this->deleteSetting($name);
			$sql = "INSERT INTO " . $application->databaseControl->parseTable("Setting") .
			" (" . $application->databaseControl->parseField("MemberId") . "," .
						 $application->databaseControl->parseField("Name") . "," .
						 $application->databaseControl->parseField("Value") . ") VALUES (" .
						 $application->databaseControl->parseValue($this->memberId) . "," .
						 $application->databaseControl->parseValue($name) . "," .
						 $application->databaseControl->parseValue($value) . ")";
			$application->databaseControl->query($sql);
			$_SESSION["Settings"][$name] = $value;
		} else {
			setcookie("Settings[$name]", $value, time() + (3600 * 24 * 1000), "/", "." . $application->registry->get("Site/Domain"));
		}
		return $value;
	}

	/**
	 * Generates a password by placing a two digit random number between
	 * two words randomly chosen from a dictionary file.
	 *
	 * @return The completed password
	 */
	function generateRandomPassword() {
		$password = null;

		for ($loop = 0; $loop < 8; $loop++) {
			$alNum = rand(0, 1);

			if ($alNum == 0) {
				$ascii = rand(48, 57);
			} else {
				$ascii = rand(97, 122);
			}

			$password .= chr($ascii);
		}

		return $password;
	}
}