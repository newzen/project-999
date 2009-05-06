<?php
/**
 * Library with the utility database classes for the Cash package.
 * @package CashDAM
 * @author Roberto Oliveros
 */

/**
 * Defines functionality for accessing the bank's database tables.
 * @package CashDAM
 * @author Roberto Oliveros
 */
class BankDAM{
	static private $_mName = 'GyT Continental';
	
	/**
	 * Returns a Bank if it founds an id match in the database. Otherwise returns NULL.
	 *
	 * @param integer $id
	 * @return Bank
	 */
	static public function getInstance($id){
		if($id == 123){
			$bank = new Bank(123, PersistObject::CREATED);
			$bank->setData(self::$_mName);
			return $bank;
		}
		else
			return NULL;
	}
	
	/**
	 * Insert a Bank in the database.
	 *
	 * @param Bank $obj
	 * @return void
	 */
	static public function insert(Bank $obj){
		return 123;
	}
	
	/**
	 * Updates a Bank data in the database.
	 *
	 * @param Bank $obj
	 * @return void
	 */
	static public function update(Bank $obj){
		self::$_mName = $obj->getName();
	}
	
	/**
	 * Deletes a Bank from the database. Returns true on success, otherwise it has dependencies and
	 * returns false.
	 *
	 * @param Bank $obj
	 * @return boolean
	 */
	static public function delete(Bank $obj){
		if($obj->getId() == 123)
			return true;
		else
			return false;
	}
}


/**
 * Defines functionality for accessing the database.
 * @package CashDAM
 * @author Roberto Oliveros
 */
class BankAccountDAM{
	/**
	 * Verifies if the provided number already exists in the database.
	 *
	 * @param string $number
	 * @return boolean
	 */
	static public function exists($number){
		if($number == '123')
			return true;
		else
			return false;
	}
	
 	/** Returns a BankAccount if it founds a match in the database. Otherwise returns NULL.
	 *
	 * @param string $number
	 * @return BankAccount
	 */
	static public function getInstance($number){
		if($number == '123'){
			$bank_account = new BankAccount('123', PersistObject::CREATED);
			$bank = Bank::getInstance(123);
			$bank_account->setData('Roberto Oliveros', $bank);
			return $bank_account;
		}
		else
			return NULL;
	}
	
	/**
	 * Insert a BankAccount in the database.
	 *
	 * @param BankAccount $obj
	 * @return void
	 */
	static public function insert(BankAccount $obj){
		// Code here...
	}
	
	/**
	 * Updates a BankAccount data in the database.
	 *
	 * @param BankAccount $obj
	 * @return void
	 */
	static public function update(BankAccount $obj){
		// Code here...
	}
	
	/**
	 * Deletes a BankAccount from the datase. Returns true on success, otherwise it has dependencies and 
	 * returns false.
	 *
	 * @param BankAccount $obj
	 * @return boolean
	 */
	static public function delete(BankAccount $obj){
		if($obj->getNumber() == '123')
			return true;
		else
			return false;
	}
}


/**
 * Defines functionality for accessing the shift's database tables.
 * @package CashDAM
 * @author Roberto Oliveros
 */
class ShiftDAM{
	static private $_mName = 'Diurno';
	
	/**
	 * Returns a Shift if it founds an id match in the database. Otherwise returns NULL.
	 *
	 * @param integer $id
	 * @return Shift
	 */
	static public function getInstance($id){
		if($id == 123){
			$shift = new Shift(123, PersistObject::CREATED);
			$shift->setData(self::$_mName, '8am - 6pm');
			return $shift;
		}
		else
			return NULL;
	}
	
	/**
	 * Insert a Shift in the database.
	 *
	 * @param Shift $obj
	 * @return void
	 */
	static public function insert(Shift $obj){
		return 123;
	}
	
	/**
	 * Updates a Shift data in the database.
	 *
	 * @param Shift $obj
	 * @return void
	 */
	static public function update(Shift $obj){
		self::$_mName = $obj->getName();
	}
	
