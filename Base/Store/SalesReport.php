<?php
/**
 * @package Base
 * @subpackage Store
 * @copyright Clock Limited 2007
 * @version 3.0 - $Revision$ - $Date$
 */

/**
 * Include Data.php so that DataControl can be extended.
 * Include Report.php so that ReportControl can be extended.
 */
require_once("Atrox/3.0/Core/Data/Data.php");

require_once("Atrox/3.0/Core/Data/Report.php");

/**
 *
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2007
 * @version 3.0 - $Revision$ - $Date$
 * @package Base
 * @subpackage Store
 */
class SalesReportControl extends ReportControl {
	var $searchFields = array();

	function SalesReportControl() {
		$this->fieldMeta["SupplierName"] = new  FieldMeta(
			"Supplier Name", "", FM_TYPE_STRING, null, FM_STORE_REPORT, false);

		$this->fieldMeta["SupplierName"] = new  FieldMeta(
			"Supplier Name", "", FM_TYPE_INTEGER, null, FM_STORE_REPORT, false);

		$this->fieldMeta["ProductName"] = new FieldMeta(
			"Product Name", "", FM_TYPE_STRING, 50, FM_STORE_REPORT, false);

		$this->fieldMeta["StockDescripiton"] = new FieldMeta(
			"Stock Descripiton", "", FM_TYPE_STRING, null, FM_STORE_REPORT, false);

		$this->fieldMeta["Quantity"] = new FieldMeta(
			"Quantity", "", FM_TYPE_INTEGER, null, FM_STORE_REPORT, false);

		$this->fieldMeta["DispatchCost"] = new FieldMeta(
			"Dispatch Cost", "", FM_TYPE_CURRENCY, null, FM_STORE_REPORT, false);

		$this->fieldMeta["ProductionCost"] = new FieldMeta(
			"Production Cost", "", FM_TYPE_CURRENCY, null, FM_STORE_REPORT, false);

		$this->fieldMeta["NetPrice"] = new FieldMeta(
			"Net Price", "", FM_TYPE_CURRENCY, 1000, FM_STORE_ALWAYS, false);

		$this->fieldMeta["Vat"] = new FieldMeta(
			"Vat", "", FM_TYPE_CURRENCY, null, FM_STORE_ALWAYS, false);

		$this->fieldMeta["SupplierProfitShare"] = new FieldMeta(
			"Supplier Profit Share", "", FM_TYPE_FLOAT, null, FM_STORE_REPORT, false);

		$this->fieldMeta["ShareBeforeCosts"] = new FieldMeta(
			"Share Before Costs", "", FM_TYPE_BOOLEAN, null, FM_STORE_REPORT, false);

		$this->init();
	}

	function generate($startDate = null, $endDate = null) {

		$sql = "SELECT ";
		$sql .= "\"Supplier\".\"Name\" AS \"SupplierName\", ";
		$sql .= "\"Supplier\".\"Id\" AS \"SupplierId\", ";
		$sql .= "\"Products\".\"Name\" AS \"ProductName\", ";
		$sql .= "\"StockItems\".\"Description\"  AS \"StockDescripiton\", ";
		$sql .= "SUM(\"OrderItem\".\"Quantity\") AS \"Quantity\", ";
		$sql .= "SUM(\"OrderItem\".\"ProductionCost\") AS \"ProductionCost\", ";
		$sql .= "SUM(\"OrderItem\".\"DispatchCost\") AS \"DispatchCost\", ";
		$sql .= "SUM(\"OrderItem\".\"NetPrice\") AS \"NetPrice\", ";
		$sql .= "SUM(\"OrderItem\".\"Vat\") AS \"Vat\", ";
		$sql .= "\"OrderItem\".\"SupplierProfitShare\", ";
		$sql .= "\"OrderItem\".\"ShareBeforeCosts\" ";
		$sql .= "FROM ";
		$sql .= "\"OrderItem\" ";
		$sql .= "LEFT JOIN \"StockItems\" ON \"OrderItem\".\"StockItemId\" = \"StockItems\".\"Id\" ";
		$sql .= "LEFT JOIN \"Products\" ON \"StockItems\".\"ProductId\" = \"Products\".\"Id\" ";
		$sql .= "LEFT JOIN \"Supplier\" ON \"OrderItem\".\"SupplierId\" = \"Supplier\".\"Id\" ";
		$sql .= "LEFT JOIN \"Order\" ON \"OrderItem\".\"OrderId\" = \"Order\".\"Id\" ";

		if ($startDate != null) {
					$sql	.= "WHERE \"Order\".\"DateCreated\" ";
			$sql .= "BETWEEN " . $this->databaseControl->parseValue($startDate)
				. " AND " .  $this->databaseControl->parseValue($endDate);
		}

		$sql .= "GROUP BY ";
		$sql .= "\"Supplier\".\"Name\", ";
		$sql .= "\"Supplier\".\"Id\", ";
		$sql .= "\"Products\".\"Name\", ";
		$sql .= "\"StockItems\".\"Description\", ";
		$sql .= "\"OrderItem\".\"SupplierProfitShare\", ";
		$sql .= "\"OrderItem\".\"ShareBeforeCosts\" ";

		$this->runQuery($sql);
	}
}