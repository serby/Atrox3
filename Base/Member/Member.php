<?php
/**
 * @package Base
 * @subpackage Member
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 */

/**
 * Include Data.php so that DataControl can be extended.
 * Include DataEntity.php so that DataEntity can be extended.
 */
require_once("Atrox/Core/Data/Data.php");
require_once("Atrox/Core/Data/DataEntity.php");

define("MEMBER_GENDER_UNKNOWN", 0);
define("MEMBER_GENDER_MALE", 1);
define("MEMBER_GENDER_FEMALE", 2);

/**
 *
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Base
 * @subpackage Member
 */
class MemberControl extends DataControl {
	var $table = "Member";
	var $key = "Id";
	var $sequence = "Member_Id_seq";
	var $defaultOrder = "EmailAddress";
	var $searchFields = array("EmailAddress", "Alias", "LastName");
	var $fullTextIndex = "TextIndex";
	var $shortTitleFields = array("FirstName", "LastName");
	var $shortTitleFormat = "%s %s";

	var $genders = array(
		MEMBER_GENDER_MALE => "Male",
		MEMBER_GENDER_FEMALE => "Female"
	);

	var $imageSize = array(
		"MinWidth" => 50,
		"MinHeight" => 50,
		"MaxWidth" => 500,
		"MaxHeight" => 500);

