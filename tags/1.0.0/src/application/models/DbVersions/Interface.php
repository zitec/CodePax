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
 * Base type for db versions table models
 * 
 * Because every model will have its own dedicated
 * methods and because we need every model
 * on at least 2 SQL engines, we need a data
 * type for each model
 * 
 * @category CodePax
 * @subpackage Models
 * @copyright Copyright (c) 2012 Zitec COM srl, Romania
 * @license New BSD http://www.codepax.com/license.html
 * */
interface CodePax_DbVersions_Interface {
	
	/**
	 * Return the current DB version which can be
	 * either a change script or a baseline
	 * 
	 * The array keys are: version, date_added
	 * 
	 * @param int $_script_type can take values of 0,1,2
	 * @return array
	 * */
	public function getLatestVersion($_script_type);
	
	/**
	 * Adds a new version to database. The version can be
	 * a change script, a baseline or a data change script
	 * 
	 * @param string $_version_string represented like x.y.z
	 * @param int $_script_type can take values of 0,1,2
	 * @return void
	 * */
	public function addVersion($_version_string, $_script_type);
	
	/**
	 * Get all revisions and baselines registered
	 * in DB versioning table
	 * 
	 * Sample of the returned array
	 * Array (
	 * 	[0] => Array (
	 * 			[id] => 36
	 * 			[major] => 1
	 * 			[minor] => 1
	 * 			[point] => 0
	 * 			[baseline] => true
	 * 			[date_added] => 2010-08-25 11:07:47
	 * 		)
	 * )
	 * 
	 * @return array
	 * */
	public function getAll();
	
	/**
	 * Returns true if version is already registred
	 * 
	 * @param string $_version_string
	 * @param int $_script_type
	 * @return bool
	 */
	public function checkIsVersionRegistred($_version_string, $_script_type);
}