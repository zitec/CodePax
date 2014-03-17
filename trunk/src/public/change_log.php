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
 * Simple page to show the change log for each version
 *
 * @category CodePax
 * @copyright Copyright (c) 2012 Zitec COM srl, Romania
 * @license New BSD http://www.codepax.com/license.html
 */
require '../application/bootstrap.php';

// initialize view object
$view = new CodePax_View();
$view->setViewsPath(VIEWS_PATH);
$view->setCurrentView('change_log');

$view->project_name = PROJECT_NAME;
$view->environment = strtoupper(APPLICATION_ENVIRONMENT);

try {
    $view->render();
} catch (View_Exception $e) {
    echo $e->getMessage();
    exit();
}