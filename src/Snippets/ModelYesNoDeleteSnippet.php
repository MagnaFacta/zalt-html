<?php

/**
 *
 *
 * @package    Zalt
 * @subpackage Snippets
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 */

namespace Zalt\Snippets;

use Symfony\Contracts\Translation\TranslatorInterface;
use Zalt\Base\RequestInfo;
use Zalt\Model\Data\DataReaderInterface;
use Zalt\SnippetsLoader\SnippetOptions;

/**
 * Ask conformation for deletion and deletes item when confirmed.
 *
 * The model is set through the $model snippet parameter.
 *
 * @package    Zalt
 * @subpackage Snippets
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.4.4
 */
class ModelYesNoDeleteSnippet extends ModelYesNoDeleteSnippetAbstract
{
    use FullDataGenericModelTrait;

    public function __construct(SnippetOptions $snippetOptions, RequestInfo $requestInfo, TranslatorInterface $translate, DataReaderInterface $model)
    {
        parent::__construct($snippetOptions, $requestInfo, $translate);

        $this->model = $model;
    }
}
