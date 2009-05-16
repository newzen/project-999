<?php
/**
 * Library with utility classes for obtaining data lists of any of the system's subjects.
 * @package List
 * @author Roberto Oliveros
 */

/**
 * For validation purposes.
 */
require_once('business/validator.php');
/**
 * For accessing the database.
 */
require_once('data/list_dam.php');

/**
 * Defines the interface for the rest of the classes that extend it.
 * @package List
 * @author Roberto Oliveros
 */
abstract class DataList{
	/**
	 * Defines the method signature.
	 *
	 * @param integer $page
	 * @param integer &$total_pages
	 * @param integer &$total_items
	 * @return array
	 */
	abstract static public function getList($page = 0, &$total_pages = 0, &$total_items = 0);
}


/**
 * Utility class for obtaining a list of banks from the database.
 * @package List
 * @author Roberto Oliveros
 */
class BankList extends DataList{
	/**
	 * Returns an array with the banks' ids and names from the database.
	 *
	 * The total_pages and total_items arguments are necessary to return their respective values. If no page
	 * argument is passed or a cero is passed, all the details are returned.
	 * @param integer $page
	 * @param integer &$total_pages
	 * @param integer &$total_items
	 * @return array
	 */
	static public function getList($page = 0, &$total_pages = 0, &$total_items = 0){
		if($page !== 0)
			Number::validatePositiveInteger($page, 'Pagina inv&aacute;lida.');
			
		return BankListDAM::getList($page, $total_pages, $total_items);
	}
}


/**
 * Utility class for obtaining a list of pending deposits from confirmation from the database.
 * @package List
 * @author Roberto Oliveros
 */
class PendingDepositList extends DataList{
	/**
	 * Returns an array with the deposits' date, id, number, bank_account, bank and amount from the database.
	 *
	 * The total_pages and total_items arguments are necessary to return their respective values. If no page
	 * argument is passed or a cero is passed, all the details are returned.
	 * @param integer $page
	 * @param integer &$total_pages
	 * @param integer &$total_items
	 * @return array
	 */
	static public function getList($page = 0, &$total_pages = 0, &$total_items = 0){
		if($page !== 0)
			Number::validatePositiveInteger($page, 'Pagina inv&aacute;lida.');
			
		return PendingDepositListDAM::getList($page, $total_pages, $total_items);
	}
}


/**
 * Utility class for obtaining a list of manufacturers from the database.
 * @package List
 * @author Roberto Oliveros
 */
class ManufacturerList extends DataList{
	/**
	 * Returns an array with the manufacturers' id and name from the database.
	 *
	 * The total_pages and total_items arguments are necessary to return their respective values. If no page
	 * argument is passed or a cero is passed, all the details are returned.
	 * @param integer $page
	 * @param integer &$total_pages
	 * @param integer &$total_items
	 * @return array
	 */
	static public function getList($page = 0, &$total_pages = 0, &$total_items = 0){
		if($page !== 0)
			Number::validatePositiveInteger($page, 'Pagina inv&aacute;lida.');
			
		return ManufacturerListDAM::getList($page, $total_pages, $total_items);
	}
}


/**
 * Utility class for obtaining a list of correlatives from the database.
 * @package List
 * @author Roberto Oliveros
 */
class CorrelativeList extends DataList{
	/**
	 * Returns an array with the correlatives' serial_number and default flag from the database.
	 *
	 * The total_pages and total_items arguments are necessary to return their respective values. If no page
	 * argument is passed or a cero is passed, all the details are returned.
	 * @param integer $page
	 * @param integer &$total_pages
	 * @param integer &$total_items
	 * @return array
	 */
	static public function getList($page = 0, &$total_pages = 0, &$total_items = 0){
		if($page !== 0)
			Number::validatePositiveInteger($page, 'Pagina inv&aacute;lida.');
			
		return CorrelativeListDAM::getList($page, $total_pages, $total_items);
	}
}


