<?php

/**
 *
 * @package    Zalt
 * @subpackage Snippets
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 */

namespace Zalt\Snippets;

use Zalt\Html\Paginator\LinkPaginator;
use Zalt\Html\Paginator\PaginatorInterface;
use Zalt\Html\TableElement;
use Zalt\Model\Bridge\BridgeAbstract;
use Zalt\Model\Bridge\BridgeInterface;
use Zalt\Model\Data\DataReaderInterface;
use Zalt\Model\MetaModelInterface;
use Zalt\Snippets\ModelBridge\TableBridge;

/**
 * Displays multiple items in a model below each other in an Html table.
 *
 * To use this class either subclass or use the existing default ModelTableSnippet.
 *
 * @see ModelTableSnippet
 *
 * @package    Zalt
 * @subpackage Snippets
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.3
 */
abstract class ModelTableSnippetAbstract extends \Zalt\Snippets\ModelSnippetAbstract
{
    use ModelTextFilterTrait;

    /**
     * Assume we have this many items if row counting is disabled.
     */
    const NO_COUNT_ITEMS = 1000000;

    /**
     * One of the BridgeInterface MODE constants
     *
     * @var int
     */
    protected $bridgeMode = BridgeInterface::MODE_ROWS;

    /**
     * Sets pagination on or off.
     *
     * @var boolean
     */
    public $browse = false;

    /**
     * Show total number of records in pagination.
     *
     * @var boolean
     */
    public $showTotal = true;

    /**
     * Optional table caption.
     *
     * @var string
     */
    public $caption;

    /**
     * An array of nested arrays, each defining the input for setMultiSort
     *
     * @var array
     */
    public $columns = [];

    /**
     * Content to show when there are no rows.
     *
     * Null shows '&hellip;'
     *
     * @var mixed
     */
    public $onEmpty = null;

    /**
     * @var int Pagenumber starting with offset zero
     */
    protected int $pageItems = 10;

    /**
     * @var int Maximum number of items to show on a page.
     */
    protected int $maxPageItems = 1000;

    /**
     * @var int Pagenumber starting with offset ONE
     */
    protected int $pageNumber = 1;

    /**
     * @var string
     */
    protected string $paginatorClass = LinkPaginator::class;

    /**
     * When true (= default) the headers get sortable links.
     *
     * @var boolean
     */
    public $sortableLinks = true;

    /**
     * When true query only the used columns
     *
     * @var boolean
     */
    public $trackUsage = true;

    /**
     * @var bool When false do not add the data to the Late Stack
     */
    protected bool $useAsLateStack = true;

    /**
     * Adds columns from the model to the bridge that creates the browse table.
     *
     * Overrule this function to add different columns to the browse table, without
     * having to recode the core table building code.
     *
     * @param TableBridge $bridge
     * @param DataReaderInterface $dataModel
     * @return void
     */
    protected function addBrowseTableColumns(TableBridge $bridge, DataReaderInterface $dataModel)
    {
        $model = $dataModel->getMetaModel();
            
        if ($this->columns) {
            foreach ($this->columns as $column) {
                call_user_func_array(array($bridge, 'addMultiSort'), $column);
            }
        } elseif ($this->sortableLinks) {
            foreach($model->getItemsOrdered() as $name) {
                if ($model->has($name, 'label')) {
                    $label = $model->get($name, 'label');
                    $bridge->addSortable($name, $label);
                }
            }
        } else {
            foreach($model->getItemsOrdered() as $name) {
                if ($model->has($name, 'label')) {
                    $label = $model->get($name, 'label');
                    $bridge->add($name, $label);
                }
            }
        }
    }

    /**
     * Add the paginator panel to the table.
     *
     * Only called when $this->browse is true. Overrule this function
     * to define your own method.
     *
     * @param \Zalt\Html\TableElement $table
     * $param \Zend_Paginator $paginator
     */
    protected function addPaginator(TableElement $table, int $count, int $page, int $items)
    {
        $paginator = $this->getPaginator();
        $paginator->setCount($count)
            ->setPageItems($items)
            ->setPageNumber($page)
            ->validatePageNumber()
            ->setShowCount($this->showTotal);

        $table->tfrow()->append($paginator->getHtmlPagelinks());
    }
    
