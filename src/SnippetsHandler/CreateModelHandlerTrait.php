<?php

declare(strict_types=1);

/**
 *
 * @package    Zalt
 * @subpackage SnippetsHandler
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 */

namespace Zalt\SnippetsHandler;

use Zalt\Model\MetaModellerInterface;
use Zalt\SnippetsActions\SnippetActionInterface;

/**
 *
 * @package    Zalt
 * @subpackage SnippetsHandler
 * @since      Class available since version 1.0
 */
trait CreateModelHandlerTrait
{
    private MetaModellerInterface $_model;
    
    /**
     * Creates a model for getModel(). Called only for each new $action.
     *
     * @return MetaModellerInterface
     */
    abstract protected function createModel(SnippetActionInterface $action): MetaModellerInterface;

    /**
     * Returns the model for the current $action.
     *
     * @return MetaModellerInterface
     */
    protected function getModel(SnippetActionInterface $action): MetaModellerInterface
    {
        // Only get new model if there is no model or the model was for a different action
        if (! isset($this->_model)) { //} && $this->_model->getMetaModel()->isMeta('action', $this->currentAction))) {
            $this->_model = $this->createModel($action);
//            $this->_model->getMetaModel()->setMeta('action', $action);
        }

        return $this->_model;
    }
}