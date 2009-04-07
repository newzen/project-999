<?php
/**
 * Library containing all the classes regarding products.
 * @package Product
 * @author Roberto Oliveros
 */

/**
 * Includes the Persist package.
 */
require_once('business/persist.php');
/**
 * Includes the ProductDAM package for accessing the database.
 */
require_once('data/product_dam.php');

/**
 * Represents a unit of measure for a certain product.
 * @package Product
 * @author Roberto Oliveros
 */
class UnitOfMeasure extends Identifier{
	/**
	 * Constructs the object with the provided id and status.
	 *
	 * Parameters must be set only if the method is called from the database layer.
	 * @param integer $id
	 * @param integer $status
	 */
	public function __construct($id = NULL, $status = Persist::IN_PROGRESS){
		parent::__construct($id, $status);
	}
	
	/**
	 * Returns an instance of a unit of measure.
	 * 
	 * Returns NULL if there was no match in the database.
	 * @param integer $id
	 * @return UnitOfMeasure
	 */
	static public function getInstance($id){
		self::validateId($id);
		return UnitOfMeasureDAM::getInstance($id);
	}
	
	/**
	 * Deletes the unit of measure from database.
	 * 
	 * Returns true confirming the deletion, otherwise false due dependencies.
	 * @param UnitOfMeasure $obj
	 * @return boolean
	 */
	static public function delete(UnitOfMeasure $obj){
		self::validateObjectForDelete($obj);
		return UnitOfMeasureDAM::delete($obj);
	}
	
	/**
	 * Inserts the object's data to the database.
	 *
	 * Returns the new created id from the database.
	 * @return integer
	 */
	protected function insert(){
		return UnitOfMeasureDAM::insert($this);
	}
	
	/**
	 * Updates the object's data in the database.
	 *
	 */
	protected function update(){
		UnitOfMeasureDAM::update($this);
	}
}


/**
 * Represents the manufacturer of a certain product.
 * @package Product
 * @author Roberto Oliveros
 */
class Manufacturer extends Identifier{
/**
	 * Constructs the object with the provided id and status.
	 *
	 * Parameters must be set only if the method is called from the database layer.
	 * @param integer $id
	 * @param integer $status
	 */
	public function __construct($id = NULL, $status = Persist::IN_PROGRESS){
		parent::__construct($id, $status);
	}
	
	/**
	 * Returns an instance of a manufacturer.
	 * 
	 * Returns NULL if there was no match in the database.
	 * @param integer $id
	 * @return Manufacturer
	 */
	static public function getInstance($id){
		self::validateId($id);
		return ManufacturerDAM::getInstance($id);
	}
	
	/**
	 * Deletes the manufacturer from the database.
	 * 
	 * Returns true confirming the deletion, otherwise false due dependencies.
	 * @param Manufacturer $obj
	 * @return boolean
	 */
	static public function delete(Manufacturer $obj){
		self::validateObjectForDelete($obj);
		return ManufacturerDAM::delete($obj);
	}
	
	/**
	 * Inserts the object's data to the database.
	 *
	 * Returns the new created id from the database.
	 * @return integer
	 */
	protected function insert(){
		return ManufacturerDAM::insert($this);
	}
	
	/**
	 * Updates the object's data in the database.
	 *
	 */
	protected function update(){
		ManufacturerDAM::update($this);
	}
}


/**
 * Class with necessary functionality for controlling a product's inventory.
 * @package Product
 * @author Roberto Oliveros
 */
class Inventory{
	/**
	 * Returns the available quantity of the inventory's product.
	 *
	 * @param Product $product
	 * @return integer
	 */
	static public function getAvailable(Product $product){
		self::validateProduct($product);
		return InventoryDAM::getAvailable($product);
	}
	
	/**
	 * Returns the quantity on hand of the inventory's product.
	 *
	 * @param Product $product
	 * @return integer
	 */
	static public function getQuantity(Product $product){
		self::validateProduct($product);
		return InventoryDAM::getQuantity($product);
	}
	
