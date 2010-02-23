<?php
/**
 * @package Base
 * @subpackage Store
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 */

/**
 * Include Data.php so that DataControl can be extended.
 */
require_once("Atrox/Core/Data/Data.php");

define("RECOMMENDATION_EMAIL", "emailtemplate/recommendation.tpl");
define("RECOMMENDATION_EMAIL_PLAIN", "emailtemplate/recommendationplain.tpl");

/**
 * @author Robert Arnold (Clock Ltd) {@link mailto:robert.arnold@clock.co.uk robert.arnold@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Base
 * @subpackage Store
 */
class RecommendationControl extends DataControl {
	var $table = "Recommendation";
	var $key = "Id";
	var $sequence = "Recommendation_Id_seq";
	var $defaultOrder = "Id";
	
	var $searchFields = array("Id", "MemberId", "EmailAddress", "ProductId");

	function init() {

		$this->fieldMeta["Id"] = new FieldMeta(
			"Id", "", FM_TYPE_INTEGER, null, FM_STORE_NEVER, false);

		$this->fieldMeta["MemberId"] = new FieldMeta(
			"Member Id", "", FM_TYPE_RELATION, null, FM_STORE_ALWAYS, true);
			
		$this->fieldMeta["MemberId"]->setRelationControl(BaseFactory::getMemberControl());
		$this->fieldMeta["MemberId"]->setAutoData(CoreFactory::getCurrentMember());

		$this->fieldMeta["EmailAddress"] = new FieldMeta(
			"E-mail Address", "", FM_TYPE_EMAILADDRESS, 255, FM_STORE_ALWAYS, false);

		$this->fieldMeta["DateCreated"] = new FieldMeta(
			"Date Created", "", FM_TYPE_DATE, null, FM_STORE_NEVER, false);

		$this->fieldMeta["DateCreated"]->setFormatter(CoreFactory::getDateTimeFieldFormatter());		
		
		$this->fieldMeta["Body"] = new FieldMeta(
			"Body", "", FM_TYPE_STRING, 500, FM_STORE_ALWAYS, true);

		$this->fieldMeta["Body"]->setFormatter(CoreFactory::getBodyTextFormatter());	
		
		$this->fieldMeta["ProductId"] = new FieldMeta(
			"Product Id", "", FM_TYPE_RELATION, null, FM_STORE_ALWAYS, false);
			
		$this->fieldMeta["ProductId"]->setRelationControl(BaseFactory::getProductControl());
		
		$this->fieldMeta["RecommendedUserId"] = new FieldMeta(
			"Recommended User Id", "", FM_TYPE_RELATION, null, FM_STORE_ALWAYS, true);
			
		$this->fieldMeta["RecommendedUserId"]->setRelationControl(BaseFactory::getMemberControl());

	}
	
	function sendRecommendationEmail($recommendation) {
		$product = $recommendation->getRelation("ProductId");		
		$productHtmlControl = BaseFactory::getProductHtmlControl();			
		$htmlControl = CoreFactory::getHtmlControl();
		
		$emailTemplate = CoreFactory::getTemplate();
		$emailTemplate->setTemplateFile(RECOMMENDATION_EMAIL);
		$emailTemplate->set("SITE_NAME", SITE_NAME);
		$emailTemplate->set("SITE_ADDRESS", SITE_ADDRESS);
		$emailTemplate->set("SITE_SALES_EMAIL", SITE_SALES_EMAIL);

		$emailTemplate->set("EMAIL", $recommendation->get("EmailAddress"));
		$emailTemplate->set("ALIAS", $recommendation->getRelationValue("MemberId", "Alias"));
		$emailTemplate->set("BLOB_URL", $htmlControl->getImageBinaryWithSiteAddress($product->get("ImageId"), $product->get("Name"), 120, 120));
		$emailTemplate->set("PRODUCTID", $product->get("Id"));
		$emailTemplate->set("PRODUCTDESCRIPTION", $product->get("Description"));
		$emailTemplate->set("IMAGEID", $product->get("ImageId"));
		$emailTemplate->set("PRODUCTNAME", $product->get("Name"));
		
		if ($product->get("Creator") != "") {
			$html = "<p>" . $productHtmlControl->getCreator($product) . ": <strong>" . $product->getFormatted("Creator"). "</strong></p>";
			$emailTemplate->set("CREATOR", $html);	
		}

		if ($product->get("Publisher") != "") {
			$html = "<p>Publisher: <strong>" . $product->getFormatted("Publisher") . "</strong></p>";
			$emailTemplate->set("PUBLISHER", $html);	
		}

		if ($recommendation->get("Body") != "") {
			$html = "<h3>And this is what they had to say:</h3>";
			$html .= "<p>" . $recommendation->getFormatted("Body") . "</p>";
			$html .= "<p><a href=\"" . SITE_ADDRESS . "/show/index.php?Id=" . $product->get("Id") ."\">View this product here!</a></p>";
			$emailTemplate->set("BODY", $html);	
		}


		$plainEmailTemplate = CoreFactory::getTemplate();
		$plainEmailTemplate->setTemplateFile(RECOMMENDATION_EMAIL_PLAIN);
		$plainEmailTemplate->set("EMAIL", $recommendation->get("EmailAddress"));
		$plainEmailTemplate->set("ALIAS", $recommendation->getRelationValue("MemberId", "Alias"));
		$plainEmailTemplate->set("PRODUCTID", $product->get("Id"));
		$plainEmailTemplate->set("PRODUCTDESCRIPTION", $product->get("Description"));
		$plainEmailTemplate->set("IMAGEID", $product->get("ImageId"));
		$plainEmailTemplate->set("PRODUCTNAME", $product->get("Name"));
		
		if ($product->get("Creator") != "") {
			$html = "<p>" . $productHtmlControl->getCreator($product) . ": <strong>" . $product->getFormatted("Creator"). "</strong></p>";
			$plainEmailTemplate->set("CREATOR", $html);	
		}

		if ($product->get("Publisher") != "") {
			$html = "<p>Publisher: <strong>" . $product->getFormatted("Publisher") . "</strong></p>";
			$plainEmailTemplate->set("PUBLISHER", $html);	
		}

		if ($recommendation->get("Body") != "") {
			$html = "<h3>And this is what they had to say:</h3>";
			$html .= "<p>" . $recommendation->getFormatted("Body") . "</p>";
			$html .= "<p><a href=\"" . SITE_ADDRESS . "/show/index.php?Id=" . $product->get("Id") ."\">View this product here!</a></p>";
			$plainEmailTemplate->set("BODY", $html);	
		}

		$email = CoreFactory::getEmail();
		$email->setTo($recommendation->get("EmailAddress"));
		$email->setSubject(SITE_NAME . " - " . $recommendation->getRelationValue("MemberId", "Alias") . " has recommended...");
		$email->setBody($emailTemplate->parseTemplate());
		$email->setPlainBody($plainEmailTemplate->parseTemplate());
		$email->setFrom(SITE_SUPPORT_EMAIL);
		$email->sendMail();
	}
	
	function updateRecommendation($emailAddress, $memberId) {
		$filter = CoreFactory::getFilter();
		$filter->addConditional($this->table, "EmailAddress", $emailAddress);
		$this->setFilter($filter);
		
		while ($recommendation = $this->getNext()) {
			$this->updateField($recommendation, "RecommendedUserId",  $memberId);				
		}
	}
}