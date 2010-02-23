<?php

/**
 * @package Core
 * @subpackage Data
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 */

/**
 * Filter Class for filtering DataControl results
 *
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Core
 * @subpackage Data
 */
class Filter {

	/**
	 * The Sql for the filters join
	 * @access private
	 * @var String
	 */
	var $joins = null;

	/**
	 * The Sql for the filters conditional logic
	 * @access private
	 * @var String
	 */
	var $conditions = null;

	/**
	 * The Sql for the filters 'ORDER BY' clause
	 * @access private
	 * @var String
	 */
	var $orders = null;

	/**
	 * The Sql for the filters distinct clause
	 * @access private
	 * @var String
	 */
	var $distinct = null;

	/**
	 * The Sql for the filters limiting clause
	 * @access private
	 * @var String
	 */
	var $limit = null;

	/**
	 * The Sql for the filters offset clause
	 * @access private
	 * @var String
	 */
	var $offset = null;

	/**
	 * Database Control Obect
	 * @access private
	 * @var DatabaseControl
	 */
	var $databaseControl = null;

	function Filter() {
		$this->databaseControl = &CoreFactory::getDatabaseControl();
	}

	/**
	 * Adds and order by clause to the filter.
	 * <b>If this is invoked twice specifing the same field then the first
	 * order will be overriden.</b>
	 * @param String $order The field to order by
	 * @param String $desc Boolean inicating true for asending and false for desending
	 * @return void
	 */
	function addOrder($order, $desc = false, $ignoreCase = false) {

		if ($order === -1) {
			$newOrder["FullOrder"] = "RANDOM()";
			$newOrder["Descending"] = $desc;
			$newOrder["IgnoreCase"] = $ignoreCase;
		} else {
			$newOrder["FullOrder"] = $this->databaseControl->parseField($order);
			$newOrder["Descending"] = $desc;
			$newOrder["IgnoreCase"] = $ignoreCase;
		}
		$this->orders[$newOrder["FullOrder"]] = $newOrder;
	}

	/**
	 * Adds and order by clause to the filter.
	 * @param String $field The full text search field
	 * @param String $value The Expression to search for
	 */
	function addFreeTextCondition($table, $field, $value, $logicalOperator = "AND") {
		if ($value != "") {
			$value = preg_replace(array("/\s+\or\s+/", "/[^\d\w\s\|\']/", "/\b\s\s*\b/", "/\'/"), array("|","", "&", "&"), mb_strtolower(trim($value)));
			// Stop if word is too short
			if (($operator == "ILIKE") || ($operator == "NOT ILIKE")) {
				$newCondition["FullField"] = $this->databaseControl->parseField($field) . "::text";
			} else {
				$newCondition["FullField"] = $this->databaseControl->parseField($field) . "l";
			}
			$newCondition["FullField"] = $this->databaseControl->parseField($field);
			$newCondition["Value"] = "'$value'::tsquery";
			$newCondition["Operator"] = "@@";
			$newCondition["LogicalOperator"] = $logicalOperator;
			$newCondition["Type"] = 0;
			$this->conditions[] = $newCondition;
			$newOrder["FullOrder"] = "RANK({$newCondition["FullTable"]}.{$newCondition["FullField"]}, '$value')";
			$newOrder["Descending"] = true;
			$this->orders[$newOrder["FullOrder"]] = $newOrder;
		}
		return true;
	}

	/**
	 * Clears all current order fields
	 * @return void
	 */
	function clearOrder() {
		$this->orders = array();
	}
	
	function makeConditional($table, $field, $value, $operator = " = ", $logicalOperator = "AND") {
		$newCondition["FullTable"] = $this->databaseControl->parseTable($table);
		if (($operator == "ILIKE") || ($operator == "NOT ILIKE")) {
			$newCondition["FullField"] = $this->databaseControl->parseField($field) . "::text";
		} else {
			$newCondition["FullField"] = $this->databaseControl->parseField($field);
		}
		if ($value === null) {
  	  $newCondition["Value"] = "null";
		} else if (is_array($value)) {
			$newCondition["Value"] = "('" . implode("','", $value) . "')";
		} else {
  	  $newCondition["Value"] = $this->databaseControl->parseValue($value);
		}
		$newCondition["Operator"] = $operator;
		$newCondition["LogicalOperator"] = $logicalOperator;
		$newCondition["Type"] = 0;
		return $newCondition;
	}

	function addConditional($table, $field, $value, $operator = " = ", $logicalOperator = "AND") {
		$this->conditions[] = $this->makeConditional($table, $field, $value, $operator, $logicalOperator);
		return true;
	}

