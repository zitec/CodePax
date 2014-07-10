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
 * Called via AJAX to run the DB scripts
 *
 * @category CodePax
 * @copyright Copyright (c) 2012 Zitec COM srl, Romania
 * @license New BSD http://www.codepax.com/license.html
 */
require '../application/bootstrap.php';

// initialize view object
$view = new CodePax_View();
$view->setViewsPath(VIEWS_PATH);
$view->setCurrentView('run_db_scripts');

try {
    $db_versioning_handler = CodePax_DbVersioning_Environments_Factory::factory(APPLICATION_ENVIRONMENT);

    $generate_test_data = false;
    // generate test data
    if (APPLICATION_ENVIRONMENT == 'dev' && isset($_POST['preserve_test_data']) && $_POST['preserve_test_data'] == 1) {
        $generate_test_data = true;
    }

    // run the change scripts
    $db_scripts_result = $db_versioning_handler->runScripts($generate_test_data);

    // unset some keys that are redundant on DEV
    unset($db_scripts_result['run_change_scripts'], $db_scripts_result['run_data_change_scripts']);

    $view->db_scripts = $db_scripts_result;
} catch (CodePax_DbVersioning_Exception $dbv_e) {
    $view->error_message = 'DB versioning error: ' . $dbv_e->getMessage();
    $view->render();
    exit();
} catch (Exception $e) {
    $view->error_message = 'Generic error: ' . $e->getMessage();
    $view->render();
    exit();
}

try {
    $view->render();
} catch (CodePax_View_Exception $e) {
    echo $e->getMessage();
    exit();
}