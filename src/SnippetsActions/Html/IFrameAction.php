<?php

declare(strict_types=1);

/**
 * @package    Zalt
 * @subpackage SnippetsActions\Html
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 */

namespace Zalt\SnippetsActions\Html;

use Zalt\Snippets\IFrameSnippet;
use Zalt\SnippetsActions\AbstractAction;

/**
 * @package    Zalt
 * @subpackage SnippetsActions\Html
 * @since      Class available since version 1.0
 */
class IFrameAction extends AbstractAction
{
    /**
     * @var array Of snippet class names
     */
    protected array $_snippets = [
        IFrameSnippet::class,
    ];

    public string $iframeUrl = '';
}