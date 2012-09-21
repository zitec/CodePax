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
 * MySQL engine. It uses the mysql utility found on
 * most of the Unix/Windows system
 * 
 * @category CodePax
 * @subpackage DbVersioning
 * @copyright Copyright (c) 2012 Zitec COM srl, Romania
 * @license New BSD http://www.codepax.com/license.html
 * */
class CodePax_DbVersioning_SqlEngines_MySql extends CodePax_DbVersioning_SqlEngines_Abstract {

	/**
	 * Executes the given SQL file
	 * 
	 * @param string $_sql_file path to file to be executed
	 * @return void
	 * */
	public function executeChangeScript($_sql_file) {
		$command_pattern = '%s --user=%s --password=%s --database=%s --host=%s < %s';
		$shell_command = sprintf($command_pattern, PATH_TO_SQL_BIN, DB_USER,
			DB_PASS, DB_NAME, DB_HOST, $_sql_file);
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
        $backtick = $this->is_windows ? '`' : '\`';
        $database_specifier = $backtick . '%s' . $backtick;
        
        $drop_command_pattern = '%s --user=%s --password=%s --host=%s --execute="DROP DATABASE ' . $database_specifier . '"';
        $create_command_pattern = '%s --user=%s --password=%s --host=%s --execute="CREATE DATABASE ' . $database_specifier . '"';
        
		// first drop the existing DB		
		$drop_shell_command = sprintf($drop_command_pattern, PATH_TO_SQL_BIN, DB_USER, DB_PASS,
			DB_HOST, DB_NAME);
		$this->runCommand($drop_shell_command);	
		
		// then recreate it from baseline		
		$create_shell_command = sprintf($create_command_pattern, PATH_TO_SQL_BIN, DB_USER, DB_PASS,
			DB_HOST, DB_NAME);
		$this->runCommand($create_shell_command);
	}
	
	/**
	 * Load and apply a baseline script
	 * 
	 * Just an alias for @see executeChangeScript
	 * Maybe extra logic would be needed at a later
	 * time to apply a baseline script and that's
	 * why a separate method was created
	 * 
	 * @param string $_baseline_file path to baseline
	 * @return void
	 * */
	public function executeBaseline($_baseline_file) {
		$this->executeChangeScript($_baseline_file);
	}
	
	/**
	 * Will generate the baseline into baselines
	 * directory
	 * 
	 * @see CodePax_DbVersioning_Files_Manager for actual location
	 * of the baselines directory
	 * @see http://wiki.zitec.ro/MySQL_issues#Clean_MySQL_Export
	 * 
	 * @param string $_target_sql_file
	 * @return void
	 * */
	public function generateBaseline($_target_sql_file) {
		if ($this->is_windows === true) {
			$command_pattern = '%s --user=%s --password=%s --host=%s --routines --no-data --triggers %s --result-file=%s';
			$shell_command = sprintf($command_pattern, PATH_TO_SQL_DUMP_BIN, DB_USER, DB_PASS,
				DB_HOST, DB_NAME, $_target_sql_file);
		}
		else {// on Unix, also clean-up the dump
			$command_pattern = "%s --user=%s --password=%s --host=%s --routines --no-data --triggers %s | 
				sed 's/`%s`\.//g' | sed 's/\/\*\![0-9]* DEFINER=[^*]*\*\///g' | tee %s";
			$shell_command = sprintf($command_pattern, PATH_TO_SQL_DUMP_BIN, DB_USER, DB_PASS,
				DB_HOST, DB_NAME, DB_NAME, $_target_sql_file);
		}
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
	protected function generateSqlTestDataFile( $_target_sql_file ) {
		$ignore_pattern = $this->getIgnoreTablesOption( '--ignore-table=%s.%s' );
		$command_pattern = '%s --user=%s --password=%s --host=%s --skip-triggers --skip-disable-keys ';
		$command_pattern .= '--no-create-info --complete-insert %s %s --result-file=%s';
		$shell_command = sprintf( $command_pattern, PATH_TO_SQL_DUMP_BIN, DB_USER, DB_PASS,
						DB_HOST, $ignore_pattern, DB_NAME, $_target_sql_file );
		$this->runCommand( $shell_command );
	}
	
	/**
	 * Load and apply the test data on the
	 * current database
	 * 
	 * Just an alias for @see executeChangeScript
	 * Maybe extra logic would be needed at a later
	 * time to load the test data and that's
	 * why a separate method was created
	 * 
	 * @param string $_test_data_file path to test data
	 * @return void
	 * */
	protected function loadSqlTestDataFile($_sql_file) {
		$this->executeChangeScript($_sql_file);
	}
}