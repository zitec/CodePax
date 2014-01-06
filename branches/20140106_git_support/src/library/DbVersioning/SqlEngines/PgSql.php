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
 * Class to handle DB versioning operations for
 * PostgreSQL engine. It uses the psql utility found on
 * most of the Unix/Windows system
 * 
 * @category CodePax
 * @subpackage DbVersioning
 * @copyright Copyright (c) 2012 Zitec COM srl, Romania
 * @license New BSD http://www.codepax.com/license.html
 * */
class CodePax_DbVersioning_SqlEngines_PgSql extends CodePax_DbVersioning_SqlEngines_Abstract {
	
	/**
	 * Executes the given SQL file
	 * 
	 * @param string $_sql_file path to file to be executed
	 * @return void
	 * */
	public function executeChangeScript($_sql_file) {
		$command_pattern = '%s -h %s -U %s -f %s %s ';
		$shell_command = sprintf($command_pattern, PATH_TO_SQL_BIN,
			DB_HOST, DB_USER, $_sql_file, DB_NAME);
		$this->runCommand($shell_command);
	}
	
	/**
	 * Drop and create database. Will be used only
	 * on DEV environment where more than one
	 * developers are applying change scripts for
	 * different development branches
	 * 
	 * @return void     
	 * */
	public function dropAndCreateDb() {
        // TODO: on Windows platforms the following commands has to be 
        // properly tested
        $postgres_server = PATH_TO_POSTGRES_SERVICE;
        
        if (!$this->is_windows) {
            $postgres_server = 'sudo -u root ' . $postgres_server;
        }
        
		// stop the DB server
		$this->runCommand($postgres_server . ' stop');
		
		// start the DB server
		$this->runCommand($postgres_server . ' start');
		
		// make sure the server starts
		sleep(2);
		
		// drop the existing DB
		$drop_command_pattern = '%s -h %s -U %s -c "DROP DATABASE %s" ';
		$drop_shell_command = sprintf($drop_command_pattern, PATH_TO_SQL_BIN, DB_HOST, DB_USER, DB_NAME);
		$this->runCommand($drop_shell_command);	
		
		// recreate it from baseline
		$create_command_pattern = '%s -h %s -U %s -c "CREATE DATABASE %s WITH OWNER = %s ENCODING = \'UTF8\'" ';
		$create_shell_command = sprintf($create_command_pattern, PATH_TO_SQL_BIN, DB_HOST,
			DB_USER, DB_NAME, DB_USER);
		$this->runCommand($create_shell_command);
	}
	
	/**
	 * Load and apply a baseline script
	 * 
	 * Because psql utility outputs the result and stops,
	 * the base command will have no longer the output()
	 * redirect directive to allow other scripts to run
	 * after it. This is required on DEV env
	 * 
	 * @param string $_baseline_file path to baseline
	 * @return void
	 * */
	public function executeBaseline($_baseline_file) {
		$command_pattern = '%s -h %s -U %s -f %s %s';
		$shell_command = sprintf($command_pattern, PATH_TO_SQL_BIN,
			DB_HOST, DB_USER, $_baseline_file, DB_NAME);
		$this->runCommand($shell_command);
	}
	
	/**
	 * Will generate the baseline into baselines
	 * directory
	 * 
	 * @see CodePax_DbVersioning_Files_Manager for actual location
	 * of the baselines directory
	 * 
	 * @param string $_target_sql_file
	 * @return void
	 * */
	public function generateBaseline($_target_sql_file) {
		$command_pattern = '%s -h %s -U %s -s -f %s %s ';
		$shell_command = sprintf($command_pattern, PATH_TO_SQL_DUMP_BIN,
			DB_HOST, DB_USER, $_target_sql_file, DB_NAME);
		$this->runCommand($shell_command);
	}
	
	/**
	 * Will generate the test data file without the
	 * versioning table
	 * 
	 * The resulted file will contain INSERT statments
	 * only
	 * 
	 * @see CodePax_DbVersioning_Files_Manager for actual location
	 * of the test_data directory
	 * 
	 * @param string $_target_sql_file
	 * @return void
	 * */
	protected function generateSqlTestDataFile($_target_sql_file) {
		$ignore_pattern = $this->getIgnoreTablesOption('-T %s', false);
		$command_pattern = '%s -h %s %s -U %s -a -F c -f %s %s ';
		$shell_command = sprintf($command_pattern, PATH_TO_SQL_DUMP_BIN,
			DB_HOST, $ignore_pattern, DB_USER, $_target_sql_file, DB_NAME);
		$this->runCommand($shell_command);
	}
	
	/**
	 * Load and apply the test data on the
	 * current database
	 * 
	 * This one uses pg_restore utility which in most
	 * of the cases lies next to pg_dump. That is why
	 * we will simply replace pg_dump with pg_restore
	 * in the command pattern
	 * 
	 * @param string $_test_data_file path to test data
	 * @return void
	 * */
	protected function loadSqlTestDataFile($_sql_file) {
		$pg_restore = str_replace('_dump', '_restore', PATH_TO_SQL_DUMP_BIN);
		$command_pattern = '%s -h %s -U %s -a -d %s -O --disable-triggers %s';
		$shell_command = sprintf($command_pattern, $pg_restore, DB_HOST,
			DB_USER, DB_NAME, $_sql_file);
		$this->runCommand($shell_command);
	}
}