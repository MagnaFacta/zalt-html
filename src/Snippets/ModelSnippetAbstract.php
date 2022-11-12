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

use Zalt\Model\Data\DataReaderInterface;

/**
 * Contains base functionality to use a model in a snippet.
 *
 * A snippet is a piece of html output that is reused on multiple places in the code.
 *
 * Variables are intialized using the {@see \Zalt\Registry\TargetInterface} mechanism.
 * Description of ModelSnippet
 *
 * @package    Zalt
 * @subpackage Snippets
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.1
 */
abstract class ModelSnippetAbstract extends TranslatableSnippetAbstract
{
    /**
     * Set a fixed model filter.
     *
     * Leading _ means not overwritten by sources.
     *
     * @var array
     */
    protected $_fixedFilter;

    /**
     * Set a fixed model sort.
     *
     * Leading _ means not overwritten by sources.
     *
     * @var array
     */
    protected $_fixedSort;

    /**
     * The model, use $this->getModel() to fill
     *
     * @var \MUtil\Model\ModelAbstract
     */
    private $_model;

    /**
     * Optional extra filter
     *
     * @var array
     */
    public $extraFilter;

    /**
     * Optional extra sort(s)
     *
     * @var array
     */
    public $extraSort;

    /**
     * Searchfilter to use including model sorts, etcc..
     *
     * The default is false, to signal that no data was passed. Any other value including
     * null means the value is used.
     *
     * @var array
     */
    protected $searchFilter = false;

    /**
     * The $request param that stores the ascending sort
     *
     * @var string
     */
    protected $sortParamAsc;

    /**
     * The $request param that stores the descending sort
     *
     * @var string
     */
    protected $sortParamDesc;

    /**
     * Creates the model
     *
     * @return \Zalt\Model\Data\DataReaderInterface
     */
    abstract protected function createModel(): DataReaderInterface;

    /**
     * Returns the model, always use this function
     *
     * @return \Zalt\Model\Data\DataReaderInterface
     */
    protected function getModel(): DataReaderInterface
    {
        \MUtil\Model::setDefaultBridge('itemTable', \Zalt\Snippets\ModelBridge\DetailTableBridge::class);
        \MUtil\Model::setDefaultBridge('display',  \Zalt\Model\Bridge\DisplayBridge::class);
        \MUtil\Model::setDefaultBridge('table', \Zalt\Snippets\ModelBridge\TableBridge::class);

        if (! $this->_model) {
            $this->_model = $this->createModel();

            $this->prepareModel($this->_model);
        }

        return $this->_model;
    }

    /**
     * The place to check if the data set in the snippet is valid
     * to generate the snippet.
     *
     * When invalid data should result in an error, you can throw it
     * here but you can also perform the check in the
     * checkRegistryRequestsAnswers() function from the
     * {@see \Zalt\Registry\TargetInterface}.
     *
     * @return boolean
     */
    public function hasHtmlOutput(): bool
    {
        return (boolean) $this->getModel();
    }

    /**
     * Default processing of model from standard settings
     *
     * @param \Zalt\Model\Data\DataReaderInterface $dataModel
     */
    protected final function prepareModel(DataReaderInterface $dataModel)
    {
        if ($this->sortParamAsc) {
            $dataModel->setSortParamAsc($this->sortParamAsc);
        }
        if ($this->sortParamDesc) {
            $dataModel->setSortParamDesc($this->sortParamDesc);
        }

        $this->processFilterAndSort($dataModel);

        if ($this->_fixedFilter) {
            $dataModel->addFilter($this->_fixedFilter);
        }
        if ($this->extraFilter) {
            $dataModel->addFilter($this->extraFilter);
        }
        if ($this->extraSort) {
            $dataModel->addSort($this->extraSort);
        }
        if ($this->_fixedSort) {
            $dataModel->addSort($this->_fixedSort);
        }
        file_put_contents('data/logs/echo.txt', print_r($dataModel->getFilter(), true) . "\n", FILE_APPEND);
    }

    /**
     * Overrule to implement snippet specific filtering and sorting.
     *
     * @param \MUtil\Model\ModelAbstract $dataModel
     */
    protected function processFilterAndSort(DataReaderInterface $dataModel)
    {
        if (false !== $this->searchFilter) {
            $dataModel->addFilter($this->searchFilter);

        } elseif (count($this->requestInfo->getParams())) {
            $params = $this->requestInfo->getParams();

            // Remove all empty values (but not arrays) from the filter
            $params = array_filter($params, function($i) {
                return is_array($i) || strlen($i);
            });
            
            $keys = $dataModel->getMetaModel()->getKeys();
            foreach ($keys as $key => $field) {
                if (isset($params[$key])) {
                    $params[$field] = $params[$key];
                    unset($params[$key]);
                }
            }

            $dataModel->addFilter($params);
        }
    }

    /**
     * Use this when overruling processFilterAndSort()
     *
     * Overrule to implement snippet specific filtering and sorting.
     *
     * @param \MUtil\Model\ModelAbstract $dataModel
     */
    protected function processSortOnly(DataReaderInterface $dataModel)
    {
        if (count($this->requestInfo->getRequestQueryParams())) {
            $queryParams = $this->requestInfo->getParams();
            if (isset($queryParams[$dataModel->getSortParamAsc()])) {
                $sort = $queryParams[$dataModel->getSortParamAsc()];
                $dataModel->addSort([$sort => SORT_ASC]);
            } elseif (isset($queryParams[$dataModel->getSortParamDesc()])) {
                $sort = $queryParams[$dataModel->getSortParamAsc()];
                $dataModel->addSort(array($sort => SORT_DESC));
            }
        }
    }
}
