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
 * Handles versioning operations on PROD environment
 *
 * Only the change scripts are being applied with
 * version number higher than the last one applied
 *
 * @category CodePax
 * @subpackage DbVersioning
 * @copyright Copyright (c) 2012 Zitec COM srl, Romania
 * @license New BSD http://www.codepax.com/license.html
 * */
class CodePax_DbVersioning_Environments_Prod extends CodePax_DbVersioning_Environments_Abstract {

    /**
     * Set the latest database version to an internal
     * class property
     *
     * Override @see CodePax_DbVersioning_Environments_Abstract::setLatestDbVersion
     *
     * @return void
     * */
    protected function setLatestDbVersion()
    {
        $latest_db_version = $this->db_versions_model->getLatestVersion(CodePax_DbVersions::TYPE_CHANGE_SCRIPT);
        $this->latest_db_version = $latest_db_version[CodePax_DbVersions::VERSION_ATTRIBUTE];
    }

    /**
     * Set the latest database data version to an internal
     * class property
     *
     * Override @see CodePax_DbVersioning_Environments_Abstract::setLatestDbVersion
     *
     * @return void
     * */
    protected function setLatestDataDbVersion()
    {
        $latest_db_version = $this->db_versions_model->getLatestVersion(CodePax_DbVersions::TYPE_DATA_CHANGE_SCRIPT);
        $this->latest_data_db_version = $latest_db_version[CodePax_DbVersions::VERSION_ATTRIBUTE];
    }

    /**
     * Run the versioning process
     *
     * Override @see CodePax_DbVersioning_Environments_Abstract::runScripts
     * Sample of the returned array can be found in base
     * class method
     *
     * @param bool[optional] $_generate_test_data
     * @return array
     * */
    public function runScripts($_generate_test_data = false)
    {
        // run the structure scripts
        $this->runChangeScripts($this->getChangeScripts());
        // run the data scripts
        $this->runDataChangeScripts($this->getDataChangeScripts());
        return $this->results;
    }

    /**
     * Returns the latest baseline version.
     *
     * @return string baseline version number
     * */
    public function getLatestBaselineVersion()
    {
        return $this->latest_db_version;
    }
}
