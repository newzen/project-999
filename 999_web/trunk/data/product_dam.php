<?php
/**
 * Library with classes for accessing the database tables regarding products.
 * @package ProductDAM
 * @author Roberto Oliveros
 */

/**
 * Class for accessing unit of measure tables in the database.
 * @package ProductDAM
 * @author Roberto Oliveros
 */
class UnitOfMeasureDAM{
	static private $_mName = 'Unitario';
	
	/**
	 * Returns an instance of a unit of measure.
	 *
	 * Returns NULL if there was no match of the provided id in the database.
	 * @param integer $id
	 * @return UnitOfMeasure
	 */
	static public function getInstance($id){
		if($id == 123){
			$um = new UnitOfMeasure($id, PersistObject::CREATED);
			$um->setData(self::$_mName);
			return $um;
		}
		else
			return NULL;
	}
	
	/**
	 * Insert the object's data in the database.
	 *
	 * Returns the new created id from the database.
	 * @param UnitOfMeasure $obj
	 * @return integer
	 */
	static public function insert(UnitOfMeasure $obj){
		return 123;
	}
	
	/**
	 * Updates the object's data in the database.
	 *
	 * @param UnitOfMeasure $obj
	 */
	static public function update(UnitOfMeasure $obj){
		self::$_mName = $obj->getName();
	}
	
	/**
	 * Deletes the object from the database.
	 *
	 * Returns true on success, otherwise false due dependencies.
	 * @param UnitOfMeasure $obj
	 * @return boolean
	 */
	static public function delete(UnitOfMeasure $obj){
		if($obj->getId() == 123)
			return true;
		else
			return false;
	}
}


/**
 * Class for accessing manufacturer's tables in the database.
 * @package ProductDAM
 * @author Roberto Oliveros
 */
class ManufacturerDAM{
	static private $_mName = 'Bayer';
	
	/**
	 * Returns an instance of a manufacturer.
	 *
	 * Returns NULL if there was no match of the provided id in the database.
	 * @param integer $id
	 * @return Manufacturer
	 */
	static public function getInstance($id){
		if($id == 123){
			$manufacturer = new Manufacturer($id, PersistObject::CREATED);
			$manufacturer->setData(self::$_mName);
			return $manufacturer;
		}
		else
			return NULL;
	}
	
	/**
	 * Insert the object's data in the database.
	 *
	 * Returns the new created id from the database.
	 * @param Manufacturer $obj
	 * @return integer
	 */
	static public function insert(Manufacturer $obj){
		return 123;
	}
	
	/**
	 * Updates the object's data in the database.
	 *
	 * @param Manufacturer $obj
	 */
	static public function update(Manufacturer $obj){
		self::$_mName = $obj->getName();
	}
	
	/**
	 * Deletes the object from the database.
	 *
	 * Returns true on success, otherwise false due dependencies.
	 * @param Manufacturer $obj
	 * @return boolean
	 */
	static public function delete(Manufacturer $obj){
		if($obj->getId() == 123)
			return true;
		else
			return false;
	}
}


/**
 * Class in charge of accessing the database tables regarding the product's suppliers.
 * @package ProductDAM
 * @author Roberto Oliveros
 */
class ProductSupplierDAM{
	/**
	 * Inserts the product detail's data into the database.
	 *
	 * @param Product $product
	 * @param ProductSupplier $detail
	 */
	static public function insert(Product $product, ProductSupplier $detail){
		// Code here...
	}
	
	/**
	 * Deletes the product detail from the database.
	 *
	 * @param Product $product
	 * @param ProductSupplier $detail
	 */
	static public function delete(Product $product, ProductSupplier $detail){
		// Code here...
	}
}


/**
 * Class responsible for accessing the product's tables in the database.
 * @package ProductDAM
 * @author Roberto Oliveros
 */
class ProductDAM{
	static private $_mPackaging = '120 ml';
	
	/**
	 * Returns true if a product already uses the bar code in the database.
	 *
	 * @param string $barCode
	 * @return boolean
	 */
	static public function existsBarCode(Product $product, $barCode){
		if($barCode == '123456')
			return true;
		else
			return false;
	}
	
	/**
	 * Verifies if the product detail already exists in the database.
	 *
	 * @param ProductSupplier $detail
	 * @return boolean
	 */
	static public function existsProductSupplier(ProductSupplier $detail){
		if($detail->getId() == '123ABC')
			return true;
		else
			return false;
	}
	