    /**
     * @param BridgeInterface $bridge
     * @param DataReaderInterface $dataModel
     * @return void
     */
    protected function ensureRepeater(BridgeInterface $bridge, DataReaderInterface $dataModel)
    {
        /**
         * @var TableBridge $bridge
         */
        if (! $bridge->hasRepeater()) {
            if ($this->browse) {
                $items  = $this->getPageItems();
                $page   = $this->getPageNumber();
                if ($this->showTotal) {
                    $bridge->setRepeater($dataModel->loadPageWithCount($count, $page, $items));
                    $this->addPaginator($bridge->getTable(), $count, $page, $items);
                } else {
                    $bridge->setRepeater($dataModel->loadPage($page, $items));
                    $this->addPaginator($bridge->getTable(), self::NO_COUNT_ITEMS, $page, $items);
                }
            } elseif ($this->bridgeMode === BridgeInterface::MODE_LAZY) {
                $bridge->setRepeater($dataModel->loadRepeatable());
            } elseif (($this->bridgeMode === BridgeInterface::MODE_SINGLE_ROW) && $bridge instanceof BridgeAbstract) {
                $bridge->setRow($dataModel->loadFirst());
            } else {
                $bridge->setRepeater($dataModel->load());
            }
        }
    }

    /**
     * Creates from the model a \Zalt\Html\TableElement that can display multiple items.
     *
     * Allows overruling
     *
     * @param \Zalt\Model\Data\DataReaderInterface $dataModel
     * @return \Zalt\Html\TableElement
     */
    public function getBrowseTable(DataReaderInterface $dataModel)
    {
        /**
         * @var TableBridge $bridge
         */
        $bridge = $dataModel->getBridgeFor('table');
        $this->prepareBridge($bridge);
        $table = $bridge->getTable();

        if ($this->caption) {
            $table->caption($this->caption);
        }
        if ($this->onEmpty) {
            $table->setOnEmpty($this->onEmpty);
        } else {
            $table->getOnEmpty()->raw('&hellip;');
        }

        $this->addBrowseTableColumns($bridge, $dataModel);
        $this->ensureRepeater($bridge, $dataModel);

        return $table;
    }

    public function getFilter(MetaModelInterface $metaModel) : array
    {
        $filter = parent::getFilter($metaModel);

        return $this->processTextFilter($filter, $metaModel, $this->searchFilter);
    }

    /**
     * Create the snippets content
     *
     * This is a stub function either override getHtmlOutput() or override render()
     *
     * @return mixed Something that can be rendered
     */
    public function getHtmlOutput()
    {
        $model = $this->getModel();

        if ($this->trackUsage) {
            $model->getMetaModel()->trackUsage();
        }
        $table = $this->getBrowseTable($model);

        return $table;
    }

    public function getPageItems(): int
    {
        $items = $this->requestInfo->getParam(PaginatorInterface::REQUEST_ITEMS);
        if ($items) {
            $this->pageItems = min($items, $this->maxPageItems);
        }
        return $this->pageItems;
    }

    public function getPageNumber(): int
    {
        // If this was a POST request, we assume the search parameters changed,
        // so we show the first page of results.
        if ($this->requestInfo->isPost()) {
            $this->pageNumber = 1;
            return $this->pageNumber;
        }
        $page = $this->requestInfo->getParam(PaginatorInterface::REQUEST_PAGE);
        if ($page) {
            $this->pageNumber = $page;
        }
        return $this->pageNumber;
    }

    public function getPaginator(): PaginatorInterface
    {
        $output = new $this->paginatorClass();

        if (method_exists($output, 'setCurrentUrl')) {
            $output->setCurrentUrl($this->getRequestUrl());
        }
        if (method_exists($output, 'setTranslator')) {
            $output->setTranslator($this->translate);
        }

        return $output;
    }

    public function getRequestUrl(): array
    {
        $url = [$this->requestInfo->getBasePath()];

        $url[PaginatorInterface::REQUEST_PAGE] = $this->getPageNumber();
        $url[PaginatorInterface::REQUEST_ITEMS] = $this->getPageItems();

        return $url;
    }

    public function prepareBridge(TableBridge $bridge)
    {
        $bridge->currentUrl     = $this->getRequestUrl();
        $bridge->lateStackName  = 'bridge_' . get_class($this);
        $bridge->sortAscParam   = $this->sortParamAsc;
        $bridge->sortDescParam  = $this->sortParamDesc;
        $bridge->useAsLateStack = $this->useAsLateStack;
    }
}
