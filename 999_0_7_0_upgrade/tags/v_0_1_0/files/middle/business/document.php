<?php
/**
 * Library defining all the necessary classes regarding documents.
 * @package Document
 * @author Roberto Oliveros
 */

/**
 * For persistence needs.
 */
require_once('business/persist.php');
/**
 * For validating purposes.
 */
require_once('business/validator.php');
/**
 * For paging purposes.
 */
require_once('business/itemized.php');
/**
 * For accessing the database.
 */
require_once('data/document_dam.php');

/**
 * Defines common functionality for all the document derived classes.
 * @package Document
 * @author Roberto Oliveros
 */
abstract class Document extends PersistDocument implements Itemized{
	/**
	 * Holds the date in which the document was created.
	 *
	 * Date and time format: 'dd/mm/yyyy hh:mm:ss'.
	 * @var string
	 */
	protected $_mDateTime;
	
	/**
	 * Array containing all the document details.
	 *
	 * @var array<DocumentDetail>
	 */
	protected $_mDetails = array();
	
	/**
	 * Holds the documents grand total.
	 *
	 * @var float
	 */
	private $_mTotal = 0.00;
	
	/**
	 * Holds the user who created the document.
	 *
	 * @var UserAccount
	 */
	private $_mUser;
	
	/**
	 * Constructs the document with the provided data.
	 *
	 * Arguments must be passed only when called from the database layer correponding class.
	 * @param string $dateTime
	 * @param UserAccount $user
	 * @param integer $id
	 * @param integer $status
	 * @throws Exception
	 */
	public function __construct($dateTime = NULL, UserAccount $user = NULL, $id = NULL,
			$status = PersistDocument::IN_PROGRESS){
		parent::__construct($id, $status);
		
		if(!is_null($dateTime)){
			try{
				Date::validateDateTime($dateTime, 'Fecha y hora inv&aacute;lida.');
			} catch(Exception $e){
				$et = new Exception('Interno: Llamando al metodo construct en Document con datos erroneos! ' .
						$e->getMessage());
				throw $et;
			}
			$this->_mDateTime = $dateTime;
		}
		
		if(!is_null($user)){
			$this->_mUser = $user;
		}
		else{
			$helper = ActiveSession::getHelper();
			$this->_mUser = $helper->getUser();
		}
	}
	
	/**
	 * Returns the document's creation date and time.
	 *
	 * @return string
	 */
	public function getDateTime(){
		return $this->_mDateTime;
	}
	
	/**
	 * Returns the document's grand total.
	 *
	 * @return float
	 */
	public function getTotal(){
		return $this->_mTotal;
	}
	
	/**
	 * Returns the document's user.
	 *
	 * @return UserAccount
	 */
	public function getUser(){
		return $this->_mUser;
	}
	
	/**
	 * Returns a document's detail which id match with the provided id.
	 *
	 * If there is no match NULL is returned.
	 * @param string $id
	 * @return DocumentDetail
	 */
	public function getDetail($id){
		String::validateString($id, 'Id del detalle inv&aacute;lido.');
		
		foreach($this->_mDetails as &$detail)
			if($detail->getId() == $id)
				return $detail;
				
		return NULL;
	}
	
	/**
	 * Returns all the document's details.
	 *
	 * @return array<DocumentDetail>
	 */
	public function getDetails(){
		return $this->_mDetails;
	}
	
	/**
	 * Sets the document's properties.
	 *
	 * Must be called only from the database layer corresponding class. The object's status must be set to
	 * PersistDocument::CREATED in the constructor method too.
	 * @param float $total
	 * @param array<DocumentDetail> $details
	 * @throws Exception
	 */
	public function setData($total, $details){
		try{
			Number::validatePositiveNumber($total, 'Total inv&aacute;lido.');
			if(empty($details))
				throw new Exception('No hay ningun detalle.');
		} catch(Exception $e){
			$et = new Exception('Interno: Llamando al metodo setData en Document con datos erroneos! ' .
					$e->getMessage());
			throw $et;
		}
		
		$this->_mTotal = $total;
		$this->_mDetails = $details;
	}
	
	/**
	 * Adds a detail to the document.
	 *
	 * If a similar detail is already in the document, its quantity property will be increase. NOTE: If a
	 * DocBonusDetail is duplicated (or more) the document's total will be affected while the detail's total
	 * will not, be careful! Use the Sale class for adding bonus to a document. Sorry.
	 * @param DocumentDetail $newDetail
	 */
	public function addDetail(DocumentDetail $newDetail){
		$this->_mTotal += $newDetail->getTotal();
		
		// For moving the modified detail to the last place.
		$temp_details = array();
		foreach($this->_mDetails as &$detail){
			if($detail->getId() != $newDetail->getId())
				$temp_details[] = $detail;
			else{
				$detail->increase($newDetail->getQuantity());
				
				// Must increase the reserve, if there is one...
				if($detail instanceof DocProductDetail){
					$reserve = $detail->getReserve();
					if(!is_null($reserve)){
						$new_reserve = $newDetail->getReserve();
						if(!is_null($new_reserve))
							$reserve->merge($new_reserve);
					}
				}
				
				$newDetail = $detail;
			}
		}
		
		$temp_details[] = $newDetail;
		$this->_mDetails = $temp_details;
	}
	
	/**
	 * Removes the detail from the document.
	 *
	 * @param DocumentDetail $purgeDetail
	 */
	public function deleteDetail(DocumentDetail $purgeDetail){
		$temp_details = array();
		
		foreach($this->_mDetails as &$detail)
			if($detail->getId() != $purgeDetail->getId())
				$temp_details[] = $detail;
			else
				$this->_mTotal -= $purgeDetail->getTotal();
				
		$this->_mDetails = $temp_details;
	}
	
	/**
	 * Saves the document's data in the database.
	 *
	 * Only applies if the document's status property has the PersistDocument::IN_PROGRESS value. Returns
	 * the new created id from the database on success.
	 * @return integer
	 */
	public function save(){
		if($this->_mStatus == PersistDocument::IN_PROGRESS){
			$this->validateMainProperties();
			
			$this->_mDateTime = date('d/m/Y H:i:s');
			$this->insert();
			
			$this->_mStatus = PersistDocument::CREATED;
			
			$i = 1;
			foreach($this->_mDetails as &$detail)
				$detail->save($this, $i++);
				
			return $this->_mId;
		}
	}
	
	/**
	 * Cancels the document and reverts its effects.
	 *
	 * The user argument registers who authorized the action. Only applies if the document status property is
	 * set to PersistDocument::CREATED.
	 * @param UserAccount $user
	 * @@param string $reason
	 */
	public function cancel(UserAccount $user, $reason = NULL){
		if($this->_mStatus == PersistDocument::CREATED){
			$this->cancelDetails();
			$this->updateToCancelled($user);
			$this->_mStatus = PersistDocument::CANCELLED;
		}
	}
	
	
	/**
	 * Returns a document with the details.
	 *
	 * @param integer $id
	 * @return Document
	 */
	abstract static public function getInstance($id);
	
	/**
	 * Validates the document's main properties.
	 *
	 * The details property must not be empty.
	 * @throws Exception
	 */
	protected function validateMainProperties(){
		if(empty($this->_mDetails))
			throw new ValidateException('No hay ningun detalle.', 'bar_code');
	}
	
	/**
	 * Cancels the document's details and reverts its effects.
	 *
	 * @throws Exception
	 */
	protected function cancelDetails(){
		foreach($this->_mDetails as $detail)
			if(!$detail->isCancellable())
				throw new Exception('Lotes en este documento ya fueron alterados, no se puede anular.');
				
		foreach($this->_mDetails as &$detail)
			$detail->cancel();
	}
	
	/**
	 * Method for calling database layer.
	 * @param UserAccount $user
	 * @param string $reason
	 */
	abstract protected function updateToCancelled(UserAccount $user, $reason = NULL);
}


/**
 * Represents a detail in a document.
 * @package Document
 * @author Roberto Oliveros
 */
abstract class DocumentDetail{
	/**
	 * Holds the detail's quantity.
	 *
	 * @var integer
	 */
	protected $_mQuantity;
	
	/**
	 * Holds the detail's item price.
	 *
	 * @var float
	 */
	protected $_mPrice;
	
	/**
	 * Constructs the deatail with the provided quantity and price.
	 *
	 * @param integer $quantity
	 * @param float $price
	 */
	public function __construct($quantity, $price){
		Number::validatePositiveNumber($quantity, 'Cantidad inv&aacute;lida.');
		Number::validateNumber($price, 'Precio inv&aacute;lido.');
		
		$this->_mQuantity = round($quantity);
		$this->_mPrice = round($price, 2);
	}
	
	/**
	 * Returns the detail's quantity.
	 *
	 * @return integer
	 */
	public function getQuantity(){
		return $this->_mQuantity;
	}
	
	/**
	 * Returns the detail's item price.
	 *
	 * @return float
	 */
	public function getPrice(){
		return $this->_mPrice;
	}
	
	/**
	 * Returns the detail's grand total.
	 *
	 * Returns the detail's quantity * price.
	 * @return float
	 */
	public function getTotal(){
		return $this->_mQuantity * $this->_mPrice;
	}
	
