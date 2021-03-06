<?php
/**
 * Library containing the GetInvoicePageCommand class.
 * @package Command
 * @author Roberto Oliveros
 */

/**
 * Base class.
 */
require_once('commands/get_object_page.php');

/**
 * Returns the name of the template to use for displaying the invoice's details.
 * @package Command
 * @author Roberto Oliveros
 */
class GetInvoicePageCommand extends GetObjectPageCommand{
	/**
	 * Returns the name of the template to use.
	 * @return string
	 */
	protected function getTemplate(){
		return 'invoice_page_xml.tpl';
	}
	
	/**
	 * Returns the params to display for the object.
	 * @param variant $obj
	 */
	protected function getObjectParams($obj){
		return array('sub_total' => $obj->getSubTotal(),
				'discount_percentage' => $obj->getDiscountPercentage(),
				'discount' => $obj->getTotalDiscount(), 'total' => $obj->getTotal());
	}
}
?>