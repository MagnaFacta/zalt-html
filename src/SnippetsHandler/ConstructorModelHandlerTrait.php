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
 * Not yet used, but maybe soon!
 *
 * @package    Zalt
 * @subpackage SnippetsHandler
 * @since      Class available since version 1.0
 */
trait ConstructorModelHandlerTrait
{
    /**
     * @var \Zalt\Model\MetaModellerInterface Load in Constructor!
     */
    protected MetaModellerInterface $model;

    /**
     * Returns the model for the current $action.
     *
     * @return MetaModellerInterface
     */
    protected function getModel(SnippetActionInterface $action): MetaModellerInterface
    {
//        static $applyAction = false; 
//        if ($applyAction && ($this->model instanceof SomeInterface)) {
//            $applyAction = true;
//            $this->model->applyAction($action);
//        }
        return $this->model;
    }
    
}