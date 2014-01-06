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
 * Description of Abstract
 *
 * @author marius.balteanu
 */
abstract class CodePax_Scm_Abstract {

    const GET_RESULT_DIRECTIVE = '2>&1';

    /**
     * Project folder relative to this script
     *
     * @var string
     * */
    protected $project_folder;

    /**
     * Holds all available branches for the repository
     *
     * @var array
     * */
    protected $branches = array();

    /**
     * Holds only active branches for the repository
     *
     * @var array
     * */
    protected $active_branches = array();

    /**
     * Holds only merged(to trunk) branches for the repository
     *
     * @var array
     * */
    protected $merged_branches = array();
    protected $remote_branches = array();

    /**
     * On Unix it takes the \n value, while on Win
     * it will be &&
     *
     * @var string
     * */
    protected $command_separator = "; ";

    /**
     * Flag to indicate the platform the app
     * is running on
     * */
    protected $is_windows = false;

    /**
     * Flag to indicate if there were
     * connections issues
     * */
    protected $has_error = false;

    /**
     * Holds the error messages received
     * when the ocnnection to SCM server is made
     *
     * @var string
     * */
    protected $error_message = null;

    public function __construct()
    {
        // WIN detected
        if (strpos(strtolower(PHP_OS), 'win') !== false) {
            $this->is_windows = true;
            $this->command_separator = "&&";
            $this->path_to_svn_bin = '"' . $this->path_to_svn_bin . '"';
        }
    }

    /**
     * Set branches array; both active and merged branches
     *
     * @return void
     * */
    abstract protected function setBranches();

    /**
     * Get active branches
     *
     * @return array
     * */
    abstract public function getActiveBranches();

    /**
     * Get merged branches
     *
     * @return array
     * */
    abstract public function getMergedBranches();

    /**
     * Get all branches, both merged and active
     *
     * @return array
     * */
    abstract public function getBranches();

    /**
     * Get tags
     *
     * @return array
     * */
    abstract public function getTags();

    /**
     * Switches the working copy to specified branch
     *
     * @param string $_name
     * @return string
     * */
    abstract public function switchToBranch($_name);

    /**
     * Switches the working copy to specified tag
     *
     * @param string $_name
     * @return string
     * */
    abstract public function switchToTag($_name);

    /**
     * Switches the working copy to trunk
     *
     * @return string
     * */
    abstract public function switchToTrunk();

    /**
     * Switches the working copy to the supplied revision; if none is given will switch to HEAD revision
     *
     * @param int $_revision_no revision to switch to
     * @return string
     * */
    abstract public function switchToRevision($_revision_no = null);

    /**
     * Gets info about slected project
     *
     * The info may be: revision number, last author, modified at, etc.
     *
     * @return string
     * */
    abstract public function getRepoInfo();

    abstract public function getRepoTopInfo();

    abstract public function getRepoMoreInfo();

    /**
     * Gets the error message
     * from SCM connection
     *
     * @return string
     */
    abstract public function getErrorMessage();

    /**
     * Check if connection to SCM
     * has errors
     *
     * @return boolean
     */
    abstract public function hasError();

    /**
     * Get name of the branch or tag is reading from.
     * When reading from trunk the method will return null
     *
     * @return string|null
     * */
    abstract public function getCurrentPosition();

    /**
     * Add a file or a set of files to repository
     *
     * @param string $_path relative to repo root
     * @return string
     * */
    abstract public function add($_path);

    /**
     * Commit one or more files to repository
     *
     * @param string $_message commit message
     * @return string
     * */
    abstract public function commit($_message);

    /**
     * Add a file to repo and then commit it
     *
     * @param string $_message commit message
     * @param string $_path
     * @return string
     * */
    abstract public function addAndCommit($_message, $_path);
}
