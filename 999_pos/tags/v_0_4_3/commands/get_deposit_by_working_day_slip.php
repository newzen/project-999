<?php
/**
 * Library containing the GetDepositByWorkingDaySlipCommand class.
 * @package Command
 * @author Roberto Oliveros
 */

/**
 * Base class.
 */
require_once('commands/get_object.php');
/**
 * Library with the deposit class.
 */
require_once('business/cash.php');
/**
 * For obtaining the bank list.
 */
require_once('business/list.php');

/**
 * Displays the deposit form in idle mode.
 * @package Command
 * @author Roberto Oliveros
 */
class GetDepositByWorkingDaySlipCommand extends GetObjectCommand{
	/**
	 * Gets the desired object.
	 * @return variant
	 */
	protected function getObject(){
		$working_day = $this->_mRequest->getProperty('slip_working_day'); 
		$bank_id = $this->_mRequest->getProperty('bank_id');
		
		if($working_day == '')
			throw new Exception('Ingrese la jornada.');
		if($bank_id == '')
			throw new Exception('Seleccione un banco.');
		
		$bank = Bank::getInstance((int)$bank_id);
		if(is_null($bank))
			throw new Exception('Banco no existe.');
		
		$id = Deposit::getDepositIdByWorkingDaySlip(
				WorkingDay::getInstance($working_day), $bank,
				$this->_mRequest->getProperty('slip_number'));
				
		if($id != 0)
			return Deposit::getInstance($id);
		else
			throw new Exception('Deposito no existe en esa jornada o esta anulado.');
	}
	
	/**
	 * Display failure in case the object does not exists or an error occurs.
	 * @param string $msg
	 */
	protected function displayFailure($msg){
		$back_trace = array('Inicio', 'Caja', 'Depositos');
		
		$working_day = $this->_mRequest->getProperty('slip_working_day');
		$bank_id = $this->_mRequest->getProperty('bank_id');
		$slip_number = $this->_mRequest->getProperty('slip_number');
		
		// For displaying the first blank item.
		$list = array(array());
		$list = array_merge($list, BankList::getList());
		
		Page::display(array('module_title' => POS_ADMIN_TITLE, 'main_menu' => 'main_menu_pos_admin_html.tpl',
				'back_trace' => $back_trace, 'second_menu' => 'cash_register_menu_html.tpl',
				'content' => 'deposit_menu_html.tpl', 'notify' => '1', 'bank_list' => $list,
				'type' => 'error', 'message' => $msg, 'slip_working_day' => $working_day,
				'bank_id' => $bank_id, 'slip_number' => $slip_number), 'site_html.tpl');
	}
	
	/**
	 * Display the form for the object.
	 * @param string $key
	 * @param variant $obj
	 * @param array $backQuery
	 */
	protected function displayObject($key, $obj, $backQuery){
		$back_trace = array('Inicio', 'Caja', 'Depositos');
		
		// Build the back link.
		$back_link = (is_null($backQuery)) ? 'index.php?cmd=show_deposit_menu' :
				'index.php?cmd=' . $backQuery['cmd'] . '&page=' . $backQuery['page'] . '&start_date=' .
				$this->_mRequest->getProperty('start_date') . '&end_date=' .
				$this->_mRequest->getProperty('end_date');
		
		$working_day = WorkingDay::getInstance($this->_mRequest->getProperty('slip_working_day'));
		$cash_register = $obj->getCashRegister();
		$shift = $cash_register->getShift();
		$user = $obj->getUser();
		$bank_account = $obj->getBankAccount();
		$bank = $bank_account->getBank();
		
		Page::display(array('module_title' => POS_ADMIN_TITLE, 'main_menu' => 'back_link.tpl',
				'back_trace' => $back_trace, 'second_menu' => 'none',
				'content' => 'deposit_pos_admin_form_html.tpl', 'cash_register_id' => $cash_register->getId(),
				'date' => $working_day->getDate(), 'shift' => $shift->getName() . ', ' . $shift->getTimeTable(),
				'cash_register_status' => (int)$cash_register->isOpen(),
				'status' => $obj->getStatus(),'key' => $key, 'back_link' => $back_link,
				'id' => $obj->getId(), 'date_time' => $obj->getDateTime(), 'username' => $user->getUserName(),
				'slip_number' => $obj->getNumber(),
				'bank_account' => $bank_account->getNumber() . ', ' . $bank_account->getHolderName(),
				'bank' => $bank->getName()), 'site_html.tpl');
	}
}
?>