	/**
	 * Returns the necessary lots to fulfill the requested quantity of units.
	 *
	 * Returns an array containing the necessary lots with available quantity. If the requested quantity
	 * cannot be fulfilled new lots are created and included.
	 * @param Product $product
	 * @param integer $reqUnitsQuantity
	 * @return array<Lot>
	 */
	static public function getLots(Product $product, $reqUnitsQuantity){
		self::validateProduct($product);
		self::validateQuantity($reqUnitsQuantity);
		
		// Get the lots from the database with available stock.
		$in_stock_lots = InventoryDAM::getLots($product);
		// The returned qrray with the lots which fulfill the requested quantity of units.
		$lots = array();

		$lot = current($in_stock_lots);
		do{
			// Verify the available quantity of the lot.
			if(!$lot)
				$available = 0;
			else
				$available = $lot->getAvailable();

			// If there is available quantity but doesn't fulfill the requested quantity.
			if($available > 0 && $available < $reqUnitsQuantity){
				$lots[] = $lot;
				$reqUnitsQuantity = $reqUnitsQuantity - $available;
			// If the available quantity does fulfill the requested quantity.
			}elseif($available > 0 && $available >= $reqUnitsQuantity){
				$lots[] = $lot;
				$reqUnitsQuantity = 0;
			// Otherwise create a new empty lot.
			}else {
				$new_lot = new Lot($this->_mProduct, 0, 0);
				$this->addLot($new_lot);
				$lots[] = $new_lot;
				$reqUnitsQuantity = 0;
			}
			
			$lot = next($in_stock_lots);
		} while($reqUnitsQuantity > 0);
		
		return $lots;
	}
	
	/**
	 * Returns the necessary existing lots with negative quantity.
	 *
	 * Returns an array including lots with negative quantities. If the requested quantity cannot be fulfilled
	 * new lots are created and included.
	 * @param Product $product
	 * @param integer $reqUnitsQuantity
	 * @return array<Lot>
	 */
	static public function getNegativeLots(Product $product, $reqUnitsQuantity){
		self::validateProduct($product);
		self::validateQuantity($reqUnitsQuantity);
		
		// Get the negative lots from the database.
		$negative_lots = InventoryDAM::getNegativeLots($product);
		// The returned array with the negative lots.
		$lots = array();
		
		$lot = current($negative_lots);
		do{
			// Verify the negative quantity of the lot.
			if(!$lot)
				$negative = 0;
			else
				$negative = -1 * $lot->getAvailable();

			// If it has a negative quantity but doesn't fulfill the requested quantity.
			if($negative > 0 && $negative < $reqUnitsQuantity){
				$lots[] = $lot;
				$reqUnitsQuantity = $reqUnitsQuantity - $negative;
			// If it does fulfill the requested quantity.
			}elseif($negative > 0 && $negative >= $reqUnitsQuantity){
				$lots[] = $lot;
				$reqUnitsQuantity = 0;
			// Otherwise create a new lot.
			}else {
				$lots[] = new Lot($this->_mProduct, 0, 0);
				$reqUnitsQuantity = 0;
			}
			
			$lot = next($negative_lots);
		} while($reqUnitsQuantity > 0);
		
		return $lots;
	}
	
	/**
	 * Returns an array with the details of the lots.
	 * 
	 * The passed parameters are returned with the sum quantity and the sum quantity available of all the
	 * product's lots.
	 * @param Product $product
	 * @param integer $quantity
	 * @param integer $available
	 * @return array
	 */
	static public function showLots(Product $product, &$quantity, &$available){
		self::validateProduct($product);
		
		$lots = InventoryDAM::getLots($product);
		
		foreach($lots as $lot){
			$lot_details[] = $lot->show();
			$quantity += $lot->getQuantity();
			$available += $lot->getAvailable();
		}
		
		return $lot_details;
	}
	
	/**
	 * Reserves the specified quantity from the provided product in the database.
	 *
	 * @param Product $product
	 * @param integer $quantity
	 */
	static public function reserve(Product $product, $quantity){
		self::validateProduct($product);
		self::validateQuantity($quantity);
		InventoryDAM::reserve($product, $quantity);
	}
	
	/**
	 * Decreases the product's reserve by the quantity provided.
	 *
	 * @param Product $product
	 * @param integer $quantity
	 */
	static public function decreaseReserve(Product $product, $quantity){
		self::validateProduct($product);
		self::validateQuantity($quantity);
		InventoryDAM::decreaseReserve($product, $quantity);
	}
	
	/**
	 * Decreases the product's quantity.
	 *
	 * @param Product $product
	 * @param integer $quantity
	 */
	static public function decrease(Product $product, $quantity){
		self::validateProduct($product);
		self::validateQuantity($quantity);
		InventoryDAM::decrease($product, $quantity);
	}
	
	/**
	 * Increases the product's quantity.
	 *
	 * @param Product $product
	 * @param integer $quantity
	 */
	static public function increase(Product $product, $quantity){
		self::validateProduct($product);
		self::validateQuantity($quantity);
		InventoryDAM::increase($product, $quantity);
	}
	
