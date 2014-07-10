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
 * Handles versioning operations on DEV environment
 *
 * With every switch operation the following steps are
 * followed:
 * 1. drop and create DB (an empty one at this moment)
 * 2. apply baseline script
 * 3. apply latest change scripts
 * 4. load and apply the test data
 *
 * @category CodePax
 * @subpackage DbVersioning
 * @copyright Copyright (c) 2012 Zitec COM srl, Romania
 * @license New BSD http://www.codepax.com/license.html
 * */
class CodePax_DbVersioning_Environments_Dev extends CodePax_DbVersioning_Environments_Abstract {

    /**
     * Generate the test data, which is basically a DB dump
     * with data only, and commit it to SVN repo
     *
     * @return void
     * */
    private function generateTestData()
    {
        $this->sql_engine->generateTestData(CodePax_DbVersioning_Files_Manager::getTestDataFile());
        // commit the new test data
        if (defined('SVN_USER')) {
            $svn_wrapper = new CodePax_Svn_Wrapper(SVN_USER, SVN_PASS, SVN_URL, PROJECT_DIR);
            $svn_wrapper->commit("SVN GUI generated test data file", DB_VERSIONING_DIR);
        }
    }

    /**
     * Apply the baseline, which is basically the database schema
     *
     * The sleep is added to prevent a CS to be recorded
     * with the same timestamp
     *
     * @return void
     * */
    private function applyBaseline()
    {
        $baseline_path = CodePax_DbVersioning_Files_Manager::getBaselineByVersion($this->latest_db_version);
        $this->sql_engine->executeBaseline($baseline_path);
        $this->db_versions_model->addVersion($this->latest_db_version, CodePax_DbVersions::TYPE_BASELINE);
        sleep(1);
    }

    /**
     * Apply the database test data
     *
     * @return void
     * */
    private function applyTestData()
    {
        // load and apply the test data
        $test_data_path = CodePax_DbVersioning_Files_Manager::getTestDataFile();
        $this->sql_engine->loadTestData($test_data_path);
    }

    /**
     * Drop and recreate the database
     * using the provided DB Engine
     *
     * @return void
     * */
    private function dropAndRecreateDb()
    {
        $this->sql_engine->dropAndCreateDb();

        //for SQL Server get a new DB version model instance
        //SQL Server closes the connection if we drop the database
        if (DB_ENGINE == 'sqlsrv') {
            $this->db_versions_model = CodePax_DbVersions::factory();
        }
    }

    /**
     * Set the latest database version to an internal
     * class property
     *
     * On DEV, the latest version will be considered the
     * latest baseline found on the file system
     *
     * Override @see DbVersioning_Environments_Abstract::setLatestDbVersion
     *
     * @return void
     * */
    protected function setLatestDbVersion()
    {
        $this->latest_db_version = CodePax_DbVersioning_Files_Manager::getLatestBaselineVersion();
    }

    /**
     * Set the latest database data version to an internal
     * class property
     *
     * On DEV, the latest version will be considered the
     * latest baseline found on the file system
     *
     * @return void
     * */
    protected function setLatestDataDbVersion()
    {
        $this->latest_data_db_version = $this->latest_db_version;
    }

    /**
     * Run the versioning process
     *
     * Override @see DbVersioning_Environments_Abstract::runScripts
     * Sample of the returned array can be found in base
     * class method
     *
     * @param bool[optional] $_generate_test_data
     * @return array
     * */
    public function runScripts($_generate_test_data = false)
    {
        $operation_stack = array('drop_recreate_db', 'apply_baseline', 'run_change_scripts',
            'load_test_data', 'run_data_change_scripts');

        // inject the test data saving operation at the beginning of the stack
        if (isset($_generate_test_data) && $_generate_test_data === true) {
            array_unshift($operation_stack, 'save_test_data');
        }

        // set some time and memory boundaries so that
        // the script will finish execution
        set_time_limit(0);
        ini_set('memory_limit', '256M');

        // execute operations
        foreach ($operation_stack as $operation) {
            try {
                switch ($operation) {
                    case 'save_test_data':
                        $this->generateTestData();
                        break;
                    case 'drop_recreate_db':
                        $this->dropAndRecreateDb();
                        break;
                    case 'apply_baseline':
                        $this->applyBaseline();
                        break;
                    case 'run_change_scripts':
                        $change_scripts = $this->getChangeScripts();
                        $this->runChangeScripts($change_scripts);
                        break;
                    case 'load_test_data':
                        $this->applyTestData();
                        break;
                    case 'run_data_change_scripts':
                        $data_change_scripts = $this->getDataChangeScripts();
                        $this->runDataChangeScripts($data_change_scripts);
                        break;
                }
                $this->results[$operation] = 'ok';
            } catch (CodePax_DbVersioning_Exception $dbv_e) {
                // when one operation fails, we stop the process
                $this->results[$operation] = $dbv_e->getMessage();
                return $this->results;
            }
        }

        return $this->results;
    }

    /**
     * Returns the latest baseline version
     * The method is used for DEV only to display
     *
     * @return string baseline version number
     * */
    public function getLatestBaselineVersion()
    {
        return $this->latest_db_version;
    }
}
