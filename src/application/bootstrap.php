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
 * Set up the application environment
 * 
 * @category CodePax
 * @copyright Copyright (c) 2012 Zitec COM srl, Romania
 * @license New BSD http://www.codepax.com/license.html
 */
date_default_timezone_set('UTC');

/**
 * CodePax version number to be displayed in main pages
 * and appended to static resources (css and jss)
 * */
define('VERSION_NUMBER', '1.0.0');

/**
 * ./application/
 * */
define('APPLICATION_PATH', dirname( __FILE__) . DIRECTORY_SEPARATOR);

/**
 * ./application/views/
 * */
define('VIEWS_PATH', APPLICATION_PATH . 'views' . DIRECTORY_SEPARATOR);

/**
 * references the app root "./" or
 */
define('ROOT_PATH', str_replace('application' . DIRECTORY_SEPARATOR, '', APPLICATION_PATH));

/**
 * Path to ./application/config/
 */
define('CONFIG_PATH', APPLICATION_PATH . 'config' . DIRECTORY_SEPARATOR);

require CONFIG_PATH . 'config.php';

set_include_path(APPLICATION_PATH . 'models'
	. PATH_SEPARATOR . ROOT_PATH . 'library'
	. PATH_SEPARATOR . get_include_path()
);

function CodePaxAutoload($_class_name) {
	$class = str_replace('_', DIRECTORY_SEPARATOR, substr($_class_name, 8)) . '.php';
	include_once $class;
}

spl_autoload_register('CodePaxAutoload');