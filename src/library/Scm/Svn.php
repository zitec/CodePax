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
 * Performs svn operations like: update, switch, svn info, etc.
 *
 * Used especially for testing, when needed to test a separate branch or tag
 * and keep the trunk untouched. Also used for updating the working copy to
 * a desired revison or HEAD revision.
 * This class assumes the following repository layout:
 * + repository root
 *        - trunk
 *        - branches
 *        - tags
 *
 * Important: this class requires a Subversion command line client
 * to be installed on the runnig machine
 *
 * @category CodePax
 * @subpackage Svn
 * @copyright Copyright (c) 2012 Zitec COM srl, Romania
 * @license New BSD http://www.codepax.com/license.html
 * */
class CodePax_Scm_Svn extends CodePax_Scm_Abstract
{

    const TAGS = 'tags/';

    /**
     * Connection string; contains path to SVN binaries, username and password
     *
     * @var string
     * */
    protected $svn_connection_string;

    /**
     * Repository info
     *
     * @var string
     * */
    protected $svn_info = null;

    /**
     * Repository info
     *
     * @var string
     * */
    protected $top_info = null;

    /**
     * Repository URL
     *
     * @var string
     * */
    protected $svn_url;

    /**
     * Full path to SVN binaries. Can be
     * overwritten from constructor
     *
     * @var string
     * */
    protected $path_to_svn_bin = '/usr/bin/svn --config-dir=/tmp';

    /**
     * Flag to indicate if the
     * working copy is locked
     * or not
     *
     * @var integer
     * */
    protected $svn_copy_locked = 0;

    /**
     * The difference between branch revision
     * and the line marked as stable
     *
     * @var integer
     * */
    protected $revision_status;

    /**
     * Class constructor
     *
     * @param string $_svn_user svn user
     * @param string $_svn_pass svn pass
     * @param string $_svn_url url to repository
     * @param string $_project_folder project folder
     * */
    public function __construct($_svn_user, $_svn_pass, $_svn_url, $_project_folder)
    {
        parent::__construct();
        if (defined('PATH_TO_SVN_BIN')) {
            $this->path_to_svn_bin = PATH_TO_SVN_BIN;
        }

        $this->svn_url = $_svn_url;
        $this->project_folder = $_project_folder;

        if ($this->is_windows) {
            $this->path_to_svn_bin = "\"{$this->path_to_svn_bin }\"";
            $this->project_folder = "\"{$_project_folder}\"";
        }

        $this->svn_connection_string = "{$this->path_to_svn_bin } --non-interactive --username={$_svn_user} --password={$_svn_pass}";

        //--- set repository info
        $shell_command = "{$this->path_to_svn_bin} info {$this->project_folder}";
        $response_string = shell_exec($shell_command);
        if (isset($response_string)) {
            $this->svn_info = urldecode(substr($response_string, 0, -2));
        }
    }

    /**
     * Set branches array; both active and merged branches
     *
     * @return void
     * */
    protected function setBranches()
    {
        if (empty($this->branches)) {
            $shell_command = "echo p|{$this->svn_connection_string} ls " . $this->svn_url . '/' . SCM_BRANCH_PREFIX;
            $response_string = shell_exec($shell_command);

            $this->branches = array_map('trim', explode("\n", str_replace('/', '', $response_string)));
            //--- pop the last value since it is empty
            if (count($this->branches) > 1) {
                array_pop($this->branches);
            }
            foreach ($this->branches as $name) {
                if (substr($name, 0, 2) == MERGED_BRANCH_MARKER) {
                    $this->merged_branches[] = $name;
                } else {
                    $this->active_branches[] = $name;
                }
            }
        }
    }

    /**
     * Get active branches
     *
     * @return array
     * */
    public function getActiveBranches()
    {
        $this->setBranches();
        return $this->active_branches;
    }

    /**
     * Get merged branches
     *
     * @return array
     * */
    public function getMergedBranches()
    {
        $this->setBranches();
        return $this->merged_branches;
    }

    /**
     * Get all branches, both merged and active
     *
     * @return array
     * */
    public function getBranches()
    {
        $this->setBranches();
        return $this->branches;
    }

    /**
     * Get tags
     *
     * @return array
     * */
    public function getTags()
    {
        $shell_command = "echo p|{$this->svn_connection_string} ls " . $this->svn_url . '/' . SCM_TAG_PREFIX;
        $response_string = shell_exec($shell_command);
        $res = array_map('trim', explode("\n", str_replace('/', '', $response_string)));
        //--- popout the last value since it is empty
        array_pop($res);
        return $res;
    }

    /**
     * Switches the working copy to specified branch
     *
     * @param string $_name
     * @return string
     * */
    public function switchToBranch($_name)
    {
        $shell_command = "cd {$this->project_folder}" . $this->command_separator;
        $shell_command .= "{$this->svn_connection_string} switch " . $this->svn_url . '/' . SCM_BRANCH_PREFIX . $_name . self::GET_RESULT_DIRECTIVE;

        return shell_exec($shell_command);
    }

    /**
     * Switches the working copy to specified tag
     *
     * @param string $_name
     * @return string
     * */
    public function switchToTag($_name)
    {
        $shell_command = "cd {$this->project_folder}" . $this->command_separator;
        $shell_command .= "{$this->svn_connection_string} switch  " . $this->svn_url . '/' . SCM_TAG_PREFIX . $_name . self::GET_RESULT_DIRECTIVE;
        return shell_exec($shell_command);
    }

