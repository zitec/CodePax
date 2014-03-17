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
 * Base class for the versioning process. It encapsulates
 * the common functionalities for all environments
 *
 * @category CodePax
 * @subpackage DbVersioning
 * @copyright Copyright (c) 2012 Zitec COM srl, Romania
 * @license New BSD http://www.codepax.com/license.html
 * */
abstract class CodePax_DbVersioning_Environments_Abstract {

    /**
     * Latest structure version
     *
     * @var string xx.yy.zz
     * */
    protected $latest_db_version;

    /**
     * Latest data version
     *
     * @var string xx.yy.zz
     * */
    protected $latest_data_db_version;

    /**
     * @var CodePax_DbVersioning_SqlEngines_Abstract
     * */
    protected $sql_engine;

    /**
     * @var CodePax_DbVersions_Interface
     * */
    protected $db_versions_model;

    /**
     * Holds the result of each versioning
     * process operation
     *
     * It has the following structure:
     * Array (
     * [save_test_data] => ok
     * [drop_recreate_db] => ok
     * [apply_baseline] => ok
     * [structure] => Array (
     * 		[1.1.2] => ok
     * 		[1.1.3] => ok
     * 		)
     * [load_test_data] => ok
     * [data] => Array (
     * 		[1.1.6] => ok
     * 		[1.1.7] => ok
     * 		)
     * )
     *
     * @var array
     * */
    protected $results = array();

    /**
     * Create required instances
     *
     * @return void
     * */
    public function __construct()
    {
        // create the sql handler
        $this->sql_engine = CodePax_DbVersioning_SqlEngines_Factory::factory();

        // factory the Model that handles the versions table
        $this->db_versions_model = CodePax_DbVersions::factory();

        // set the structure version
        $this->setLatestDbVersion();

        // set the data version
        $this->setLatestDataDbVersion();
    }

    /**
     * Set the latest database structure version to an internal
     * class property
     *
     * @return void
     * */
    abstract protected function setLatestDbVersion();

    /**
     * Set the latest database data version to an internal
     * class property
     *
     * @return void
     * */
    abstract protected function setLatestDataDbVersion();

    /**
     * Run the DB versioning process. It may
     * contain change scripts, test data and other
     * DB operations
     *
     * Test data generation will be used on DEV only
     * for the moment.
     *
     * @param bool[optional] $_generate_test_data flag to allow test data generation
     * @return array returns the @see $this->results
     * */
    abstract protected function runScripts($_generate_test_data = false);

    /**
     * Returns the structure related change scripts list from
     * the filesystem
     *
     * This method may be called outside the
     * overall versioning process to simply
     * return a list of change scripts starting
     * with the @see $latest_db_version
     *
     * The returned array looks like this:
     * Array (
     * 	[1.1.2] => /var/www/site/dbv/change_scripts/1.1.2.sql
     * 	[1.1.3] => /var/www/site/dbv/change_scripts/1.1.3.sql
     * 	)
     *
     * @return array
     * */
    final public function getChangeScripts()
    {
        return CodePax_DbVersioning_Files_Manager::getChangeScriptsByVersion($this->latest_db_version);
    }

    /**
     * Returns the data change scripts list from
     * the filesystem
     *
     * This method may be called outside the
     * overall versioning process to simply
     * return a list of change scripts starting
     * with the @see $latest_db_version
     *
     * The returned array looks like this:
     * Array (
     * 	[1.1.2] => /var/www/site/dbv/data_change_scripts/1.1.2.sql
     * 	[1.1.3] => /var/www/site/dbv/data_change_scripts/1.1.3.sql
     * 	)
     *
     * @return array
     * */
    final public function getDataChangeScripts()
    {
        return CodePax_DbVersioning_Files_Manager::getDataChangeScriptsByVersion($this->latest_data_db_version);
    }

    /**
     * Executes the (structure related)change scripts
     *
     * This method should be executed only in DB versioning
     * process and that is why it is being marked as protected
     *
     * The input array is identical with the one that
     * @se getChangeScripts returns
     *
     * @param array $_change_scripts
     * @return void
     * */
    final protected function runChangeScripts(array $_change_scripts)
    {
        foreach ($_change_scripts as $version_string => $script_path) {
            try {
                $this->sql_engine->executeChangeScript($script_path);
                $this->db_versions_model->addVersion($version_string, CodePax_DbVersions::TYPE_CHANGE_SCRIPT);
                $this->results['change_scripts'][$version_string] = 'ok';
                sleep(1); //to avoid similar timestamps for small change scripts
            } catch (CodePax_DbVersioning_Exception $dbv_e) {
                $this->results['change_scripts'][$version_string] = $dbv_e->getMessage();
            }
        }
    }

    /**
     * Executes the data change scripts
     *
     * This method should be executed only in DB versioning
     * process and that is why it is being marked as protected
     *
     * The input array is identical with the one that
     * @se getChangeScripts returns
     *
     * @param array $_change_scripts
     * @return void
     * */
    final protected function runDataChangeScripts(array $_change_scripts)
    {
        foreach ($_change_scripts as $version_string => $script_path) {
            try {
                $this->sql_engine->executeChangeScript($script_path);
                $this->db_versions_model->addVersion($version_string, CodePax_DbVersions::TYPE_DATA_CHANGE_SCRIPT);
                $this->results['data_change_scripts'][$version_string] = 'ok';
                sleep(1); //to avoid similar timestamps for small change scripts
            } catch (CodePax_DbVersioning_Exception $dbv_e) {
                $this->results['data_change_scripts'][$version_string] = $dbv_e->getMessage();
            }
        }
    }
}
