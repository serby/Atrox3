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
require_once("Atrox/Core/Data/Data.php");


/**
 * @author Robert Arnold (Clock Ltd) {@link mailto:robert.arnold@clock.co.uk robert.arnold@clock.co.uk }
 * @copyright Clock Limited 2007
 * @version 3.0 - $Revision$ - $Date$
 * @package Base
 * @subpackage Store
 */
class ReviewControl extends DataControl {
	var $table = "Review";
	var $key = "Id";
	var $sequence = "Review_Id_seq";
	var $defaultOrder = "Id";

	var $rating = array(
		1 => "1",
		2 => "2",
		3 => "3",
		4 => "4",
		5 => "5");

	var $searchFields = array("Id", "Title", "Review");

	function init() {

		$this->fieldMeta["Id"] = new FieldMeta(
			"Id", "", FM_TYPE_INTEGER, null, FM_STORE_NEVER, false);

		$this->fieldMeta["Title"] = new FieldMeta(
			"Title", "", FM_TYPE_STRING, 50, FM_STORE_ALWAYS, false);

		$this->fieldMeta["Review"] = new FieldMeta(
			"Review", "", FM_TYPE_STRING, 5000, FM_STORE_ALWAYS, false);

		$this->fieldMeta["AuthorId"] = new FieldMeta(
			"Author", "", FM_TYPE_RELATION, null, FM_STORE_ALWAYS, false);

		$this->fieldMeta["AuthorId"]->setRelationControl(BaseFactory::getMemberControl());
		$this->fieldMeta["AuthorId"]->setAutoData(CoreFactory::getCurrentMember());

		$this->fieldMeta["Anonymous"] = new FieldMeta(
			"Anonymous", "", FM_TYPE_BOOLEAN, null, FM_STORE_ALWAYS, true);

		$this->fieldMeta["DateCreated"] = new FieldMeta(
			"DateCreated", "", FM_TYPE_DATE, null, FM_STORE_NEVER, false);

		$this->fieldMeta["DateCreated"]->setFormatter(CoreFactory::getDateTimeFieldFormatter());

		$this->fieldMeta["Approved"] = new FieldMeta(
			"Approved", 'f', FM_TYPE_BOOLEAN, null, FM_STORE_ALWAYS, true);

		$this->fieldMeta["ProductId"] = new FieldMeta(
			"Product Id", "", FM_TYPE_RELATION, null, FM_STORE_ALWAYS, false);
		$this->fieldMeta["ProductId"]->setRelationControl(BaseFactory::getProductControl());

		$this->fieldMeta["Rating"] = new FieldMeta(
			"Rating", "", FM_TYPE_INTEGER, null, FM_STORE_ALWAYS, false);

		$this->fieldMeta["Rating"]->setFormatter(
			CoreFactory::getArrayRelationFormatter($this->rating, "1"));
	}

	function getReviewRatingsArray(){
		return $this->rating;
	}

	function toggleApproved($ids) {
		if (!is_array($ids)) {
			return false;
		}
		$averageRating = 0;			
		foreach ($ids as $id) {
			if ($review = $this->item($id)) {
				if ($review->get("Approved") == "t") {
					$review->set("Approved", "f");
					$review->save();
					$this->updateAverageRating($review);
				} else {
					$review->set("Approved", "t");
					$review->save();
					$this->updateAverageRating($review);
				}					
			}
		}			
		return true;
	}
	
	function afterDelete($review) {
		$this->updateAverageRating($review);
	}

	function getAverageRating($product) {
		$filter = CoreFactory::getFilter();
		$filter->addConditional($this->table, "ProductId", $product->get("Id"));
		$filter->addConditional($this->table, "Approved", "t");
		$this->setFilter($filter);
		return $this->averageField("Rating");
	}
	
	function updateAverageRating($review) {
		$averageRating = $this->getAverageRating($review->getRelation("ProductId"));				
		$productControl = $review->getRelationControl("ProductId");
		$product = $productControl->item($review->get("ProductId"));
		$productControl->updateField($product, "AverageReview", $averageRating);				
	}
	
	function retrieveForProduct() {				
		$filter = CoreFactory::getFilter();
		$filter->addConditional($this->table, "ProductId", $_GET["Id"]);
		$filter->addConditional($this->table, "Approved", "t");
		$this->setFilter($filter);
		return $this->retrieveAll();
	}
	
	function getMemberRatingForProduct($member, $product) {
		$filter = CoreFactory::getFilter();
		$filter->addConditional($this->table, "ProductId", $product->get("Id"));
		$filter->addConditional($this->table, "AuthorId", $member->get("Id"));
		$filter->addLimit("1");
		$this->setFilter($filter);			
	}
}