	/**
	 * Validates if the product's status property other than Persist::IN_PROGRESS.
	 *
	 * Throws an exception if it is not.
	 * @param Product $product
	 */
	static private function validateProduct(Product $product){
		if($product->getStatus() == PersistObject::IN_PROGRESS)
			throw new Exception('Persist::IN_PROGRESS product provided.');
	}
	
	/**
	 * Validates the quantity.
	 *
	 * Must be greater than cero. Otherwise it throws an exception.
	 * @param integer $quantity
	 */
	static private function validateQuantity($quantity){
		if(!is_int($quantity) || $quantity < 1)
			throw new Exception('Cantidad inv&aacute;lida.');
	}
}



class ProductDetail extends Persist{
	/**
	 * Holds the product's supplier.
	 *
	 * @var Supplier
	 */
	private $_mSupplier;
	
	/**
	 * Holds the supplier's product's SKU.
	 *
	 * @var string
	 */
	private $_mProductSKU;
	
	/**
	 * Flag that indicates if the detail has to be deteled.
	 *
	 * @var boolean
	 */
	private $_mDeleted;
	
	/**
	 * Constructs the detail with the provided supplier, product sku and status.
	 *
	 * @param Supplier $supplier
	 * @param string $productSKU
	 * @param integer $status
	 */
	public function __construct(Supplier $supplier, $productSKU, $status = Persist::IN_PROGRESS){
		parent::__construct($status);
		
		$this->validateSupplier($supplier);
		$this->validateProductSKU($productSKU);
		
		$this->_mSupplier = $supplier;
		$this->_mProductSKU = $productSKU;
	}
	
	/**
	 * Returns the detail's id.
	 *
	 * @return string
	 */
	public function getId(){
		return $this->_mSupplier->getId() . $this->_mProductSKU;
	}
	
	/**
	 * Returns the supplier's product's SKU.
	 *
	 * @return string
	 */
	public function getProductSKU(){
		return $this->_mProductSKU;
	}
	
	/**
	 * Returns the detail's supplier.
	 *
	 * @return Supplier
	 */
	public function getSupplier(){
		return $this->_mSupplier;
	}
	
	/**
	 * Returns the detail's deleted flag.
	 *
	 * @return boolean
	 */
	public function isDeleted(){
		return $this->_mDeleted;
	}
	
	/**
	 * Retuns an array for displaying the detail's data.
	 *
	 * An array with the fields supplier and product_sku is returned.
	 * @return array
	 */
	public function show(){
		return array('supplier' => $this->_mSupplier->getName(), 'product_sku' => $this->_mProductSKU);
	}
	
	/**
	 * Sets the detail's deleted flag to true.
	 *
	 */
	public function delete(){
		$this->_mDeleted = true;
	}
	
	
	public function commit(Product $product){
		if($this->_mStatus == Persist::IN_PROGRESS)
			ProductDetailDAM::insert($product, $this);
		elseif($this->_mStatus == Persist::CREATED && $this->_mDeleted)
			ProductDetailDAM::delete($product, $this);
	}
	
	/**
	 * Validates the provided supplier.
	 * 
	 * The supplier status property must be set to other than Persist::IN_PROGRESS. Otherwise it throws an
	 * exception.
	 * @param Supplier $obj
	 */
	private function validateSupplier(Supplier $obj){
		if($obj->getStatus() == Persist::IN_PROGRESS)
			throw new Exception('Persist::IN_PROGRESS supplier provided.');
	}
	
	/**
	 * Validates the provided product id.
	 * 
	 * Must not be empty. Otherwise it throws an exception.
	 * @param integer $ProductSKU
	 */
	private function validateProductSKU($productSKU){
		if(empty($productSKU))
			throw new Exception('Invalid product SKU!');
	}
}


/**
 * Represents a product in the inventory.
 * @package Product
 * @author Roberto Oliveros
 */
class Product extends PersistObject{
	/**
	 * Holds the product's bar code.
	 *
	 * @var string
	 */
	private $_mBarCode;
	
	/**
	 * Holds the product's packaging.
	 *
	 * @var string
	 */
	private $_mPackaging;
	
	/**
	 * Holds the product's description.
	 *
	 * @var string
	 */
	private $_mDescription;
	
	/**
	 * Holds the product's unit of measure.
	 *
	 * @var UnitOfMeasure
	 */
	private $_mUM;
	
	/**
	 * Holds the product's manufacturer.
	 *
	 * @var Manufacturer
	 */
	private $_mManufacturer;
	
