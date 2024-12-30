<?php

declare(strict_types=1);

/**
 * @package    Zalt
 * @subpackage Snippets
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 */

namespace Zalt\Snippets;

use Zalt\Base\RequestInfo;
use Zalt\Html\Raw;
use Zalt\SnippetsLoader\SnippetOptions;

/**
 * @package    Zalt
 * @subpackage Snippets
 * @since      Class available since version 1.0
 */
class HtmlFromFileContentSnippet extends HtmlContentSnippet
{
    protected string $contentFile = '';

    public function __construct(SnippetOptions $snippetOptions, RequestInfo $requestInfo)
    {
        parent::__construct($snippetOptions, $requestInfo);

        $this->loadContent();
    }

    protected function loadContent()
    {
        if ($this->contentFile && file_exists($this->contentFile)) {
            $this->htmlContent = new Raw(file_get_contents($this->contentFile));
        }
    }
}