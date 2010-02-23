<?php
/**
 * @package Base
 * @subpackage Store
 * @copyright Clock Limited 2007
 * @version 3.0 - $Revision$ - $Date$
 */

/**
 * Include Data.php so that DataControl can be extended.
 */
require_once("Atrox/3.0/Core/Data/Data.php");

/**
 * Control Object used to issue Refund
 *
 * @author Paul Serby {@link mailto:adam.forster@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2007
 * @version 3.0 - $Revision$ - $Date$
 * @package Base
 * @subpackage Store
 */
class DigitalRightControl extends DataControl {
	var $table = "DigitalRight";
	var $key = "Id";
	var $sequence = "DigitalRight_Id_seq";
	var $defaultOrder = "Id";
	var $searchFields = array("Key");

	function init() {
		$this->fieldMeta["Id"] = new FieldMeta(
			"Id", "", FM_TYPE_INTEGER, null, FM_STORE_NEVER, false);

		$this->fieldMeta["Key"] = new FieldMeta(
			"Key", "", FM_TYPE_STRING, 50, FM_STORE_ALWAYS, false);

		$this->fieldMeta["AllowBackupRestore"] = new FieldMeta(
			"Allow Backup Restore", "f", FM_TYPE_BOOLEAN, null, FM_STORE_ALWAYS, false);	

		$this->fieldMeta["PlayCount"] = new FieldMeta(
			"Play Count", 0, FM_TYPE_INTEGER, null, FM_STORE_ALWAYS, false);

		$this->fieldMeta["AllowCopy"] = new FieldMeta(
			"AllowCopy", "f", FM_TYPE_BOOLEAN, null, FM_STORE_ALWAYS, false);						

		$this->fieldMeta["CopyCount"] = new FieldMeta(
			"Copy Count", "1", FM_TYPE_INTEGER, null, FM_STORE_ALWAYS, false);

		$this->fieldMeta["AllowBurn"] = new FieldMeta(
			"Allow Burn", "f", FM_TYPE_BOOLEAN, null, FM_STORE_ALWAYS, false);

		$this->fieldMeta["BurnCount"] = new FieldMeta(
			"Burn Count", 0, FM_TYPE_INTEGER, null, FM_STORE_ALWAYS, false);
			
		$this->fieldMeta["BeginDate"] = new FieldMeta(
			"Begin Date", null, FM_TYPE_DATE, null, FM_STORE_ALWAYS, true);

		$this->fieldMeta["BeginDate"]->setFormatter(CoreFactory::getDatabaseDateEncoder());
		$this->fieldMeta["BeginDate"]->setEncoder(CoreFactory::getArrayDateEncoder());

		$this->fieldMeta["ExpirationDate"] = new FieldMeta(
			"Expiration Date", null, FM_TYPE_DATE, null, FM_STORE_ALWAYS, true);														

		$this->fieldMeta["ExpirationDate"]->setFormatter(CoreFactory::getDatabaseDateEncoder());
		$this->fieldMeta["ExpirationDate"]->setEncoder(CoreFactory::getArrayDateEncoder());
	}
	
	function getLicense($digitalRights, $clientInfo) {
		$key = $digitalRights->get("Key");
		require_once("nusoap/1.94/nusoap.php");
		$drmWebService = "http://www.thedrmcentre.com/Customer/Service/Soap20060418.asmx";
		$client = new nusoapclient($drmWebService, true, false, false, false, false, 120, 120);
		$params = "
			<GetLicenses xmlns=\"http://namespaces.makeni.com/Makeni.DrmCentre/\">
				<login>
					<ApplicationId xsi:type=\"xsd:string\">D4D1F20B-0386-4632-B094-B2996E9C84F0</ApplicationId>
					<Password xsi:type=\"xsd:string\">dgyOH85FtiO654Sruy</Password>
				</login>
				<requests>
					<Request>
						<Client xsi:type=\"ClientInfo\">
							<String>" . htmlentities($clientInfo) . "</String>
						</Client>
						<IpAddress>" . $_SERVER["REMOTE_ADDR"] . "</IpAddress>
						<UserAgent>" . $_SERVER["HTTP_USER_AGENT"] . "</UserAgent>
						<Licenses>
							<LicenseRequest>
								<Key>
									<Id xsi:nil=\"true\"/>
									<String>{$key}</String>
								</Key>
								<Rights xsi:type=\"Rights10point1\">
									<AllowBackupRestore>" . ($digitalRights->get("AllowBackupRestore") == "t" ? "true" : "false") . "</AllowBackupRestore>
									<DeleteOnClockRollback xsi:nil=\"true\"/>
									<DisableOnClockRollback xsi:nil=\"true\"/>
									<GracePeriod xsi:nil=\"true\"/>					
									" . ($digitalRights->get("PlayCount") > 0 ? 
									"<PlayCount>" . $digitalRights->get("PlayCount") . "</PlayCount>" :
									"<PlayCount xsi:nil=\"true\"/>" ) . 
									"<AllowCollaborativePlay xsi:nil=\"true\"/>
									<AllowCopy>" . ($digitalRights->get("AllowCopy") == "t" ? "true" : "false") . "</AllowCopy>
									<CopyCount>" . $digitalRights->get("CopyCount") . "</CopyCount>
									<AllowTransferToNonSdmi>true</AllowTransferToNonSdmi>
									<AllowTransferToSdmi>true</AllowTransferToSdmi>
									<TransferCount>5</TransferCount>
									<AllowBurn>" . ($digitalRights->get("AllowBurn") == "t" ? "true" : "false") . "</AllowBurn>
									" . ($digitalRights->get("BurnCount") > 0 ? 
									"<BurnCount>" . $digitalRights->get("BurnCount"). "</BurnCount>" :
									"<BurnCount xsi:nil=\"true\"/>" ) .
									"<BurnInAParticularPlaylistCount xsi:nil=\"true\"/>
									" . ($digitalRights->get("BeginDate") ? 
									"<BeginDate>" . $digitalRights->getFormatted("BeginDate") . "T00:00:01</BeginDate>" :
									"<BeginDate xsi:nil=\"true\"/>" ) . 	
									($digitalRights->get("ExpirationDate") ? 
									"<ExpirationDate>" . $digitalRights->getFormatted("ExpirationDate") . "T00:00:01</ExpirationDate>" :
									"<ExpirationDate xsi:nil=\"true\"/>" ) . 																			
									"<ExpirationAfterFirstUse xsi:nil=\"true\"/>
									<ExpirationOnStore xsi:nil=\"true\"/>
								</Rights>
								<Priority xsi:nil=\"true\"/>
							</LicenseRequest>
						</Licenses>
					</Request>
				</requests>
			</GetLicenses>";
									
		$searchResults = $client->call(
			"GetLicenses",
			$params,
			"http://namespaces.makeni.com/Makeni.DrmCentre/",
			"http://namespaces.makeni.com/Makeni.DrmCentre/GetLicenses",
			false, null, "", "literal"
		);
					
		$licenseString = false;
		if ($client->fault) {
			$this->application->errorControl->addError(
				"There is a problem with the licensing service. Please try again later.");
		} else {
			$err = $client->getError();
			if ($err) {
				$this->application->errorControl->addError("DRM Error: " . $err);
			} else {
				$licenseString = $this->javascriptEncode($searchResults["Response"]["String"]);	
			}		
		}
		return $licenseString;
	}
		
	function javascriptEncode($s) {
		$s = str_replace("\\", "\\\\", $s);
		$s = str_replace("\"", "\\\"", $s);
		return $s;
	}
}