	function init() {
		$lists = CoreFactory::getLists();

		$this->fieldMeta["Id"] = new  FieldMeta(
			"Id", "", FM_TYPE_INTEGER, null, FM_STORE_NEVER, false);

		$this->fieldMeta["EmailAddress"] = new FieldMeta(
			"E-mail Address", "", FM_TYPE_EMAILADDRESS, 255, FM_STORE_ALWAYS, false,
			FM_OPTIONS_UNIQUE);

		$this->fieldMeta["Alias"] = new FieldMeta(
			"User Name", "", FM_TYPE_STRING, 20, FM_STORE_ALWAYS, false, FM_OPTIONS_UNIQUE);

		$this->fieldMeta["Alias"]->setFormatter(CoreFactory::getLeftCropEllipsisEncoder(20, true));

		$this->fieldMeta["Password"] = new FieldMeta(
			"Password", "", FM_TYPE_PASSWORD, 20, FM_STORE_ADD, false);

		$this->fieldMeta["NamePrefix"] = new FieldMeta(
			"Title", "", FM_TYPE_STRING, 10, FM_STORE_ALWAYS, true);

		$this->fieldMeta["FirstName"] = new FieldMeta(
			"First Name", "", FM_TYPE_STRING, 50, FM_STORE_ALWAYS, false);

		$this->fieldMeta["LastName"] = new FieldMeta(
			"Last Name", "", FM_TYPE_STRING, 50, FM_STORE_ALWAYS, false);

		$this->fieldMeta["DateOfBirth"] = new FieldMeta(
			"Date Of Birth", "", FM_TYPE_DATE, 1, FM_STORE_ALWAYS, true);

		$this->fieldMeta["DateOfBirth"]->setEncoder(CoreFactory::getArrayDateEncoder());

		$this->fieldMeta["DateOfBirth"]->setFormatter(CoreFactory::getDateFieldFormatter());

		$this->fieldMeta["ConfirmationId"] = new FieldMeta(
			"Confirmation Id", "", FM_TYPE_GUID, 1, FM_STORE_ADD, true);

		$this->fieldMeta["Confirmed"] = new FieldMeta(
			"Confirmed", "", FM_TYPE_BOOLEAN, 1, FM_STORE_ALWAYS, true);

		$this->fieldMeta["DateCreated"] = new FieldMeta(
			"DateCreated", "", FM_TYPE_DATE, 1, FM_STORE_NEVER, true);

		$this->fieldMeta["DateCreated"]->setFormatter(CoreFactory::getDateTimeFieldFormatter());

		$this->fieldMeta["LastVisit"] = new FieldMeta(
			"Last Visit", "", FM_TYPE_DATE, 1, FM_STORE_NEVER, true);

		$this->fieldMeta["LastVisit"]->setFormatter(CoreFactory::getDateTimeFieldFormatter());

		$this->fieldMeta["TimeZone"] = new FieldMeta(
			"Time Zone", "GMT", FM_TYPE_STRING, 5, FM_STORE_ALWAYS, false);

		$this->fieldMeta["MobileNumber"] = new FieldMeta(
			"Mobile Number", "", FM_TYPE_STRING, 20, FM_STORE_ALWAYS, true);

		$this->fieldMeta["MobileOperator"] = new FieldMeta(
			"Mobile Operator", "", FM_TYPE_STRING, 10, FM_STORE_ALWAYS, true);

		$this->fieldMeta["MobileConfirmed"] = new FieldMeta(
			"Mobile Confirmed", "f", FM_TYPE_BOOLEAN, 1, FM_STORE_ALWAYS, true);

		$this->fieldMeta["ReceiveEmailUpdates"] = new FieldMeta(
			"Receive Email Updates", "", FM_TYPE_BOOLEAN, 1, FM_STORE_ALWAYS, true);

		$this->fieldMeta["ReceiveSmsUpdates"] = new FieldMeta(
			"Receive SMS Updates", "", FM_TYPE_BOOLEAN, 1, FM_STORE_ALWAYS, true);

		$this->fieldMeta["ShowOnlineStatus"] = new FieldMeta(
			"Show Online Status", "t", FM_TYPE_BOOLEAN, 1, FM_STORE_ALWAYS, false);

		$this->fieldMeta["ShowEmailAddress"] = new FieldMeta(
			"Show E-mail Address", "f", FM_TYPE_BOOLEAN, 1, FM_STORE_ALWAYS, false);

		$this->fieldMeta["ShowProfile"] = new FieldMeta(
			"Show Profile", "t", FM_TYPE_BOOLEAN, 1, FM_STORE_ALWAYS, false);

		$this->fieldMeta["WebSite"] = new FieldMeta(
			"Web Site", "", FM_TYPE_STRING, 255, FM_STORE_ALWAYS, true);

		$this->fieldMeta["Signature"] = new FieldMeta(
			"Signature", "", FM_TYPE_STRING, 255, FM_STORE_ALWAYS, true);

		$this->fieldMeta["Posts"] = new FieldMeta(
			"Posts", "", FM_TYPE_INTEGER, null, FM_STORE_NEVER, true);

		$this->fieldMeta["Visits"] = new FieldMeta(
			"Visits", "", FM_TYPE_INTEGER, null, FM_STORE_NEVER, true);

		$this->fieldMeta["Signature"]->setFormatter(CoreFactory::getBodyTextFormatter());

		$this->fieldMeta["ImageId"] = new FieldMeta(
			"Image", "", FM_TYPE_BINARY, null, FM_STORE_ALWAYS, true);

		$this->fieldMeta["ImageId"]->setValidation(CoreFactory::getImageValidation(
			$this->imageSize["MinWidth"],
			$this->imageSize["MinHeight"],
			$this->imageSize["MaxWidth"],
			$this->imageSize["MaxHeight"]));

		$this->fieldMeta["AutoLogonId"] = new FieldMeta(
			"Auto Logon Id", "", FM_TYPE_GUID, 1, FM_STORE_NEVER, false);

		$this->manyToMany["SecurityGroup"] = CoreFactory::getManyToMany($this->table,
				"MemberToSecurityGroup", "MemberId",
				"SecurityGroupId", BaseFactory::getSecurityGroupControl());

		$this->manyToMany["Addresses"] = CoreFactory::getManyToMany($this->table,
				"MemberToAddress", "MemberId",
				"AddressId", BaseFactory::getAddressControl());

		$this->fieldMeta["Blocked"] = new FieldMeta(
			"Blocked", "f", FM_TYPE_BOOLEAN, 1, FM_STORE_ALWAYS, false);

		$this->fieldMeta["AuthId"] = new FieldMeta(
			"Auth Id", "", FM_TYPE_STRING, 40, FM_STORE_NEVER, true);

		$this->fieldMeta["PasswordRequestId"] = new FieldMeta(
		"Password Request Id", "", FM_TYPE_STRING, 40, FM_STORE_ALWAYS, true);

		$this->fieldMeta["PasswordRequestTime"] = new FieldMeta(
		"Password Request Time", "", FM_TYPE_STRING, 40, FM_STORE_ALWAYS, true);

		$this->fieldMeta["Gender"] = new FieldMeta(
			"Gender", "", FM_TYPE_INTEGER, null, FM_STORE_ALWAYS, true);

		$this->fieldMeta["Gender"]->setFormatter(
			CoreFactory::getArrayRelationFormatter($lists->getGenders(true), GEN_UNSPECIFIED));

		$this->fieldMeta["Question1"] = new FieldMeta(
			"Question1", "", FM_TYPE_BOOLEAN, 1, FM_STORE_ALWAYS, true);

		$this->fieldMeta["Comments"] = new FieldMeta(
			"Comments", "", FM_TYPE_STRING, 2000, FM_STORE_ALWAYS, true);

		$this->fieldMeta["HeardAboutUs"] = new FieldMeta(
			"Heard About Us", "", FM_TYPE_STRING, 200, FM_STORE_ALWAYS, true);

		$this->fieldMeta["PrepaidCredit"] = new FieldMeta(
			"Prepaid Credit", "0", FM_TYPE_CURRENCY, null, FM_STORE_NEVER, true);

		$this->fieldMeta["SecurityQuestion"] = new FieldMeta(
			"Security Question", "", FM_TYPE_STRING, 255, FM_STORE_ALWAYS, true);

		$this->fieldMeta["SecurityAnswer"] = new FieldMeta(
			"Security Answer", "", FM_TYPE_STRING, 100, FM_STORE_ALWAYS, true);

		$this->fieldMeta["ReceiveRelatedPromotions"] = new FieldMeta(
			"Receive Related Promotions", "f", FM_TYPE_BOOLEAN, 1, FM_STORE_ALWAYS, true);

		$this->fieldMeta["TermsAgreed"] = new FieldMeta(
			"Terms Agreed", "", FM_TYPE_BOOLEAN, null, FM_STORE_ALWAYS, false);
	}

