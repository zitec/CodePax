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
 * for MySql engine
 *
 * @category CodePax
 * @subpackage Models
 * @copyright Copyright (c) 2012 Zitec COM srl, Romania
 * @license New BSD http://www.codepax.com/license.html
 * */
class CodePax_DbVersions_MySql extends CodePax_Sql implements CodePax_DbVersions_Interface {

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
        $query = $this->db->prepare('SELECT CONCAT(major, ".", minor, ".", point) AS version, date_added
			FROM ' . CodePax_DbVersions::TABLE_NAME . '
			WHERE script_type = ?
		 	ORDER BY date_added DESC LIMIT 1');
        $query->execute(array($_script_type));
        $version_array = $query->fetch(PDO::FETCH_ASSOC);

        if ($_script_type == CodePax_DBVersions::TYPE_CHANGE_SCRIPT) {
            $query->execute(array(CodePax_DbVersions::TYPE_BASELINE));
            $baseline_version_array = $query->fetch(PDO::FETCH_ASSOC);
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
        $params[] = $_script_type;
        $query = $this->db->prepare('INSERT INTO ' . CodePax_DbVersions::TABLE_NAME . '
			(major, minor, point, script_type, date_added)
			VALUES (?, ?, ?, ?, NOW())');
        $query->execute($params);
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
        $query = $this->db->prepare('SELECT * FROM ' . CodePax_DbVersions::TABLE_NAME
            . ' ORDER BY date_added DESC');
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
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
        $query = $this->db->prepare('SELECT COUNT(*) as count
			FROM ' . CodePax_DbVersions::TABLE_NAME . '
			WHERE script_type = ? AND CONCAT(major, ".", minor, ".", point) = ?
		 	');
        $query->execute(array($_script_type, $_version_string));
        $result = $query->fetchColumn();
        return ($result > 0);
    }
}