	/**
	 * Deletes a Shift from the datase. Returns true on success, otherwise it has dependencies and returns false.
	 *
	 * @param Bank $obj
	 * @return boolean
	 */
	static public function delete(Shift $obj){
		if($obj->getId() == 123)
			return true;
		else
			return false;
	}
}


/**
 * Defines functionality for accessing the cash register tables.
 *	@package CashDAM
 *  @author Roberto Oliveros
 */
class CashRegisterDAM{
	static private $_mIsOpen = true;
	static private $_mIsOpen123 = true;
	static private $_mIsOpen124 = true;
	
	/**
	 * Returns the status of the cash register.
	 *
	 * @param CashRegister $obj
	 * @return boolean
	 */
	static public function isOpen(CashRegister $obj){
		switch($obj->getId()){
			case 123:
				return self::$_mIsOpen123;
				break;
				
			case 124:
				return self::$_mIsOpen124;
				break;
				
			default:
				return self::$_mIsOpen;
		}
	}
	
	/**
	 * Close the cash register in the database.
	 *
	 * @param CashRegister $obj
	 */
	static public function close(CashRegister $obj){
		switch($obj->getId()){
			case 123:
				self::$_mIsOpen123 = false;
				break;
				
			case 124:
				self::$_mIsOpen124 = false;
				break;
				
			default:
				self::$_mIsOpen = false;
		}
	}
	
	/**
	 * Returns an instance of a cash register.
	 *
	 * Returns NULL if not match was found in the database.
	 * @param integer $id
	 * @return CashRegister
	 */
	static public function getInstance($id){
		switch($id){
			case 123:
				$shift = Shift::getInstance(123);
				$cash_register = new CashRegister($shift, 123);
				return $cash_register;
				break;
				
			case 124:
				$shift = Shift::getInstance(123);
				$cash_register = new CashRegister($shift, 124);
				return $cash_register;
				break;
				
			default:
				return NULL;
		}
	}
}


/**
 * Defines functionality for accessing the type of payment card database tables.
 * @package CashDAM
 * @author Roberto Oliveros
 */
class PaymentCardTypeDAM{
	static private $_mName = 'Credito';
	
	/**
	 * Returns a type of payment card if it founds an id match in the database. Otherwise returns NULL.
	 *
	 * @param integer $id
	 * @return PaymentCardType
	 */
	static public function getInstance($id){
		if($id == 123){
			$type = new PaymentCardType(123, PersistObject::CREATED);
			$type->setData(self::$_mName);
			return $type;
		}
		else
			return NULL;
	}
	
	/**
	 * Insert a type of payment card in the database.
	 *
	 * @param PaymentCardType $obj
	 */
	static public function insert(PaymentCardType $obj){
		return 123;
	}
	
	/**
	 * Updates a type of payment card data in the database.
	 *
	 * @param PaymentCardType $obj
	 */
	static public function update(PaymentCardType $obj){
		self::$_mName = $obj->getName();
	}
	
	/**
	 * Deletes a type of payment card from the database. Returns true on success, otherwise it has dependencies
	 * and returns false.
	 *
	 * @param PaymentCardType $obj
	 * @return boolean
	 */
	static public function delete(PaymentCardType $obj){
		if($obj->getId() == 123)
			return true;
		else
			return false;
	}
}


/**
 * Defines functionality for accessing the payment card brand database tables.
 * @package CashDAM
 * @author Roberto Oliveros
 */
class PaymentCardBrandDAM{
	static private $_mName = 'Visa';
	
	/**
	 * Returns a payment card brand if it founds an id match in the database. Otherwise returns NULL.
	 *
	 * @param integer $id
	 * @return PaymentCardBrand
	 */
	static public function getInstance($id){
		if($id == 123){
			$brand = new PaymentCardBrand(123, PersistObject::CREATED);
			$brand->setData(self::$_mName);
			return $brand;
		}
		else
			return NULL;
	}
	
