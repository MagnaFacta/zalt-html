<?php

declare(strict_types=1);

/**
 *
 * @package    Zalt
 * @subpackage Snippets
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 */

namespace Zalt\Snippets;

/**
 *
 * @package    Zalt
 * @subpackage Snippets
 * @since      Class available since version 1.0
 */
class HtmlContentSnippet extends \MUtil\Snippets\SnippetAbstract
{
    /**
     * @var \Zalt\Html\HtmlInterface
     */
    protected $htmlContent;

    public function getHtmlOutput(\Zend_View_Abstract $view)
    {
        return $this->htmlContent;
    }


}