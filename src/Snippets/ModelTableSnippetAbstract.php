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

use Zalt\Html\Marker;
use Zalt\Html\Paginator\LinkPaginator;
use Zalt\Html\Paginator\PaginatorInterface;
use Zalt\Html\TableElement;
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
     * @var string The parameter name that contains the search text
     */
    protected string $textSearchField = 'search';

    /**
     * When true query only the used columns
     *
     * @var boolean
     */
    public $trackUsage = true;

    /**
     * Adds columns from the model to the bridge that creates the browse table.
     *
     * Overrule this function to add different columns to the browse table, without
     * having to recode the core table building code.
     *
     * @param TableBridge $bridge
     * @param DataReaderInterface $model
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
            ->setPageNumber($page);

        $table->tfrow()->append($paginator->getHtmlPagelinks());
    }
    
    public function cleanUpTextFilter(string $searchText) : array
    {
        return array_filter(explode(' ', strtolower(preg_replace("[^A-Za-z0-9]", " ", $searchText))));
    }

    /**
     * @param BridgeInterface $bridge
     * @param DataReaderInterface $dataModel
     * @return void
     */
    protected function ensureRepeater(BridgeInterface $bridge, DataReaderInterface $dataModel)
    {
        if (! $bridge->hasRepeater()) {
            if ($this->browse) {
                $items  = $this->getPageItems();
                $page   = $this->getPageNumber();
                $bridge->setRepeater($dataModel->loadPageWithCount($count, $page, $items));

                $this->addPaginator($bridge->getTable(), $count, $page, $items);
            } elseif ($this->bridgeMode === BridgeInterface::MODE_LAZY) {
                $bridge->setRepeater($dataModel->loadRepeatable());
            } elseif ($this->bridgeMode === BridgeInterface::MODE_SINGLE_ROW) {
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
        $bridge = $dataModel->getBridgeFor('table');
        $this->prepareBridge($bridge);

        if ($this->caption) {
            $bridge->caption($this->caption);
        }
        if ($this->onEmpty) {
            $bridge->setOnEmpty($this->onEmpty);
        } else {
            $bridge->getOnEmpty()->raw('&hellip;');
        }

        $this->addBrowseTableColumns($bridge, $dataModel);
        $this->ensureRepeater($bridge, $dataModel);

        return $bridge->getTable();
    }

    public function getFilter(MetaModelInterface $metaModel) : array
    {
        $filter = parent::getFilter($metaModel);
        
        $searchText = $this->requestInfo->getParam($this->textSearchField);
        if ($searchText) {
            // Add generic text search filter and marker
            $searchFilter = $this->getTextFilter($metaModel, $searchText);
            if ($searchFilter) {
                $filter = array_merge($filter, $searchFilter);
            }
        }
        
        return $filter;
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
            $this->pageItems = $items;
        }
        return $this->pageItems;
    }

    public function getPageNumber(): int
    {
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

    public function getRequestUrl($skipSort = false): array
    {
        $url = [$this->requestInfo->getBasePath()];

        if (! $skipSort) {
            // Loop to add the sort in parameter order
            foreach ($this->requestInfo->getParams() as $key => $field) {
                switch ($key) {
                    case $this->sortParamAsc:
                        $url[$this->sortParamAsc] = $field;
                        break;

                    case $this->sortParamDesc:
                        $url[$this->sortParamDesc] = $field;
                        break;

                    default:
                        // Intentional fall through
                }
            }
        }
        $url[PaginatorInterface::REQUEST_PAGE] = $this->getPageNumber();
        $url[PaginatorInterface::REQUEST_ITEMS] = $this->getPageItems();

        return $url;
    }

    public function getTextFilter(MetaModelInterface $metaModel, string $searchText): array
    {
        $output = [];
        $searches = $this->cleanUpTextFilter($searchText);
        if ($searches) {
            $fields = $metaModel->getCol('label');
            foreach ($metaModel->getCol('no_text_search') as $field => $value)  {
                if ($value) {
                    unset($fields[$field]);
                }
            }
            
            $marker = new Marker($searches, 'strong', 'UTF-8');
            $metaModel->setCol(array_keys($fields), ['markCallback' => [$marker, 'mark']]);
            
            $options = $metaModel->getCol('multiOptions');

            foreach ($searches as $search) {
                $current = [];
                foreach ($fields as $field => $label) {
                    if (isset($options[$field])) {
                        $inValues = [];
                        foreach ($options[$field] as $value => $label) {
                            if (str_contains(strtolower($label), $search)) {
                                $inValues[] = $value;
                            }
                        }
                        if ($inValues) {
                            $current[$field] = $inValues;
                        }
                    } else {
                        switch ($metaModel->get($field, 'type')) {
                            case MetaModelInterface::TYPE_DATE:
                            case MetaModelInterface::TYPE_DATETIME:
                            case MetaModelInterface::TYPE_TIME:
                            case MetaModelInterface::TYPE_NUMERIC:
                                if (intval($search)) {
                                    $current[$field] = [MetaModelInterface::FILTER_CONTAINS => $search];
                                }
                                break;
                            case MetaModelInterface::TYPE_CHILD_MODEL:
                                break;
                            default:
                                $current[$field] = [MetaModelInterface::FILTER_CONTAINS => $search];
                        }
                    }
                }
                if ($current) {
                    $output[] = $current;
                }
            }
        }
        return $output;
    }

    public function prepareBridge(TableBridge $bridge)
    {
        $bridge->currentUrl    = $this->getRequestUrl(true);
        $bridge->sortAscParam  = $this->sortParamAsc;
        $bridge->sortDescParam = $this->sortParamDesc;
    }
}