	/**
	 * Holds the product's price.
	 *
	 * @var float
	 */
	private $_mPrice;
	
	/**
	 * Holds the product's last price.
	 *
	 * @var float
	 */
	private $_mLastPrice;
	
	/**
	 * Holds flag that indicates whether the product is deactivated or not.
	 *
	 * @var boolean
	 */
	private $_mDeactivated;
	
	/**
	 * Holds the suppliers of this product.
	 *
	 * @var array<ProductDetail>
	 */
	private $_mDetails;
	
	/**
	 * Returns the product's bar code.
	 *
	 * @return string
	 */
	public function getBarCode(){
		return $this->_mBarCode;
	}
	
	/**
	 * Returns the product's packaging.
	 *
	 * @return string
	 */
	public function getPackaging(){
		return $this->_mPackaging;
	}
	
	/**
	 * Returns the product's description.
	 *
	 * @return string
	 */
	public function getDescription(){
		return $this->_mDescription;
	}
	
	/**
	 * Returns the product's price.
	 *
	 * @return float
	 */
	public function getPrice(){
		return $this->_mPrice;
	}
	
	/**
	 * Returns the flag's value.
	 *
	 * @return boolean
	 */
	public function isDeactivated(){
		return $this->_mDeactivated;
	}
	
	/**
	 * Returns an array with the product's details' data.
	 *
	 * @return array
	 */
	public function showDetails(){
		foreach($this->_mDetails as $detail)
			$details[] = $detail->show();
			
		return $details;
	}
	
	/**
	 * Returns the product's unit of measure.
	 *
	 * @return UnitOfMeasure
	 */
	public function getUnitOfMeasure(){
		return $this->_mUM;
	}
	
	/**
	 * Returns the product's manufacturer.
	 *
	 * @return Manufacturer
	 */
	public function getManufacturer(){
		return $this->_mManufacturer;
	}
	
	/**
	 * Returns a product's detail.
	 *
	 * Returns the detail which id matches the provided id.
	 * @param string $id
	 * @return ProductDetail
	 */
	public function getDetail($id){
		$this->validateDetailId($id);
		foreach($this->_mDetails as &$detail)
			if($detail->getId() == $id)
				return $detail;
	}
	
	/**
	 * Sets the product's bar code.
	 *
	 * @param string $barCode
	 */
	public function setBarCode($barCode){
		$this->validateBarCode($barCode);
		$this->verifyBarCode($barCode);
		$this->_mBarCode = $barCode;
	}
	
	/**
	 * Sets the product's packaging
	 *
	 * @param string $packaging
	 */
	public function setPackaging($packaging){
		$this->validatePackaging($packaging);
		$this->_mPackaging = $packaging;
	}
	
	/**
	 * Sets the product's description.
	 *
	 * @param string $description
	 */
	public function setDescription($description){
		$this->validateDescription($description);
		$this->_mDescription = $description;
	}
	
	/**
	 * Sets the product's unit of measure.
	 *
	 * @param UnitOfMeasure $um
	 */
	public function setUnitOfMeasure(UnitOfMeasure $um){
		$this->validateUnitOfMeasure($um);
		$this->_mUM = $um;
	}
	
	/**
	 * Sets the product's manufacturer.
	 *
	 * @param Manufacturer $obj
	 */
	public function setManufacturer(Manufacturer $obj){
		$this->validateManufacturer($obj);
		$this->_mManufacturer = $obj;
	}
	
	/**
	 * Sets the product new price.
	 *
	 * @param float $price
	 */
	public function setPrice($price){
		$this->validatePrice($price);
		$price = number_format($price, 2);
		if($this->_mPrice != $price){
			$this->_mLastPrice = $this->_mPrice;
			$this->_mPrice = $price;
		}
	}
	
	/**
	 * Sets the product's deactivated flag value.
	 *
	 * @param boolean $bool
	 */
	public function deactivate($bool){
		$this->_mDeactivated = (boolean)$bool;
	}
	
