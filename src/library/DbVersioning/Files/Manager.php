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
 * Will handle operation at file system level
 * for change scripts and baselines
 * 
 * @category CodePax
 * @subpackage DbVersioning
 * @copyright Copyright (c) 2012 Zitec COM srl, Romania
 * @license New BSD http://www.codepax.com/license.html
 * */
class CodePax_DbVersioning_Files_Manager {
	
	/**
	 * Extension used for change scripts
	 * and baselines
	 * */
	const SQL_FILE_EXTENSION = '.sql';
	
	/**
	 * Extension for compressed test data
	 * filw
	 * */
	const ARCHIVE_FILE_EXTENSION = '.zip';
	
	/**
	 * Usually under /project_root/db/change_scripts/
	 * */
	protected static $change_scripts_path = '%s%s%schange_scripts';
	
	/**
	 * Usually under /project_root/db/data_change_scripts/
	 * */
	protected static $data_change_scripts_path = '%s%s%sdata_change_scripts';
	
	/**
	 * Usually under /project_root/db/baselines/
	 * */
	protected static $baselines_path = '%s%s%sbaselines';
	
	/**
	 * Usually under /project_root/db/test_data/data.zip
	 * */
	protected static $test_data_file = '%s%s%stest_data%sdata%s';
	
	/**
	 * Return the full path to baseline
	 * for the specified version
	 * 
	 * @param string $_version
	 * @return string path\to\baseline\1.0.4.sql
	 * @throws CodePax_DbVersioning_Exception no baseline found
	 * */
	public static function getBaselineByVersion($_version) {
		$baseline_path = sprintf(self::$baselines_path,
			PROJECT_DIR, DB_VERSIONING_DIR, DIRECTORY_SEPARATOR) 
			. DIRECTORY_SEPARATOR . $_version . self::SQL_FILE_EXTENSION;
		if(is_file($baseline_path)) {
			return $baseline_path;
		}
		else {
			throw new CodePax_DbVersioning_Exception('No baseline file was found');
		}
	}
	
	/** 
	 * Will return the newest baseline found
	 * on the file system
	 * 
	 * Will be used only on DEV, when the
	 * db_version table will not be available
	 * and we have to populate it starting with
	 * the latest baseline + the change scripts
	 * newer than the latest baseline
	 * 
	 * @return string baseling version represented like x.y.z
	 * */
	public static function getLatestBaselineVersion() {
		$baselines = array();
		$baselines_path = sprintf(self::$baselines_path,
			PROJECT_DIR, DB_VERSIONING_DIR, DIRECTORY_SEPARATOR);
		
		if (is_dir($baselines_path)) {
			$handle = opendir($baselines_path);
			if ($handle) {
				while (false !== ($file = readdir($handle))) {
					if ($file[0] != '.') {
						$baselines[] = str_replace(self::SQL_FILE_EXTENSION, '', $file);
					}
				}
				closedir($handle);
				natsort($baselines);
			}
		}
		return array_pop($baselines);
	}
	
	/**
	 * Return the full path to test data 
	 * 
	 * The returned file can be either a zip file
	 * or a sql one
	 * 
	 * @return string path\to\test_data\data.zip
	 * @throws CodePax_DbVersioning_Exception no test data found
	 * */
	public static function getTestDataFile() {
		$file_extension = defined('USE_TEST_DATA_COMPRESSION') === true 
			&& USE_TEST_DATA_COMPRESSION === true ? self::ARCHIVE_FILE_EXTENSION : self::SQL_FILE_EXTENSION;
		
		$test_data_file = sprintf(self::$test_data_file, PROJECT_DIR, DB_VERSIONING_DIR,
			DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, $file_extension);
			
		if (is_file($test_data_file)) {
			return $test_data_file;
		}
		else {
			throw new CodePax_DbVersioning_Exception('No test data file was found');
		}
	}
	
	/**
	 * Reads the chnage script list for regular and data 
	 * change scripts
	 * 
	 * The returned array looks like this:
	 * Array (
	 * 	[1.1.2] => /project_root/db/change_scripts/1.1.2.sql
	 * 	[1.1.3] => /project_root/db/change_scripts/1.1.3.sql
	 * 	)
	 * 
	 * @param $_change_scripts_path regular or data change scripts location
	 * @param string $_version comes like x.y.z
	 * @return array
	 * */
	protected static function readChangeScriptsByVersion($_change_scripts_path, $_version) {
		$change_scripts = array();
		if (is_dir($_change_scripts_path)) {
			$handle = opendir($_change_scripts_path);
			if ($handle) {
				while (false !== ($file = readdir($handle))) {
					$change_script = str_replace(self::SQL_FILE_EXTENSION, '', $file);
					if ($file[0] != '.' && version_compare($change_script, $_version) == 1) {
						$change_scripts[$change_script] = $_change_scripts_path . DIRECTORY_SEPARATOR . $file;
					}
				}
				closedir($handle);
				natsort($change_scripts);
			}
		}
		return $change_scripts;
	}
	
	/**
	 * Get all change scripts with the version number
	 * higher than $_version
	 * 
	 * The returned array looks like this:
	 * Array (
	 * 	[1.1.2] => /project_root/db/change_scripts/1.1.2.sql
	 * 	[1.1.3] => /project_root/db/change_scripts/1.1.3.sql
	 * 	)
	 * 
	 * @param string $_version comes like x.y.z
	 * @return array
	 * */
	public static function getChangeScriptsByVersion($_version) {
		$change_scripts_path = sprintf(self::$change_scripts_path,
			PROJECT_DIR, DB_VERSIONING_DIR, DIRECTORY_SEPARATOR);
		return self::readChangeScriptsByVersion($change_scripts_path, $_version);
	}
	
	/**
	 * Get all data change scripts with the version number
	 * higher than $_version
	 * 
	 * The returned array looks like this:
	 * Array (
	 * 	[1.1.2] => /project_root/db/data_change_scripts/1.1.2.sql
	 * 	[1.1.3] => /project_root/db/data_change_scripts/1.1.3.sql
	 * 	)
	 * 
	 * @param string $_version comes like x.y.z
	 * @return array
	 * */
	public static function getDataChangeScriptsByVersion($_version) {
		$data_change_scripts_path = sprintf(self::$data_change_scripts_path,
			PROJECT_DIR, DB_VERSIONING_DIR, DIRECTORY_SEPARATOR);
		return self::readChangeScriptsByVersion($data_change_scripts_path, $_version);
	}
	
	/**
	 * Get path to baseline directory
	 * 
	 * @return string
	 * */
	public static function getPathToBaselines() {
		return sprintf(self::$baselines_path, PROJECT_DIR,
			DB_VERSIONING_DIR, DIRECTORY_SEPARATOR);
	}

}