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
 * Utility script to generate the table that keeps track
 * of database versions
 *
 * @category CodePax
 * @package Utils
 * @copyright Copyright (c) 2012 Zitec COM srl, Romania
 * @license New BSD http://www.codepax.com/license.html
 */
require '../application/bootstrap.php';

try {
    $sql_engine = CodePax_DbVersioning_SqlEngines_Factory::factory();
    $dir = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'sql' . DIRECTORY_SEPARATOR;

    switch (DB_ENGINE) {
        case 'mysql':
            $sql_file_name = 'db_versions_mysql.sql';
            break;
        case 'pgsql':
            $sql_file_name = 'db_versions_pgsql.sql';
            break;
        case 'sqlsrv':
            $sql_file_name = 'db_versions_sqlsrv.sql';
            break;
    }

    $sql_engine->executeChangeScript($dir . $sql_file_name);
} catch (CodePax_DbVersioning_Exception $dbv_e) {
    echo 'An error ocurred: ' . $dbv_e->getMessage();
} catch (Exception $e) {
    echo 'Generic error: ' . $e->getMessage();
}