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
 * The main page of the app
 *
 * @category CodePax
 * @copyright Copyright (c) 2012 Zitec COM srl, Romania
 * @license New BSD http://www.codepax.com/license.html
 */
require '../application/bootstrap.php';

// initialize view object
$view = new CodePax_View();
$view->setViewsPath(VIEWS_PATH);
$view->setCurrentView('index');

try {
    $repo_wrapper = CodePax_Scm_Factory::Factory(VERSIONING);

    //--- execute some action: update, switch, etc
    if (!empty($_GET)) {
        $response_string = null;
        //--- switch to branch
        if (isset($_GET['branch']) && strlen($_GET['branch']) > 1 && defined(
                        'SWITCH_TO_BRANCH'
                ) && SWITCH_TO_BRANCH === true
        ) {
            $response_string = $repo_wrapper->switchToBranch($_GET['branch']);
        }
        //--- switch to tag
        if (isset($_GET['tag']) && strlen($_GET['tag']) > 1 && defined('SWITCH_TO_TAG') && SWITCH_TO_TAG === true) {
            $response_string = $repo_wrapper->switchToTag($_GET['tag']);
        }
        //--- switch to stable
        if (isset($_GET['stable']) && defined('SWITCH_TO_TRUNK') && SWITCH_TO_TRUNK === true) {
            $response_string = $repo_wrapper->switchToTrunk();
        }
        //--- switch to revision
        if (isset($_GET['revision_no']) && defined('SWITCH_TO_REVISION') && SWITCH_TO_REVISION === true) {
            $response_string = $repo_wrapper->switchToRevision($_GET['revision_no']);
        }
        //--- run SVN cleanup
        if (isset($_GET['svncleanup']) && VERSIONING == 'SVN') {
            $response_string = $repo_wrapper->svnCleanup();
        }

        $view->response_string = array_filter(explode("\n", $response_string));

        //--- recreate object with new info
        $repo_wrapper = CodePax_Scm_Factory::Factory(VERSIONING);
    }
} catch (Exception $e) {
    $view->error_message = $e->getMessage();
    $view->render();
    exit();
}

$view->project_name = PROJECT_NAME;
$view->environment = APPLICATION_ENVIRONMENT;

$view->repo_info = $repo_wrapper->getRepoInfo();
$view->repo_top_info = $repo_wrapper->getRepoTopInfo();
$view->repo_more_info = $repo_wrapper->getRepoMoreInfo();
$view->revision_status = $repo_wrapper->getBranchStatus();

// repo current working copy
$view->current_position = $repo_wrapper->getCurrentPosition(); //$repo_wrapper->getCurrentPosition(); //FIXME
//--- show "switch to trunk" button
if (defined('SWITCH_TO_TRUNK') && SWITCH_TO_TRUNK === true) {
    $view->switch_to_trunk = true;
    $view->switch_to_trunk_button = SCM_STABLE_NAME;
}

//--- show/hide branches
if (defined('SWITCH_TO_BRANCH') && SWITCH_TO_BRANCH === true) {
    $view->active_branches = $repo_wrapper->getActiveBranches();
}

//--- show/hide tags
if (defined('SWITCH_TO_TAG') && SWITCH_TO_TAG === true) {
    $view->tags = $repo_wrapper->getTags();
}

//--- hide "switch to revision"
if (defined('SWITCH_TO_REVISION') && SWITCH_TO_REVISION === true) {
    $view->switch_to_revision = true;
}

if (VERSIONING == 'SVN' &&
        !empty($response_string) &&
        is_numeric(
                strpos(
                        strtolower($response_string), "run 'svn cleanup' to remove locks (type 'svn help cleanup' for details)"
                )
        )
) {
    $view->working_copy_locked = true;
}

//--- show HOOKS section
if (defined('USE_HOOKS')) {
    $view->use_hooks = USE_HOOKS;

    $hooks_hanlder = new CodePax_Hooks_Handler();
    $hooks = $hooks_hanlder->getList();
    $view->hooks = array_keys($hooks);
}

//--- show Db versioning section
if (defined('USE_DB_VERSIONING') && USE_DB_VERSIONING === true) {
    $view->use_db_versioning = true;
    try {
        // get current DB version
        $db_versions_model = CodePax_DbVersions::factory();
        if (in_array(APPLICATION_ENVIRONMENT, array('dev', 'prod'))) {
            $latest_baseline_file = CodePax_DbVersioning_Files_Manager::getLatestBaselineVersion();
            if (!$db_versions_model->checkIsVersionRegistred(
                            $latest_baseline_file, CodePax_DbVersions::TYPE_BASELINE
                    )
            ) {
                $db_versions_model->addVersion($latest_baseline_file, CodePax_DbVersions::TYPE_BASELINE);
            }
        }

        $latest_structure_version = $db_versions_model->getLatestVersion(CodePax_DbVersions::TYPE_CHANGE_SCRIPT);
        $latest_data_version = $db_versions_model->getLatestVersion(CodePax_DbVersions::TYPE_DATA_CHANGE_SCRIPT);

        $view->database_name = DB_NAME;
        $view->database_structure_version = $latest_structure_version[CodePax_DbVersions::VERSION_ATTRIBUTE];
        $view->database_structure_last_update = $latest_structure_version[CodePax_DbVersions::DATE_ADDED_ATTRIBUTE];
        $view->database_data_version = $latest_data_version[CodePax_DbVersions::VERSION_ATTRIBUTE];
        $view->database_data_last_update = !empty($latest_data_version[CodePax_DbVersions::DATE_ADDED_ATTRIBUTE]) ? $latest_data_version[CodePax_DbVersions::DATE_ADDED_ATTRIBUTE] : 'n/a';

        // get change scripts to run
        $db_versioning_handler = CodePax_DbVersioning_Environments_Factory::factory(APPLICATION_ENVIRONMENT);
        $change_scripts = $db_versioning_handler->getChangeScripts();
        $data_change_scripts = $db_versioning_handler->getDataChangeScripts();

        $new_baseline_available = false;
        if (in_array(APPLICATION_ENVIRONMENT, array('dev', 'prod'))) {
            $new_baseline_available = (version_compare(
                            $latest_structure_version[CodePax_DbVersions::VERSION_ATTRIBUTE], $db_versioning_handler->getLatestBaselineVersion()
                    ) == -1);
        }

        // database is up-to-date
        if (empty($change_scripts) && empty($data_change_scripts) && !$new_baseline_available) {
            $view->db_is_updated = true;
        } else {
            // data change scripts
            if (!empty($change_scripts)) {
                $view->db_scripts = array_keys($change_scripts);
            }

            // data change scripts
            if (!empty($data_change_scripts)) {
                $view->data_db_scripts = array_keys($data_change_scripts);
            }


            // when on DEV, show extra info
            if (APPLICATION_ENVIRONMENT == 'dev') {
                // `getLatestBaselineVersion` is available only for DEV
                $view->baseline_script = $db_versioning_handler->getLatestBaselineVersion();
                $view->db_versioning_dev_note = true;
            }
        }
    } catch (PDOException $pdo_e) {
        $view->error_message = $pdo_e->getMessage();
        $view->render();
        exit();
    }
}

try {
    $view->render();
} catch (CodepPax_View_Exception $e) {
    echo $e->getMessage();
    exit();
}
