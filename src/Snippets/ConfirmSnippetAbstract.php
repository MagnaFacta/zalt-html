<?php

/**
 *
 * @package    Zalt
 * @subpackage YesNoDeleteSnippet
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2016 Erasmus MC
 * @license    New BSD License
 */

namespace Zalt\Snippets;

use Zalt\Html\Html;

/**
 *
 *
 * @package    Zalt
 * @subpackage YesNoDeleteSnippet
 * @copyright  Copyright (c) 2016 Erasmus MC
 * @license    New BSD License
 * @since      Class available since \Zalt version 1.8.2 Sep 7, 2016 5:39:53 PM
 */
abstract class ConfirmSnippetAbstract extends \Zalt\Snippets\MessageableSnippetAbstract
{
    use ConfirmSnippetTrait;

    /**
     * @var string After action actual return url
     */
    protected string $afterActionRouteUrl = '';

    /**
     * Create the snippets content
     *
     * This is a stub function either override getHtmlOutput() or override render()
     *
     * @return \Zalt\Html\HtmlInterface Something that can be rendered
     */
    public function getHtmlOutput()
    {
        return $this->getHtmlQuestion();
    }

    /**
     * When hasHtmlOutput() is false a snippet user should check
     * for a redirectRoute.
     *
     * When hasHtmlOutput() is true this functions should not be called.
     *
     * @return string|null Nothing or a string that is acceptable for Redector->gotoRoute()
     */
    public function getRedirectRoute(): ?string
    {
        return $this->afterActionRouteUrl;
    }

    /**
     * The place to check if the data set in the snippet is valid
     * to generate the snippet.
     *
     * @return boolean
     */
    public function hasHtmlOutput(): bool
    {
        return (! $this->isActionConfirmed()) && parent::hasHtmlOutput();
    }

    /**
     * Set what to do when the form is 'finished'.
     */
    protected function setAfterActionRoute()
    {
        $url = $this->getAfterActionUrl();
        if ($url) {
            $this->afterActionRouteUrl = $url;
        }
    }
}
