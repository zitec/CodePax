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
 * Class to handle DB versioning operations for the
 * SQL Server engine. It uses the sqlcmd utility that
 * gets installed alongside SQL Server. Windows only
 *
 * @category CodePax
 * @subpackage DbVersioning
 * @copyright Copyright (c) 2012 Zitec COM srl, Romania
 * @license New BSD http://www.codepax.com/license.html
 * */
class CodePax_DbVersioning_SqlEngines_SqlSrv extends CodePax_DbVersioning_SqlEngines_Abstract {
	
	const TARGETED_SQL_VERSION = '2008';
	    
    const COMMAND_SET_NOCOUNT_ON = 'SET NOCOUNT ON';
    
	/**
	 * Executes the given SQL file
	 *
	 * @param string $_sql_file path to file to be executed
	 * @return void
	 * */
	public function executeChangeScript($_sql_file) {
		$command_pattern = '%s -U %s -P %s -d %s -S %s -i %s 2>&1';
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
		// first drop the existing DB
		$drop_command_pattern = '%s -U %s -P %s -S %s -Q "ALTER DATABASE [%s] SET SINGLE_USER WITH ROLLBACK IMMEDIATE;DROP DATABASE [%s];" 2>&1';
		$drop_shell_command = sprintf($drop_command_pattern, PATH_TO_SQL_BIN, DB_USER, DB_PASS,
			DB_HOST, DB_NAME, DB_NAME);

		$this->runCommand($drop_shell_command);

		// then recreate it from baseline
		$create_command_pattern = '%s -U %s -P %s -S %s -Q "CREATE DATABASE [%s]; ALTER DATABASE [%s] SET MULTI_USER;" 2>&1';
		$create_shell_command = sprintf($create_command_pattern, PATH_TO_SQL_BIN, DB_USER, DB_PASS,
			DB_HOST, DB_NAME, DB_NAME);
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
	 * 
	 * @param string $_target_sql_file
	 * @return void
	 * */
	public function generateBaseline($_target_sql_file) {
		$command_pattern = '"%s" script -U %s -P %s -S %s -d %s -schemaonly -targetserver %s -f %s 2>&1';
		$shell_command = sprintf($command_pattern, PATH_TO_SQL_DUMP_BIN, DB_USER, DB_PASS,
			DB_HOST, DB_NAME, self::TARGETED_SQL_VERSION, $_target_sql_file);
		
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
		$this->dropTablesToIgnore();

		$command_pattern = '"%s" script -U %s -P %s -S %s -d %s -dataonly -targetserver %s -f %s 2>&1';
		$shell_command = sprintf( $command_pattern, PATH_TO_SQL_DUMP_BIN, DB_USER, DB_PASS,
						DB_HOST, DB_NAME, self::TARGETED_SQL_VERSION,$_target_sql_file );
		
		$this->runCommand( $shell_command );
        
        $this->prependNoCountToTestDataFile($_target_sql_file);
	}

    protected function prependNoCountToTestDataFile($_target_sql_file) { 	
        $first_line = self::COMMAND_SET_NOCOUNT_ON;
        
        if(file_exists($_target_sql_file)) {
            $rhandle = fopen($_target_sql_file, 'r');
            if ($rhandle !== false) {
                $temp_write_file = $_target_sql_file . '.temp';
                $whandle = fopen($temp_write_file, 'w');
                
                if ($whandle !== false) {
                    // the source sql file uses UCS-2 Little Endian encoding
                    mb_internal_encoding('UCS-2');

                    while (!feof($rhandle)) {
                        $read_pos = ftell($rhandle);
                        $line = fgets($rhandle);
                        if ($line !== false) {
                            // skip the first line for the moment
                            if ($read_pos === 0) {
                                fwrite($whandle, $first_line . PHP_EOL);
                            } else {
                                $new_line = mb_convert_encoding($line, 'UTF-8', 'UCS-2');
                                fwrite($whandle, $new_line);
                            }                            
                        }
                    }

                    // done reading
                    fclose($rhandle);
                    // done writing
                    fclose($whandle);     
                    
                    // delete original read file
                    unlink($_target_sql_file);
                    // rename temp file to target file name
                    rename($temp_write_file, $_target_sql_file);
                } else {
                    $msg = "Cannot open temp data test file for writing!";
                    throw new CodePax_DbVersioning_Exception($msg);
                }
            } else {
                $msg = "The test data file cannot be open!";
                throw new CodePax_DbVersioning_Exception($msg);
            }	            
        } else{
            $msg = "The test data file does not exist. Check the archive!";
            throw new CodePax_DbVersioning_Exception($msg);
        }	
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

	/**
	 * Checks whether the supplied string contains
	 * the string "Msg". The Sql Server returns this
	 * string when an error has occured
	 *
	 * @return bool
	 * */
	protected function isError($_string) {
		// no error
		if (strpos($_string, 'Msg') === false) {
			return false;
		}
		else {
			return true;
		}
	}

	/**
	 * Drop tables whose contents should be ignored
	 * when preserving table data
	 *
	 * @return void
	 */
	private function dropTablesToIgnore(){
		$this->dropForeignKeyConstraints();
		
		$drop_command_pattern = '%s -U %s -P %s -S %s -d %s -Q "DROP table [%s];" 2>&1';
		foreach($this->getTablesToIgnore() as $table){
			$drop_shell_command = sprintf($drop_command_pattern, PATH_TO_SQL_BIN, DB_USER, DB_PASS,
				DB_HOST, DB_NAME, $table);
			
			$this->runCommand($drop_shell_command);
		}
	}
	
	/**
	 * Drop foreign key constraints for all 
	 * tables that should be ignored
	 * 
	 * @return void
	 */
	private function dropForeignKeyConstraints(){
		$drop_command_pattern = '%s -U %s -P %s -S %s -d %s -Q "ALTER DATABASE [%s] SET SINGLE_USER WITH ROLLBACK IMMEDIATE; DECLARE @statement nvarchar(500);SELECT @statement = \'ALTER TABLE \' + OBJECT_NAME(parent_object_id) + \' DROP CONSTRAINT \' + name FROM sys.foreign_keys WHERE referenced_object_id = object_id(\'%s\'); EXECUTE sp_executesql @statement; ALTER DATABASE [%s] SET MULTI_USER;" 2>&1';
		
		foreach($this->getTablesToIgnore() as $table){
			$drop_shell_command = sprintf($drop_command_pattern, PATH_TO_SQL_BIN, DB_USER, DB_PASS,
				DB_HOST, DB_NAME, DB_NAME, $table, DB_NAME);
			
			$this->runCommand($drop_shell_command);
		}
	}
}