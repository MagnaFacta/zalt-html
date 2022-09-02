<?php

declare(strict_types=1);

/**
 *
 * @package    Zalt
 * @subpackage Snippets
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 */

namespace Zalt\Snippets;

use Zalt\Html\Html;

/**
 *
 * @package    Zalt
 * @subpackage Snippets
 * @since      Class available since version 1.0
 */
class DivClass extends SnippetAbstract
{
    protected ?string $content = null;
    
    protected ?string $style = null;
    
    public function getHtmlOutput()
    {
        return Html::div($this->content, ['style' => $this->style]);
    }
}