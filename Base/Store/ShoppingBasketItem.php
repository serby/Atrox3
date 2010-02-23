<?php
/**
 * @package Base
 * @subpackage Store
 * @copyright Clock Limited 2007
 * @version 3.0 - $Revision$ - $Date$
 */

/**
 * Include Data.php so that DataControl can be extended.
 * Include StockItem.php so that StockItemControl can be extended.
 */
require_once("Atrox/Core/Data/Data.php");
require_once("StockItem.php");

/**
 *
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2007
 * @version 3.0 - $Revision$ - $Date$
 * @package Base
 * @subpackage Store
 */
class ShoppingBasketItemControl extends DataControl {
	var $table = "ShoppingBasketItem";
	var $key = "Id";
	var $sequence = "ShoppingBasketItem_Id_seq";
	var $defaultOrder = "Id";

	function init() {

		$this->fieldMeta["Id"] = new FieldMeta(
			"Id", "", FM_TYPE_INTEGER, null, FM_STORE_NEVER, false);

		$this->fieldMeta["ShoppingBasketId"] = new FieldMeta(
			"Shopping Basket Id", "", FM_TYPE_RELATION, null, FM_STORE_ALWAYS, false);

		$this->fieldMeta["ShoppingBasketId"]->setRelationControl(BaseFactory::getShoppingBasketControl());

		$this->fieldMeta["StockItemId"] = new FieldMeta(
			"Stock Item Id", "", FM_TYPE_RELATION, null, FM_STORE_ALWAYS, false);

		$this->fieldMeta["StockItemId"]->setRelationControl(BaseFactory::getStockItemControl());

		$this->fieldMeta["Quantity"] = new FieldMeta(
			"Quantity", "", FM_TYPE_INTEGER, null, FM_STORE_ALWAYS, false);

	}

	function retrieveShoppingBasket($shoppingBasket) {
		$filter = CoreFactory::getFilter();
		$filter->addConditional($this->table, "ShoppingBasketId", $shoppingBasket->get("Id"));
		$this->setFilter($filter);
		$this->retrieveAll();
	}
	
	function removeProductDiscount($shoppingBasket, $product) {
		$filter = CoreFactory::getFilter();
		$filter->addJoin($this->table, "StockItemId", "StockItems", "Id");
		$filter->addConditional($this->table, "ShoppingBasketId", $shoppingBasket->get("Id"));
		$filter->addConditional("StockItems", "ProductId", $product->get("Id"));
		$filter->addConditional("StockItems", "Type", STOCK_TYPE_WHOLEPRODUCTDISCOUNT);
		$this->setFilter($filter);
		$this->retrieveAll();
		while ($shoppingBasketItem = $this->getNext()) {
			$this->delete($shoppingBasketItem->get("Id"));
		}
	}
	
	function deleteProductFromShoppingBasket($productId) {
		$filter = CoreFactory::getFilter();
		$filter->addJoin($this->table, "StockItemId", "StockItems", "Id");
		$filter->addConditional("StockItems", "ProductId", $productId);
		$this->setFilter($filter);
		$this->retrieveAll();
		while ($shoppingBasketItem = $this->getNext()) {
			$this->delete($shoppingBasketItem->get("Id"));				
		}
	}		
}