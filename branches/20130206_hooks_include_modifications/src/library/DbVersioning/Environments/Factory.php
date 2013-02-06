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
 * Main class that deals with DB versioning
 * operations
 * 
 * Implements the Factory design
 * 
 * @category CodePax
 * @subpackage DbVersioning
 * @copyright Copyright (c) 2012 Zitec COM srl, Romania
 * @license New BSD http://www.codepax.com/license.html
 * */
class CodePax_DbVersioning_Environments_Factory {
	
	/**
	 * Create the appropriate object by the
	 * supplied environment
	 * 
	 * @param string $_environment prod/stg/dev
	 * @return CodePax_DbVersioning_Environments_Abstract
	 * @throws CodePax_DbVersioning_Exception unsupported development environment
	 * */
	public static function factory($_environment) {
		switch (strtolower($_environment)) {
			case 'prod':
				return new CodePax_DbVersioning_Environments_Prod();
			case 'stg':
				return new CodePax_DbVersioning_Environments_Stg();
			case 'dev':
				return new CodePax_DbVersioning_Environments_Dev();
			default:
				throw new CodePax_DbVersioning_Exception('Unsupported development environment');
		}
	}
	
}