	/**
	 * Saves the detail's data in the database.
	 *
	 * The document is where this detail belongs to. The number parameter is necessary to keep the order in
	 * which all the details where created.
	 * @param Document $doc
	 * @param integer $number
	 */
	public function save(Document $doc, $number){
		Number::validatePositiveInteger($number, 'N&uacute;mero de pagina inv&aacute;lido.');
		$this->insert($doc, $number);
	}
	
	/**
	 * Returns the detail's id.
	 * @return string
	 */
	abstract public function getId();
	
	/**
	 * Shows the detail's data for displaying.
	 *
	 * Returns an array with the detail's data.
	 * @return array
	 */
	abstract public function show();
	
	/**
	 * Increase the detail's quantity.
	 *
	 * @param integer $quantity
	 */
	abstract public function increase($quantity);
	
	/**
	 * Undoes every action previously taken.
	 *
	 */
	abstract public function cancel();
	
	/**
	 * Returns true if the detail can be cancelled.
	 * @return boolean
	 */
	abstract public function isCancellable();
	
	/**
	 * Inserts the detail's data in the database.
	 *
	 * @param Document $doc
	 * @param integer $number
	 */
	abstract protected function insert(Document $doc, $number);
}


/**
 * Represents a document detail with a bonus.
 * @package Document
 * @author Roberto Oliveros
 */
class DocBonusDetail extends DocumentDetail{
	/**
	 * Holds the detail's bonus.
	 *
	 * @var Bonus
	 */
	private $_mBonus;
	
	/**
	 * Constructs the detail with the provided data.
	 *
	 * @param Bonus $bonus
	 * @param float $price
	 */
	public function __construct(Bonus $bonus, $price){
		parent::__construct(1, $price);
		
		$this->_mBonus = $bonus;
	}
	
	/**
	 * Returns the id of the detail.
	 *
	 * @return string
	 */
	public function getId(){
		return 'bon' . $this->_mBonus->getProduct()->getId();
	}
	
	/**
	 * Returns the detail's bonus.
	 *
	 * @return Bonus
	 */
	public function getBonus(){
		return $this->_mBonus;
	}
	
	/**
	 * Returns an array with the detail's data.
	 *
	 * The array contains the fields id, product, quantity, price, total. All the others fields
	 * are blank.
	 * @return array
	 */
	public function show(){
		$product = $this->_mBonus->getProduct();
		
		return array('id' => $this->getId(), 'bar_code' => '', 'manufacturer' => '',
				'product' => $product->getName(), 'um' => '',
				'quantity' => $this->_mQuantity, 'price' => $this->_mPrice,
				'total' => $this->getTotal(), 'expiration_date' => '', 'is_bonus' => '1',
				'percentage' => $this->_mBonus->getPercentage());
	}
	
	/**
	 * Does nothing, just to fulfill the abstraction.
	 *
	 * @param integer $quantity
	 */
	public function increase($quantity){
		// Do nothing.
	}
	
	/**
	 * Does nothing, just to fulfill the abstraction.
	 *
	 */
	public function cancel(){
		// Do nothing.
	}
	
	/**
	 * Returns true. Always can be cancellable.
	 *
	 * @return boolean
	 */
	public function isCancellable(){
		return true;
	}
	
	/**
	 * Inserts the detail's data in the database.
	 *
	 * The document is where this detail belongs to. The number parameter is necessary to keep the order in
	 * which all the details where created.
	 * @param Document $doc
	 * @param integer $number
	 */
	protected function insert(Document $doc, $number){
		DocBonusDetailDAM::insert($this, $doc, $number);
	}
}


/**
 * Represents a detail containing a product in document.
 * @package Document
 * @author Roberto Oliveros
 */
class DocProductDetail extends DocumentDetail{
	/**
	 * Holds the detail's lot.
	 *
	 * @var Lot
	 */
	private $_mLot;
	
	/**
	 * Holds the detail's transaction.
	 *
	 * @var Transaction
	 */
	private $_mTransaction;
	
	/**
	 * The reserve of the detail's product.
	 *
	 * @var Reserve
	 */
	private $_mReserve;
	
	/**
	 * Constructs the detail with the provided data.
	 *
	 * Warning! if the method is not called from the database layer take note of the following instructions
	 * please: Note that if the transaction is an instance of Entry class the detail must only receive a
	 * Persist::IN_PROGRESS Lot. If it is an instance of Withdraw class, it needs a Reserve
	 * to work. Sorry.
	 * @param Lot $lot
	 * @param Transaction $transaction
	 * @param Reserve $reserve
	 * @param integer $quantity
	 * @param float $price
	 */
	public function __construct(Lot $lot, Transaction $transaction, $quantity, $price, Reserve $reserve = NULL){
		parent::__construct($quantity, $price);
			
		$this->_mLot = $lot;
		$this->_mTransaction = $transaction;
		$this->_mReserve = $reserve;
	}
	
	/**
	 * Returns the detail's id.
	 *
	 * @return string
	 */
	public function getId(){
		$lot = $this->_mLot;
		// For new lots that has their id equal to cero must be distinguish.
		$lot_id = (!$lot->getId()) ? str_replace('/', '', $lot->getExpirationDate()) : $lot->getId();
		
		/**
		 * @TODO Check if this is OK.
		 * For distinguish new lots with same product same expiration date but with different price.
		 */
		if($this->_mTransaction instanceof Entry)
			$lot_id = $lot_id . number_format($this->_mPrice, 2);
		
		return $lot->getProduct()->getId() . $lot_id; 
	}
	
	/**
	 * Returns the detail's lot.
	 *
	 * @return Lot
	 */
	public function getLot(){
		return $this->_mLot;
	}
	
	/**
	 * Returns the detail product's reserve.
	 *
	 * @return Reserve
	 */
	public function getReserve(){
		return $this->_mReserve;
	}
	
	/**
	 * Returns an array with the detail's data.
	 *
	 * The array contains the fields id, bar_code, manufacturer, product, unit of measure,
	 * quantity, price, total, expiration_date.
	 * @return array
	 */
	public function show(){
		$product = $this->_mLot->getProduct();
		$manufacturer = $product->getManufacturer();
		$um = $product->getUnitOfMeasure();
		$expiration_date =
				(is_null($this->_mLot->getExpirationDate())) ? 'N/A' : $this->_mLot->getExpirationDate();

		return array('id' => $this->getId(), 'bar_code' => $product->getBarCode(),
				'manufacturer' => $manufacturer->getName(), 'product' => $product->getName(),
				'um' => $um->getName(), 'quantity' => $this->_mQuantity,
				'price' => $this->_mPrice, 'total' => $this->getTotal(),
				'expiration_date' => $expiration_date);
	}
	
	/**
	 * Increases the detail's quantity property.
	 *
	 * @param integer $quantity
	 */
	public function increase($quantity){
		Number::validatePositiveNumber($quantity, 'Cantidad inv&aacute;lida.');
		$this->_mQuantity += $quantity;
		if($this->_mTransaction instanceof Entry)
			$this->_mLot->increase($quantity);
	}
	
	/**
	 * Cancels the details action.
	 *
	 * Cancels the detail's transaction previous taken action.
	 */
	public function cancel(){
		$this->_mTransaction->cancel($this);
	}
	
	/**
	 * Returns true if the details previous action can be cancel.
	 *
	 * @return boolean
	 */
	public function isCancellable(){
		return $this->_mTransaction->isCancellable($this);
	}
	
	/**
	 * Inserts the detail's data into the database.
	 *
	 * The document is where this detail belongs to. The number parameter is necessary to keep the order in
	 * which all the details where created.
	 * @param Document $doc
	 * @param integer $number
	 */
	protected function insert(Document $doc, $number){
		// Perform the inventory transaction.
		$this->_mTransaction->apply($this);
		DocProductDetailDAM::insert($this, $doc, $number);
	}
}


/**
 * Represents a product reserve in the inventory.
 * @package Document
 * @author Roberto Oliveros
 */
class Reserve extends Persist{
	/**
	 * Holds the internal id.
	 *
	 * @var integer
	 */
	private $_mId;
	
	/**
	 * Holds the reserve's lot.
	 *
	 * @var Lot
	 */
	private $_mLot;
	
	/**
	 * Holds the reserved quantity.
	 *
	 * @var integer
	 */
	private $_mQuantity;
	
	/**
	 * Holds the user who created the reserve.
	 *
	 * @var UserAccount
	 */
	private $_mUser;
	
	/**
	 * Holds the date and time in which the reserve was created.
	 *
	 * Date and time format: 'dd/mm/yyyy hh:mm:ss'.
	 * @var string
	 */
	private $_mDateTime;
	
	/**
	 * Constructs the reserve with the provided data.
	 *
	 * Must be called only from the database layer corresponding class. Use create method instead if
	 * a new reserve is needed. Lack of experience, sorry.
	 * @param integer $id
	 * @param Lot $lot
	 * @param integer $quantity
	 * @param UserAccount $user
	 * @param string $date
	 * @throws Exception
	 */
	public function __construct($id, Lot $lot, $quantity, UserAccount $user, $dateTime,
			$status = Persist::IN_PROGRESS){
		parent::__construct($status);
				
		try{
			Number::validatePositiveInteger($id, 'Id inv&aacute;lido.');
			Number::validatePositiveNumber($quantity, 'Cantidad inv&aacute;lida.');
			Date::validateDateTime($dateTime, 'Fecha y hora inv&aacute;lida.');
		} catch(Exception $e){
			$et = new Exception('Interno: Llamando al metodo construct en Reserve con datos erroneos! ' .
					$e->getMessage());
			throw $et;
		}
		
		$this->_mId = $id;
		$this->_mLot = $lot;
		$this->_mQuantity = $quantity;
		$this->_mUser = $user;
		$this->_mDateTime = $dateTime;
	}
	
