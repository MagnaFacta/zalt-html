<?php

/**
 *
 * @package    Zalt
 * @subpackage Snippets
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 */

namespace Zalt\Snippets;

use Zalt\Html\HtmlInterface;

/**
 * A snippet is a piece of html output that can be reused on multiple places in the code
 * or that isolates the processing needed for that output.
 *
 * Variables are intialized using the \Zalt\Registry\TargetInterface mechanism.
 * The snippet is then rendered using \Zalt\Html\HtmlInterface.
 *
 * The only "program flow" that can be initiated by a snippet is that it can reroute
 * the browser to another page.
 *
 * @see \Zalt\Html\HtmlInterface
 *
 * @package    Zalt
 * @subpackage Snippets
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.1
 */
interface SnippetInterface extends HtmlInterface
{
    /**
     * Create the snippets content
     *
     * This is a stub function either override getHtmlOutput() or override render()
     *
     * @return mixed Something that can be rendered
     */
    public function getHtmlOutput();
        
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
     * @return string Nothing or a string that can be used for redirection
     */
    public function getRedirectRoute(): ?string;

    /**
     * The place to check if the data set in the snippet is valid
     * to generate the snippet.
     *
     * When invalid data should result in an error, you can throw it
     * here but you can also perform the check in the
     * checkRegistryRequestsAnswers() function from the
     *
     * @return boolean
     */
    public function hasHtmlOutput(): bool;
}