	/**
	 * Sets the bar code of an existing product.
	 *
	 * @param string $barCode
	 */
	static public function setBarCode($barCode){
		// Code here...
	}
	
	/**
	 * Returns an instance of a product.
	 *
	 * Returns NULL in case there was no match for the provided id in the database.
	 * @param integer $id
	 * @return Product
	 */
	static public function getInstance($id){
		if($id == 123){
			$product = new Product($id, Persist::CREATED);
			$um = UnitOfMeasure::getInstance(123);
			$manufacturer = Manufacturer::getInstance(123);
			$details = array();
			$details[] = new ProductSupplier(Supplier::getInstance(123), 'Abb213', Persist::CREATED);
			$product->setData('Pepto Bismol', '12345', self::$_mPackaging, 'Para dolores de estomagol.', $um,
					$manufacturer, 12.65, false, $details);
			return $product;
		}
		else
			return NULL;
	}
	
	/**
	 * Returns an instance of a product.
	 *
	 * Returns a product whick bar code matches the one provided. If not found returns NULL.
	 * @param string $barCode
	 * @return Product
	 */
	static public function getInstanceByBarCode($barCode){
		if($barCode == '12345'){
			$product = new Product(123, Persist::CREATED);
			$um = UnitOfMeasure::getInstance(123);
			$manufacturer = Manufacturer::getInstance(123);
			$details = array();
			$details[] = new ProductSupplier(Supplier::getInstance(123), 'Abb213', Persist::CREATED);
			$product->setData('Pepto Bismol', '12345', self::$_mPackaging, 'Para dolores de estomagol.', $um,
					$manufacturer, 12.65, false, $details);
			return $product;
		}
		else
			return NULL;
	}
	
	/**
	 * Returns an instance of a product.
	 *
	 * Returns a product which has a supplier with the provided product's sku. If not found returns NULL.
	 * @param Supplier $supplier
	 * @param string $sku
	 * @return Product
	 */
	static public function getInstanceBySupplier(Supplier $supplier, $sku){
		if($supplier->getId() == 123 && $sku == 'Abb213'){
			$product = new Product(123, Persist::CREATED);
			$um = UnitOfMeasure::getInstance(123);
			$manufacturer = Manufacturer::getInstance(123);
			$details = array();
			$details[] = new ProductSupplier($supplier, 'Abb213', Persist::CREATED);
			$product->setData('Pepto Bismol', '12345', self::$_mPackaging, 'Para dolores de estomagol.', $um,
					$manufacturer, 12.65, false, $details);
			return $product;
		}
		else
			return NULL;
	}

	/**
	 * Inserts the product's data into the database.
	 *
	 * Returns the new created id from the database.
	 * @param Product $obj
	 * @return integer
	 */
	static public function insert(Product $obj){
		return 123;
	}
	
	/**
	 * Updates the product's data in the database.
	 *
	 * @param Product $obj
	 */
	static public function update(Product $obj){
		self::$_mPackaging = $obj->getPackaging();
	}
	
	/**
	 * Deletes the object from the database.
	 *
	 * Returns true on success, otherwise false due dependencies.
	 * @param Product $obj
	 * @return boolean
	 */
	static public function delete(Product $obj){
		if($obj->getId() == 123)
			return true;
		else
			return false;
	}
}


/**
 * Class in charge of accessing database tables regarding the bonus.
 * @package ProductDAM
 * @author Roberto Oliveros
 */
class BonusDAM{
	/**
	 * Verifies if the bonus already exists in the database.
	 *
	 * Returns true if it does.
	 * @param Product $product
	 * @param integer $quantity
	 * @return boolean
	 */
	static public function exists(Product $product, $quantity){
		if($product->getId() == 123 && $quantity == 4)
			return true;
		else
			return false;
	}
	
	/**
	 * Returns an instance of a bonus.
	 *
	 * Returns NULL if there was no match for the provided id in the database.
	 * @param integer $id
	 * @return Bonus
	 */
	static public function getInstance($id){
		if($id == 123){
			$product = Product::getInstance(123);
			$bonus = new Bonus($product, 4, 0.25, '15/05/2009', '01/04/2009', $id, Persist::CREATED);
			return $bonus;
		}
		else
			return NULL;
	}
	
