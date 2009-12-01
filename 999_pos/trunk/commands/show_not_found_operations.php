<?php
/**
 * Library containing the ShowNotFoundOperationsCommand class.
 * @package Command
 * @author Roberto Oliveros
 */

/**
 * Base class.
 */
require_once('commands/show_not_found.php');
/**
 * For displaying the results.
 */
require_once('presentation/page.php');

/**
 * Command to display the not found message on the operations site.
 * @package Command
 * @author Roberto Oliveros
 */
class ShowNotFoundOperationsCommand extends ShowNotFoundCommand{
	/**
	 * Displays the failure message to the user in html format.
	 * @param string $msg
	 */
	protected function displayFailure($msg){
		$back_trace = array('Inicio');
		Page::display(array('module_title' => OPERATIONS_TITLE,
				'main_menu' => 'main_menu_operations_html.tpl', 'back_trace' => $back_trace,
				'second_menu' => 'none', 'content' => 'none', 'notify' => '1', 'type' => 'error',
				'message' => $msg), 'site_html.tpl');
	}
}
?>