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

use Zalt\Model\Data\DataReaderInterface;

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
    /**
     *
     * @var \Zalt\Model\Data\DataReaderInterface
     */
    protected $model;

    /**
     * Creates the model
     *
     * @return \Zalt\Model\ModelAbstract
     */
    protected function createModel(): DataReaderInterface
    {
        return $this->model;
    }
}
