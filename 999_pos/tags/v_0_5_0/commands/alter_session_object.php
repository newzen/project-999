<?php
/**
 * Library containing the AlterSessionObjectCommand base class.
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
 * Defines common functionality for the alter session object derived classes.
 * @package Command
 * @author Roberto Oliveros
 */
abstract class AlterSessionObjectCommand extends Command{
	/**
	 * Execute the command.
	 * @param Request $request
	 * @param SessionHelper $helper
	 */
	public function execute(Request $request, SessionHelper $helper){
		$user = $helper->getUser();
		
		if($this->testRights($user)){
			try{
				$obj = $helper->getObject((int)$request->getProperty('key'));
				$this->alterObject($obj);
			} catch(Exception $e){
				$msg = $e->getMessage();
				Page::display(array('message' => $msg), 'error_xml.tpl');
				return;
			}
			
			Page::display(array(), 'success_xml.tpl');
		}
		else{
			$msg = 'Usuario no cuenta con los suficientes privilegios.';
			Page::display(array('message' => $msg), 'error_xml.tpl');
		}
	}
	
	/**
	 * Tests if the user has the right to alter the object.
	 * @param UserAccount $user
	 * @return boolean
	 */
	abstract protected function testRights(UserAccount $user);
	
	/**
	 * Alters the desired object.
	 * @param variant $obj
	 */
	abstract protected function alterObject($obj);
}
?>