	function afterInsert(&$dataEntity) {
		$noteControl = BaseFactory::getNoteControl();
		$noteControl->addNote("Member: Added (Id:" . $dataEntity->get("Id") . ")", "Member", $dataEntity->get("Id"));
	}

	function afterUpdate(&$dataEntity) {
		$noteControl = BaseFactory::getNoteControl();
		$noteControl->addNote("Member: Edited (Id:" . $dataEntity->get("Id") . ")", "Member", $dataEntity->get("Id"), 2);
	}

	function afterDelete(&$dataEntity) {
		$noteControl = BaseFactory::getNoteControl();
		$noteControl->addNote("Member: Deleted (Id:" . $dataEntity->get("Id") . ")", "Member", $dataEntity->get("Id"), 3);
	}

	function validateRegistration(&$member, $confirmPassword) {
		if ($member->get("Password") != $confirmPassword) {
			$this->errorControl->addError("Your passwords do not match. Both passwords must be exacly the same",
				"ConfirmPassword");
			return false;
		}

		if ($member->get("TermsAgreed") != "t") {
			$this->errorControl->addError("You must agree to the terms and conditions and make sure all required fields are valid",
				"Terms");
			return false;
		}
		return true;
	}

	function register(&$member, $confirmPassword, $address = null, $assignVoucher = false) {

		if (!$this->validateRegistration($member, $confirmPassword)) {
			return false;
		}

		if (isset($address)) {
	    	$addressControl = BaseFactory::getAddressControl();
	    	$address = $addressControl->createRegistrationAddress($address, $member);
		}

		if (!$member->save()) {
			return false;
		}

		if (isset($address)) {
			$member->removeManyRelation("Address", $address->get("Id"));
			$member->addManyRelation("Address", $address->get("Id"));
		}

	    $securityGroupControl = BaseFactory::getSecurityGroupControl();
	    if ($securityGroup = $securityGroupControl->itemByField("Basic", "Name")) {
	    	// Add the new member to the basic group
	    	$member->addManyRelation("SecurityGroup", $securityGroup->get("Id"));
	    }

		// Send Registration E-mail
		$welcomeEmailTemplate = CoreFactory::getTemplate();
		$welcomeEmailTemplate->setTemplateFile(
			$this->application->registry->get("Template/Path", "/Site/resource/template") .
			$this->application->registry->get("Template/Email/Registration/Html", "/account/html/registration.php")
		);

		$welcomeEmailTemplate->setData($member);
		$welcomeEmailTemplate->set("SITE_NAME", $this->application->registry->get("Name"));
		$welcomeEmailTemplate->set("SITE_ADDRESS", $this->application->registry->get("Site/Address"));
		$welcomeEmailTemplate->set("SITE_SUPPORT_EMAIL", $this->application->registry->get("EmailAddress/Support"));

		//New members 10% voucher
		if ($assignVoucher) {
			$voucher = $this->makeNewUserVoucher();
			$welcomeEmailTemplate->set("REGISTRATION_VOUCHER", $voucher->get("VoucherCode"));
		}

		$email = CoreFactory::getEmail();
		$email->setTo($member->get("EmailAddress"));
		$email->setFrom($this->application->registry->get("EmailAddress/Support"));
		$email->setSubject("Welcome to " . $this->application->registry->get("Name"));
		$email->setBody($welcomeEmailTemplate->parseTemplate());
		$email->sendMail();

		// TODO: Rob: Take this out of the default registration method and extend it on a per site basis
		//$recommendationsControl = BaseFactory::getRecommendationControl();
		//$recommendationsControl->updateRecommendation($member->get("EmailAddress"), $member->get("Id"));

		return true;
	}

