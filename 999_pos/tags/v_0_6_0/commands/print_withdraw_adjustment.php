<?php
/**
 * Library containing the PrintWithdrawAdjustmentCommand class.
 * @package Command
 * @author Roberto Oliveros
 */

/**
 * Base class.
 */
require_once('commands/print_object.php');
/**
 * For displaying the results.
 */
require_once('presentation/page.php');

/**
 * Displays the withdraw adjustment data for printing purposes.
 * @package Command
 * @author Roberto Oliveros
 */
class PrintWithdrawAdjustmentCommand extends PrintObjectCommand{
	/**
	 * Display the object.
	 * @param variant $obj
	 * @param array $details
	 */
	protected function displayObject($obj, $details){		
		$user = $obj->getUser();
		
		Page::display(array('main_data' => 'adjustment_main_data_html.tpl',
				'document_name' => 'Vale de Salida', 'status' => $obj->getStatus(), 'id' => $obj->getId(),
				'username' => $user->getUserName(), 'date_time' => $obj->getDateTime(),
				'reason' => $obj->getReason(), 'total' => $obj->getTotal(), 'total_items' => count($details),
				'details' => $details), 'document_print_html.tpl');
	}
}
?>