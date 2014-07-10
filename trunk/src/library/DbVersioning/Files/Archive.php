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
 * Handles the test data compression and decompression
 *
 * This class uses ZipArchive and therefore requires
 * the php_zip.dll extension to be enabled
 * or --enable-zip
 *
 * @category CodePax
 * @subpackage DbVersioning
 * @copyright Copyright (c) 2012 Zitec COM srl, Romania
 * @license New BSD http://www.codepax.com/license.html
 * */
class CodePax_DbVersioning_Files_Archive {

    /**
     * Takes the input file and compress it with the
     * destination name
     *
     * For a given input file "/var/data/data.sql" and
     * a destination "/var/www/data/data.zip":
     * 1. a zip archive with the @$_destination_file will be created
     * 2. the zip archive will contain the file "data.sql"
     *
     * @param string $_source_file i.e /var/data/data.sql
     * @param string $_destination_file i.e /var/www/data/data.zip
     * @return void
     * @throws CodePax_DbVersioning_Exception missing file or ZIP handling error
     * */
    public static function compress($_source_file, $_destination_file)
    {
        if (is_file($_source_file)) {
            $zip = new ZipArchive();
            if ($zip->open($_destination_file, ZIPARCHIVE::OVERWRITE) === true) {
                $zip->addFile($_source_file, basename($_source_file));
                $zip->close();
            } else {
                throw new CodePax_DbVersioning_Exception('Could not open/create the ZIP file for writting');
            }
        } else {
            throw new CodePax_DbVersioning_Exception('Colud not compress file: source file is missing');
        }
    }

    /**
     * Un compress the input file into the destination file
     *
     * @param string $_destination_file i.e /var/www/data/data.zip
     * @param string $_source_file i.e /var/data/data.sql
     * @return void
     * @throws CodePax_DbVersioning_Exception missing file or ZIP handling error
     * */
    public static function unCompress($_source_file, $_destination_file)
    {
        if (is_file($_source_file)) {
            $zip = new ZipArchive();
            if ($zip->open($_source_file) === true) {
                $zip->extractTo(dirname($_destination_file));
                $zip->close();
            } else {
                throw new CodePax_DbVersioning_Exception('Could not open the ZIP file for reading');
            }
        } else {
            throw new CodePax_DbVersioning_Exception('Colud not un-compress file: source file is missing');
        }
    }
}
