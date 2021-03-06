<?php
/**
 * Library containing the ShowTaskMenuAdminCommand class.
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
 * Command to display the task menu on the Administration site.
 * @package Command
 * @author Roberto Oliveros
 */
class ShowTaskMenuAdminCommand extends Command{
	/**
	 * Execute the command.
	 * @param Request $request
	 * @param SessionHelper $helper
	 */
	public function execute(Request $request, SessionHelper $helper){
		$back_trace = array('Inicio', 'Herramientas', 'Tareas');
		Page::display(array('module_title' => ADMIN_TITLE, 'main_menu' => 'main_menu_admin_html.tpl',
				'back_trace' => $back_trace, 'second_menu' => 'tools_menu_admin_html.tpl',
				'content' => 'task_menu_admin_html.tpl', 'notify' => '0'), 'site_html.tpl');
	}
}
?>