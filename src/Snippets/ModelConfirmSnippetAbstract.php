<?php

declare(strict_types=1);

/**
 * @package    Zalt
 * @subpackage Snippets
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 */

namespace Zalt\Snippets;

use Symfony\Contracts\Translation\TranslatorInterface;
use Zalt\Base\RequestInfo;
use Zalt\Message\MessengerInterface;
use Zalt\Model\Bridge\BridgeInterface;
use Zalt\Model\Data\DataReaderInterface;
use Zalt\Model\Data\FullDataInterface;
use Zalt\Model\Type\ActivatingYesNoType;
use Zalt\Snippets\ModelBridge\DetailTableBridge;
use Zalt\SnippetsLoader\SnippetOptions;

/**
 * @package    Zalt
 * @subpackage Snippets
 * @since      Class available since version 1.0
 */
abstract class ModelConfirmSnippetAbstract extends ModelDetailTableSnippetAbstract
{
    use ConfirmSnippetTrait {
        ConfirmSnippetTrait::getQuestion as protected getTraitQuestion;
        ConfirmSnippetTrait::getMessage as protected getTraitMessage;
    }

    protected const MODE_ACTIVATE = 2;
    protected const MODE_DEACTIVATE = 1;
    protected const MODE_DELETE = 0;

    protected array $actionValues = [];

    protected int $actionMode = self::MODE_DELETE;

    /**
     * @var string After action actual return url
     */
    protected string $afterActionRouteUrl = '';

    /**
     * One of the \Zalt\Model\Bridge\BridgeAbstract MODE constants
     *
     * @var int
     */
    protected $bridgeMode = BridgeInterface::MODE_SINGLE_ROW;

    public function __construct(SnippetOptions $snippetOptions, RequestInfo $requestInfo, TranslatorInterface $translate, MessengerInterface $messenger)
    {
        $this->messenger = $messenger;

        parent::__construct($snippetOptions, $requestInfo, $translate);
    }

    public function checkModel(FullDataInterface $dataModel): void
    {
        $metaModel = $dataModel->getMetaModel();

        // If already set, we assume the programmers knows what he/she is doing
        if (! $this->actionValues) {
            // These array should be equal length, otherwise the result may be goofy
            $activatingValues = $metaModel->getCol(ActivatingYesNoType::$activatingValue);
            $deactivatingValues = $metaModel->getCol(ActivatingYesNoType::$deactivatingValue);

            if ($activatingValues && $deactivatingValues) {
                // First check for values to activate!
                $row = $dataModel->loadFirst();
                foreach ($activatingValues as $name => $value) {
                    if (isset($row[$name], $deactivatingValues[$name]) && $row[$name] == $value) {
                        $this->actionValues[$name] = $deactivatingValues[$name];
                    }
                }
                if ($this->actionValues) {
                    $this->actionMode = self::MODE_DEACTIVATE;
                } else {
                    // In nothing then check for values to deactivate!
                    foreach ($deactivatingValues as $name => $value) {
                        if (isset($row[$name], $activatingValues[$name]) && $row[$name] == $value) {
                            $this->actionValues[$name] = $activatingValues[$name];
                        }
                    }
                    if ($this->actionValues) {
                        $this->actionMode = self::MODE_ACTIVATE;
                    }
                }
            }
        }
        // dump($this->actionValues);
    }


    public function getActivationMessage(): string
    {
        return sprintf(
            $this->_('One %s activated!'),
            $this->getTopic(1)
        );
    }

    public function getActivationQuestion(): string
    {
        return sprintf(
            $this->_('Do you want to activate this %s?'),
            $this->getTopic(1)
        );
    }

    public function getDeactivationMessage(): string
    {
        return sprintf(
            $this->_('One %s deactivated!'),
            $this->getTopic(1)
        );
    }

    public function getDeactivationQuestion(): string
    {
        return sprintf(
            $this->_('Do you want to deactivate this %s?'),
            $this->getTopic(1)
        );
    }

    public function getDeletionMessage(): string
    {
        return sprintf(
            $this->_('One %s deleted!'),
            $this->getTopic(1)
        );
    }

    public function getDeletionQuestion(): string
    {
        return sprintf(
            $this->_('Do you want to delete this %s?'),
            $this->getTopic(1)
        );
    }

    protected function getMessage(): string
    {
        if ($this->afterActionMessage) {
            return $this->afterActionMessage;
        }

        return match($this->actionMode) {
            self::MODE_DELETE => $this->getDeletionMessage(),
            self::MODE_DEACTIVATE => $this->getDeactivationMessage(),
            self::MODE_ACTIVATE => $this->getActivationMessage(),
            default => $this->getTraitMessage(),
        };
    }

    protected function getQuestion(): string
    {
        if (isset($this->question)) {
            return $this->question;
        }

        return match($this->actionMode) {
            self::MODE_DELETE => $this->getDeletionQuestion(),
            self::MODE_DEACTIVATE => $this->getDeactivationQuestion(),
            self::MODE_ACTIVATE => $this->getActivationQuestion(),
            default => $this->getTraitQuestion(),
        };
    }

    /**
     * When hasHtmlOutput() is false a snippet user should check
     * for a redirectRoute.
     *
     * When hasHtmlOutput() is true this functions should not be called.
     *
     * @return string|null Nothing or a string that is acceptable for Redector->gotoRoute()
     */
    public function getRedirectRoute(): ?string
    {
        return $this->afterActionRouteUrl;
    }

    /**
     * The place to check if the data set in the snippet is valid
     * to generate the snippet.
     *
     * @return boolean
     */
    public function hasHtmlOutput(): bool
    {
        /**
         * @var FullDataInterface $model
         */
        $model = $this->getModel();
        $this->checkModel($model);
        return (! $this->isActionConfirmed()) && parent::hasHtmlOutput();
    }


    protected function performAction(): bool
    {
        /**
         * @var FullDataInterface $model
         */
        $model  = $this->getModel();
        $filter = $model->getFilter();

        if ($this->actionValues) {
            $model->save($this->actionValues + $filter, $filter);
        } else {
            /**
             * @var $model FullDataInterface
             */
            $model->delete($filter);
        }
        return true;
    }

    /**
     * Set what to do when the form is 'finished'.
     */
    protected function setAfterActionRoute()
    {
        $url = $this->getAfterActionUrl();
        if ($url) {
            $this->afterActionRouteUrl = $url;
        }
    }

    /**
     * Set the footer of the browse table.
     *
     * Overrule this function to set the header differently, without
     * having to recode the core table building code.
     */
    protected function setShowTableFooter(DetailTableBridge $bridge, DataReaderInterface $dataModel)
    {
        $footer = $bridge->getTable()->tfrow($this->getHtmlQuestion());
    }
}