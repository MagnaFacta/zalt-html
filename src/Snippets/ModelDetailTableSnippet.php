<?php


/**
 *
 * @package    Zalt
 * @subpackage Snippets
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 */

namespace Zalt\Snippets;

use Zalt\Base\RequestInfo;
use Zalt\Base\TranslatorInterface;
use Zalt\Model\Data\DataReaderInterface;
use Zalt\SnippetsLoader\SnippetOptions;

/**
 * Displays each field of a single item in a model in a row in a Html table
 * the model set through the $model snippet parameter.
 *
 * @package    Zalt
 * @subpackage Snippets
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.2
 */
class ModelDetailTableSnippet extends ModelDetailTableSnippetAbstract
{
    use DataReaderGenericModelTrait;
    
    public function __construct(SnippetOptions $snippetOptions, RequestInfo $requestInfo, TranslatorInterface $translate, DataReaderInterface $model)
    {
        parent::__construct($snippetOptions, $requestInfo, $translate);

        $this->model = $model;
    }
}
