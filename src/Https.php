<?php

/**
 *
 * @package    MUtil
 * @subpackage Https
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 */

namespace MUtil;

/**
 * Static utility function for determining wether https is on.
 *
 * @package    MUtil
 * @subpackage Https
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.0
 */
class Https
{
    /**
     * Reroutes if http was not used
     *
     * @return void
     */
    public static function enforce()
    {
        if (self::on() || \MUtil\Console::isConsole() || \Zend_Session::$_unitTestEnabled) {
            return;
        }

        $request    = \Zend_Controller_Front::getInstance()->getRequest();
        $url        = 'https://' . $_SERVER['HTTP_HOST'] . $request->getRequestUri();
        $redirector = \Zend_Controller_Action_HelperBroker::getStaticHelper('redirector');
        $redirector->gotoUrl($url);

    }

    /**
     * True when the url is a HTTPS url, false when HTTP, null otherwise
     *
     * @return boolean True when HTTPS, false when HTTP, null otherwise
     */
    public static function isHttps($url)
    {
        $url = strtolower(substr($url, 0, 8));

        if ('https://' == $url) {
            return true;
        }

        if ('http://' == substr($url, 0, 7)) {
            return false;
        }
        return null;
    }

    /**
     * True when https is used.
     *
     * @return boolean
     */
    public static function on()
    {
        if (isset($_SERVER['SERVER_PORT']) && ($_SERVER['SERVER_PORT'] == 443)) {
            return true;
        }

        if (empty($_SERVER['HTTPS'])) {
            return false;
        }

        return strtolower($_SERVER['HTTPS']) !== 'off';
    }
}