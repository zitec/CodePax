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
 * To be used by all driver/engine specific
 * concrete classes
 * 
 * @category CodePax
 * @subpackage Models
 * @copyright Copyright (c) 2012 Zitec COM srl, Romania
 * @license New BSD http://www.codepax.com/license.html
 * */
class CodePax_Sql {
	
	/**
	 * @var PDO
	 * */
	protected $db;
	
	/**
	 * Factory the PDO object and runs the setupTable
	 * command
	 * 
	 * @return void
	 * */
	public function __construct() {
		switch (DB_ENGINE) {
			case 'mysql':
				$this->db = new PDO(
					sprintf('mysql:host=%s;dbname=%s', DB_HOST, DB_NAME), 
					DB_USER, DB_PASS);
				break;
			case 'pgsql':
				$this->db = new PDO(
					sprintf('pgsql:host=%s;port=5432;dbname=%s', DB_HOST, DB_NAME), 
					DB_USER, DB_PASS);
				break;
			case 'sqlsrv':
				$this->db = sqlsrv_connect(DB_HOST, array("Database" => DB_NAME, 'UID' => DB_USER, 'PWD' => DB_PASS, 'ConnectionPooling' => 0));
				break;
		}
	}
}