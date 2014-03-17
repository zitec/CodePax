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
 * Main class that deals with SCM
 *
 * Implements the Factory design
 *
 * @category CodePax
 * @subpackage Scm
 * @copyright Copyright (c) 2013 Zitec COM srl, Romania
 * @license New BSD http://www.codepax.com/license.html
 * */
class CodePax_Scm_Factory {

    /**
     * Create the appropriate object by the
     * supplied environment
     *
     * @param string $_scm svn/git
     * @return CodePax_Scm_Abstract
     * @throws CodePax_Scm_Exception unsupported version control
     * */
    public static function factory($_scm)
    {
        switch (strtolower($_scm)) {
            case 'svn':
                return new CodePax_Scm_Svn(SCM_USER, SCM_PASS, REPO_URL, PROJECT_DIR);
            case 'git':
                return new CodePax_Scm_Git(SCM_USER, SCM_PASS, REPO_URL, PROJECT_DIR);
            default:
                throw new CodePax_Scm_Exception('Unsupported version control system');
        }
    }
}
