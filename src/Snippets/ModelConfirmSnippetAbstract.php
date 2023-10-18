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
use Zalt\Snippets\ModelBridge\DetailTableBridge;
use Zalt\SnippetsLoader\SnippetException;
use Zalt\SnippetsLoader\SnippetOptions;

/**
 * @package    Zalt
 * @subpackage Snippets
 * @since      Class available since version 1.0
 */
abstract class ModelConfirmSnippetAbstract extends ModelDetailTableSnippetAbstract
{
    use ConfirmSnippetTrait { getQuestion as protected getTraitQuestion; getMessage as protected getTraitMessage; }

    protected const MODE_ACTIVATE = 2;
    protected const MODE_DEACTIVATE = 1;
    protected const MODE_DELETE = 0;


    const MODEL_ACTIVE_FIELD = 'modelActivationField';
    const MODEL_ACTIVE_VALUE_ACTIVE = 'modelActiveValueActive';
    const MODEL_ACTIVE_VALUE_INACTIVE = 'modelActiveValueInactive';
    
    protected ?string $actionField = null;

    protected mixed $actionValue = null;

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
        
        $this->actionField = $metaModel->getMeta(self::MODEL_ACTIVE_FIELD);

        if ($this->actionField) {
            $active   = $metaModel->getMeta(self::MODEL_ACTIVE_VALUE_ACTIVE, 1);
            $inactive = $metaModel->getMeta(self::MODEL_ACTIVE_VALUE_ACTIVE, 0);

            $row = $dataModel->loadFirst();
            if (isset($row[$this->actionField]) ) {
                if ($active == $row[$this->actionField]) {
                    $this->actionMode = self::MODE_DEACTIVATE;
                    $this->actionValue = $inactive;

                } elseif ($inactive == $row[$this->actionField]) {
                    $this->actionMode = self::MODE_ACTIVATE;
                    $this->actionValue = $active;

                } else {
                    throw new SnippetException(sprintf(
                        $this->_("Unknown activation value %s for field %s. Expected %s or %s."),
                        $row[$this->actionField],
                        $this->actionField,
                        $active,
                        $inactive
                    ));
                }
            }
        }
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
        $model = $this->getModel();

        if ($this->actionField) {
            $model->save([$this->actionField => $this->actionValue], $model->getFilter());
        } else {
            /**
             * @var $model FullDataInterface
             */
            $model->delete($model->getFilter());
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