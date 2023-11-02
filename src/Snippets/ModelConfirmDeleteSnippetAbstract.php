<?php

declare(strict_types=1);

/**
 * @package    Zalt
 * @subpackage Snippets
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 */

namespace Zalt\Snippets;

use Zalt\Html\HtmlElement;
use Zalt\Model\Data\FullDataInterface;
use Zalt\Model\Data\DataReaderInterface;
use Zalt\Model\Type\ActivatingYesNoType;

/**
 * @package    Zalt
 * @subpackage Snippets
 * @since      Class available since version 1.0
 */
abstract class ModelConfirmDeleteSnippetAbstract extends ModelConfirmSnippetAbstract
{
    /**
     * @var bool When false real deletion is not allowed
     */
    protected bool $allowDeletion = true;

    protected DeleteModeEnum $deletionMode = DeleteModeEnum::Delete;

    protected function getDeletionMode(DataReaderInterface $dataModel): DeleteModeEnum
    {
        $metaModel = $dataModel->getMetaModel();
        $metaModel->trackUsage(false);

        if (ActivatingYesNoType::hasActivation($metaModel)) {
            if (ActivatingYesNoType::isActive($metaModel, $dataModel->loadFirst())) {
                $this->actionValues = ActivatingYesNoType::getDectivatingValues($metaModel);
                return DeleteModeEnum::Deactivate;
            }
            $this->actionValues = ActivatingYesNoType::getActivatingValues($metaModel);
            return DeleteModeEnum::Activate;
        }
        if (! $this->allowDeletion) {
            return DeleteModeEnum::Block;
        }
        return $this->deletionMode;
    }

    public function getYesButton(): HtmlElement
    {
        if (DeleteModeEnum::Block == $this->deletionMode) {
            return new HtmlElement(
                'span',
                $this->getYesButtonLabel(),
                ['class' => $this->buttonBlockedClass]
            );
        }

        return parent::getYesButton();
    }

    public function hasHtmlOutput(): bool
    {
        // Must be known
        $this->deletionMode = $this->getDeletionMode($this->getModel());

        return parent::hasHtmlOutput();
    }


    /**
     * @return bool
     */
    protected function performAction(): bool
    {
        /**
         * @var FullDataInterface $model
         */
        $model  = $this->getModel();
        $filter = $model->getFilter();

        switch ($this->deletionMode) {
            case DeleteModeEnum::Activate:
            case DeleteModeEnum::Deactivate:
                return parent::performAction();

            case DeleteModeEnum::Delete:
                /**
                 * @var $model FullDataInterface
                 */
                $model->delete($filter);
                return true;

            case DeleteModeEnum::Block:
                // Do nothing
        }

        return false;
    }
}