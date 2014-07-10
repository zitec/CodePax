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
 * Factory class to load the appropriate DB engine
 * by the value of the specific constant from the
 * config file
 *
 * Implements the Factory design
 *
 * @package ZIT
 * @category CodePax
 * @subpackage DbVersioning
 * @copyright Copyright (c) 2012 Zitec COM srl, Romania
 * @license New BSD http://www.codepax.com/license.html
 * */
class CodePax_DbVersioning_SqlEngines_Factory {

    /**
     * Generate the concrete DB instance
     *
     * @return CodePax_DbVersioning_SqlEngines_Abstract
     * @throws CodePax_DbVersioning_Exception SQL engine not supported
     * */
    public static function factory()
    {
        switch (DB_ENGINE) {
            case 'mysql':
                return new CodePax_DbVersioning_SqlEngines_MySql();
            case 'pgsql':
                return new CodePax_DbVersioning_SqlEngines_PgSql();
            case 'sqlsrv':
                return new CodePax_DbVersioning_SqlEngines_SqlSrv();
            default:
                throw new CodePax_DbVersioning_Exception('Unsupported SQL engine');
        }
    }
}
