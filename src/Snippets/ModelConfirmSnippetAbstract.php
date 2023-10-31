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
use Zalt\SnippetsLoader\SnippetOptions;

/**
 * @package    Zalt
 * @subpackage Snippets
 * @since      Class available since version 1.0
 */
abstract class ModelConfirmSnippetAbstract extends ModelDetailTableSnippetAbstract
{
    use ConfirmSnippetTrait;

    protected array $actionValues = [];


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
        return (! $this->isActionConfirmed()) && parent::hasHtmlOutput();
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

        if ($this->actionValues) {
            $model->save($this->actionValues + $filter, $filter);
            return true;
        }
        return false;
    }

    /**
     * Set what to do when the form is 'finished'.
     */
    protected function setAfterActionRoute(): void
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