	/**
	 * Sets the product's properties.
	 *
	 * Must be called only from the database layer corresponding class. The object's status must be set to
	 * Persist::CREATED in the constructor method too.
	 * @param string $barCode
	 * @param string $packaging
	 * @param string $description
	 * @param UnitOfMeasure $um
	 * @param Manufacturer $manufacturer
	 * @param float $price
	 * @param boolean $deactivated
	 * @param array<ProductDetail> $details
	 */
	public function setData($barCode, $packaging, $description, UnitOfMeasure $um, Manufacturer $manufacturer, 
			$price, $deactivated, $details){
		try{
			$this->validateBarCode($barCode);
			$this->validatePackaging($packaging);
			$this->validateDescription($description);
			$this->validateUnitOfMeasure($um);
			$this->validateManufacturer($manufacturer);
			$this->validatePrice($price);
		} catch(Exception $e){
			$et = new Exception('Internal error, calling Product constructor method with bad data! ' .
					$e->getMessage());
			throw $et;
		}
		
		$this->_mBarCode = $barCode;
		$this->_mPackaging = $packaging;
		$this->_mDescription = $description;
		$this->_mUM = $um;
		$this->_mManufacturer = $manufacturer;
		$this->_mPrice = $price;
		$this->_mDeactivated = (boolean)$deactivated;
		$this->_mDetails = $details;
	}
	
	/**
	 * Adds a supplier to the list of suppliers of this product.
	 *
	 * @param ProductDetail $newDetail
	 */
	public function addDetail(ProductDetail $newDetail){
		foreach($this->_mDetails as $detail)
			if($detail === $newDetail && !$detail->isDeleted())
				throw new Exception('Codigo del proveedor ya esta ingresado.');
				
		if(ProductDAM::existsDetail($newDetail))
			throw new Exception('Proveedor del proveedor ya existe.');
			
		$this->_mDetails[] = $newDetail;
	}
	
	
	public function deleteDetail(ProductDetail $purgeDetail){
		$temp_detail = array();
		
		
	}
	
	/**
	 * Returns true if the product has any suppliers. False if not.
	 *
	 * @return boolean
	 */
	private function hasDetails(){
		if(empty($this->_mDetails))
			return false;
			
		$count = count($this->_mDetails);
		$deleted = 0;
		
		foreach($this->_mDetails as $detail)
			if($detail->isDeleted())
				$deleted++;
				
		if($deleted == $count)
			return false;
		else
			return true;
	}
	
	/**
	 * Validates the product's bar code.
	 *
	 * Must not be empty. Otherwise it throws an exception.
	 * @param string $barCode
	 */
	private function validateBarCode($barCode){
		if(empty($barCode))
			throw new Exception('Codigo de barra inv&aacute;lido;');
	}
	
	/**
	 * Validates the product's packaging.
	 *
	 * Must not be empty. Otherwise it throws an exception.
	 * @param string $packaging
	 */
	private function validatePackaging($packaging){
		if(empty($packaging))
			throw new Exception('Presentacion inv&aacute;lida.');
	}
	
	/**
	 * Validates the product's description.
	 *
	 * Must not be empty. Otherwise it throws an exception.
	 * @param string $description
	 */
	private function validateDescription($description){
		if(empty($description))
			throw new Exception('Descripcion inv&aacute;lida.');
	}
	
	/**
	 * Validates the product's unit of measure.
	 *
	 * The object's status property must be set to other than Persist::IN_PROGRESS. Otherwise it throws an
	 * exception.
	 * @param UnitOfMeasure $um
	 */
	private function validateUnitOfMeasure(UnitOfMeasure $um){
		if($um->getStatus() == Persist::IN_PROGRESS)
			throw new Exception('Persist::IN_PROGRESS UnitOfMeasure provided.');
	}
	
	/**
	 * Validates the product's manufacturer.
	 *
	 * The object's status property must be set to other than Persist::IN_PROGRESS. Otherwise it throws an
	 * exception.
	 * @param Manufacturer $obj
	 */
	private function validateManufacturer(Manufacturer $obj){
		if($obj->getStatus() == Persist::IN_PROGRESS)
			throw new Exception('Persist::IN_PROGRESS Manufacturer provided.');
	}
	
	/**
	 * Validates the product's price.
	 *
	 * Must be greater or equal to cero. Otherwise it throws an exception.
	 * @param float $price
	 */
	private function validatePrice($price){
		if(!is_float($price) || $price < 0)
			throw new Exception('Precio inv&accute;lido.');
	}
	
	/**
	 * Validates a detail's id.
	 *
	 * Must not be empty. Otherwise it throws an exception.
	 * @param string $id
	 */
	private function validateDetailId($id){
		if(empty($id))
			throw new Exception('Id inv&aacute;lido.');
	}
	
	/**
	 * Verifies if another product is already using the bar code.
	 *
	 * @param string $barCode
	 */
	private function verifyBarCode($barCode){
		if(ProductDAM::existsBarCode($this, $barCode))
			throw new Exception('Codigo de barra ya esta siendo utilizado.');
	}
}
?>