<?php

declare(strict_types=1);

/**
 *
 * @package    Zalt
 * @subpackage SnippetsActions
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 */

namespace Zalt\SnippetsActions;

/**
 *
 * @package    Zalt
 * @subpackage SnippetsActions
 * @since      Class available since version 1.0
 */
trait ModelActionTrait
{
    /**
     * Optional extra filter
     * @var array
     */
    public $extraFilter = [];

    /**
     * Optional extra sort(s)
     * @var array
     */
    public $extraSort = [];

    /**
     * Searchfilter. The default is false, to signal that no data was passed. Any other value means the value is used.
     * @var array|bool
     */
    public $searchFilter = false;

    /**
     * @var string The request param that contains the ascending sort
     */
    public $sortParamAsc = 'asort';

    /**
     * @var string The request param that contains the descending sort
     */
    public $sortParamDesc = 'dsort';

    /**
     * @param array $filter Extra filter statements to add
     * @return void
     */
    public function addToFilter(array $filter)
    {
        $this->extraFilter = array_merge($this->extraFilter, $filter);
    }
    
    /**
     * @param array $sort Extra sort statements to add
     * @return void
     */
    public function addToSort(array $sort)
    {
        $this->extraSort = array_merge($this->extraSort, $sort);
    }
}