	/**
	 * Returns the reserve's id.
	 *
	 * @return integer
	 */
	public function getId(){
		return $this->_mId;
	}
	
	/**
	 * Returns the reserve's lot.
	 *
	 * @return Lot
	 */
	public function getLot(){
		return $this->_mLot;
	}
	
	/**
	 * Returns the reserve's quantity.
	 *
	 * @return integer
	 */
	public function getQuantity(){
		return $this->_mQuantity;
	}
	
	/**
	 * Merge the provided reserve's quantity to this object quantity.
	 *
	 * Took the provided reserve's quantity and adds it to this reserve's quantity property. Then it deletes
	 * the the provided reserve from database.
	 * @param Reserve $obj
	 */
	public function merge(Reserve $obj){
		$this->_mQuantity += $obj->getQuantity();
		ReserveDAM::increase($this, $obj->getQuantity());
		ReserveDAM::delete($obj);
	}
	
	/**
	 * Creates and reserve the provided quantity from the product in the database.
	 *
	 * Creates a reserve in the database and returns the instance of it.
	 * @param Lot $lot
	 * @param integer $quantity
	 * @return Reserve
	 */
	static public function create(Lot $lot, $quantity){
		Number::validatePositiveNumber($quantity, 'Cantidad inv&aacute;lida.');
		
		$lot->reserve($quantity);
		$product = $lot->getProduct();
		Inventory::reserve($product, $quantity);
		
		$helper = ActiveSession::getHelper();
		return ReserveDAM::insert($lot, $quantity, $helper->getUser(), date('d/m/Y H:i:s'));
	}
	
	/**
	 * Returns an instance of a reserve with database data.
	 *
	 * Returns NULL if there was no match of the provided id in the database.
	 * @param integer $id
	 * @return Reserve
	 */
	static public function getInstance($id){
		Number::validatePositiveInteger($id, 'Id inv&aacute;lido.');
		return ReserveDAM::getInstance($id);
	}
	
	/**
	 * Deletes the reserve from the database.
	 *
	 * @param Reserve $obj
	 */
	static public function delete(Reserve $obj){
		$quantity = $obj->getQuantity();
		$lot = $obj->getLot();
		$lot->decreaseReserve($quantity);
		$product = $lot->getProduct();
		Inventory::decreaseReserve($product, $quantity);
		
		ReserveDAM::delete($obj);
	}
}


/**
 * Represents the correlative numbers invoices use to operate.
 * @package Document
 * @author Roberto Oliveros
 */
class Correlative extends Persist{
	/**
	 * Status type.
	 * 
	 * Indicates that the correlative was never used before its expiration date.
	 */
	const EXPIRED = 2;
	
	/**
	 * Status type.
	 * 
	 * Indicates that this correlative is the current one in use.
	 */
	const CURRENT = 3;
	
	/**
	 * Status type.
	 * 
	 * Indicates that all the correlative's numbers have been used.
	 */
	const USED_UP = 4;
	
	/**
	 * Holds the correlative's id.
	 * @var integer
	 */
	private $_mId;
	
	/**
	 * Holds the serial number of the correlative.
	 *
	 * @var string
	 */
	private $_mSerialNumber;
	
	/**
	 * Holds the correlative's resolution number.
	 *
	 * @var string
	 */
	private $_mResolutionNumber;
	
	/**
	 * Holds the date of the correlative's resolution.
	 *
	 * Date format: 'dd/mm/yyyy'.
	 * @var string
	 */
	private $_mResolutionDate;
	
	/**
	 * Holds the entry date of the correlative.
	 *
	 * Date format: 'dd/mm/yyyy'.
	 * @var string
	 */
	private $_mCreatedDate;
	
	/**
	 * Holds the correlative's regime policy.
	 *
	 * @var string
	 */
	private $_mRegime;
	
	/**
	 * Holds the first of the correlative's range of numbers.
	 *
	 * @var integer
	 */
	private $_mInitialNumber = 0;
	
	/**
	 * Holds the last of the correlative's range of numbers.
	 *
	 * @var integer
	 */
	private $_mFinalNumber = 0;
	
	/**
	 * Holds the current of the correlative's range of numbers.
	 *
	 * @var integer
	 */
	private $_mCurrentNumber;
	
	/**
	 * Construct the correlative with the provided data.
	 * 
	 * Parameters must be set only if called from the database layer.
	 * @param integer $id
	 * @param string $serialNumber
	 * @param integer $currentNumber
	 * @param integer $status
	 * @throws Exception
	 */
	public function __construct($id = NULL, $serialNumber = NULL, $currentNumber = 0, $status = Correlative::IN_PROGRESS){
		parent::__construct($status);
		
		if(!is_null($id))
			try{
				Number::validatePositiveNumber($id, 'Id inv&aacute;lido.');
			} catch(Exception $e){
				$et = new Exception('Interno: Llamando al metodo construct en Correlative con datos ' .
						'erroneos! ' . $e->getMessage());
				throw $et;
			}
			
		if(!is_null($serialNumber))
			try{
				String::validateString($serialNumber, 'N&uacute;mero de serie inv&aacute;lido.');
			} catch(Exception $e){
				$et = new Exception('Interno: Llamando al metodo construct en Correlative con datos ' .
						'erroneos! ' . $e->getMessage());
				throw $et;
			}
			
		if($currentNumber !== 0)
			try{
				Number::validatePositiveInteger($currentNumber, 'N&uacute;mero actual inv&aacute;lido.');
			} catch(Exception $e){
				$et = new Exception('Interno: Llamando al metodo construct en Correlative con datos ' .
						'erroneos! ' . $e->getMessage());
				throw $et;
			}
			
		$this->_mId = $id;
		$this->_mSerialNumber = $serialNumber;
		$this->_mCurrentNumber = $currentNumber;
	}
	
	/**
	 * Returns the correlative's id.
	 * 
	 * @return integer
	 */
	public function getId(){
		return $this->_mId;
	}
	
	/**
	 * Returns the correlative's serial number.
	 *
	 * @return string
	 */
	public function getSerialNumber(){
		return $this->_mSerialNumber;
	}
	
	/**
	 * Returns the correlative's resolution number.
	 *
	 * @return string
	 */
	public function getResolutionNumber(){
		return $this->_mResolutionNumber;
	}
	
	/**
	 * Returns the correlative's resolution date.
	 *
	 * @return string
	 */
	public function getResolutionDate(){
		return $this->_mResolutionDate;
	}
	
	/**
	 * Returns the correlative's entry date.
	 *
	 * @return string
	 */
	public function getCreatedDate(){
		return $this->_mCreatedDate;
	}
	
	/**
	 * Returns the correlative's regime policy.
	 *
	 * @return string
	 */
	public function getRegime(){
		return $this->_mRegime;
	}
	
	/**
	 * Returns the first of the correlative's range of numbers.
	 *
	 * @return integer
	 */
	public function getInitialNumber(){
		return $this->_mInitialNumber;
	}
	
	/**
	 * Returns the last of the correlative's range of numbers.
	 *
	 * @return integer
	 */
	public function getFinalNumber(){
		return $this->_mFinalNumber;
	}
	
	/**
	 * Returns the current of the correlative's range of numbers.
	 *
	 * @return integer
	 */
	public function getCurrentNumber(){
		return $this->_mCurrentNumber;
	}
	
	/**
	 * Returns the next to be used in the correlative's range of numbers.
	 *
	 * Only applies when the object's status property is set to Correlative::CREATED
	 * or Correlative::CURRENT.
	 * @return integer
	 */
	public function getNextNumber(){
		if($this->_mStatus == Correlative::CREATED || $this->_mStatus == Correlative::CURRENT){
			$number = CorrelativeDAM::getNextNumber($this);
			
			if($this->_mStatus == Correlative::CREATED){
				CorrelativeDAM::updateStatus($this, Correlative::CURRENT);
				ResolutionLog::write($this);
				$this->_mStatus = Correlative::CURRENT;
			}
			
			return $number; 
		}
		else
			return 0;
	}
	
	/**
	 * Sets the correlative's serial number.
	 *
	 * Only applies if the object's status property is set to Persist::IN_PROGRESS.
	 * @param string $serialNumber
	 */
	public function setSerialNumber($serialNumber){
		if($this->_mStatus == Persist::IN_PROGRESS){
			$this->_mSerialNumber = strtoupper($serialNumber);
			String::validateString($serialNumber, 'N&uacute;mero de serie inv&aacute;lido.');
			$this->_mInitialNumber = $this->getSerialFinalNumber($this->_mSerialNumber) + 1;
		}
	}
	
