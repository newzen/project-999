<?php
/**
 * Library for accessing the database for multiple purposes.
 * @package VariousDAM
 * @author Roberto Oliveros
 */

/**
 * For accessing the database.
 */
require_once('data/database_handler.php');

/**
 * Class to perform a closing event in the system's data.
 * @package VariousDAM
 * @author Roberto Oliveros
 */
class ClosingEventDAM{
	/**
	 * Makes a closing on the system's data leaving only the provided months of information.
	 *
	 * @param integer $months
	 */
	static public function apply($months){
		// Code here...
	}
}


/**
 * Class for accessing database information regarding the company.
 * @package VariousDAM
 * @author Roberto Oliveros
 */
class CompanyDAM{
	/**
	 * Returns an instace of the company with database information.
	 *
	 * @return Company
	 */
	static public function getInstance(){
		$sql = 'CALL company_get()';
		$result = DatabaseHandler::getRow($sql);
		return new Company($result['nit'], $result['name']);
	}
	
	/**
	 * Updates the company's data in the database.
	 *
	 * @param Company $obj
	 */
	static public function update(Company $obj){
		$sql = 'CALL company_update(:nit, :name)';
		$params = array(':nit' => $obj->getNit(), ':name' => $obj->getName());
		DatabaseHandler::execute($sql, $params);
	}
}


/**
 * Utility class for accessing database information regarding the change of prices report.
 * @package VariousDAM
 * @author Roberto Oliveros
 */
class ChangePriceListDAM{
	/**
	 * Returns an array with data regarding price changes that ocurred between the provided dates.
	 *
	 * The array's fields are logged_date, user_account_username, bar_code, manufacturer, name, packaging,
	 * last_price and new_price. If no page argument or cero is passed all the details are returned. The
	 * totalPages and totalItems arguments are necessary to return their respective values.
	 * @param string $firstDate
	 * @param string $lastDate
	 * @param integer &$totalPages
	 * @param integer &$totalItems
	 * @param integer $page
	 * @return array
	 */
	static public function getList($firstDate, $lastDate, &$totalPages, &$totalItems, $page){
		$sql = 'CALL change_price_log_count(:first_date, :last_date)';
		$params = array(':first_date' => Date::dbFormat($firstDate), ':last_date' => Date::dbFormat($lastDate));
		$totalItems = DatabaseHandler::getOne($sql, $params);
		
		$totalPages = ceil($totalItems / ITEMS_PER_PAGE);
		
		if($page > 0)
			$params = array_merge($params, 
					array(':start_item' => ($page - 1) * ITEMS_PER_PAGE, 'items_per_page' => ITEMS_PER_PAGE));
		else
			$params = array_merge($params, array(':start_item' => 0, ':items_per_page' => $totalItems));
		
		$sql = 'CALL change_price_log_get(:first_date, :last_date, :start_item, :items_per_page)';
		return DatabaseHandler::getAll($sql, $params);
	}
}


/**
 * Utility class for obtaing the discount report data from the database.
 * @package VariousDAM
 * @author Roberto Oliveros
 */
class DiscountListDAM{
	/**
	 * Retuns an array with the report information.
	 *
	 * The array's fields are date, username, serial_number, number, subtotal, percentage, amount and total.
	 * If no page argument or cero is passed all the details are returned. The total_pages and total_items
	 * arguments are necessary to return their respective values. Date format: 'dd/mm/yyyy'.
	 * @param string $firstDate
	 * @param string $lastDate
	 * @param integer &$total_pages
	 * @param integer &$total_items
	 * @param integer $page
	 * @return array
	 */
	static public function getList($firstDate, $lastDate, &$total_pages, &$total_items, $page){
		$total_pages = 1;
		$total_items = 2;
		return array(array('date' => '01/05/2009', 'username' => 'roboli', 'serial_number' => 'A021',
				'number' => '4359', 'subtotal' => 124.83, 'percentage' => 12.00, 'total' => 100.83),
				array('date' => '05/05/2009', 'username' => 'roboli', 'serial_number' => 'A021',
				'number' => '1351', 'subtotal' => 245.83, 'percentage' => 12.00, 'total' => 200.83));
	}
}
?>