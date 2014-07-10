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
 * Class that handles "z_db_versions" table
 * for the Sql Server engine
 *
 * @category CodePax
 * @subpackage Models
 * @copyright Copyright (c) 2012 Zitec COM srl, Romania
 * @license New BSD http://www.codepax.com/license.html
 * */
class CodePax_DbVersions_SqlSrv extends CodePax_Sql implements CodePax_DbVersions_Interface {

    /**
     * Return the current DB version which can be
     * either a change script or a data change script
     *
     * The array keys are: version, date_added
     *
     * @param int $_script_type can take values of 0,1,2
     * @return array
     * */
    public function getLatestVersion($_script_type)
    {

        $query = 'SELECT TOP 1
			(CAST(major AS varchar) + \'.\' + CAST(minor AS varchar) + \'.\' + CAST(point AS varchar)) as version,
			date_added
			FROM ' . CodePax_DbVersions::TABLE_NAME . '
			WHERE script_type = ?
		 	ORDER BY date_added DESC';

        //set the script type param
        $stmt = sqlsrv_prepare($this->db, $query, array(&$_script_type));
        sqlsrv_execute($stmt);
        $version_array = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

        if (!empty($version_array)) {
            $version_array['date_added'] = $version_array['date_added']->format('Y-m-d H:i:s');
        }

        if ($_script_type == CodePax_DbVersions::TYPE_CHANGE_SCRIPT) {
            $_script_type = CodePax_DbVersions::TYPE_BASELINE;
            sqlsrv_execute($stmt);
            $baseline_version_array = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

            if (!empty($baseline_version_array)) {
                $baseline_version_array['date_added'] = $baseline_version_array['date_added']->format('Y-m-d H:i:s');
            }
            $version_array = CodePax_DbVersions::getLatestDbVersion($version_array, $baseline_version_array);
        }

        //when nothing is found, a default is returned
        return empty($version_array) ? CodePax_DbVersions::getDefaultVersion() : $version_array;
    }

    /**
     * Adds a new version to database. The version can be
     * a change script, a baseline or a data change script
     *
     * @param string $_version_string represented like x.y.z
     * @param int $_script_type can take values of 0,1,2
     * @return void
     * */
    public function addVersion($_version_string, $_script_type)
    {
        $params = explode('.', $_version_string);
        $params[] = &$_script_type;

        $query = 'INSERT INTO ' . CodePax_DbVersions::TABLE_NAME . '
			(major, minor, point, script_type, date_added)
			VALUES (?, ?, ?, ?, GETDATE())';

        $stmt = sqlsrv_prepare($this->db, $query, $params);
        sqlsrv_execute($stmt);
    }

    /**
     * Get all revisions and baselines registered
     * in DB versioning table
     *
     * Sample of the returned array
     * Array (
     * 	[0] => Array (
     * 			[id] => 36
     * 			[major] => 1
     * 			[minor] => 1
     * 			[point] => 0
     * 			[script_type] => true
     * 			[date_added] => 2010-08-25 11:07:47
     * 		)
     * )
     *
     * @return array
     * */
    public function getAll()
    {
        $query = 'SELECT * FROM ' . CodePax_DbVersions::TABLE_NAME
            . ' ORDER BY date_added DESC';

        $stmt = sqlsrv_prepare($this->db, $query);
        sqlsrv_execute($stmt);

        $rows = array();
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            if (!empty($row['date_added'])) {
                $row['date_added'] = $row['date_added']->format('Y-m-d H:i:s');
            }
            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * Returns true if version is already registred
     *
     * @param string $_version_string
     * @param int $_script_type
     * @return bool
     */
    public function checkIsVersionRegistred($_version_string, $_script_type)
    {
        $query = 'SELECT COUNT(*) as count
			FROM ' . CodePax_DbVersions::TABLE_NAME . '
			WHERE script_type = ? AND (CAST(major AS varchar) + \'.\' + CAST(minor AS varchar) + \'.\' + CAST(point AS varchar)) = ?';

        $stmt = sqlsrv_prepare($this->db, $query, array(&$_script_type, &$_version_string));
        sqlsrv_execute($stmt);
        $version_array = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

        return $version_array && $version_array['count'] > 0;
    }
}