/**
 * Utility class for obtaining a list of bank accounts from the database.
 * @package List
 * @author Roberto Oliveros
 */
class BankAccountList extends DataList{
	/**
	 * Returns an array with the bank accounts' number and name from the database.
	 *
	 * The total_pages and total_items arguments are necessary to return their respective values. If no page
	 * argument is passed or a cero is passed, all the details are returned.
	 * @param integer $page
	 * @param integer &$total_pages
	 * @param integer &$total_items
	 * @return array
	 */
	static public function getList($page = 0, &$total_pages = 0, &$total_items = 0){
		if($page !== 0)
			Number::validatePositiveInteger($page, 'Pagina inv&aacute;lida.');
			
		return BankAccountListDAM::getList($page, $total_pages, $total_items);
	}
}


/**
 * Utility class for obtaining a list of user accounts from the database.
 * @package List
 * @author Roberto Oliveros
 */
class UserAccountList extends DataList{
	/**
	 * Returns an array with the user accounts' username and name from the database.
	 *
	 * The total_pages and total_items arguments are necessary to return their respective values. If no page
	 * argument is passed or a cero is passed, all the details are returned.
	 * @param integer $page
	 * @param integer &$total_pages
	 * @param integer &$total_items
	 * @return array
	 */
	static public function getList($page = 0, &$total_pages = 0, &$total_items = 0){
		if($page !== 0)
			Number::validatePositiveInteger($page, 'Pagina inv&aacute;lida.');
			
		return UserAccountListDAM::getList($page, $total_pages, $total_items);
	}
}


/**
 * Utility class for obtaining a list of payment card brands from the database.
 * @package List
 * @author Roberto Oliveros
 */
class PaymentCardBrandList extends DataList{
	/**
	 * Returns an array with the payment card brands' id and name from the database.
	 *
	 * The total_pages and total_items arguments are necessary to return their respective values. If no page
	 * argument is passed or a cero is passed, all the details are returned.
	 * @param integer $page
	 * @param integer &$total_pages
	 * @param integer &$total_items
	 * @return array
	 */
	static public function getList($page = 0, &$total_pages = 0, &$total_items = 0){
		if($page !== 0)
			Number::validatePositiveInteger($page, 'Pagina inv&aacute;lida.');
			
		return PaymentCardBrandListDAM::getList($page, $total_pages, $total_items);
	}
}


/**
 * Utility class for obtaining a list of products from the database.
 * @package List
 * @author Roberto Oliveros
 */
class ProductList extends DataList{
	/**
	 * Returns an array with the products' id and name from the database.
	 *
	 * The total_pages and total_items arguments are necessary to return their respective values. If no page
	 * argument is passed or a cero is passed, all the details are returned.
	 * @param integer $page
	 * @param integer &$total_pages
	 * @param integer &$total_items
	 * @return array
	 */
	static public function getList($page = 0, &$total_pages = 0, &$total_items = 0){
		if($page !== 0)
			Number::validatePositiveInteger($page, 'Pagina inv&aacute;lida.');
			
		return ProductListDAM::getList($page, $total_pages, $total_items);
	}
}


/**
 * Utility class for obtaining a list of suppliers from the database.
 * @package List
 * @author Roberto Oliveros
 */
class SupplierList extends DataList{
	/**
	 * Returns an array with the suppliers' id and name from the database.
	 *
	 * The total_pages and total_items arguments are necessary to return their respective values. If no page
	 * argument is passed or a cero is passed, all the details are returned.
	 * @param integer $page
	 * @param integer &$total_pages
	 * @param integer &$total_items
	 * @return array
	 */
	static public function getList($page = 0, &$total_pages = 0, &$total_items = 0){
		if($page !== 0)
			Number::validatePositiveInteger($page, 'Pagina inv&aacute;lida.');
			
		return SupplierListDAM::getList($page, $total_pages, $total_items);
	}
}


