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
 * Load the INI config file and make the variables
 * available as class properties
 *
 * The class implements a Singleton design
 *
 * @category CodePax
 * @copyright Copyright (c) 2012 Zitec COM srl, Romania
 * @license New BSD http://www.codepax.com/license.html
 * */
class CodePax_ConfigLoader
{

    /**
     * @var CodePax_ConfigLoader
     * */
    private static $instance;

    /**
     * @var array
     * */
    protected $config_data;

    /**
     * Load the ini file
     *
     * @param string $_config_file
     * @return void
     * */
    private function __construct($_config_file)
    {
        $this->config_data = parse_ini_file($_config_file);
    }

    /**
     * Returns the config loader class instance
     *
     * @param string $_config_file
     * @return SvnGui_ConfigLoader
     * */
    public static function getInstance($_config_file)
    {
        if (is_null(self::$instance)) {
            self::$instance = new CodePax_ConfigLoader($_config_file);
        }
        return self::$instance;
    }

    /**
     * Checks if the supplied key exists in the
     * config array
     *
     * @param string $_key
     * @return bool
     * */
    public function __isset($_key)
    {
        return isset($this->config_data[$_key]);
    }

    /**
     * Returns the value from the config array
     * for the supplied key
     *
     * @param string $_key
     * @return mixed
     * */
    public function __get($_key)
    {
        return isset($this->config_data[$_key]) ? $this->config_data[$_key] : null;
    }

}
