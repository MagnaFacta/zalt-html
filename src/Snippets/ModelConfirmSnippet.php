<?php

declare(strict_types=1);

/**
 * @package    Zalt
 * @subpackage Snippets
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 */

namespace Zalt\Snippets;

use Zalt\Base\RequestInfo;
use Zalt\Base\TranslatorInterface;
use Zalt\Message\MessengerInterface;
use Zalt\Model\Data\DataReaderInterface;
use Zalt\SnippetsLoader\SnippetOptions;

/**
 * @package    Zalt
 * @subpackage Snippets
 * @since      Class available since version 1.0
 */
class ModelConfirmSnippet extends ModelConfirmSnippetAbstract
{
    use DataReaderGenericModelTrait;

    public function __construct(
        SnippetOptions $snippetOptions,
        RequestInfo $requestInfo,
        TranslatorInterface $translate,
        MessengerInterface $messenger,
        DataReaderInterface $model)
    {
        parent::__construct($snippetOptions, $requestInfo, $translate, $messenger);

        $this->model = $model;
    }
}