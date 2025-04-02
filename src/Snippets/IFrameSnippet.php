<?php

declare(strict_types=1);

/**
 * @package    Zalt
 * @subpackage Snippets
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 */

namespace Zalt\Snippets;

use Zalt\Html\IFrame;
use Zalt\Snippets\SnippetAbstract;

/**
 * @package    Zalt
 * @subpackage Snippets
 * @since      Class available since version 1.0
 */
class IFrameSnippet extends SnippetAbstract
{
    protected ?string $iframeUrl = null;

    public function getHtmlOutput()
    {
        return new IFrame($this->iframeUrl, $this->attributes);
    }

    public function hasHtmlOutput(): bool
    {
        return (bool) $this->iframeUrl;
    }
}