<?php

declare(strict_types=1);

/**
 * @package    Zalt
 * @subpackage Snippets
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 */

namespace Zalt\Snippets;

use DOMDocument;
use DOMElement;
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

    protected string $tagName = '';

    public function __construct(SnippetOptions $snippetOptions, RequestInfo $requestInfo)
    {
        parent::__construct($snippetOptions, $requestInfo);

        $this->loadContent();
    }

    protected function loadContent()
    {
        if ($this->tagName) {
            $content = '';
            $dom     = new DOMDocument();

            // Prevent strange tags to trigger an error:
            // https://stackoverflow.com/questions/9149180/domdocumentloadhtml-error
            libxml_use_internal_errors(true);
            $dom->loadHTMLFile($this->contentFile);
            libxml_use_internal_errors(false);
            $items   = $dom->getElementsByTagName($this->tagName);

            foreach ($items as $item) {
                if ($item instanceof DOMElement) {
                    $content .= $dom->saveHTML($item);
                }
            }
            $this->htmlContent = new Raw($content);

        } elseif ($this->contentFile && file_exists($this->contentFile)) {
            $this->htmlContent = new Raw(file_get_contents($this->contentFile));
        }
    }
}