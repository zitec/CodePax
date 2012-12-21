<?php
/**
 * CodePax
 * 
 * LICENSE
 *
 * This source file is subject to the New BSD license that is bundled
 * with this package in the file LICENSE
 * It is also available through the world-wide-web at this URL:
 * http://www.codepax.com/license.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@codepax.com so we can send you a copy immediately.
 * */

/**
 * Utility script to generate the baseline file. This script
 * is intended to be run on staging machine.
 * 
 * @category CodePax
 * @package Utils
 * @copyright Copyright (c) 2012 Zitec COM srl, Romania
 * @license New BSD http://www.codepax.com/license.html
 */
require '../src/application/bootstrap.php';

if (APPLICATION_ENVIRONMENT == 'stg') {
	try {
		$db_versioning_handler = CodePax_DbVersioning_Environments_Factory::factory(APPLICATION_ENVIRONMENT);
		$db_versioning_handler->generateBaseline();
	}
	catch (CodePax_DbVersioning_Exception $dbv_e) {
		echo 'An error ocurred: ' . $dbv_e->getMessage();
	}
	catch (Exception $e) {
		echo 'Generic error: ' . $e->getMessage();
	}
}
else {
	echo 'You are not running on STG. The ' . APPLICATION_ENVIRONMENT . ' environment was detected';
}