	/**
	 * Insert a payment card brand in the database.
	 *
	 * @param PaymentCardBrand $obj
	 */
	static public function insert(PaymentCardBrand $obj){
		return 123;
	}
	
	/**
	 * Updates a payment card brand data in the database.
	 *
	 * @param PaymentCardBrand $obj
	 */
	static public function update(PaymentCardBrand $obj){
		self::$_mName = $obj->getName();
	}
	
	/**
	 * Deletes a payment card brand from the database. Returns true on success, otherwise it has dependencies
	 * and returns false.
	 *
	 * @param PaymentCardBrand $obj
	 * @return boolean
	 */
	static public function delete(PaymentCardBrand $obj){
		if($obj->getId() == 123)
			return true;
		else
			return false;
	}
}


/**
 * Utility class for modifiying cash amount values on the receipt table in the database.
 * @package CashDAM
 * @author Roberto Oliveros
 */
class CashDAM{
	static private $_mAmount123 = 95.55;
	static private $_mReserved123 = 10.00;
	static private $_mDeposited123 = 0.0;
	
	/**
	 * Returns the cash amount of the provided object from the database.
	 *
	 * @param Cash $obj
	 * @return float
	 */
	static public function getAmount(Cash $obj){
		switch($obj->getId()){
			case 123:
				return self::$_mAmount123;
				break;
			
			default:
				return 0;
		}
	}
	
	
	static public function getAvailable(Cash $obj){
		switch($obj->getId()){
			case 123:
				return self::$_mAmount123 - (self::$_mReserved123 + self::$_mDeposited123);
				break;
			
			default:
				return 0;
		}
	}
	
	/**
	 * Reserves the provided cash amount in the database.
	 *
	 * @param Cash $obj
	 * @param float $amount
	 */
	static public function reserve(Cash $obj, $amount){
		switch($obj->getId()){
			case 123:
				self::$_mReserved123 += $amount;
				return;
				
			default:
		}
	}
	
	/**
	 * Decreases the reserved cash amount in the database.
	 *
	 * @param Cash $obj
	 * @param float $amount
	 */
	static public function decreaseReserve(Cash $obj, $amount){
		switch($obj->getId()){
			case 123:
				self::$_mReserved123 -= $amount;
				return;
				
			default:
		}
	}
	
	/**
	 * Increases the deposited cash amount in the database.
	 *
	 * @param Cash $obj
	 * @param float $amount
	 */
	static public function deposit(Cash $obj, $amount){
		switch($obj->getId()){
			case 123:
				self::$_mDeposited123 += $amount;
				return;
				
			default:
		}
	}
	
	/**
	 * Decreases the deposited cash amount in the database.
	 *
	 * @param Cash $obj
	 * @param float $amount
	 */
	static public function decreaseDeposit(Cash $obj, $amount){
		switch($obj->getId()){
			case 123:
				self::$_mDeposited123 -= $amount;
				return;
				
			default:
		}
	}
}


/**
 * Utility class for accessing database tables regarding receipts.
 * @package CashDAM
 * @author Roberto Oliveros
 */
class ReceiptDAM{
	/**
	 * Returns an instance of a receipt.
	 *
	 * Returns NULL if there was no match for the provided invoice in the database.
	 * @param Invoice $obj
	 * @return Receipt
	 */
	static public function getInstance(Invoice $obj){
		switch($obj->getId()){
			case 123:
				$receipt = new Receipt($obj, 123, PersistDocument::CREATED);
				$cash = new Cash(43.50, 123, Persist::CREATED);
				$receipt->setData($cash);
				return $receipt;
				break;
				
			default:
				return NULL;
		}
	}
	
	/**
	 * Inserts the receipt's data in the database.
	 *
	 * @param Receipt $obj
	 */
	static public function insert(Receipt $obj){
		// Code here...
	}
	
	/**
	 * Cancels the document in the database.
	 *
	 * @param Receipt $obj
	 */
	static public function cancel(Receipt $obj){
		// Code here...
	}
}
?>