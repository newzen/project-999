<?php
/**
 * Library containing the ShowUnitOfMeasureMenuCommand class.
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
 * Command to display the unit of measure menu.
 * @package Command
 * @author Roberto Oliveros
 */
class ShowUnitOfMeasureMenuCommand extends Command{
	/**
	 * Execute the command.
	 * @param Request $request
	 * @param SessionHelper $helper
	 */
	public function execute(Request $request, SessionHelper $helper){
		$back_trace = array('Inicio', 'Mantenimiento', 'Unidades de Medida');
		Page::display(array('module_title' => INVENTORY_TITLE, 'main_menu' => 'main_menu_inventory_html.tpl',
				'back_trace' => $back_trace, 'second_menu' => 'maintenance_menu_inventory_html.tpl',
				'content' => 'object_menu_html.tpl', 'notify' => '0',
				'create_link' => 'index.php?cmd=create_unit_of_measure',
				'show_list_link' => 'index.php?cmd=show_unit_of_measure_list&page=1'), 'site_html.tpl');
	}
}
?>