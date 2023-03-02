<?php

declare(strict_types=1);

/**
 *
 * @package    Zalt
 * @subpackage Snippets\Zend
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 */

namespace Zalt\Snippets\Zend;

use Symfony\Contracts\Translation\TranslatorInterface;
use Zalt\Base\RequestInfo;
use Zalt\Message\MessengerInterface;
use Zalt\Model\Data\FullDataInterface;
use Zalt\Snippets\FullDataGenericModelTrait;
use Zalt\SnippetsLoader\SnippetOptions;

/**
 *
 * @package    Zalt
 * @subpackage Snippets\Zend
 * @since      Class available since version 1.0
 */
class ZendModelFormSnippet extends ZendModelFormSnippetAbstract
{
    use FullDataGenericModelTrait;

    public function __construct(SnippetOptions $snippetOptions, RequestInfo $requestInfo, TranslatorInterface $translate, MessengerInterface $messenger, FullDataInterface $model)
    {
        parent::__construct($snippetOptions, $requestInfo, $translate, $messenger);

        // We're setting trait variables so no constructor promotion
        $this->model = $model;
    }
}