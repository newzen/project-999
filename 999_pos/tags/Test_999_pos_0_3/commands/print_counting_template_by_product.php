<?php
/**
 * Library containing the PrintCountingTemplateByProductCommand class.
 * @package Command
 * @author Roberto Oliveros
 */

/**
 * Base class.
 */
require_once('commands/print_counting_template.php');
/**
 * For displaying the results.
 */
require_once('presentation/page.php');
/**
 * For obtaining the results.
 */
require_once('business/inventory.php');

/**
 * Command to display a counting template ordered by product.
 * @package Command
 * @author Roberto Oliveros
 */
class PrintCountingTemplateByProductCommand extends PrintCountingTemplateCommand{
	/**
	 * Return an array consisting on a range of products' data for displaying on the template as details.
	 * @param string $first
	 * @param string $last
	 * @return array
	 */
	protected function getRangeResults($first, $last){
		return CountingTemplate::getDataByProduct($first, $last);
	}
	
	/**
	 * Returns the name of the type of order that was used on the details.
	 * @return string
	 */
	protected function getOrderByType(){
		return 'Nombre';
	}
}
?>