<?php

/**
 *
 * @package    Zalt
 * @subpackage Snippets\Standard
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2015 Erasmus MC
 * @license    New BSD License
 */

namespace Zalt\Snippets\Standard;

use Zalt\Snippets\ModelConfirmDataChangeSnippetAbstract;

/**
 *
 *
 * @package    Zalt
 * @subpackage Snippets\Standard
 * @copyright  Copyright (c) 2015 Erasmus MC
 * @license    New BSD License
 * @since      Class available since \Zalt version 1.7.2 30-sep-2015 19:15:02
 */
class ModelConfirmDataChangeSnippet extends ModelConfirmDataChangeSnippetAbstract
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