	function confirm($uid) {
		if ($member = $this->itemByField($uid, "ConfirmationId")) {
			$member->set("Confirmed", "t");
			$member->save();
			return true;
		} else {
			return false;
		}
	}

	function changePassword(&$member, $oldPassword, $newPassword, $confirmPassword) {
		if ($member->get("Password") != PasswordEncoder::format($oldPassword)) {
			$this->errorControl->addError("Your old password is not correct");
			return false;
		}
		if ($newPassword != $confirmPassword) {
			$this->errorControl->addError("Your passwords do not match. Both passwords must be exactly the same");
			return false;
		}
		$member->set("Password", $newPassword);
		if ($this->validateField($member, "Password", FM_STORE_ADD)) {
			return $this->updateField($member, "Password", $member->get("Password"));
		} else {
			return false;
		}
	}

	function adminChangePassword(&$member, $newPassword, $confirmPassword) {
		if ($newPassword != $confirmPassword) {
			$this->errorControl->addError("Your passwords do not match. Both passwords must be exactly the same");
			return false;
		}
		$member->set("Password", $newPassword);
		if ($this->validateField($member, "Password", FM_STORE_ADD)) {
			return $this->updateField($member, "Password", $member->get("Password"));
		} else {
			return false;
		}
	}

	function adminSave($member, $newPassword, $confirmPassword) {
		if ($newPassword != $confirmPassword) {
			$this->errorControl->addError("Your passwords do not match. Both passwords must be exactly the same");
		}
		if ($member->save()) {
			if ($this->adminChangePassword($member, $newPassword, $confirmPassword)) {
				return true;
			}
		} else {
			return false;
		}
	}

	function addAddress($member, $address) {
		if ($country = $address->getRelation("CountryId")) {
			$address->set("Country", $country->get("Name"));
		}

		if (!$address->save()) {
			return false;
		}

		@$member->addManyRelation("Address", $address->get("Id"));
		return true;
	}

	function retrieveTodaysMembers($date) {
		$filter = CoreFactory::getFilter();
		$filter->addConditional($this->table, "DateCreated", "$date%", "iLIKE");
		$filter->addOrder("DateCreated", true);
		$this->setFilter($filter);
		return $this->retrieveAll();
	}

	function retrieveRecentLogins($admin = false, $limit = null) {
		$filter = CoreFactory::getFilter();
		require_once("Date.php");
		$date = new Date();
		$date->subtractSeconds(1800);
		$dateString = $date->format("%Y-%m-%d %H:%M:%S");
		$filter->addConditional($this->table, "LastVisit", $dateString, ">=");
		$filter->addOrder("DateCreated", true);
		if ($limit != null && is_numeric($limit)) {
			$filter->addLimit($limit);
		}
		$this->setFilter($filter);
		return $this->retrieveAll();
	}

	function getNewestMember() {
		$filter = CoreFactory::getFilter();
		$filter->addOrder("DateCreated", true);
		$filter->addLimit(1);
		$this->setFilter($filter);
		$this->retrieveAll();
		return $this->getNext();
	}

	function retrieveByUpdateStatus($type) {
		if ($type == "Email") {
			$filter = $this->getFilter();
			$filter->addConditional($this->table, "ReceiveEmailUpdates", "t");
			$filter->addOrder("EmailAddress");
			$this->setFilter($filter);
		} else if ($type == "Sms") {
			$filter = $this->getFilter();
			$filter->addConditional($this->table, "ReceiveSmsUpdates", "t");
			$filter->addConditional($this->table, "MobileNumber", "", "!=");
			$filter->addOrder("MobileNumber");
			$this->setFilter($filter);
		}
	}

