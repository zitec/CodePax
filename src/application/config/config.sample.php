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
 * Sample config file
 *
 * @category CodePax
 * @package Config
 * @copyright Copyright (c) 2012 Zitec COM srl, Romania
 * @license New BSD http://www.codepax.com/license.html
 */
/**
 * Project name as will appear in page
 * */
define('PROJECT_NAME', 'Sample Project');

/**
 * Whether the application will use SVN/GIT
 * */
define('USE_CODE_VERSIONING', true);

/**
 * Defines the versioning system : SVN/GIT
 * */
define('VERSIONING', 'SVN');

/**
 * Define the git protocol to be used
 * Currently accepted: SSH and HTTPS
 * 
 * */
define('GIT_PROTOCOL', 'HTTPS');

/**
 * Takes the following values(lowercased): dev/stg/prod
 *
 * Used especially for DB versioning module but
 * it will be used for further modules as well
 * */
define('APPLICATION_ENVIRONMENT', 'dev');

/**
 * Shows/hides switch to branch option
 * */
define('SWITCH_TO_BRANCH', true);

/**
 * Shows/hides switch to tag option
 * */
define('SWITCH_TO_TAG', true);

/**
 * Shows/hides switch to revision option
 * */
define('SWITCH_TO_REVISION', true);

/**
 * Shows/hides switch to trunk button
 * */
define('SWITCH_TO_TRUNK', true);

/**
 * Indicates that a branch is merged to trunk
 * */
define('MERGED_BRANCH_MARKER', 'm_');

/**
 * SCM user
 * */
define('SCM_USER', 'xxxx');

/**
 * SCM pass
 * */
define('SCM_PASS', 'yyyy');

/**
 * SCM url to repository
 * */
define('REPO_URL', '');

/**
 * Indicates the SCM prefix used for branches
 * Examples:
 * "branches/" for SVN
 * "features/" for GIT
 * */
define('SCM_BRANCH_PREFIX', "branches/");

/**
 * Indicates the SCM line used as stable
 * Example:
 * "stable" for both SVN and GIT
 * */
define('SCM_STABLE_NAME', "stable");

/**
 * Indicates the remote name to use for SCM
 * GIT ONLY
 * */
define('GIT_REMOTE_NAME', 'origin');

/**
 * Indicates the SCM prefix used for tags
 * Available only for SVN

 * Examples:
 * "tags/" for SVN
 * */
define('SCM_TAG_PREFIX', "tags/");

/**
 * Full path to SVN binaries
 *
 * This constant is OPTIONAL and the path is
 * assumed to be: /usr/bin/svn --config-dir=/tmp
 *
 * When specified it will overwrite the default value
 * */
define('PATH_TO_SVN_BIN', '/usr/bin/svn --config-dir=/tmp');

/**
 * Full path to GIT binaries
 *
 * This constant is OPTIONAL and the path is
 * assumed to be: /usr/bin/git
 *
 * When specified it will overwrite the default value
 * */
define('PATH_TO_GIT_BIN', 'git');

/**
 * Absolute path to project
 * */
define('PROJECT_DIR', '/home/sites/');

/**
 * Whether the application will search
 * and use hooks
 * */
define('USE_HOOKS', false);

/**
 * Path where hooks are located
 */
define('HOOKS_DIR', '');

/**
 * Whether the application use the DB versioning module
 * */
define('USE_DB_VERSIONING', false);

/**
 * The location of DB versioning scripts relative
 * to project folder @see PROJECT_DIR
 * */
define('DB_VERSIONING_DIR', 'dbv');

/**
 * Path to SQL binaries
 *
 * ATTENTION!
 * For Windows platform the specified path should be prepended with the command
 * for cmd.exe i.e. 'C:\WINDOWS\system32\cmd.exe /c '
 * */
define('PATH_TO_SQL_BIN', '/usr/bin/mysql');

/**
 * Path to SQL dump binaries
 *
 * ATTENTION!
 * For Windows platform the specified path should be prepended with the command
 * for cmd.exe i.e. 'C:\WINDOWS\system32\cmd.exe /c '
 * */
define('PATH_TO_SQL_DUMP_BIN', '/usr/bin/mysqldump');

/**
 * Path to PostgreSQL service (eg. /etc/init.d/postgresql)
 *
 * If the sql engine uses PostgreSQL than this constant has to be defined
 * */
define('PATH_TO_POSTGRES_SERVICE', '/etc/init.d/postgresql');

/**
 * Engine type to use
 * Currently accepted: mysql and pgsql
 * */
define('DB_ENGINE', 'mysql');

/**
 * DB connection host
 * */
define('DB_HOST', 'localhost');

/**
 * DB name
 * */
define('DB_NAME', 'xxxx');

/**
 * DB username
 * */
define('DB_USER', 'yyyy');

/**
 * DB password
 * */
define('DB_PASS', 'zzzz');

/**
 * Indicates whether the compression will
 * be used or not for the test data file
 *
 * If not provided "false" will be assumed
 * */
define('USE_TEST_DATA_COMPRESSION', false);

/**
 * Optional constat to indicate the name of
 * the table(s) to ignore on data export,
 * separated by ,
 * */
define('TABLES_TO_IGNORE', '');
