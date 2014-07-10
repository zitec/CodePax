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
 * Class that handles "z_db_versions" table inside
 * each database it controls. This class is basically
 * a wrapper on the driver oriented ones
 *
 * This table accommodates 3 types of scripts:
 * 1 - regular change scripts that affect the structure only
 * 2 - baselines
 * 3 - data change scripts that affect the data only
 *
 * @category CodePax
 * @subpackage Models
 * @copyright Copyright (c) 2012 Zitec COM srl, Romania
 * @license New BSD http://www.codepax.com/license.html
 * */
class CodePax_DbVersions {

    const TABLE_NAME = 'z_db_versions';
    const VERSION_ATTRIBUTE = 'version';
    const DATE_ADDED_ATTRIBUTE = 'date_added';
    const TYPE_CHANGE_SCRIPT = 0;
    const TYPE_BASELINE = 1;
    const TYPE_DATA_CHANGE_SCRIPT = 2;

    /**
     * Do the constructor private to avoid
     * non-driver oriented class instances
     * */
    private function __construct()
    {

    }

    /**
     * Get the DbVersions driver oriented instance
     *
     * This should be moved into a dedicated class
     * when the second model should be added
     *
     * @return CodePax_DbVersions_Interface
     * */
    public static function factory()
    {
        switch (DB_ENGINE) {
            case 'mysql':
                return new CodePax_DbVersions_MySql();
            case 'pgsql':
                return new CodePax_DbVersions_PgSql();
            case 'sqlsrv':
                return new CodePax_DbVersions_SqlSrv();
        }
    }

    /**
     * Return the default version which is 0.0.0
     * This usually happens for the very first time,
     * when the DB versioning is set to place. Once
     * at least one version is added(a CS or baseline),
     * the function will not get called
     *
     * The returned array contains 2 keys only:
     * version and date_added
     *
     * @return array
     * */
    public static function getDefaultVersion()
    {
        return array(
            self::VERSION_ATTRIBUTE => '0.0.0',
            self::DATE_ADDED_ATTRIBUTE => ''
        );
    }

    /**
     * Helper method that encapsulted the logic
     * to decide what structure version number to
     * be returned
     *
     * When requesting the latest version of a
     * structure change script, the system will check
     * if there is any baseline with a higher version number
     * registered. In case it is, its number will be returned.
     * This happens because on dev/stg environments, after
     * generating a baseline, the system will return a wrong
     * version number for the structure
     *
     * @param array|null|boolean $_version_array contains 2 keys version, added
     * @param array|null $_baseline_version_array contains 2 keys version, added
     * @return array
     * */
    public static function getLatestDbVersion($_version_array, $_baseline_version_array)
    {
        if (empty($_version_array) && empty($_baseline_version_array)) {
            return array();
        }
        // only a baseline script registered
        else if (empty($_version_array)) {
            return $_baseline_version_array;
        } else {// change scripts and baseleine registered
            return version_compare($_version_array[self::VERSION_ATTRIBUTE], $_baseline_version_array[self::VERSION_ATTRIBUTE]) == -1 ? $_baseline_version_array : $_version_array;
        }
    }
}