	/**
	 * Sets the correlative's resolution number
	 *
	 * @param string $number
	 */
	public function setResolutionNumber($number){
		$this->_mResolutionNumber = $number;
		String::validateString($number, 'N&uacute;mero de resoluci&oacute;n inv&aacute;lido.');
		$this->verifyResolutionNumber($number);
	}
	
	/**
	 * Sets the correlative's resolution date.
	 *
	 * @param string $date
	 */
	public function setResolutionDate($date){
		$this->_mResolutionDate = $date;
		Date::validateDate($date, 'Fecha de resoluci&oacute;n inv&aacute;lida.');
		$this->verifyResolutionDate($date);
	}
	
	/**
	 * Sets the correlative's regime policy.
	 *
	 * @param string $regime
	 */
	public function setRegime($regime){
		$this->_mRegime = $regime;
		String::validateString($regime, 'R&eacute;gimen inv&aacute;lido.');
	}
	
	/**
	 * Sets the last of the correlative's range of numbers.
	 *
	 * @param integer $number
	 */
	public function setFinalNumber($number){
		$this->_mFinalNumber = $number;
		Number::validatePositiveNumber($number, 'N&uacute;mero final inv&aacute;lido.');
	}
	
	/**
	 * Set the object's properties.
	 * 
	 * Must be call only from the database layer corresponding class. The object's status must be set to
	 * Persist::CREATED in the constructor method too.
	 * @param string $resolutionNumber
	 * @param string $resolutionDate
	 * @param string $createdDate
	 * @param integer $initialNumber
	 * @param integer $finalNumber
	 * @throws Exception
	 */
	public function setData($resolutionNumber, $resolutionDate, $createdDate, $regime, $initialNumber, $finalNumber){
		try{
			String::validateString($resolutionNumber, 'N&uacute;mero de resoluci&oacute;n inv&aacute;lido.');
			Date::validateDate($resolutionDate, 'Fecha de resoluci&oacute;n inv&aacute;lida.');
			Date::validateDate($createdDate, 'Fecha de ingreso inv&aacute;lida.');
			String::validateString($regime, 'R&eacute;gimen inv&aacute;lido.');
			Number::validatePositiveInteger($initialNumber, 'N&uacute;mero inicial inv&aacute;lido.');
			Number::validatePositiveInteger($finalNumber, 'N&uacute;mero final inv&aacute;lido.');
		} catch(Exception $e){
			$et = new Exception('Interno: Llamando al metodo setData en Correlative con datos erroneos! ' .
					$e->getMessage());
			throw $et;
		}
		
		$this->_mResolutionNumber = $resolutionNumber;
		$this->_mResolutionDate = $resolutionDate;
		$this->_mCreatedDate = $createdDate;
		$this->_mRegime = $regime;
		$this->_mInitialNumber = $initialNumber;
		$this->_mFinalNumber = $finalNumber;
	}
	
	/**
	 * Saves the correlative's data in the database.
	 *
	 * Only applies if the object's status property is set to Persist::IN_PROGRESS.
	 * @return string
	 */
	public function save(){
		if($this->_mStatus == Persist::IN_PROGRESS){
			$this->validateMainProperties();
			
			$this->verifyResolutionNumber($this->_mResolutionNumber);
			$this->verifySerialNumber($this->_mSerialNumber,
					$this->_mInitialNumber, $this->_mFinalNumber);
			
			$this->_mCreatedDate = date('d/m/Y');
				
			$this->_mId = CorrelativeDAM::insert($this);
			$this->_mStatus = Correlative::CREATED;
			
			// For presentation purposes.
			return $this->_mId;
		}
	}
	
	/**
	 * Returns an instance of a correlative with database data.
	 *
	 * Returns NULL if there was no match for the provided id in the database.
	 * @param integer $id
	 * @return Correlative
	 */
	static public function getInstance($id){
		$correlative = CorrelativeDAM::getInstance($id);
		
		if($correlative->getStatus() == Correlative::CREATED)
			if($correlative->isExpired($correlative->getResolutionDate())){
				CorrelativeDAM::updateStatus($correlative, Correlative::EXPIRED);
				$correlative->_mStatus = Correlative::EXPIRED;
			}
		
		if($correlative->getStatus() == Correlative::CURRENT)
			if($correlative->getFinalNumber() == $correlative->getCurrentNumber()){
				CorrelativeDAM::updateStatus($correlative, Correlative::USED_UP);
				$correlative->_mStatus = Correlative::USED_UP;
			}
		
		return $correlative;
	}
	
	/**
	 * Returns the id of the current correlative.
	 *
	 * @return integer
	 */
	static public function getCurrentCorrelativeId(){
		return CorrelativeDAM::getCurrentCorrelativeId();
	}
	
	/**
	 * Deletes the correlative from the database.
	 *
	 * Throws an exception due dependencies..
	 * @param Correlative $obj
	 * @return boolean
	 * @throws Exception
	 */
	static public function delete(Correlative $obj){
		if(!CorrelativeDAM::delete($obj))
			throw new Exception('Correlativo tiene dependencias (facturas) y no se puede eliminar.');
	}
	
	/**
	 * Returns a new correlative object.
	 * 
	 * Throws an exception if there is no more room for creating another correlative.
	 * @return Correlative
	 * @throws Exception
	 */
	static public function create(){
		if(CorrelativeDAM::isQueueEmpty())
			return new Correlative();
		else
			throw new Exception('No es posible crear otro correlativo, ya existe uno (inactivo) pendiente de uso.');
	}
	
	/**
	 * Validates the correlative main properties.
	 *
	 * Serial and resolution numbers must not be empty. Resolution date must be a valid date. And initial
	 * and final numbers must be greater than cero.
	 */
	private function validateMainProperties(){
		String::validateString($this->_mSerialNumber, 'N&uacute;mero de serie inv&aacute;lido.',
				'serial_number');
		String::validateString($this->_mResolutionNumber,
				'N&uacute;mero de resoluci&oacute;n inv&aacute;lido.', 'resolution_number');
		Date::validateDate($this->_mResolutionDate, 'Fecha de resoluci&oacute;n inv&aacute;lida.',
				'resolution_date');
		$this->verifyResolutionDate($this->_mResolutionDate);
		String::validateString($this->_mRegime, 'R&eacute;gimen inv&aacute;lido.', 'regime');
		Number::validatePositiveNumber($this->_mInitialNumber, 'N&uacute;mero inicial inv&aacute;lido.',
				'initial_number');
		Number::validatePositiveNumber($this->_mFinalNumber, 'N&uacute;mero final inv&aacute;lido.',
				'final_number');
		$this->validateRangeNumbers($this->_mInitialNumber, $this->_mFinalNumber);
	}
	
	/**
	 * Validates if the final number is greater than the initial.
	 *
	 * @param integer $initial
	 * @param integer $final
	 * @throws Exception
	 */
	private function validateRangeNumbers($initial, $final){
		if($initial >= $final)
			throw new ValidateException('N&uacute;mero inicial debe ser menor al n&uacute;mero final.',
					'initial_number');
	}
	
	/**
	 * Verifies if a correlative with the serial number already exists in the database.
	 *
	 * Throws an exception if it does.
	 * @param string $serialNumber
	 * @param integer $initialNumber
	 * @param integer $finalNumber
	 * @throws Exception
	 */
	private function verifySerialNumber($serialNumber, $initialNumber, $finalNumber){
		if(CorrelativeDAM::exists($serialNumber, $initialNumber, $finalNumber))
			throw new ValidateException('N&uacute;mero de serie con ese correlativo ya existe o se traslapa.',
					'serial_number');
	}
	
	/**
	 * Fetches the serial number final number.
	 *
	 * If exists, it returns the final number of the last serial number created. If does not
	 * exists it returns cero.
	 * @param string $serialNumber
	 * @return integer
	 */
	private function getSerialFinalNumber($serialNumber){
		return CorrelativeDAM::getSerialFinalNumber($serialNumber);
	}
	
	/**
	 * Verifies if the resolution number already exists in the database.
	 * 
	 * Throws an exception if it does.
	 * @param string $resolutionNumber
	 * @throws Exception
	 */
	private function verifyResolutionNumber($resultionNumber){
		if(CorrelativeDAM::existsResolutionNumber($resultionNumber))
			throw new ValidateException('N&uacute;mero de resoluci&oacute;n ya existe.', 'resolution_number');
	}
	
	/**
	 * Throws an exception if the date provided has expired according to SAT norms.
	 * 
	 * @param string $date
	 * @throws ValidateException
	 */
	private function verifyResolutionDate($date){
		if(self::isExpired($date))
			throw new ValidateException('Los '. CORRELATIVE_VALID_DAYS . ' dias de vigencia para registrar correlativo ya caducaron.', 'resolution_date');
	}
	
	/**
	 * Checks if the date provided has not passed the 10 days availble according to SAT norms.
	 * 
	 * @param string $date
	 * @return boolean
	 */
	static private function isExpired($date){
		$dateObj = new DateTime(Date::dbFormat($date));
		$dateObj->modify('+'. CORRELATIVE_VALID_DAYS . ' day');
		
		return Date::compareDates($dateObj->format('d/m/Y'), date('d/m/Y'));
	}
}


/**
 * V.A.T. Value Added Tax. (I.V.A.)
 * @package Document
 * @author Roberto Oliveros
 */
