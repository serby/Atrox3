<?php
/**
 * @package Base
 * @subpackage Community
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision: 508 $ - $Date: 2008-03-27 18:50:24 +0000 (Thu, 27 Mar 2008) $
 */

/**
 * Include Data.php so that DataControl can be extended.
 * Include DataEntity.php so that DataControl can be extended.
 */
require_once("Atrox/Core/Data/Data.php");
require_once("Atrox/Core/Data/DataEntity.php");

/**
 * Generic Comments that can be used anywhere on the site.
 *
 * @author Paul Serby (Clock Ltd) {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Base
 * @subpackage Community
 */
class CommentControl extends DataControl {
	var $table = "Comment";
	var $key = "Id";
	var $sequence = "Comment_Id_seq";
	var $defaultOrder = "Id";

	function init() {
		$this->fieldMeta["Id"] = new FieldMeta(
			"Id", "", FM_TYPE_INTEGER, null, FM_STORE_NEVER, false);

		$this->fieldMeta["RelationId"] = new FieldMeta(
			"RelationId Id", "", FM_TYPE_INTEGER , null, FM_STORE_ALWAYS, false);

		$this->fieldMeta["RelationType"] = new FieldMeta(
			"Relation Type", "", FM_TYPE_STRING, 255, FM_STORE_ALWAYS, false);

		$this->fieldMeta["AuthorId"] = new FieldMeta(
			"Author Id", "", FM_TYPE_RELATION, null, FM_STORE_ALWAYS, false);

		$this->fieldMeta["AuthorId"]->setRelationControl(BaseFactory::getMemberControl());
		$this->fieldMeta["AuthorId"]->setAutoData(CoreFactory::getCurrentMember());

		$this->fieldMeta["Anonymous"] = new FieldMeta(
			"Anonymous", "", FM_TYPE_BOOLEAN, 1, FM_STORE_ALWAYS, false);

		$this->fieldMeta["DateCreated"] = new FieldMeta(
			"DateCreated", "", FM_TYPE_DATE, null, FM_STORE_NEVER, true);

		$this->fieldMeta["DateCreated"]->setFormatter(CoreFactory::getDateTimeFieldFormatter());
		//$this->fieldMeta["DateCreated"]->setFormatter(CoreFactory::geImprovedDateTimeFieldFormatter());

		$this->fieldMeta["Body"] = new FieldMeta(
				"Body", "", FM_TYPE_STRING, 10000, FM_STORE_ALWAYS, false);

		$this->fieldMeta["LastModified"] = new FieldMeta(
			"LastModified", "", FM_TYPE_DATE, null, FM_STORE_NEVER, true);

		$this->fieldMeta["LastModified"]->setFormatter(CoreFactory::getDateTimeFieldFormatter());
		//$this->fieldMeta["LastModified"]->setFormatter(CoreFactory::geImprovedDateTimeFieldFormatter());

		$ipAddressControl = CoreFactory::getIpAddress();
		$this->fieldMeta["IpAddress"] = new FieldMeta(
			"Ip Address", $ipAddressControl->get(), FM_TYPE_STRING, 15, FM_STORE_ALWAYS, false);
	}

	/**
	 * Retrieve all the comments for a relation with a given id and type
	 *
	 * @param integer $id
	 * @param string $type
	 */
	function retrieveForType($type, $id = false, $order = false, $desc = false) {
		$filter = $this->getFilter();

		if ($id) {
			$filter->addConditional($this->table, "RelationId", $id);
		}
		$filter->addConditional($this->table, "RelationType", $type);
		if ($order) {
			$filter->clearOrder();
			$filter->addOrder($order, $desc);
		}

		$this->setFilter($filter);
		$this->retrieveAll();
	}


	function count($type, $id) {
		$this->initTable();
		$this->filter = CoreFactory::getFilter();
		$this->filter->addConditional($this->table, "RelationType", $type);
		$this->filter->addConditional($this->table, "RelationId", $id);
		return parent::count();
	}

	/**
	 * Deletes all the comments by the author of the given comment ID
	 *
	 */
	function deleteAllCommentByAuthor($id) {
		if ($comment = $this->item($id)) {
			$this->initTable();
			$this->filter = CoreFactory::getFilter();
			$this->filter->addConditional($this->table, "RelationType", $comment->get("RelationType"));
			$this->filter->addConditional($this->table, "RelationId", $comment->get("RelationId"));
			$this->filter->addConditional($this->table, "AuthorId", $comment->get("AuthorId"));
			$this->deleteAll();
		}
	}

	function getDataEntity() {
		return new CommentDataEntity($this);
	}

	function getTopComments($type, $limit = 6) {

		$sql = <<<SQL
SELECT  COUNT(*) AS "Count", "RelationId"
FROM "Comment"
WHERE  "Comment"."RelationType"  =  '$type'
GROUP BY "RelationId"
ORDER BY "Count" DESC
LIMIT $limit
SQL;

		$result = pg_query($sql);
		$items = array();
		while ($table = pg_fetch_array($result)) {
			$items[] = $table;
		}
		return $items;
	}
}

class CommentDataEntity extends DataEntity {
	function getAuthor() {
		return
			$this->get("Anonymous") == "t" && !$this->isMemberAllowedToModify()
				? "Anonymous" : $this->getRelationValue("AuthorId", "Alias");
	}
	function isMemberAllowedToModify() {
		$application = CoreFactory::getApplication();
		return $application->securityControl->isAllowed("Comments Admin", false) ||
			$application->securityControl->isAllowed($this->get("RelationType") . "Comments Admin", false);
	}
}