<?php

declare(strict_types=1);

/**
 *
 * @package    Zalt
 * @subpackage Snippets
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 */

namespace Zalt\Snippets;

use Zalt\Html\HtmlInterface;

/**
 *
 * @package    Zalt
 * @subpackage Snippets
 * @since      Class available since version 1.0
 */
class HtmlContentSnippet extends SnippetAbstract
{
    /**
     * @var string|\Zalt\Html\HtmlInterface
     */
    protected string|HtmlInterface $htmlContent = '';

    public function getHtmlOutput()
    {
        return $this->htmlContent;
    }
}