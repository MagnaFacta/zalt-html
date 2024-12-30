<?php

declare(strict_types=1);

/**
 * @package    Zalt
 * @subpackage SnippetsActions\Html
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 */

namespace Zalt\SnippetsActions\Html;

use Zalt\Html\HtmlInterface;
use Zalt\Snippets\HtmlContentSnippet;
use Zalt\SnippetsActions\AbstractAction;

/**
 * @package    Zalt
 * @subpackage SnippetsActions\Html
 * @since      Class available since version 1.0
 */
class HtmlAction extends AbstractAction
{
    /**
     * @var array Of snippet class names
     */
    protected array $_snippets = [
        HtmlContentSnippet::class,
        ];

    /**
     * @var string|\Zalt\Html\HtmlInterface
     */
    public string|HtmlInterface $htmlContent = '';
}