    /**
     * Switches the working copy to trunk
     *
     * @return string
     * */
    public function switchToTrunk()
    {
        $shell_command = "cd \"{$this->project_folder}\"" . $this->command_separator;
        $shell_command .= "{$this->svn_connection_string} switch " . $this->svn_url . '/' . SCM_STABLE_NAME . ' ' . self::GET_RESULT_DIRECTIVE;
        return shell_exec($shell_command);
    }

    /**
     * Switches the working copy to the supplied revision; if none is given will switch to HEAD revision
     *
     * @param int $_revision_no revision to switch to
     * @return string
     * */
    public function switchToRevision($_revision_no = null)
    {
        $revision = !empty($_revision_no) ? " -r{$_revision_no}" : null;

        $shell_command = "cd {$this->project_folder}" . $this->command_separator;
        $shell_command .= "{$this->svn_connection_string} update{$revision} " . self::GET_RESULT_DIRECTIVE;
        return shell_exec($shell_command);
    }

    /**
     * Run SVN cleanup command in the project root
     *
     *
     * @return string
     * */
    public function svnCleanup()
    {
        $shell_command = "cd {$this->project_folder}" . $this->command_separator;
        $shell_command .= "{$this->path_to_svn_bin} cleanup" . self::GET_RESULT_DIRECTIVE;

        return shell_exec($shell_command);
    }

    /**
     * Get revision for line marked as stable
     *
     * @return integer
     * */
    private function getStableRevision()
    {
        $shell_command = "{$this->svn_connection_string} info " . $this->svn_url . "/" . SCM_STABLE_NAME;
        $response_string = shell_exec($shell_command);

        $revision = preg_match('/Last Changed Rev: (\d+)/', $response_string, $matches);
        list(, $revision) = $matches;

        return $revision;
    }

    /**
     * Return the difference between branch revision
     * and stable line revision
     *
     * @return integer
     * */
    public function getBranchStatus()
    {
        $current_revision = $this->top_info['Revision'];
        $stable_revision= $this->getStableRevision();

        $revision_status = $current_revision-$stable_revision;

        if ($revision_status == 0) {
            return false;
        }

        if ($this->revision_status > 0) {
            return "This branch is  {$revision_status } revision(s) ahead of '" . SCM_STABLE_NAME . "'";
        } else {
            return "This branch is " . ($revision_status * -1) . " revision(s) behind '" . SCM_STABLE_NAME . "'";
        }
    }

    /**
     * Gets info about selected project
     *
     * The info may be: revision number, last author, modified at, etc.
     *
     * @return string
     * */
    public function getRepoInfo()
    {
        //--- populate REPO info data
        $top_markers = array('URL' => 'URL', 'Last Changed Rev' => 'Revision', 'Last Changed Author' => 'Last changed');

        $repo_info = explode("\n", $this->svn_info);
        $markers_keys = array_keys($top_markers);
        foreach ($repo_info as $info) {
            $colon_first_pos = strpos($info, ':');
            $info_key = trim(substr($info, 0, $colon_first_pos));
            $info_value = trim(substr($info, $colon_first_pos + 1));

            if (in_array($info_key, $markers_keys)) {
                $this->top_info[$top_markers[$info_key]] = $info_value;
            } else {
                $this->more_info[$info_key] = $info_value;
            }
        }
        //remove the Revision info from more_info array because
        //this info shows the highest revision in the repo and not
        //the current revision
        unset($this->more_info['Revision']);

        return $this->svn_info;
    }

    public function getRepoTopInfo()
    {
        return $this->top_info;
    }

    public function getRepoMoreInfo()
    {
        return $this->more_info;
    }

    public function getWorkingCopyStatus()
    {
        return $this->svn_copy_locked;
    }

    /**
     * Get name of the branch or tag is reading from.
     * When reading from trunk the method will return null
     *
     * @return string|null
     * */
    public function getCurrentPosition()
    {
        $info_pieces = explode("\n", $this->svn_info);

        $url = $this->is_windows ? $info_pieces[2] : $info_pieces[1];
        list($tag_or_branch, $name) = array_slice(explode('/', $url), -2);
        $tag_or_branch = $tag_or_branch . '/';
        if ($tag_or_branch == SCM_BRANCH_PREFIX || $tag_or_branch == SCM_TAG_PREFIX) {
            return $name;
        } else { //--- reading from trunk
            return null;
        }
    }

    /**
     * Add a file or a set of files to repository
     *
     * @param string $_path relative to repo root
     * @return string
     * */
    public function add($_path)
    {
        $shell_command = "cd {
                $this->project_folder}" . $this->command_separator;
        $shell_command .= "echo p |{
                $this->path_to_svn_bin} --force add {
                $_path} " . self::GET_RESULT_DIRECTIVE;
        return shell_exec($shell_command);
    }

    /**
     * Commit one or more files to repository
     *
     * @param string $_message commit message
     * @return string
     * */
    public function commit($_message)
    {
        $shell_command = "cd {
                $this->project_folder}" . $this->command_separator;
        $shell_command .= "echo p |{
                $this->svn_connection_string} commit--message \"{
                $_message}\" " . self::GET_RESULT_DIRECTIVE;

        return shell_exec($shell_command);
    }

        /**
         * Add a file to repo and then commit it
         *
         * @param string $_message commit message
         * @param string $_path
         * @return string
         * */
        public
        function addAndCommit($_message, $_path)
        {
            $this->add($_path);
            return $this->commit($_message);
        }
    }
