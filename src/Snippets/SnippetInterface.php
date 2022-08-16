<?php

/**
 *
 * @package    MUtil
 * @subpackage Snippets
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 */

namespace MUtil\Snippets;

/**
 * A snippet is a piece of html output that can be reused on multiple places in the code
 * or that isolates the processing needed for that output.
 *
 * Variables are intialized using the \MUtil\Registry\TargetInterface mechanism.
 * The snippet is then rendered using \MUtil\Html\HtmlInterface.
 *
 * The only "program flow" that can be initiated by a snippet is that it can reroute
 * the browser to another page.
 *
 * @see \MUtil\Registry\TargetInterface
 * @see \MUtil\Html\HtmlInterface
 *
 * @package    MUtil
 * @subpackage Snippets
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.1
 */
interface SnippetInterface extends \MUtil\Registry\TargetInterface, \MUtil\Html\HtmlInterface
{
    /**
     * When hasHtmlOutput() is false a snippet code user should check
     * for a redirectRoute. Otherwise the redirect calling render() will
     * execute the redirect.
     *
     * This function should never return a value when the snippet does
     * not redirect.
     *
     * Also when hasHtmlOutput() is true this function should not be
     * called.
     *
     * @see \Zend_Controller_Action_Helper_Redirector
     *
     * @return mixed Nothing or either an array or a string that is acceptable for Redector->gotoRoute()
     */
    public function getRedirectRoute();

    /**
     * The place to check if the data set in the snippet is valid
     * to generate the snippet.
     *
     * When invalid data should result in an error, you can throw it
     * here but you can also perform the check in the
     * checkRegistryRequestsAnswers() function from the
     * {@see \MUtil\Registry\TargetInterface}.
     *
     * @return boolean
     */
    public function hasHtmlOutput();

    /**
     * When there is a redirectRoute this function will execute it.
     *
     * When hasHtmlOutput() is true this functions should not be called.
     *
     * @see \Zend_Controller_Action_Helper_Redirector
     */
    public function redirectRoute();
}