class Vat{
	/**
	 * Holds the percentage value of the tax.
	 *
	 * @var float
	 */
	private $_mPercentage;
	
	/**
	 * Construct the vat with the provided percentage.
	 *
	 * Use getInstance method if an instance is required. Called this method only from the database layer
	 * corresponding class.
	 * @param float $percentage
	 */
	public function __construct($percentage){
		try{
			Number::validatePositiveFloat($percentage, 'Porcentaje inv&aacute;lido.');
		} catch(Exception $e){
			$et = new Exception('Interno: Llamando al metodo construct en Vat con datos erroneos! ' .
					$e->getMessage());
			throw $et;
		}
		
		$this->_mPercentage = $percentage;
	}
	
	/**
	 * Returns the percentage value of the tax.
	 *
	 * @return float
	 */
	public function getPercentage(){
		return $this->_mPercentage;
	}
	
	/**
	 * Sets the percentage value of the tax.
	 *
	 * @param float $value
	 */
	public function setPercentage($value){
		$this->_mPercentage = $value;
		Number::validateBetweenCeroToNinetyNineNumber($value,
				'Porcentaje inv&aacute;lido.');
	}
	
	/**
	 * Updates the vat values in the database.
	 *
	 */
	public function save(){
		$this->validateMainProperties();
		VatDAM::update($this);
	}
	
	/**
	 * Returns an instance of the V.A.T.
	 *
	 * @return Vat
	 */
	static public function getInstance(){
		return VatDAM::getInstance();
	}
	
	/**
	 * Validates the object's percentage property.
	 * 
	 * Verifies that the V.A.T. percentage is set correctly. Otherwise it throws an exception.
	 */
	private function validateMainProperties(){
		Number::validateBetweenCeroToNinetyNineNumber($this->_mPercentage,
				'Porcentaje inv&aacute;lido.', 'percentage');
	}
}


/**
 * Represents a sales invoice.
 * @package Document
 * @author Roberto Oliveros
 */
class Invoice extends Document{
	/**
	 * Holds the invoice's number.
	 *
	 * @var integer
	 */
	private $_Number;
	
	/**
	 * Holds the invoice's correlative.
	 *
	 * @var Correlative
	 */
	private $_mCorrelative;
	
	/**
	 * Holds the invoice customer's nit.
	 *
	 * @var string
	 */
	private $_mCustomerNit;
	
	/**
	 * Holds the invoice customer's name.
	 *
	 * @var string
	 */
	private $_mCustomerName;
	
	/**
	 * Holds the Vat's percentage value.
	 *
	 * @var float
	 */
	private $_mVatPercentage;
	
	/**
	 * Holds from which cash register this invoice was emitted.
	 *
	 * @var CashRegister
	 */
	private $_mCashRegister;
	
	/**
	 * Holds the invoice's additional discount.
	 *
	 * @var Discount
	 */
	private $_mDiscount;
	
	/**
	 * Holds the reason why the invoice was cancelled.
	 * @var string
	 */
	private $_mCancelledReason;
	
	/**
	 * Constructs the invoice with the provided data.
	 *
	 * Arguments must be passed only when called from the database layer correponding class. If a new Invoice
	 * its been created (PersistDocument::IN_PROGRESS) the cash register must be open, otherwise it doesn't
	 * matter because it is an already created (PersistDocument::CREATED) invoice.
	 * @param CashRegister $cashRegister
	 * @param string $dateTime
	 * @param UserAccount $user
	 * @param integer $id
	 * @param integer $status
	 * @throws Exception
	 */
	public function __construct(CashRegister $cashRegister, $dateTime = NULL, UserAccount $user = NULL,
			$id = NULL, $status = PersistDocument::IN_PROGRESS){
		parent::__construct($dateTime, $user, $id, $status);
		
		if($this->_mStatus == PersistDocument::IN_PROGRESS && !$cashRegister->isOpen())
			throw new Exception('Caja ya esta cerrada.');
				
		$this->_mCashRegister = $cashRegister;
	}
	
	/**
	 * Returns the invoice's number.
	 *
	 * @return integer
	 */
	public function getNumber(){
		return $this->_mNumber;
	}
	
	/**
	 * Returns the invoice's correlative.
	 *
	 * @return Correlative
	 */
	public function getCorrelative(){
		return $this->_mCorrelative;
	}
	
	/**
	 * Returns the invoice customer's nit.
	 *
	 * @return string
	 */
	public function getCustomerNit(){
		return $this->_mCustomerNit;
	}
	
	/**
	 * Returns the invoice customer's name.
	 *
	 * @return string
	 */
	public function getCustomerName(){
		return $this->_mCustomerName;
	}
	
	/**
	 * Returns the invoice vat's percentage.
	 *
	 * @return float
	 */
	public function getVatPercentage(){
		return $this->_mVatPercentage;
	}
	
	/**
	 * Returns the invoice discount's percentage.
	 *
	 * @return float
	 */
	public function getDiscountPercentage(){
		return (is_null($this->_mDiscount)) ? 0.00 : $this->_mDiscount->getPercentage();
	}
	
	/**
	 * Returns the invoice's cash register.
	 *
	 * @return CashRegister
	 */
	public function getCashRegister(){
		return $this->_mCashRegister;
	}
	
	/**
	 * Returns the quantity of certain product in the invoice.
	 *
	 * @param Product $product
	 * @return integer
	 */
	public function getProductQuantity(Product $product){
		$quantity = 0;
		
		foreach($this->_mDetails as $detail)
			if($detail instanceof DocProductDetail)
				if($detail->getLot()->getProduct()->getId() == $product->getId())
					$quantity += $detail->getQuantity();
					
		return $quantity;
	}
	
	/**
	 * Returns the invoice's subtotal.
	 *
	 * @return float
	 */
	public function getSubTotal(){
		return parent::getTotal();
	}
	
	/**
	 * Returns the discount value applied to the invoice subtotal.
	 *
	 * @return float
	 */
	public function getTotalDiscount(){
		/**
		 * @TODO Verify it it the result needs rounding.
		 */
		$discount = (is_null($this->_mDiscount)) ? 0.00 :
				$this->getSubTotal() * ($this->_mDiscount->getPercentage() / 100);
		return $discount;
	}
	
	/**
	 * Returns the document's grand total.
	 *
	 * @return float
	 */
	public function getTotal(){
		return parent::getTotal() - $this->getTotalDiscount();
	}
	
	/**
	 * Returns a document bonus detail of certain product.
	 *
	 * @param Product $product
	 * @return DocBonusDetail
	 */
	public function getBonusDetail(Product $product){
		foreach($this->_mDetails as $detail)
			if($detail instanceof DocBonusDetail){
				$bonus = $detail->getBonus();
				if($bonus->getProduct()->getId() == $product->getId())
					return $detail;
			}
	}
	
	/**
	 * Returns the reason why the invoice was cancelled.
	 * 
	 * Use only inmediately after the cancelation.
	 * @return string
	 */
	public function getCancelledReason(){
		return $this->_mCancelledReason;
	}
	
	/**
	 * Sets the invoice customer's nit and name.
	 *
	 * @param Customer $obj
	 */
	public function setCustomer(Customer $obj){
		$this->_mCustomerNit = $obj->getNit();
		$this->_mCustomerName = $obj->getName();
	}
	
	/**
	 * Sets the invoice's discount.
	 *
	 * @param Discount $obj
	 */
	public function setDiscount(Discount $obj = NULL){
		$this->_mDiscount = $obj;
		
		if(!is_null($obj))
			$obj->setInvoice($this);
	}
	
	/**
	 * Sets the invoice's properties.
	 *
	 * Must be called only from the database layer corresponding class. The object's status must be set to
	 * PersistDocument::CREATED in the constructor method too.
	 * @param integer $number
	 * @param Correlative $correlative
	 * @param string $nit
	 * @param string $name
	 * @param float $vatPercentage
	 * @param Discount $discount
	 * @param float $total
	 * @param array<DocumentDetail> $details
	 * @throws Exception
	 */
	public function setData($number, Correlative $correlative, $nit, $vatPercentage, $total, $details,
			$name = NULL, Discount $discount = NULL){
		parent::setData($total, $details);
		
		try{
			Number::validatePositiveInteger($number, 'N&uacute;mero de factura inv&aacute;lido.');
			Number::validatePositiveFloat($vatPercentage, 'Porcentage Iva inv&aacute;lido.');
		} catch(Exception $e){
			$et = new Exception('Interno: Llamando al metodo setData en Invoice con datos erroneos! ' .
					$e->getMessage());
			throw $et;
		}
		
		$this->_mNumber = $number;
		$this->_mCorrelative = $correlative;
		$this->_mCustomerNit = $nit;
		$this->_mCustomerName = $name;
		$this->_mVatPercentage = $vatPercentage;
		$this->_mDiscount = $discount;
	}
	
	/**
	 * Call the validateMainProperties method.
	 */
	public function validate(){
		$this->validateMainProperties();
	}
	
