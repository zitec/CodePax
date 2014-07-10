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
 * Base class used by Engine specific classes
 * to handle versioning operation
 *
 * @category CodePax
 * @subpackage DbVersioning
 * @copyright Copyright (c) 2012 Zitec COM srl, Romania
 * @license New BSD http://www.codepax.com/license.html
 * */
abstract class CodePax_DbVersioning_SqlEngines_Abstract {

    const COMMAND_OUTPUT_FILE_FORMAT = 'CodePax-%s-%s.log';
    const COMMAND_OUTPUT_LINES = 10;

    /**
     * Flag to indicate the platform the app
     * is running on
     * */
    protected $is_windows = false;

    /**
     * Flag to indicate if we should use the log dir
     * for command output or not. This will be true
     * if the directory is defined and is writeable
     *
     */
    protected $use_logs_dir = false;

    /**
     * Set the OS the app is running on
     *
     * @return void
     * */
    public function __construct()
    {
        // WIN detected
        if (strpos(strtolower(PHP_OS), 'win') !== false) {
            $this->is_windows = true;
        }

        if (defined('LOGS_DIR') && is_writable(LOGS_DIR)) {
            $this->use_logs_dir = true;
        }
    }

    /**
     * Returns the name of the file used to log the command output.
     *
     * @return string The file name
     */
    public function getOutputFileName()
    {
        return sprintf(self::COMMAND_OUTPUT_FILE_FORMAT, str_replace(" ", "_", PROJECT_NAME), APPLICATION_ENVIRONMENT);
    }

    /**
     * Post format shell command string. This is
     * mainly used to make the command to run on
     * Windows systems.
     *
     * Path to cmd.exe is prepended to command string
     * "C:\path\to\cmd.exe". In the end, a string like
     * this will result "C:\path\to\cmd.exe /c the\actual\command\"
     *
     * @param string $_shell_command
     * */
    protected function postFormatString($_shell_command)
    {
        //redirect stdout to a specified log file
        if ($this->use_logs_dir) {
            $_shell_command .= ' > ' . LOGS_DIR . DIRECTORY_SEPARATOR . $this->getOutputFileName();
        }

        //redirect stderr output to stdout input
        $_shell_command .= ' 2>&1';

        return $_shell_command;
    }

