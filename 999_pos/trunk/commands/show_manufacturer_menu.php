<?php
/**
 * Library containing the ShowManufacturerMenu command.
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
 * Command to display the manufacturers menu.
 * @package Command
 * @author Roberto Oliveros
 */
class ShowManufacturerMenuCommand extends Command{
	/**
	 * Execute the command.
	 * @param Request $request
	 * @param SessionHelper $helper
	 */
	public function execute(Request $request, SessionHelper $helper){
		$back_trace = array('Inicio', 'Mantenimiento', 'Casas');
		Page::display(array('module_title' => OPERATIONS_TITLE, 'main_menu' => 'main_menu_operations_html.tpl',
				'back_trace' => $back_trace, 'second_menu' => 'maintenance_menu_operations_html.tpl',
				'content' => 'object_menu_html.tpl', 'notify' => '0',
				'create_link' => 'index.php?cmd=create_manufacturer',
				'show_list_link' => 'index.php?cmd=show_manufacturer_list&page=1'), 'site_html.tpl');
	}
}
?>