/**
 * Utility class for obtaining a list of user roles from the database.
 * @package List
 * @author Roberto Oliveros
 */
class RoleList extends DataList{
	/**
	 * Returns an array with the user roles' id and name from the database.
	 *
	 * The total_pages and total_items arguments are necessary to return their respective values. If no page
	 * argument is passed or a cero is passed, all the details are returned.
	 * @param integer $page
	 * @param integer &$total_pages
	 * @param integer &$total_items
	 * @return array
	 */
	static public function getList($page = 0, &$total_pages = 0, &$total_items = 0){
		if($page !== 0)
			Number::validatePositiveInteger($page, 'Pagina inv&aacute;lida.');
			
		return RoleListDAM::getList($page, $total_pages, $total_items);
	}
}


/**
 * Utility class for obtaining a list of branches from the database.
 * @package List
 * @author Roberto Oliveros
 */
class BranchList extends DataList{
	/**
	 * Returns an array with the branches' id and name from the database.
	 *
	 * The total_pages and total_items arguments are necessary to return their respective values. If no page
	 * argument is passed or a cero is passed, all the details are returned.
	 * @param integer $page
	 * @param integer &$total_pages
	 * @param integer &$total_items
	 * @return array
	 */
	static public function getList($page = 0, &$total_pages = 0, &$total_items = 0){
		if($page !== 0)
			Number::validatePositiveInteger($page, 'Pagina inv&aacute;lida.');
			
		return BranchListDAM::getList($page, $total_pages, $total_items);
	}
}


/**
 * Utility class for obtaining a list of payment card types from the database.
 * @package List
 * @author Roberto Oliveros
 */
class PaymentCardTypeList extends DataList{
	/**
	 * Returns an array with the payment card types' id and name from the database.
	 *
	 * The total_pages and total_items arguments are necessary to return their respective values. If no page
	 * argument is passed or a cero is passed, all the details are returned.
	 * @param integer $page
	 * @param integer &$total_pages
	 * @param integer &$total_items
	 * @return array
	 */
	static public function getList($page = 0, &$total_pages = 0, &$total_items = 0){
		if($page !== 0)
			Number::validatePositiveInteger($page, 'Pagina inv&aacute;lida.');
			
		return PaymentCardTypeListDAM::getList($page, $total_pages, $total_items);
	}
}


/**
 * Utility class for obtaining a list of cash register shifts from the database.
 * @package List
 * @author Roberto Oliveros
 */
class ShiftList extends DataList{
	/**
	 * Returns an array with the shifts' id, name and time_table from the database.
	 *
	 * The total_pages and total_items arguments are necessary to return their respective values. If no page
	 * argument is passed or a cero is passed, all the details are returned.
	 * @param integer $page
	 * @param integer &$total_pages
	 * @param integer &$total_items
	 * @return array
	 */
	static public function getList($page = 0, &$total_pages = 0, &$total_items = 0){
		if($page !== 0)
			Number::validatePositiveInteger($page, 'Pagina inv&aacute;lida.');
			
		return ShiftListDAM::getList($page, $total_pages, $total_items);
	}
}


/**
 * Utility class for obtaining a list of units of measure from the database.
 * @package List
 * @author Roberto Oliveros
 */
class UnitOfMeasureList extends DataList{
	/**
	 * Returns an array with the units' of measure id and name from the database.
	 *
	 * The total_pages and total_items arguments are necessary to return their respective values. If no page
	 * argument is passed or a cero is passed, all the details are returned.
	 * @param integer $page
	 * @param integer &$total_pages
	 * @param integer &$total_items
	 * @return array
	 */
	static public function getList($page = 0, &$total_pages = 0, &$total_items = 0){
		if($page !== 0)
			Number::validatePositiveInteger($page, 'Pagina inv&aacute;lida.');
			
		return UnitOfMeasureListDAM::getList($page, $total_pages, $total_items);
	}
}
?>