    /**
     * Checks whether the supplied string contains
     * the string "error". Most of the DB engines
     * return this word when a server error occurs
     *
     * This class can be overwritten at higher level
     * if custom or more detailed checks are needed
     *
     * @return bool
     * */
    protected function isError($_string)
    {
        // no error
        if (strpos(strtolower($_string), 'error') === false) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Run the supplied shell command
     *
     * This class can be overwritten at higher level
     * if custom behavior is needed
     *
     * @param string $_shell_command
     * @return void
     * @throws CodePax_DbVersioning_Exception command execution error
     * */
    protected function runCommand($_shell_command)
    {
        $command_result = exec($this->postFormatString($_shell_command));

        //if the a logs(writable) directory is defined
        //load the command output from there
        if ($this->use_logs_dir) {
            $command_result = $this->getCommandOutput();
        }

        if ($this->isError($command_result) === true) {
            throw new CodePax_DbVersioning_Exception($command_result);
        }
    }

    /**
     * Read only a limited number of lines
     * from the command output
     *
     * @return string
     */
    protected function getCommandOutput()
    {
        $count = 0;
        $result = '';

        $file_name = $this->getOutputFileName();

        //open file handler
        $fh = fopen(LOGS_DIR . DIRECTORY_SEPARATOR . $file_name, 'r');
        if ($fh !== false) {
            //read only a specified number of lines or until EOF
            while (!feof($fh) && $count < self::COMMAND_OUTPUT_LINES) {
                $result .= fgets($fh);
                $count++;
            }

            //close handler
            fclose($fh);

            //delete file
            unlink(LOGS_DIR . DIRECTORY_SEPARATOR . $file_name);
        }

        return $result;
    }

    /**
     * Executes the given SQL file
     *
     * @param string $_sql_file path to file to be executed
     * @return void
     * */
    abstract protected function executeChangeScript($_sql_file);

    /**
     * Drop and create database. Will be used only
     * on DEV environment where more than one
     * developers are applying change scripts for
     * different development branches
     *
     * @return void
     * */
    abstract protected function dropAndCreateDb();

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
    abstract protected function executeBaseline($_sql_file);

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
    abstract protected function generateBaseline($_target_sql_file);

    /**
     * Will generate the test data SQL file
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
    abstract protected function generateSqlTestDataFile($_target_sql_file);

    /**
     * Generate a sql dump file, compress it
     * to a new file and delete the initial one
     *
     * Using the Template design to enforce the
     * sequence of operations
     *
     * @param string $_target_compressed_file
     * @return void
     * */
    public function generateTestData($_target_file)
    {
        if (defined('USE_TEST_DATA_COMPRESSION') && USE_TEST_DATA_COMPRESSION === true) {
            $target_sql_file = str_replace(CodePax_DbVersioning_Files_Manager::ARCHIVE_FILE_EXTENSION, CodePax_DbVersioning_Files_Manager::SQL_FILE_EXTENSION, $_target_file);

            $this->generateSqlTestDataFile($target_sql_file);
            CodePax_DbVersioning_Files_Archive::compress($target_sql_file, $_target_file);
            unlink($target_sql_file);
        } else {
            $this->generateSqlTestDataFile($_target_file);
        }
    }

    /**
     * Load and apply the sql test data on the
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
    abstract protected function loadSqlTestDataFile($_sql_file);

    /**
     * Load the zip or sql file and pass it
     * to SQL specific handler @see loadSqlTestDataFile
     *
     * @param string $_source_file
     * @return void
     * */
    public function loadTestData($_source_file)
    {
        if (defined('USE_TEST_DATA_COMPRESSION') && USE_TEST_DATA_COMPRESSION === true) {
            $sql_file = str_replace(CodePax_DbVersioning_Files_Manager::ARCHIVE_FILE_EXTENSION, CodePax_DbVersioning_Files_Manager::SQL_FILE_EXTENSION, $_source_file);

            CodePax_DbVersioning_Files_Archive::unCompress($_source_file, $sql_file);

            if (file_exists($sql_file)) {
                $this->loadSqlTestDataFile($sql_file);
                unlink($sql_file);
            } else {
                throw new CodePax_DbVersioning_Exception("The test data file does not exist. Check the archive!");
            }
        } else {
            $this->loadSqlTestDataFile($_source_file);
        }
    }

    /**
     * Get the list of tables to ignore on generate test data fiel
     * Will contain at least the versioning table
     * Can provide other tables to be ignored by using in config file
     * define( 'TABLES_TO_IGNORE', 'application_logs,application_data' )
     *
     * Example return data:
     * array(
     * 	'application_logs',
     *  'application_data',
     * 	'z_db_versions'
     * );
     *
     *
     * @return array
     */
    protected function getTablesToIgnore()
    {
        $tables_to_ignore = array();
        if (defined('TABLES_TO_IGNORE') && TABLES_TO_IGNORE !== '') {
            $tables_to_ignore = explode(',', TABLES_TO_IGNORE);
        }
        $tables_to_ignore[] = CodePax_DbVersions::TABLE_NAME;
        return $tables_to_ignore;
    }

    /**
     * Construct the ignore table option
     * Example input:
     *  --ignore-table=%s.%s
     *
     * Example output:
     *  --ignore-table=teste.table1 --ignore-table=teste.table2 --ignore-table=teste.table3
     *
     * @param string $_ignore_table_option_pattern
     * @param boolean $_contains_db_name | add the db name to the table name
     * @return string
     */
    protected function getIgnoreTablesOption($_ignore_table_option_pattern, $_contains_db_name = true)
    {
        $return_command = array();
        $tables_to_ignore = $this->getTablesToIgnore();
        foreach ($tables_to_ignore as $table_name) {
            if ($_contains_db_name) {
                $return_command[] = sprintf($_ignore_table_option_pattern, DB_NAME, $table_name);
            } else {
                $return_command[] = sprintf($_ignore_table_option_pattern, $table_name);
            }
        }
        return implode(' ', $return_command);
    }
}