	function getGenderFromTitle(&$member) {
		switch ($member->get("NamePrefix")) {
			case "Mr":
			case "Sir":
			case "Lord":
				return MEMBER_GENDER_MALE;
				break;
			case "Mrs":
			case "Ms":
			case "Miss":
				return MEMBER_GENDER_FEMALE;
				break;
			case "Dr":
			case "Rev":
			case "Prof":
				return MEMBER_GENDER_UNKNOWN;
				break;
			default:
				return MEMBER_GENDER_UNKNOWN;
				break;
		}
	}

	function getGender($gender) {
		return isset($this->genders[$gender]) ? $this->genders[$gender] : null;
	}

	function getGenderArray() {
		return $this->genders;
	}

	function getMembersForCalendar($memberIdList) {
		$filter = CoreFactory::getFilter();
		foreach ($memberIdList as $memberId) {
			$filter->addConditional($this->table, "Id", $memberId, "=", "OR");
		}
		$this->setFilter($filter);
	}

	function getDataEntity() {
		return new MemberDataEntity($this);
	}

	function getMembersWithSiteUpdates() {
		$filter = CoreFactory::getFilter();
		$filter->addConditional($this->table, "ReceiveEmailUpdates", "t");
		$filter->addOrder("EmailAddress");
		$this->setFilter($filter);
		return $this->retrieveAll();
	}

	function getMembersWithRelatedPromotions() {
		$filter = CoreFactory::getFilter();
		$filter->addConditional($this->table, "ReceiveRelatedPromotions", "t");
		$filter->addOrder("EmailAddress");
		$this->setFilter($filter);
		return $this->retrieveAll();
	}

	function getAge($member) {
		$mathControl = CoreFactory::getMathControl();
		return $mathControl->getAge($member->get("DateOfBirth"));
	}

		/**
	 * Sends a new password to the user's e-mail address.
	 *
	 * @param String $emailAddress User's E-mail address
	 * @return Returns true on success
	 */
	function requestPassword($emailAddress) {

		if ($member = $this->itemByField($emailAddress, "EmailAddress")) {
			$this->updateField($member, "PasswordRequestId", sha1(time()));
			$this->updateField($member, "PasswordRequestTime", time());

			$emailTemplate = CoreFactory::getTemplate();

			$emailTemplate->setTemplateFile(
				$this->application->registry->get("Template/Path", "/Site/resource/template") .
				$this->application->registry->get("Template/Email/PasswordRequest/Html", "/account/html/password.tpl"));

			$emailTemplate->set("EMAIL", $emailAddress);
			$emailTemplate->set("ID", $member->get("PasswordRequestId"));
			$emailTemplate->set("SITE_NAME", $this->application->registry->get("Name"));
			$emailTemplate->set("SITE_ADDRESS", $this->application->registry->get("Site/Address"));
			$emailTemplate->set("SITE_SUPPORT_EMAIL", $this->application->registry->get("EmailAddress/Support"));

			$plainEmailTemplate = CoreFactory::getTemplate();

			$plainEmailTemplate->setTemplateFile(
				$this->application->registry->get("Template/Path", "/Site/resource/template") .
				$this->application->registry->get("Template/Email/PasswordRequest/Plain", "/account/plain/password.tpl"));

			$plainEmailTemplate->set("EMAIL", $emailAddress);
			$plainEmailTemplate->set("ID", $member->get("PasswordRequestId"));
			$plainEmailTemplate->set("SITE_NAME", $this->application->registry->get("Name"));
			$plainEmailTemplate->set("SITE_ADDRESS", $this->application->registry->get("Site/Address"));
			$plainEmailTemplate->set("SITE_SUPPORT_EMAIL", $this->application->registry->get("EmailAddress/Support"));

			$email = CoreFactory::getEmail();
			$email->setTo($emailAddress);
			$email->setFrom($this->application->registry->get("EmailAddress/Support"));
			$email->setSubject("Password request for " . $this->application->registry->get("Name"));
			$email->setPlainBody($plainEmailTemplate->parseTemplate());
			$email->setBody($emailTemplate->parseTemplate(), false);
			$email->sendMail();
			return true;
		} else {
			return false;
		}
	}
}

class MemberDataEntity extends DataEntity {
	function getName() {
		$output = "";
		if ($this->get("NamePrefix")) {
			$output .= $this->getFormatted("NamePrefix") . " ";
		}
		$output .= $this->getFormatted("FirstName") . " " . $this->getFormatted("LastName");

		return $output;
	}
}
