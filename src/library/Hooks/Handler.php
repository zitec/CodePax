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
 * Hooks handler class
 *
 * @category CodePax
 * @subpackage Hooks
 * @copyright Copyright (c) 2012 Zitec COM srl, Romania
 * @license New BSD http://www.codepax.com/license.html
 * */
class CodePax_Hooks_Handler {

    protected $hooks_allowed = false;
    protected $hooks = array();
    protected $hooks_output = array();

    const HOOK_NAME_PATTERN = '%s_Handler';

    /**
     * Checks whether the hooks are enabled or not
     *
     * @return void
     * */
    public function __construct()
    {
        if (defined('USE_HOOKS') && USE_HOOKS === true) {
            $this->hooks_allowed = true;
        }
    }

    /**
     * Get all hooks
     *
     * @return array
     * */
    public function getList()
    {
        if ($this->hooks_allowed === true && empty($this->hooks) && $handle = opendir(HOOKS_DIR)) {
            while (false !== ($file = readdir($handle))) {
                $full_path = HOOKS_DIR . DIRECTORY_SEPARATOR . $file;
                // do the rest of checking only if we deal with a directory
                if (is_dir($full_path)) {
                    if ($file[0] != '.') {
                        $hook_class_name = sprintf(self::HOOK_NAME_PATTERN, $file);
                        if (class_exists($hook_class_name, true) && in_array('CodePax_Hooks_Interface', class_implements($hook_class_name))) {
                            $this->hooks[$file] = $hook_class_name;
                        }
                    }
                }
            }
            closedir($handle);
        }
        return $this->hooks;
    }

    /**
     * Run all hooks found
     *
     * @return void
     * */
    public function run(array $_hooks)
    {
        if ($this->hooks_allowed === true) {
            foreach ($_hooks as $hook => $hook_class_name) {
                /**
                 * @var CodePax_Hooks_Interface
                 * */
                $hook_class = new $hook_class_name();
                $hook_class->run();
                $this->hooks_output[$hook] = $hook_class->getOutput();
            }
        }
    }

    /**
     * Get the hook result list
     *
     * Sample of the returned array
     * (
     * 		[EncryptSources] => encriptarea a fost terminata
     * 		[RestartApache] => apache a fost restartat
     * 	)
     *
     * @return array
     * */
    public function getResults()
    {
        return $this->hooks_output;
    }
}
