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
 * Format the SVN response returned from the server
 * 
 * It will add a <span> tag for every line
 * For lines starting "C" the "svn_ouotput_conflict" class will be added
 * For lines not starting with "C" "svn_output_ok"
 * 
 * @category CodePax
 * @package View
 * @copyright Copyright (c) 2012 Zitec COM srl, Romania
 * @license New BSD http://www.codepax.com/license.html
 * */
class CodePax_View_Helpers_FormatOutput {
	
	const SVN_OUTPUT_CONFLICT = 'svn_ouotput_conflict';
	const SVN_OUTPUT_OK = 'svn_output_ok';
	
	/**
	 * Callback function that formats the lines
	 * 
	 * @param string $_line
	 * @return string formatted line
	 * */
	public static function formatLines($_line) {
		$line_pattern = '<span class="%s">%s</span>';
		$trimmed_line = trim($_line);
		$css_class = self::SVN_OUTPUT_OK;
		// line starts with "c" or with "svn:"
		if ((isset($trimmed_line[0]) && strtolower($trimmed_line[0]) == 'c')
			|| substr($trimmed_line, 0, 4) == 'svn:') {
			$css_class = self::SVN_OUTPUT_CONFLICT;
		}
		return sprintf($line_pattern, $css_class, $_line);
	}
	
	/**
	 * Format the SVN reponse by adding some
	 * CSS classes
	 * 
	 * @param string $_input_string
	 * @return string the formatted string
	 * */
	public static function format($_input_string) {
		$input_lines = explode("\n", $_input_string);
		return implode("\n", array_map(
			array('CodePax_View_Helpers_FormatOutput', 'formatLines'),
			$input_lines));
	}
	
}