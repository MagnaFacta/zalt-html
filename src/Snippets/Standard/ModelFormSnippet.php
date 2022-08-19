<?php

/**
 *
 * @package    Zalt
 * @subpackage Snippets
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 */

namespace Zalt\Snippets\Standard;

/**
 * Processes and displays a model form using the model
 * set through the $model snippet parameter.
 *
 * @package    Zalt
 * @subpackage Snippets
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.4
 */
class ModelFormSnippet extends \Zalt\Snippets\ModelFormSnippetAbstract
{
    /**
     *
     * @var \Zalt\Model\ModelAbstract
     */
    protected $model;

    /**
     * Creates the model
     *
     * @return \Zalt\Model\ModelAbstract
     */
    protected function createModel()
    {
        return $this->model;
    }
}
