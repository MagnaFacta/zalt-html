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
     *
     * @var \Zalt\Html\Marker Class for marking text in the output
     */
    protected $_marker;

    /**
     * Url parts added to each link in the resulting table
     *
     * @var array
     */
    public $baseUrl;

    /**
     * One of the \MUtil\Model\Bridge\BridgeAbstract MODE constants
     *
     * @var int
     */
    protected $bridgeMode = \MUtil\Model\Bridge\BridgeAbstract::MODE_ROWS;

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
     * @var string The parameter name that contains the search text
     */
    protected $searchTextParam = 'search';

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
     * Adds columns from the model to the bridge that creates the browse table.
     *
     * Overrule this function to add different columns to the browse table, without
     * having to recode the core table building code.
     *
     * @param \MUtil\Model\Bridge\TableBridge $bridge
     * @param \MUtil\Model\ModelAbstract $model
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
    protected function addPaginator(\Zalt\Html\TableElement $table, \Zend_Paginator $paginator)
    {
        //$table->tfrow()->pagePanel($paginator, null, array('baseUrl' => $this->baseUrl));
    }

    protected function ensureRepeater(BridgeInterface $bridge, DataReaderInterface $dataModel)
    {
        if (! $bridge->hasRepeater()) {
            if (false && $this->browse) {
                $paginator = $dataModel->loadPaginator();
                $bridge->setRepeater($paginator);
                $this->addPaginator($bride->getTable(), $paginator);
            } elseif ($this->bridgeMode === \MUtil\Model\Bridge\BridgeAbstract::MODE_LAZY) {
                // file_put_contents('data/logs/echo.txt', __FUNCTION__ . '(' . __LINE__ . '): ' . "LAZY\n", FILE_APPEND);
                $bridge->setRepeater($dataModel->loadRepeatable());
            } elseif ($this->bridgeMode === \MUtil\Model\Bridge\BridgeAbstract::MODE_SINGLE_ROW) {
                $bridge->setRow($dataModel->loadFirst());
            } else {
                $bridge->setRepeater($dataModel->load());
            }
        }
        // file_put_contents('data/logs/echo.txt', __FUNCTION__ . '(' . __LINE__ . '): [' . $this->bridgeMode . ']' . get_class($bridge->getRepeater()) . "\n", FILE_APPEND);
        // file_put_contents('data/logs/echo.txt', __FUNCTION__ . '(' . __LINE__ . '): ' . print_r($dataModel->load(), true) . "\n", FILE_APPEND);
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
        $bridge = $dataModel->getMetaModel()->getBridgeFor('table');
        $this->prepareBridge($bridge);

        if ($this->caption) {
            $bridge->caption($this->caption);
        }
        if ($this->onEmpty) {
            $bridge->setOnEmpty($this->onEmpty);
        } else {
            $bridge->getOnEmpty()->raw('&hellip;');
        }
        if ($this->baseUrl) {
            $bridge->setBaseUrl($this->baseUrl);
        }

        $this->addBrowseTableColumns($bridge, $dataModel);
        $this->ensureRepeater($bridge, $dataModel);

        return $bridge->getTable();
    }

    public function getFilter(MetaModelInterface $metaModel) : array
    {
        $filter = parent::getFilter($metaModel);
        
        // Add generic text search filter and marker
        $searchFilter = []; 
        $searchText   = $this->requestInfo->getParam($this->searchTextParam);
        if ($searchText) {
            // TODO: move this to the DataReaderModel!
            $this->_marker = new \Zalt\Html\Marker($metaModel->getTextSearches($searchText), 'strong', 'UTF-8');

            foreach ($metaModel->getItemNames() as $name) {
                if ($metaModel->get($name, 'label') && (!$metaModel->is($name, 'no_text_search', true))) {
                    $searchFilter[] = $name . " LIKE '%$searchText%'"; 
                    $metaModel->set($name, 'markCallback', array($this->_marker, 'mark'));
                }
            }
        }
        if ($searchFilter) {
            $filter[] = $searchFilter;
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
            $model->trackUsage();
        }
        $table = $this->getBrowseTable($model);

        return $table;
    }

    public function prepareBridge(TableBridge $bridge)
    {
        $bridge->currentUrl = $this->requestInfo->getBasePath();
        $bridge->sortAscParam = $this->sortParamAsc;
        $bridge->sortDescParam = $this->sortParamDesc;
    }
    
    /**
     * Render a string that becomes part of the HtmlOutput
     *
     * You should override either getHtmlOutput() or this function to generate output
     *
     * @return string Html output
     */
    public function render()
    {
        if ($this->_marker) {
            // $this->_marker->setEncoding();
        }

        return parent::render();
    }
}
