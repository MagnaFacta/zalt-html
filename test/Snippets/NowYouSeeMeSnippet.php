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
class NowYouSeeMeSnippet extends SnippetAbstract
{
    protected bool $visibility = false;

    public function hasHtmlOutput() : bool
    {
        return $this->visibility;
    }
}