	function addConditionalGroup($conditions, $operator = "AND") {
		if ((!is_array($conditions)) || (count($conditions) <= 0)) {
			return false;
		}
		$this->conditions[] = array("LogicalOperator" => $operator, "Type" => 1);
		$this->conditions = array_merge($this->conditions, $conditions);
		$this->conditions[] = array("LogicalOperator" => "", "Type" => 2);
	}

	function addJoin($tableFrom, $fieldFrom, $tableTo, $fieldTo, $as = null) {
		$newJoin["FullTableFrom"] = $this->databaseControl->parseTable($tableFrom);
		$newJoin["FullFieldFrom"] = $this->databaseControl->parseField($fieldFrom);
		$newJoin["FullTableTo"] = $this->databaseControl->parseTable($tableTo);
		$newJoin["FullFieldTo"] = $this->databaseControl->parseField($fieldTo);
		$newJoin["As"] = null;
		if ($as) {
			$newJoin["As"] = $this->databaseControl->parseTable($as);
		}
		$this->joins[] = $newJoin;
	}

	/**
	 * Adds a limit to the number of rows the query can return
	 * @param Integer $limit The number of rows to limit to
	 * @return void
	 */
	function addLimit($limit) {
		$this->limit = "LIMIT $limit";
	}

	/**
	 * Adds an offset to offset the rows the query return
	 * @param Integer $offset The number of rows to offset by
	 * @return void
	 */
	function addOffset($offset) {
		$this->offset = "OFFSET $offset";
	}

	/**
	 * Sets whether or not the query should return distinct rows
	 * By default all rows are returned
	 * @param Boolean $distinct True if only distinct rows should be returned
	 * @return void
	 */
	function setDistinct($distinct, $table = null, $field = null) {
		if ($distinct) {
			if ($table == null || $field == null) {
				$this->distinct = "DISTINCT";
			} else {
				$this->distinct = "DISTINCT ON (\"{$table}\".\"{$field}\")";
			}
		} else {
			$this->distinct = "";
		}
	}

	function getDistinctSql() {
		return $this->distinct;
	}

	function getJoinSql() {
		if (!is_array($this->joins)) {
			return null;
		}
		$joinSql = "";
		foreach ($this->joins as $v) {
			if ($v["As"]) {
				$joinSql .= "LEFT JOIN {$v["FullTableTo"]} AS {$v["As"]} ON {$v["FullTableFrom"]}.{$v["FullFieldFrom"]} = {$v["As"]}.{$v["FullFieldTo"]} ";
			} else {
				$joinSql .= "LEFT JOIN {$v["FullTableTo"]} ON {$v["FullTableFrom"]}.{$v["FullFieldFrom"]} = {$v["FullTableTo"]}.{$v["FullFieldTo"]} ";
			}
		}
		return $joinSql;
	}

	function getConditionSql() {
		if (!is_array($this->conditions)) {
			return null;
		}
		$whereSql = "";
		$logicalOperatorLength = 0;
		$firstItem = true;
		foreach ($this->conditions as $v) {
			if ($v["Type"] == 0) {
				$whereSql .= (!$firstItem ? $v["LogicalOperator"] : "") .
					" $v[FullTable].$v[FullField] $v[Operator] $v[Value] ";
				$firstItem = false;
			} else if ($v["Type"] == 1) {
				$whereSql .= (!$firstItem ? $v["LogicalOperator"] : "") . " (";
				$firstItem = true;
			} else if ($v["Type"] == 2) {
				$whereSql .= ")";
			}
		}
		if (!$firstItem) {
			$whereSql = $whereSql;
		}
		if ($whereSql != null) {
			return "WHERE $whereSql";
		}
	}

	function getOrderSql() {
		if (!is_array($this->orders)) {
			return null;
		}
		$orderSql = null;

		foreach ($this->orders as $v) {
			if ((isset($v["IgnoreCase"])) && ($v["IgnoreCase"])) {
				$orderSql  .= "lower(" . $v["FullOrder"]. ") " . ($v["Descending"] ? "DESC" : "") . ", ";
			} else {
				$orderSql  .= $v["FullOrder"]. " " . ($v["Descending"] ? "DESC" : "") . ", ";
			}
		}

		// Crop the last comma
		$orderSql = mb_substr($orderSql, 0,-2);

		if ($orderSql == null) {
			return null;
		} else {
			return "ORDER BY " . $orderSql;
		}
	}

	/**
	 * @return String A string containing valid sql syntax for limiting rows
	 */
	function getLimitSql() {
		return $this->limit;
	}

	/**
	 * @return String A string containing valid sql syntax for offsetting rows
	 */
	function getOffsetSql() {
		return $this->offset;
	}
}