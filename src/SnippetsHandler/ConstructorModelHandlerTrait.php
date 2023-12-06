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
use Zalt\SnippetsActions\ApplyActionInterface;
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
    private static string $_actionMeta = '_actionMeta';

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
        $actionClass = get_class($action);
        $metaModel   = $this->model->getMetaModel();

        if ((! $metaModel->isMeta(self::$_actionMeta, $actionClass)) && ($this->model instanceof ApplyActionInterface)) {
            $this->model->applyAction($action);
            $metaModel->setMeta(self::$_actionMeta, $actionClass);
        }
        return $this->model;
    }
    
}