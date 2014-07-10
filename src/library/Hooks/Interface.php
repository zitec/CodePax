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
 * Hooks interface to be implemented
 * by user defined hooks
 *
 * @category CodePax
 * @subpackage Hooks
 * @copyright Copyright (c) 2012 Zitec COM srl, Romania
 * @license New BSD http://www.codepax.com/license.html
 * */
interface CodePax_Hooks_Interface
{

    /**
     * Executes the hook
     *
     * @return void
     * */
    public function run();

    /**
     * Returns the output provided
     * by the hook
     *
     * @return mixed
     * */
    public function getOutput();
}
