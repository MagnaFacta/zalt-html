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
abstract class ModelSnippetHandlerAbstract extends SnippetHandler
{
    /**
     * Returns the model for the current $action.
     *
     * The parameters allow you to easily adapt the model to the current action. The $detailed
     * parameter was added, because the most common use of action is a split between detailed
     * and summarized actions.
     *
     * @return MetaModellerInterface
     */
    abstract protected function getModel(SnippetActionInterface $action): MetaModellerInterface;
    
    public function prepareAction(SnippetActionInterface $action) : void
    {
        $model = $this->getModel($action);
        
        $this->responder->processModel($model);

        parent::prepareAction($action);
    }
}