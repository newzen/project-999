<?php
/**
 * Library containing the GetDocumentPageCommand class.
 * @package Command
 * @author Roberto Oliveros
 */

/**
 * Base class.
 */
require_once('commands/get_object_page.php');

/**
 * Returns the name of the template to use for displaying the document's details.
 * @package Command
 * @author Roberto Oliveros
 */
class GetDocumentPageCommand extends GetObjectPageCommand{
	/**
	 * Returns the name of the template to use.
	 * @return string
	 */
	protected function getTemplate(){
		return 'document_page_xml.tpl';
	}
	
	/**
	 * Returns the params to display for the object.
	 * @param variant $obj
	 */
	protected function getObjectParams($obj){
		return array('total' => $obj->getTotal());
	}
}
?>