	/**
	 * Creates and returns a cash receipt object.
	 * 
	 * @return CashReceipt
	 */
	public function createCashReceipt(){
		$correlative_id = Correlative::getCurrentCorrelativeId();
		
		if(is_null($correlative_id))
			throw new Exception('No hay correlativo disponible. Revise que exista alguno o si lo hay, verifique que no haya alcanzado el final de su numeraci&oacute;n.');
			
		$this->_mCorrelative = Correlative::getInstance($correlative_id);
		
		if($this->_mCorrelative->getStatus() == Correlative::EXPIRED)
			throw new Exception('El correlativo disponible ya vencio debido a que nunca se utilizo dentro de los ' . CORRELATIVE_VALID_DAYS . ' dias despues de su autorizaci&oacute;n.');
		
		return new CashReceipt($this);
	}
	
	/**
	 * Saves the document's data in the database.
	 *
	 * Only applies if the document's status property has the PersistDocument::IN_PROGRESS value. Returns
	 * the new created id from the database on success. NOTE: Call only from the corresponding receipt!
	 * @return integer
	 */
	public function save(){
		if($this->_mStatus == PersistDocument::IN_PROGRESS){
			$this->_mVatPercentage = Vat::getInstance()->getPercentage();
			
			if($this->_mCorrelative->getFinalNumber()
					== $this->_mCorrelative->getCurrentNumber())
				throw new Exception('Se alcanzo el final del correlativo, favor de cambiarlo.');
							
			$this->_mNumber = $this->_mCorrelative->getNextNumber();
			
			$this->_mDateTime = date('d/m/Y H:i:s');
			$this->insert();
			$this->_mStatus = PersistDocument::CREATED;
			// Watch out, if any error occurs the database has already been altered!
			$i = 1;
			foreach($this->_mDetails as &$detail)
				$detail->save($this, $i++);
			if(!is_null($this->_mDiscount))
				$this->_mDiscount->save();
				
			InvoiceTransactionLog::write($this->getCorrelative()->getSerialNumber(), $this->getNumber(),
					$this->getDateTime(), $this->getTotal(), InvoiceTransactionLog::CREATED);
				
			return $this->_mId;
		}
	}
	
	/**
	 * Does not save the invoice in the database and reverts its effects.
	 *
	 * Only applies if the object's status property is set to PersistDocument::IN_PROGRESS.
	 */
	public function discard(){
		if($this->_mStatus == Persist::IN_PROGRESS)
			foreach($this->_mDetails as &$detail)
				if($detail instanceof DocProductDetail)
					// RetailEvent isn't called because sales aren't needed anymore.
					WithdrawEvent::cancel($this, $detail);
				else
					$this->deleteDetail($detail);
	}
	
	/**
	 * Cancels the document and reverts its effects.
	 *
	 * The user argument registers who authorized the action. Only applies if the document status property is
	 * set to PersistDocument::CREATED.
	 * @param UserAccount $user
	 * @param string $reason
	 * @throws Exception
	 */
	public function cancel(UserAccount $user, $reason = NULL){
		if($this->_mStatus == PersistDocument::CREATED){
			String::validateString($reason, 'Motivo inv&aacute;lido.', 'reason');
			
			if(!$this->_mCashRegister->isOpen())
				throw new Exception('Caja ya esta cerrada, no se puede anular.');
			
			$this->cancelDetails();
			$receipt = CashReceipt::getInstance($this);
			$receipt->cancel($user);
			$this->updateToCancelled($user, $reason);
			$this->_mStatus = PersistDocument::CANCELLED;
			
			$this->_mCancelledReason = $reason;
		}
	}
	
	/**
	 * Returns an invoice with the details.
	 *
	 * Returns NULL if there was no match for the provided id in the database. 
	 * @param integer $id
	 * @return Invoice
	 */
	static public function getInstance($id){
		Number::validatePositiveNumber($id, 'N&uacute;mero inv&aacute;lido.');	
		return InvoiceDAM::getInstance($id);
	}
	
	/**
	 * Returns the invoice identifier.
	 *
	 * Returns 0 if there was no match for the provided serial number and number in the database.
	 * @param string $serialNumber
	 * @param integer $number
	 * @return integer
	 */
	static public function getInvoiceId($serialNumber, $number){
		String::validateString($serialNumber, 'N&uacute;mero de serie inv&aacute;lido.');
		Number::validatePositiveNumber($number, 'N&uacute;mero de factura inv&aacute;lido.');
		return InvoiceDAM::getId($serialNumber, $number);
	}
	
	/**
	 * Returns the invoice identifier.
	 *
	 * Returns 0 if there was no match for the provided working day, serial number and number in the database.
	 * @param WorkingDay $workingDay
	 * @param string $serialNumber
	 * @param integer $number
	 * @return integer
	 */
	static public function getInvoiceIdByWorkingDay(WorkingDay $workingDay, $serialNumber, $number){
		String::validateString($serialNumber, 'N&uacute;mero de serie inv&aacute;lido.');
		Number::validatePositiveNumber($number, 'N&uacute;mero de factura inv&aacute;lido.');
		return InvoiceDAM::getIdByWorkingDay($workingDay, $serialNumber, $number);
	}
	
	/**
	 * Validates the invoice main properties.
	 *
	 * This method call its parent validateMainProperties method. And nit must not be empty, cash receipt and
	 * correlative must not be NULL.
	 * @throws Exception
	 */
	protected function validateMainProperties(){
		parent::validateMainProperties();
		String::validateString($this->_mCustomerNit, 'Nit inv&aacute;lido.', 'nit');
	}
	
	/**
	 * Inserts the invoice data in the database.
	 *
	 * @throws Exception
	 */
	protected function insert(){
		$this->_mId = InvoiceDAM::insert($this);
	}
	
	/**
	 * Updates the document to cancelled in the database.
	 *
	 * @param UserAccount $user
	 * @param string $reason
	 */
	protected function updateToCancelled(UserAccount $user, $reason = NULL){
		$date = date('d/m/Y H:i:s');
		InvoiceDAM::cancel($this, $user, $date, $reason);
		InvoiceTransactionLog::write($this->getCorrelative()->getSerialNumber(), $this->getNumber(),
				$date, $this->getTotal(), InvoiceTransactionLog::CANCELLED);
	}
}


/**
 * Represents an additional discount in an invoice.
 * @package Document
 * @author Roberto Oliveros
 */
class Discount extends Persist{
	/**
	 * Invoice in which the discount was created.
	 *
	 * @var Invoice
	 */
	private $_mInvoice;
	
	/**
	 * Holds the percentage value of the discount.
	 *
	 * @var float
	 */
	private $_mPercentage;
	
	/**
	 * Holds the user who created the discount.
	 *
	 * @var UserAccount
	 */
	private $_mUser;
	
	/**
	 * Constructs the discount with the provided data.
	 *
	 * @param UserAccount $user
	 * @param integer $status
	 */
	public function __construct(UserAccount $user, $status = Persist::IN_PROGRESS){
		parent::__construct($status);
		
		$this->_mUser = $user;
	}
	
	/**
	 * Returns the discount's invoice.
	 *
	 * @return invoice
	 */
	public function getInvoice(){
		return $this->_mInvoice;
	}
	
	/**
	 * Returns the discount's percentage value.
	 *
	 * @return float
	 */
	public function getPercentage(){
		return $this->_mPercentage;
	}
	
	/**
	 * Returns the discount's creator.
	 *
	 * @return UserAccount
	 */
	public function getUser(){
		return $this->_mUser;
	}
	
	/**
	 * Sets the discount's invoice.
	 *
	 * @param Invoice $obj
	 */
	public function setInvoice(Invoice $obj){
		$this->_mInvoice = $obj;
	}
	
	/**
	 * Sets the discount's percentage value.
	 *
	 * @param float $value
	 */
	public function setPercentage($value){
		$this->_mPercentage = $value;
		Number::validateBetweenCeroToNinetyNineNumber($value,
				'Porcentaje inv&aacute;lido.');
	}
	
	/**
	 * Set the object's properties.
	 * 
	 * Must be call only from the database layer corresponding class. The object's status must be set to
	 * Persist::CREATED in the constructor method too.
	 * @param Invoice $invoice
	 * @param float $percentage
	 * @throws Exception
	 */
	public function setData(Invoice $invoice, $percentage){
		try{
			Number::validateBetweenCeroToNinetyNineNumber($percentage, 'Porcentage inv&aacute;lido.');
		} catch(Exception $e){
			$et = new Exception('Interno: Llamando al metodo setData en Discount con datos erroneos! ' .
					$e->getMessage());
			throw $et;
		}
		
		$this->_mInvoice = $invoice;
		$this->_mPercentage = $percentage;
	}
	
	/**
	 * Saves the discount's data in the database.
	 *
	 * Only applies if the object's status property is set to Persist::IN_PROGRESS.
	 */
	public function save(){
		if($this->_mStatus == Persist::IN_PROGRESS){
			$this->validateMainProperties();
			DiscountDAM::insert($this);
			$this->_mStatus = Persist::CREATED;
		}
	}
	
	/**
	 * Returns an instance of a discount from the database.
	 *
	 * Returns NULL if there was no match for the provided invoice in the database.
	 * @param Invoice $obj
	 * @return Discount
	 */
	static public function getInstance(Invoice $obj){
		return DiscountDAM::getInstance($obj);
	}
	
