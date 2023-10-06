<?php

declare(strict_types=1);

/**
 *
 * @package    Zalt
 * @subpackage Snippets
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 */

namespace Zalt\Snippets;

use Zalt\Base\RequestInfo;
use Zalt\Base\TranslateableTrait;
use Zalt\Base\TranslatorInterface;
use Zalt\SnippetsLoader\SnippetOptions;

/**
 *
 * @package    Zalt
 * @subpackage Snippets
 * @since      Class available since version 1.0
 */
class TranslatableSnippetAbstract extends SnippetAbstract
{
    use TranslateableTrait;

    public function __construct(
        SnippetOptions $snippetOptions,
        RequestInfo $requestInfo, 
        TranslatorInterface $translate)
    {
        // We're setting trait variables so no constructor promotion
        $this->translate   = $translate;

        parent::__construct($snippetOptions, $requestInfo);
    }
}