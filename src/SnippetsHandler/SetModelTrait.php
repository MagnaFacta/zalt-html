<?php

declare(strict_types=1);


/**
 * @package    Zalt
 * @subpackage SnippetsHandler
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 */

namespace Zalt\SnippetsHandler;

use Zalt\Model\MetaModellerInterface;
use Zalt\SnippetsActions\ApplyActionInterface;
use Zalt\SnippetsActions\SnippetActionInterface;

/**
 * @package    Zalt
 * @subpackage SnippetsHandler
 * @since      Class available since version 1.0
 */
trait SetModelTrait
{
    private MetaModellerInterface $_model;

    protected function getModel(SnippetActionInterface $action): MetaModellerInterface
    {
        if ($this->_model instanceof ApplyActionInterface) {
            $this->_model->applyAction($action);
        }
        return $this->_model;
    }

    protected function setModel(MetaModellerInterface $model)
    {
        $this->_model = $model;
    }
}