	/**
	 * Validates the discount's main properties.
	 *
	 * Invoice must not be NULL and percentage must be greater than cero.
	 * @throws Exception
	 */
	private function validateMainProperties(){
		if(is_null($this->_mInvoice))
			throw new Exception('Factura inv&aacute;lida.');
			
		Number::validateBetweenCeroToNinetyNineNumber($this->_mPercentage,
				'Porcentage inv&aacute;lido.');
	}
}


/**
 * Represents a purchase return document.
 * @package Document
 * @author Roberto Oliveros
 */
class PurchaseReturn extends Document{
	/**
	 * Holds the supplier for whom the return is being made.
	 *
	 * @var Supplier
	 */
	private $_mSupplier;
	
	/**
	 * Holds the supplier direct contact person name.
	 *
	 * @var string
	 */
	private $_mContact;
	
	/**
	 * Holds an explanation of why the creation of the document.
	 *
	 * @var string
	 */
	private $_mReason;
	
	/**
	 * Returns the purchase return's supplier.
	 *
	 * @return Supplier
	 */
	public function getSupplier(){
		return $this->_mSupplier;
	}
	
	/**
	 * Returns the supplier direct contact person name.
	 *
	 * @return string
	 */
	public function getContact(){
		return $this->_mContact;
	}
	
	/**
	 * Returns the reason of the document.
	 *
	 * @return string
	 */
	public function getReason(){
		return $this->_mReason;
	}
	
	/**
	 * Sets the purchase return supplier and the contact's name.
	 *
	 * @param Supplier $obj
	 */
	public function setSupplier(Supplier $obj = NULL){
		$this->_mSupplier = $obj;
		if(is_null($obj))
			throw new ValidateException('Seleccione un proveedor.');
		else
			$this->_mContact = $obj->getContact();
	}
	
	/**
	 * Sets the contact's name.
	 *
	 * @param string $contact
	 */
	public function setContact($contact){
		$this->_mContact = $contact;
	}
	
	/**
	 * Sets the purchase return reason.
	 *
	 * @param string $reason
	 */
	public function setReason($reason){
		String::validateString($reason, 'Motivo inv&aacute;lido.');
		$this->_mReason = $reason;
	}
	
	/**
	 * Sets the purchase return's properties.
	 *
	 * Must be called only from the database layer corresponding class. The object's status must be set to
	 * PersistDocument::CREATED in the constructor method too.
	 * @param Supplier $supplier
	 * @param string $contact
	 * @param string $reason
	 * @param float $total
	 * @param array<DocProductDetail> $details
	 * @throws Exception
	 */
	public function setData(Supplier $supplier, $reason, $total, $details, $contact = NULL){
		parent::setData($total, $details);
		
		try{
			String::validateString($reason, 'Motivo inv&aacute;lido.');
		} catch(Exception $e){
			$et = new Exception('Interno: Llamando al metodo setData en PurchaseReturn con datos erroneos! ' .
					$e->getMessage());
			throw $et;
		}
		
		$this->_mSupplier = $supplier;
		$this->_mContact = $contact;
		$this->_mReason = $reason;
	}
	
	/**
	 * Does not save the purchase return in the database and reverts its effects.
	 *
	 * Only applies if the object's status property is set to PersistDocument::IN_PROGRESS.
	 */
	public function discard(){
		if($this->_mStatus == Persist::IN_PROGRESS)
			foreach($this->_mDetails as &$detail)
				WithdrawEvent::cancel($this, $detail);
	}
	
	/**
	 * Returns a purchase return with the details.
	 *
	 * Returns NULL if there was no match for the provided id in the database. 
	 * @param integer $id
	 * @return PurchaseReturn
	 */
	static public function getInstance($id){
		Number::validatePositiveNumber($id, 'N&uacute;mero inv&aacute;lido.');	
		return PurchaseReturnDAM::getInstance($id);
	}
	
	/**
	 * Validates the purchase return's main properties.
	 *
	 * Supplier must not be null and reason must not be empty.
	 * @throws Exception
	 */
	protected function validateMainProperties(){
		parent::validateMainProperties();
		
		if(is_null($this->_mSupplier))
			throw new ValidateException('Proveedor inv&aacute;lido.', 'supplier_id');
			
		String::validateString($this->_mReason, 'Motivo inv&aacute;lido.', 'reason');
	}
	
	/**
	 * Inserts the purchase return's data in the database.
	 *
	 */
	protected function insert(){
		$this->_mId = PurchaseReturnDAM::insert($this);
	}
	
	/**
	 * Updates the document to cancelled in the database.
	 * 
	 * @param UserAccount $user
	 * @param string $reason
	 */
	protected function updateToCancelled(UserAccount $user, $reason = NULL){
		PurchaseReturnDAM::cancel($this, $user, date('d/m/Y H:i:s'));
	}
}


/**
 * Represents a shipment document.
 * @package Document
 * @author Roberto Oliveros
 */
class Shipment extends Document{
	/**
	 * Holds the branch for whom the shipment is being made.
	 *
	 * @var Branch
	 */
	private $_mBranch;
	
	/**
	 * Holds the branch direct contact person name.
	 *
	 * @var string
	 */
	private $_mContact;
	
	/**
	 * Returns the shipment's branch.
	 *
	 * @return Branch
	 */
	public function getBranch(){
		return $this->_mBranch;
	}
	
	/**
	 * Returns the supplier direct contact person name.
	 *
	 * @return string
	 */
	public function getContact(){
		return $this->_mContact;
	}
	
	/**
	 * Sets the shipment's branch and contact name.
	 *
	 * @param Branch $obj
	 */
	public function setBranch(Branch $obj = NULL){
		$this->_mBranch = $obj;
		if(is_null($obj))
			throw new ValidateException('Seleccione una sucursal.');
		else
			$this->_mContact = $obj->getContact();
	}
	
	/**
	 * Sets the shipment's contact name.
	 *
	 * @param string $contact
	 */
	public function setContact($contact){
		$this->_mContact = $contact;
	}
	
	/**
	 * Sets the shipment's properties.
	 *
	 * Must be called only from the database layer corresponding class. The object's status must be set to
	 * PersistDocument::CREATED in the constructor method too.
	 * @param Branch $branch
	 * @param float $total
	 * @param array<DocProductDetail> $details
	 * @param string $contact
	 * @throws Exception
	 */
	public function setData(Branch $branch, $total, $details, $contact = NULL){
		parent::setData($total, $details);
		
		$this->_mBranch = $branch;
		$this->_mContact = $contact;
	}
	
	/**
	 * Does not save the shipment in the database and reverts its effects.
	 *
	 * Only applies if the object's status property is set to PersistDocument::IN_PROGRESS.
	 */
	public function discard(){
		if($this->_mStatus == Persist::IN_PROGRESS)
			foreach($this->_mDetails as &$detail)
				WithdrawEvent::cancel($this, $detail);
	}
	
	/**
	 * Returns a shipment with the details.
	 *
	 * Returns NULL if there was no match for the provided id in the database. 
	 * @param integer $id
	 * @return Shipment
	 */
	static public function getInstance($id){
		Number::validatePositiveNumber($id, 'N&uacute;mero inv&aacute;lido.');	
		return ShipmentDAM::getInstance($id);
	}
	
	/**
	 * Validates the shipment's main properties.
	 *
	 * Branch must not be null.
	 * @throws Exception
	 */
	protected function validateMainProperties(){
		parent::validateMainProperties();
		
		if(is_null($this->_mBranch))
			throw new ValidateException('Sucursal inv&aacute;lida.', 'branch_id');
	}
	
	/**
	 * Inserts the shipment's data in the database.
	 *
	 */
	protected function insert(){
		$this->_mId = ShipmentDAM::insert($this);
	}
	
	/**
	 * Updates the document to cancelled in the database.
	 * 
	 * @param UserAccount $user
	 * @param string $reason
	 */
	protected function updateToCancelled(UserAccount $user, $reason = NULL){
		ShipmentDAM::cancel($this, $user, date('d/m/Y H:i:s'));
	}
}


/**
 * Represents a purchase receipt document.
 * @package Document
 * @author Roberto Oliveros
 */
class Receipt extends Document{
	/**
	 * Holds the supplier from whom the merchandise is being received.
	 *
	 * @var Supplier
	 */
	private $_mSupplier;
	
	/**
	 * Holds the supplier's shipment document number.
	 *
	 * @var string
	 */
	private $_mShipmentNumber;
	
	/**
	 * Holds the supplie's shipment document total amount.
	 *
	 * @var float
	 */
	private $_mShipmentTotal;
	
	/**
	 * Returns the receipt's supplier.
	 *
	 * @return Supplier
	 */
	public function getSupplier(){
		return $this->_mSupplier;
	}
	
	/**
	 * Returns the supplier's shipment document number.
	 *
	 * @return string
	 */
	public function getShipmentNumber(){
		return $this->_mShipmentNumber;
	}
	
	/**
	 * Returns the supplier's shipment document total amount.
	 *
	 * @return float
	 */
	public function getShipmentTotal(){
		return $this->_mShipmentTotal;
	}
	
	/**
	 * Sets the receipt's supplier.
	 *
	 * @param Supplier $obj
	 */
	public function setSupplier(Supplier $obj = NULL){
		$this->_mSupplier = $obj;
		if(is_null($obj))
			throw new ValidateException('Seleccione un proveedor.');
	}
	