	/**
	 * Returns an instance of a bonus.
	 *
	 * Returns the bonus which belongs to the provided product and contains the same quantity.
	 * @param Product $product
	 * @param integer $quantity
	 * @return Bonus
	 */
	static public function getInstanceByProduct(Product $product, $quantity){
		if($product->getId() == 123 && $quantity == 4)
			return new Bonus($product, 4, 0.25, '15/05/2009', '01/04/2009', 123, Persist::CREATED);
		else
			return NULL;
	}
	
	/**
	 * Inserts the bonus' data in the database.
	 *
	 * Returns the new created id from the database.
	 * @param Bonus $obj
	 * @return integer
	 */
	static public function insert(Bonus $obj){
		return 123;
	}
	
	/**
	 * Deletes the object from the database.
	 *
	 * Returns true on success, otherwise false due dependencies.
	 * @param Bonus $obj
	 * @return boolean
	 */
	static public function delete(Bonus $obj){
		if($obj->getId() == 123)
			return true;
		else
			return false;
	}
}


/**
 * Class for accessing database tables regarding lots.
 * @package ProductDAM
 * @author Roberto Oliveros
 */
class LotDAM{
	/**
	 * Returns the quantity available of the lot.
	 *
	 * @param Lot $obj
	 * @return integer
	 */
	static public function getAvailable(Lot $obj){
		if($obj->getId() == 123)
			return 15;
		else
			return 0;
	}
	
	/**
	 * Deactivates the lot in the database.
	 *
	 * @param Lot $obj
	 */
	static public function deactivate(Lot $obj){
		// Code here...
	}
	
	/**
	 * Increases the lot's quantity in the database.
	 *
	 * @param Lot $obj
	 * @param integer $quantity
	 */
	static public function increase(Lot $obj, $quantity){
		// Code here...
	}
	
	/**
	 * Decrease the lot's quantity in the database.
	 *
	 * @param Lot $obj
	 * @param integer $quantity
	 */
	static public function decrease(Lot $obj, $quantity){
		// Code here...
	}
	
	/**
	 * Reserves the provided quantity in the database.
	 *
	 * @param Lot $obj
	 * @param integer $quantity
	 */
	static public function reserve(Lot $obj, $quantity){
		// Code here...
	}
	
	/**
	 * Decreases the reserve quantity of the lot in the database.
	 *
	 * @param Lot $obj
	 * @param integer $quantity
	 */
	static public function decreaseReserve(Lot $obj, $quantity){
		// Code here...
	}
	
	/**
	 * Returns an instance of a lot.
	 *
	 * Returns NULL if there was no match for the provided id in the database.
	 * @param integer $id
	 * @return Lot
	 */
	static public function getInstance($id){
		if($id == 123){
			$product = Product::getInstance(123);
			$lot = new Lot($product, 20, 12.65, '31/12/2009', '15/04/2009', $id, Persist::CREATED);
			return $lot;
		}
		else
			return NULL;
	}
	
	/**
	 * Inserts the lot's data in the database.
	 *
	 * Returns the new created id from the database.
	 * @param Lot $obj
	 * @return integer
	 */
	static public function insert(Lot $obj){
		return 123;
	}
}


/**
 * Class for accessing the database tables regarding products and lots.
 * @package ProductDAM
 * @author Roberto Oliveros
 */
class InventoryDAM{
	/**
	 * Returns the available quantity of the product's inventory.
	 *
	 * @param Product $obj
	 * @return integer
	 */
	static public function getAvailable(Product $obj){
		if($obj->getId() == 123)
			return 32;
	}
	
	/**
	 * Returns the quantity on hand of the product's inventory.
	 *
	 * @param Product $obj
	 * @return integer
	 */
	static public function getQuantity(Product $obj){
		if($obj->getId() == 123)
			return 40;
	}
	
	/**
	 * Returns the lots with available quantities of the provided product.
	 *
	 * Returns an array with all the lots that contains available quantities.
	 * @param Product $obj
	 * @return array<Lot>
	 */
	static public function getLots(Product $obj){
		if($obj->getId() == 123){
			$lots = array();
			$lots[] = new Lot($obj, 4, 14.68, '15/08/2009', '10/01/2009', 4321, Persist::CREATED);
			$lots[] = new Lot($obj, 15, 15.25, '15/11/2009', '15/02/2009', 4322, Persist::CREATED);
			return $lots;
		}
	}
	
	
	static public function getNegativeLots(Product $obj){
		if($obj->getId() == 123){
			$lots = array();
			$lots[] = new Lot($obj, -3, 14.75, NULL, NULL, 4320, Persist::CREATED);
			return $lots;
		}
	}
}
?>