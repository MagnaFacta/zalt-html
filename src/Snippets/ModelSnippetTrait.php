<?php

declare(strict_types=1);

/**
 *
 * @package    Zalt
 * @subpackage Snippets
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 */

namespace Zalt\Snippets;

use Zalt\Model\Data\DataReaderInterface;
use Zalt\Model\MetaModelInterface;

/**
 *
 * @package    Zalt
 * @subpackage Snippets
 * @since      Class available since version 1.0
 */
trait ModelSnippetTrait
{
    /**
     * The model, use $this->getModel() to fill
     *
     * @var \Zalt\Model\Data\DataReaderInterface;
     */
    protected $_dataModel;

    /**
     * Set a fixed model filter.
     *
     * Leading _ means not overwritten by sources.
     *
     * @var array
     */
    protected $_fixedFilter = [];

    /**
     * Set a fixed model sort.
     *
     * Leading _ means not overwritten by sources.
     *
     * @var array
     */
    protected $_fixedSort = [];

    /**
     * Part of sort set by the user
     *
     * @var array
     */
    public array $dynamicSort = [];

    /**
     * Optional extra filter
     *
     * @var array
     */
    public $extraFilter = [];

    /**
     * Optional extra sort(s)
     *
     * @var array
     */
    public $extraSort = [];

    /**
     * Searchfilter to use instead of filtering by request
     *
     * The default is false, to signal that no data was passed. Any other value means the value is used.
     *
     * @var array|bool
     */
    protected $searchFilter = false;

    /**
     * The request param that contains the ascending sort
     *
     * @var string
     */
    protected $sortParamAsc = 'asort';

    /**
     * The request param that contains the descending sort
     *
     * @var string
     */
    protected $sortParamDesc = 'dsort';

    /**
     * Creates the model
     *
     * @return \Zalt\Model\Data\DataReaderInterface
     */
    abstract protected function createModel() : DataReaderInterface;

    protected function cleanUpFilter(array $filter, MetaModelInterface $metaModel): array
    {
        // Change key filters to field name filters
        $keys = $metaModel->getKeys();
        foreach ($keys as $key => $field) {
            if (isset($filter[$key]) && $key !== $field) {
                $filter[$field] = $filter[$key];
                unset($filter[$key]);
            }
        }

        foreach ($filter as $field => $value) {
            if (! (is_int($field) || $metaModel->has($field))) {
                unset($filter[$field]);
            }
        }
        return $filter;
    }

    public function getFilter(MetaModelInterface $metaModel) : array
    {
        if (false !== $this->searchFilter) {
            $filter = $this->searchFilter;
        } else {
            $filter = $this->getRequestFilter($metaModel);
        }
//        file_put_contents('data/logs/echo.txt', __CLASS__ . '->' . __FUNCTION__ . '(' . __LINE__ . '): ' .  print_r($this->_fixedFilter, true) . "\n", FILE_APPEND);
//        file_put_contents('data/logs/echo.txt', __CLASS__ . '->' . __FUNCTION__ . '(' . __LINE__ . '): ' .  print_r($this->extraFilter, true) . "\n", FILE_APPEND);
//        file_put_contents('data/logs/echo.txt', __CLASS__ . '->' . __FUNCTION__ . '(' . __LINE__ . '): ' .  print_r($this->cleanUpFilter($filter, $metaModel), true) . "\n", FILE_APPEND);
        
        // Filter in request overrules same filter from extraFilter settings which again overrule fiwxedFilter settings
        // Sinc the arrays can contian numeric keys we use array_merge to include those from all filters
        return array_merge($this->_fixedFilter, $this->extraFilter, $this->cleanUpFilter($filter, $metaModel));
    }

    public function getRequestFilter(MetaModelInterface $metaModel) : array
    {
        $filter = $this->requestInfo->getRequestMatchedParams() + $this->requestInfo->getRequestMatchedParams();

        // Remove sort parameters
        unset($filter[$this->sortParamAsc], $filter[$this->sortParamDesc]);

        // Remove all empty values (but not arrays) from the filter
        $filter = array_filter($filter, function ($f) {
            return is_array($f) || strlen($f);
        });

        return $filter;
    }

    public function getSort(MetaModelInterface $metaModel) : array
    {
        // Sorts in dynamicSort overrule extraSort settings which again overrule fixedSort settings
        return $this->dynamicSort + $this->extraSort + $this->_fixedSort;
    }

    /**
     * Returns the model, always use this function
     *
     * @return \Zalt\Model\Data\DataReaderInterface
     */
    protected function getModel() : DataReaderInterface
    {
        if (! $this->_dataModel) {
            $this->_dataModel = $this->createModel();

            $this->prepareModel($this->_dataModel);
        }

        return $this->_dataModel;
    }

    /**
     * Default processing of data model from standard settings
     *
     * @param \Zalt\Model\Data\DataReaderInterface $dataModel
     */
    protected final function prepareModel(DataReaderInterface $dataModel): void
    {
        $metaModel = $dataModel->getMetaModel();

        $dataModel->setFilter($this->getFilter($metaModel));
        $dataModel->setSort($this->getSort($metaModel));

        // dump($dataModel->getFilter(), $dataModel->getSort());
    }
}