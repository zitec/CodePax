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
 * 		- trunk
 * 		- branches
 * 		- tags
 * 
 * Important: this class requires a Subversion command line client
 * to be installed on the runnig machine
 * 
 * @category CodePax
 * @subpackage Svn
 * @copyright Copyright (c) 2012 Zitec COM srl, Romania
 * @license New BSD http://www.codepax.com/license.html
 * */
class CodePax_Svn_Wrapper {

	const SVN_GET_RESULT_DIRECTIVE = '2>&1';
	const TRUNK = 'trunk/';
	const BRANCHES = 'branches/';
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
	 * Repository URL
	 * 
	 * @var string
	 * */
	protected $svn_url;
	
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
	
	/**
	 * Full path to SVN binaries. Can be
	 * overwritten from constructor
	 * 
	 * @var string
	 * */
	protected $path_to_svn_bin = '/usr/bin/svn --config-dir=/tmp';
	
	/**
	 * On Unix it takes the \n value, while on Win
	 * it will be &&
	 * 
	 * @var string
	 * */
	protected $command_separator = "\n";
    
    /**
	 * Flag to indicate the platform the app
	 * is running on
	 * */
	protected $is_windows = false;
	
	/**
	 * Class constructor
	 * 
	 * @param string $_svn_user svn user
	 * @param string $_svn_pass svn pass
	 * @param string $_svn_url url to repository
	 * @param string $_project_folder project folder
	 * */
	public function __construct($_svn_user, $_svn_pass, $_svn_url, $_project_folder) {
		
		if (defined('PATH_TO_SVN_BIN')) {
			$this->path_to_svn_bin = PATH_TO_SVN_BIN;
		}
		
		$this->svn_connection_string = $this->path_to_svn_bin . " --username={$_svn_user} --password={$_svn_pass}";
		$this->svn_url = $_svn_url;
		$this->project_folder = $_project_folder;
		
		//--- set repository info
		$shell_command = $this->path_to_svn_bin . " info {$this->project_folder}";
		$response_string = shell_exec($shell_command);
		
		if (isset($response_string)) {
			$this->svn_info = urldecode(substr($response_string, 0, -2));
		}
		
		// WIN detected
		if (strpos(strtolower(PHP_OS), 'win') !== false) {
            $this->is_windows = true;
			$this->command_separator = "&&";
		}
	}
	
	/**
	 * Set branches array; both active and merged branches
	 * 
	 * @return void
	 * */
	protected function setBranches() {
		if (empty($this->branches)) {
			$shell_command = "echo p|{$this->svn_connection_string} ls " . $this->svn_url . '/' . self::BRANCHES;
			$response_string = shell_exec($shell_command);
			$this->branches = array_map('trim', explode("\n", str_replace('/', '', $response_string)));
			//--- pop the last value since it is empty
			if (count($this->branches) > 1) {
                array_pop($this->branches);
			}
			foreach ($this->branches as $name) {
				if (substr($name, 0, 2) == MERGED_BRANCH_MARKER) {
					$this->merged_branches[] = $name;
				}
				else {
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
	public function getActiveBranches() {
		$this->setBranches();
		return $this->active_branches;
	}
	
	/**
	 * Get merged branches
	 * 
	 * @return array
	 * */
	public function getMergedBranches() {
		$this->setBranches();
		return $this->merged_branches;
	}
	
	/**
	 * Get all branches, both merged and active
	 * 
	 * @return array
	 * */
	public function getBranches() {
		$this->setBranches();
		return $this->branches;
	}
	
	/**
	 * Get tags
	 * 
	 * @return array
	 * */
	public function getTags() {
		$shell_command = "echo p|{$this->svn_connection_string} ls " . $this->svn_url . '/' . self::TAGS;
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
	public function switchToBranch($_name) {
		$shell_command = "cd {$this->project_folder}" . $this->command_separator;
		$shell_command .= "{$this->svn_connection_string} switch \"" . $this->svn_url . '/' . self::BRANCHES . $_name . '" ' . self::SVN_GET_RESULT_DIRECTIVE;
		return shell_exec($shell_command);
	}
	
	/**
	 * Switches the working copy to specified tag
	 * 
	 * @param string $_name
	 * @return string
	 * */
	public function switchToTag($_name) {
		$shell_command = "cd {$this->project_folder}" . $this->command_separator;
		$shell_command .= "{$this->svn_connection_string} switch  \"" . $this->svn_url . '/' . self::TAGS . $_name . '" ' . self::SVN_GET_RESULT_DIRECTIVE;
		return shell_exec($shell_command);
	}
	
	/**
	 * Switches the working copy to trunk
	 * 
	 * @return string
	 * */
	public function switchToTrunk() {
		$shell_command = "cd {$this->project_folder}" . $this->command_separator;
		$shell_command .= "{$this->svn_connection_string} switch " . $this->svn_url . '/' . self::TRUNK . ' ' . self::SVN_GET_RESULT_DIRECTIVE;
		return shell_exec($shell_command);
	}
	
	/**
	 * Switches the working copy to the supplied revision; if none is given will switch to HEAD revision
	 * 
	 * @param int $_revision_no revision to switch to
	 * @return string
	 * */
	public function switchToRevision($_revision_no = null) {
		$revision = isset($_revision_no) ? " -r{$_revision_no}" : null;
		$shell_command = "cd {$this->project_folder}" . $this->command_separator;
		$shell_command .= "{$this->svn_connection_string} update{$revision} " . self::SVN_GET_RESULT_DIRECTIVE;
		return shell_exec($shell_command);
	}
	
	/**
	 * Gets info about slected project
	 * 
	 * The info may be: revision number, last author, modified at, etc.
	 * 
	 * @return string
	 * */
	public function getSvnInfo() {
		return $this->svn_info;
	}
	
	/**
	 * Get name of the branch or tag is reading from.
	 * When reading from trunk the method will return null
	 * 
	 * @return string|null
	 * */
	public function getCurrentPosition() {
		$info_pieces = explode("\n", $this->svn_info);
		$url = $this->is_windows ? $info_pieces[2] : $info_pieces[1];
		list($tag_or_branch, $name) = array_slice(explode('/', $url), -2);
		$tag_or_branch = $tag_or_branch . '/';
		if ($tag_or_branch == self::BRANCHES || $tag_or_branch == self::TAGS) {
			return $name;
		}
		//--- reading from trunk
		else {
			return null;
		}
	}
	
	/**
	 * Add a file or a set of files to repository
	 * 
	 * @param string $_path relative to repo root
	 * @return string
	 * */
	public function add($_path) {
		$shell_command = "cd {$this->project_folder}" . $this->command_separator;
		$shell_command .= "echo p|{$this->path_to_svn_bin} --force add {$_path} " . self::SVN_GET_RESULT_DIRECTIVE;
		return shell_exec($shell_command);
	}
	
	/**
	 * Commit one or more files to repository
	 * 
	 * @param string $_message commit message
	 * @return string
	 * */
	public function commit($_message) {
		$shell_command = "cd {$this->project_folder}" . $this->command_separator;
		$shell_command .= "echo p|{$this->svn_connection_string} commit --message \"{$_message}\" " . self::SVN_GET_RESULT_DIRECTIVE;
		
		return shell_exec($shell_command);
	}
	
	/**
	 * Add a file to repo and then commit it
	 * 
	 * @param string $_message commit message
	 * @param string $_path
	 * @return string
	 * */
	public function addAndCommit($_message, $_path) {
		$this->add($_path);
		return $this->commit($_message);
	}
}