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
 * Handles versioning operations on STG environment
 *
 * Only the change scripts are being applied with
 * version number higher than the last one applied
 *
 * This class is almost identical to PROD one, the only
 * difference being that you can generate baseline
 * from this type of ENV
 *
 * @category CodePax
 * @subpackage DbVersioning
 * @copyright Copyright (c) 2012 Zitec COM srl, Romania
 * @license New BSD http://www.codepax.com/license.html
 * */
class CodePax_DbVersioning_Environments_Stg extends CodePax_DbVersioning_Environments_Abstract
{

    /**
     * Set the latest database structure version to an internal
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
     * Overrides @see CodePax_DbVersioning_Environments_Abstract::runScripts
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
     * Incremenet the baseline version number
     *
     * The baseline will always increment the minor number
     * of revision and will reset to 0 the point number
     *
     * @param string $_version xx.yy.zz
     * @return string the new version string
     * */
    private function incrementVersionNumber($_version)
    {
        list($major, $minor, $point) = explode('.', $_version);
        if ($minor < 99) {
            $minor++;
        } else {
            $minor = 0;
            $major++;
        }
        $point = 0;
        return sprintf('%s.%s.%s', $major, $minor, $point);
    }

    /**
     * Generates a baseline
     *
     * This method is called outside the
     * overall versioning process
     *
     * @return void
     * */
    public function generateBaseline()
    {
        // get the new version number
        $baseline_version = $this->latest_db_version == '0.0.0' ? '1.0.0' : $this->incrementVersionNumber($this->latest_db_version);

        // generate baseline path
        $baseline_absolute_path = CodePax_DbVersioning_Files_Manager::getPathToBaselines() .
                DIRECTORY_SEPARATOR . $baseline_version . CodePax_DbVersioning_Files_Manager::SQL_FILE_EXTENSION;

        // factory the SQL engine object and generate baseline file
        $this->sql_engine->generateBaseline($baseline_absolute_path);

        // register baseline to DB
        $this->db_versions_model->addVersion($baseline_version, CodePax_DbVersions::TYPE_BASELINE);

        // commit the new baseline and versioning DB
        $svn_wrapper = new CodePax_Scm_Svn(SCM_USER, SCM_PASS, REPO_URL, PROJECT_DIR);
        $svn_wrapper->addAndCommit("SVN GUI generated baseline at version {$baseline_version}", DB_VERSIONING_DIR);
    }

}
