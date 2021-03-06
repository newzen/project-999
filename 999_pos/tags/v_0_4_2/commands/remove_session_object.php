<?php
/**
 * Library containing the RemoveSessionObjectCommand class.
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
 * Defines functionality for removing an object from the session.
 * @package Command
 * @author Roberto Oliveros
 */
class RemoveSessionObjectCommand extends Command{
	/**
	 * Execute the command.
	 * @param Request $request
	 * @param SessionHelper $helper
	 */
	public function execute(Request $request, SessionHelper $helper){
		// Removed previos session object.
		$helper->removeObject((int)$request->getProperty('key'));
		Page::display(array(), 'success_xml.tpl');
	}
}
?>