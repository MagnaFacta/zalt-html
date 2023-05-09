<?php

declare(strict_types=1);

/**
 *
 * @package    Zalt
 * @subpackage Snippets
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 */

namespace Zalt\Snippets;

use Symfony\Contracts\Translation\TranslatorInterface;
use Zalt\Base\RequestInfo;
use Zalt\Message\MessageTrait;
use Zalt\Message\MessengerInterface;
use Zalt\SnippetsLoader\SnippetOptions;

/**
 *
 * @package    Zalt
 * @subpackage Snippets
 * @since      Class available since version 1.0
 */
class MessageableSnippetAbstract extends TranslatableSnippetAbstract
{
    use MessageTrait;
    
    public function __construct(SnippetOptions $snippetOptions,
                                RequestInfo $requestInfo,
                                TranslatorInterface $translate,
                                MessengerInterface $messenger)
    {
        // We're setting trait variables so no constructor promotion
        $this->messenger = $messenger;

        parent::__construct($snippetOptions, $requestInfo, $translate);
    }
}