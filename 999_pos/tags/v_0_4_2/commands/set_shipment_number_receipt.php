<?php
/**
 * Library containing the SetShipmentNumberReceiptCommand class.
 * @package Command
 * @author Roberto Oliveros
 */

/**
 * Base class.
 */
require_once('commands/set_property_object.php');

/**
 * Defines functionality for setting the shipment number to a receipt.
 * @package Command
 * @author Roberto Oliveros
 */
class SetShipmentNumberReceiptCommand extends SetPropertyObjectCommand{
	/**
	 * Set the desired property on the object.
	 * @param variant $value
	 * @param variant $obj
	 */
	protected function setProperty($value, $obj){
		$obj->setShipmentNumber($value);
	}
}
?>