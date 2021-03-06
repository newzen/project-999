<?php
/**
 * Point of departure for the 999 admin system.
 * @package 999_pos
 * @author Roberto Oliveros
 */

/**
 * Load the system configuration.
 */
require_once('../include/config.php');
/**
 * Load the autoload function.
 */
require_once('include/auto_load_libs.php');
/**
 * Load the controller library.
 */
require_once('presentation/controller.php');

// Activate the right session object. 
$helper = AdminSession::getInstance();
ActiveSession::setHelper($helper);

// Execute the admin system.
AdminController::run();
?>