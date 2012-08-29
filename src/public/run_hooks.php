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
 * Script called on AJAX used to run every
 * class found under hooks directory
 * 
 * @category CodePax
 * @copyright Copyright (c) 2012 Zitec COM srl, Romania
 * @license New BSD http://www.codepax.com/license.html
 */
require '../application/bootstrap.php';

// initialize view object
$view = new CodePax_View();
$view->setViewsPath(VIEWS_PATH);
$view->setCurrentView('run_hooks');

$hooks_hanlder = new CodePax_Hooks_Handler();

// get all the registered hooks
$available_hooks = $hooks_hanlder->getList();

// run only the hooks chose by the user
$hooks_to_run = array_intersect_key($available_hooks, $_POST);

if (!empty($hooks_to_run)) {
	$hooks_hanlder->run($hooks_to_run);
	$hook_results = $hooks_hanlder->getResults();
    
    $view->hook_results = $hook_results;
}
else {
    $view->no_hooks_selected = true;
}

try {
    $view->render();
} catch (CodePax_View_Exception $e) {
    echo $e->getMessage();
    exit();
}