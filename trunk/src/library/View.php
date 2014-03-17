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
 * View class that renders view files used by the application
 *
 * @category CodePax
 * @copyright Copyright (c) 2012 Zitec COM srl, Romania
 * @license New BSD http://www.codepax.com/license.html
 * */
class CodePax_View {

    /**
     * The default extension for the view files is `phtml`. If for any reason
     * the view files will use another extension than the default one, this
     * constant should be changed accordingly
     */
    const VIEW_FILE_EXTENSION = 'phtml';

    /**
     * Holds the absolute path where the view files resides
     *
     * @var string
     */
    protected $_views_path;

    /**
     * Holds the current view name
     *
     * @var string
     */
    protected $_view;

    /**
     * The views path can be set in the constructor also
     *
     * @param false|string $_views_path
     * @return void
     */
    public function __construct($_views_path = false)
    {
        if ($_views_path) {
            $this->setViewsPath($_views_path);
        }
    }

    /**
     * Magic method to determine if a variable
     * was set to be used in the view or not
     *
     * @param string $_name
     * @return bool
     */
    public function __isset($_name)
    {
        if ($this->_isPublicProperty($_name)) {
            return isset($this->$_name);
        }

        return false;
    }

    /**
     * Intercept direct assign of a variable to the view script.
     *
     * @param string $_name The variable name
     * @param mixed $_value The variable value
     * @throws View_Exception if the user tries to set a private or protected
     * property directly
     */
    public function __set($_name, $_value)
    {
        // check first to be sure that $_name is not a protected or private
        // property. this is done by testing the first character of the name
        // against the _ (underscore). this is not a general rule, but the rule
        // that this class uses to set private or protected properties
        if ($this->_isPublicProperty($_name)) {
            $this->set($_name, $_value);
            return;
        }

        $message = sprintf('Setting protected/private properties is not allowed');
        throw new CodePax_View_Exception($message);
    }

    /**
     * Magic method to intercept all the properties that do not exist
     * and see if they are variables set to be used in the view or not.
     * If true, the variable is returned, false otherwise.
     *
     * @param string $_name
     * @return boo|mixed
     */
    public function __get($_name)
    {
        return isset($this->$_name) ? $this->$_name : false;
    }

    /**
     * Set variables to be used in the view files.
     *
     * The variables are set directly as public properties of the class because
     * if we have used a container (eg. array of variables) to hold all this
     * variables, when we would have wanted to do some operations on arrays that
     * work by reference (eg. array_pop), we would have received notices of
     * indirect modification of overloaded property.
     *
     * @param string $_variable The variable name
     * @param mixed $_value The variable value
     */
    public function set($_variable, $_value)
    {
        $this->$_variable = $_value;
    }

    /**
     * Sets the absolute path where the view files will be found
     *
     * @param string $_views_path
     * @return void
     */
    public function setViewsPath($_views_path)
    {
        $this->_views_path = $_views_path;

        if (substr($_views_path, -1) != DIRECTORY_SEPARATOR) {
            $this->_views_path .= DIRECTORY_SEPARATOR;
        }
    }

    /**
     * Set the name for the view that should be rendered
     *
     * @param string $_name
     */
    public function setCurrentView($_name)
    {
        $this->_view = $_name;
    }

    /**
     * Render the current view and extracts the variables that were set.
     * If the corresponding view file does not exists an error is thrown
     *
     * @throws View_Exception if the view file does not exists
     */
    public function render()
    {
        $view_file = $this->_getPath();
        if (file_exists($view_file)) {
            include_once $view_file;
        } else {
            $message = sprintf('View file %s does not exists', $this->_view);
            throw new CodePax_View_Exception($message);
        }
    }

    /**
     * Formats the current view path and returns it
     *
     * @return string
     */
    protected function _getPath()
    {
        $view_file = $this->_views_path . $this->_view . '.'
            . self::VIEW_FILE_EXTENSION;
        return $view_file;
    }

    /**
     * Test if a name can be used for a public member class or not.
     *
     * A <i>name</i> can be used as a public property if it does not starts
     * with an underscore (_). This is not a general rule, is just the rule
     * that this class uses.
     *
     * @param string $_name The property name
     * @return bool
     */
    protected function _isPublicProperty($_name)
    {
        return '_' != substr($_name, 0, 1);
    }
}
