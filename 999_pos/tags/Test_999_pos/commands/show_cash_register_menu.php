<?php
/**
 * Library containing the ShowCashRegisterMenuCommand class.
 * @package Command
 * @author Roberto Oliveros
 */

/**
 * Base class.
 */
require_once('presentation/command.php');
/**
 * For displaying the results.
 */
require_once('presentation/page.php');

/**
 * Command to display the cash register menu on the POS Administration site.
 * @package Command
 * @author Roberto Oliveros
 */
class ShowCashRegisterMenuCommand extends Command{
	/**
	 * Execute the command.
	 * @param Request $request
	 * @param SessionHelper $helper
	 */
	public function execute(Request $request, SessionHelper $helper){
		$back_trace = array('Inicio', 'Caja');
		Page::display(array('module_title' => POS_ADMIN_TITLE, 'main_menu' => 'main_menu_pos_admin_html.tpl',
				'back_trace' => $back_trace, 'second_menu' => 'cash_register_menu_html.tpl',
				'content' => 'none', 'notify' => '0'), 'site_html.tpl');
	}
}
?>