	/**
	 * Sets the receipt's shipment number.
	 *
	 * @param string $number
	 */
	public function setShipmentNumber($number){
		$this->_mShipmentNumber = $number;
		String::validateString($number, 'N&uacute;mero de envio inv&aacute;lido.');
	}
	
	/**
	 * Sets the receipt's shipment total amount.
	 *
	 * @param float $amount
	 */
	public function setShipmentTotal($amount){
		$this->_mShipmentTotal = $amount;
		Number::validatePositiveNumber($amount, 'Total del envio inv&aacute;lido.');
	}
	
	/**
	 * Sets the receipt's properties.
	 *
	 * Must be called only from the database layer corresponding class. The object's status must be set to
	 * PersistDocument::CREATED in the constructor method too.
	 * @param Supplier $supplier
	 * @param string $shipmentNumber
	 * @param float $shipmentTotal
	 * @param float $total
	 * @param array<DocProductDetail> $details
	 * @throws Exception
	 */
	public function setData(Supplier $supplier, $shipmentNumber, $total, $details){
		parent::setData($total, $details);
		
		try{
			String::validateString($shipmentNumber, 'N&uacute;mero de envio inv&aacute;lido.');
		} catch(Exception $e){
			$et = new Exception('Interno: Llamando al metodo setData en Receipt con datos erroneos! ' .
					$e->getMessage());
			throw $et;
		}
		
		$this->_mSupplier = $supplier;
		$this->_mShipmentNumber = $shipmentNumber;
		$this->_mShipmentTotal = $total;
	}
	
	/**
	 * Does not save the receipt in the database and reverts its effects.
	 *
	 * Only applies if the object's status property is set to PersistDocument::IN_PROGRESS.
	 */
	public function discard(){
		if($this->_mStatus == Persist::IN_PROGRESS)
			foreach($this->_mDetails as &$detail)
				EntryEvent::cancel($this, $detail);
	}
	
	/**
	 * Returns a receipt with the details.
	 *
	 * Returns NULL if there was no match for the provided id in the database. 
	 * @param integer $id
	 * @return Receipt
	 */
	static public function getInstance($id){
		Number::validatePositiveNumber($id, 'N&uacute;mero inv&aacute;lido.');
		return ReceiptDAM::getInstance($id);
	}
	
	/**
	 * Validates the receipt's main properties.
	 *
	 * Supplier must not be NULL, the shipment number must not be empty and the shimpment total amount must
	 * match with the receipt total amount.
	 */
	protected function validateMainProperties(){
		parent::validateMainProperties();
		
		if(is_null($this->_mSupplier))
			throw new ValidateException('Proveedor inv&aacute;lido.', 'supplier_id');
			
		String::validateString($this->_mShipmentNumber, 'N&uacute;mero de envio inv&aacute;lido.',
				'shipment_number');
		
		if(bccomp($this->_mShipmentTotal, $this->getTotal(), 2) != 0)
			throw new ValidateException('El total del envio no coincide con el del recibo.',
					'shipment_total');
	}
	
	/**
	 * Inserts the receipt's data in the database.
	 *
	 */
	protected function insert(){
		$this->_mId = ReceiptDAM::insert($this);
	}
	
	/**
	 * Updates the document to cancelled in the database.
	 * 
	 * @param UserAccount $user
	 * @param string $reason
	 */
	protected function updateToCancelled(UserAccount $user, $reason = NULL){
		ReceiptDAM::cancel($this, $user, date('d/m/Y H:i:s'));
	}
}


/**
 * Defines common functionality for the inventory adjustment documents.
 * @package Document
 * @author Roberto Oliveros
 */
abstract class AdjustmentDocument extends Document{
/**
	 * Holds the reason of why the creation of the document.
	 *
	 * @var string
	 */
	private $_mReason;
	
	/**
	 * Returns the document's reason.
	 *
	 * @return string
	 */
	public function getReason(){
		return $this->_mReason;
	}
	
	/**
	 * Sets the document's reason.
	 *
	 * @param string $reason
	 */
	public function setReason($reason){
		String::validateString($reason, 'Motivo inv&aacute;lido.');
		$this->_mReason = $reason;
	}
	
	/**
	 * Sets the document's properties.
	 *
	 * Must be called only from the database layer corresponding class. The object's status must be set to
	 * PersistDocument::CREATED in the constructor method too.
	 * @param string $reason
	 * @param float $total
	 * @param array<DocProductDetail> $details
	 * @throws Exception
	 */
	public function setData($reason, $total, $details){
		parent::setData($total, $details);
		
		try{
			String::validateString($reason, 'Motivo inv&aacute;lido.');
		} catch(Exception $e){
			$et = new Exception('Interno: Llamando al metodo setData en EntryIA con datos erroneos! ' .
					$e->getMessage());
			throw $et;
		}
		
		$this->_mReason = $reason;
	}
	
	/**
	 * Validates the document's main properties.
	 *
	 * Reason must not be empty.
	 */
	protected function validateMainProperties(){
		parent::validateMainProperties();
			
		String::validateString($this->_mReason, 'Motivo inv&aacute;lido.', 'reason');
	}
}


/**
 * Represents an entry inventory adjustment document.
 * @package Document
 * @author Roberto Oliveros
 */
class EntryIA extends AdjustmentDocument{
	/**
	 * Does not save the document in the database and reverts its effects.
	 *
	 * Only applies if the object's status property is set to PersistDocument::IN_PROGRESS.
	 */
	public function discard(){
		if($this->_mStatus == Persist::IN_PROGRESS)
			foreach($this->_mDetails as &$detail)
				EntryEvent::cancel($this, $detail);
	}
	
	/**
	 * Returns an entry inventory adjustment document with the details.
	 *
	 * Returns NULL if there was no match for the provided id in the database. 
	 * @param integer $id
	 * @return EntryIA
	 */
	static public function getInstance($id){
		Number::validatePositiveNumber($id, 'N&uacute;mero inv&aacute;lido.');
		return EntryIADAM::getInstance($id);
	}
	
	/**
	 * Inserts the document's data in the database.
	 *
	 */
	protected function insert(){
		$this->_mId = EntryIADAM::insert($this);
	}
	
	/**
	 * Updates the document to cancelled in the database.
	 * 
	 * @param UserAccount $user
	 * @param string $reason
	 */
	protected function updateToCancelled(UserAccount $user, $reason = NULL){
		EntryIADAM::cancel($this, $user, date('d/m/Y H:i:s'));
	}
}


/**
 * Represents a withdraw inventory adjustment document.
 * @package Document
 * @author Roberto Oliveros
 */
class WithdrawIA extends AdjustmentDocument{
	/**
	 * Does not save the document in the database and reverts its effects.
	 *
	 * Only applies if the object's status property is set to PersistDocument::IN_PROGRESS.
	 */
	public function discard(){
		if($this->_mStatus == Persist::IN_PROGRESS)
			foreach($this->_mDetails as &$detail)
				WithdrawEvent::cancel($this, $detail);
	}
	
	/**
	 * Returns a withdraw inventory adjustment document with the details.
	 *
	 * Returns NULL if there was no match for the provided id in the database. 
	 * @param integer $id
	 * @return WithdrawIA
	 */
	static public function getInstance($id){
		Number::validatePositiveNumber($id, 'N&uacute;mero inv&aacute;lido.');
		return WithdrawIADAM::getInstance($id);
	}
	
	/**
	 * Inserts the document's data in the database.
	 *
	 */
	protected function insert(){
		$this->_mId = WithdrawIADAM::insert($this);
	}
	
	/**
	 * Updates the document to cancelled in the database.
	 * 
	 * @param UserAccount $user
	 * @param string $reason
	 */
	protected function updateToCancelled(UserAccount $user, $reason = NULL){
		WithdrawIADAM::cancel($this, $user, date('d/m/Y H:i:s'));
	}
}


/**
 * Utility class to register the invoice transactions.
 * @package Document
 * @author Roberto Oliveros
 */
class InvoiceTransactionLog{
	/**
	 * State type.
	 * 
	 * Indicates in which state the document is.
	 */
	const CREATED = 'EMITIDO';
	
	/**
	 * State type.
	 * 
	 * Indicates in which state the document is.
	 */
	const CANCELLED = 'ANULADO';
	
	/**
	 * Register the event in the database.
	 *
	 * @param string $serial_number
	 * @param integer $number
	 * @param string $dateTime
	 * @param float $total
	 * @param integer $state
	 */
	static public function write($serial_number, $number, $dateTime, $total, $state){
		InvoiceTransactionLogDAM::insert($serial_number, $number, $dateTime, $total, $state);
	}
}


/**
 * Utility class to register the correlatives activation.
 * @package Document
 * @author Roberto Oliveros
 */
class ResolutionLog{
	/**
	 * Document type.
	 * 
	 * Indicates which type of document the resolution belongs to.
	 */
	const INVOICE = 'FACTURA';
	
	/**
	 * Register the event in the database.
	 *
	 * @param Correlative $correlative
	 */
	static public function write(Correlative $correlative){
		ResolutionLogDAM::insert($correlative, self::INVOICE);
	}
}
?>