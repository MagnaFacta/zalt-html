<?php

declare(strict_types=1);

/**
 *
 * @package    Zalt
 * @subpackage SnippetsActions
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 */

namespace Zalt\SnippetsActions;

use Zalt\Model\MetaModellerInterface;

/**
 *
 * @package    Zalt
 * @subpackage SnippetsActions
 * @since      Class available since version 1.0
 */
trait ModelActionTrait
{
    /**
     * Part of sort set by the user
     *
     * @var array
     */
    public array $dynamicSort = [];

    /**
     * @var array Optional extra filter
     */
    public array $extraFilter = [];

    /**
     * Optional extra sort(s)
     * @var array
     */
    public array $extraSort = [];

    public MetaModellerInterface $model;

    /**
     * @var array|bool The default is false, to signal that no data was passed. Any other value means the value is used.
     */
    public mixed $searchFilter = false;

    /**
     * @var string The request param that contains the ascending sort
     */
    public string $sortParamAsc = 'asort';

    /**
     * @var string The request param that contains the descending sort
     */
    public string $sortParamDesc = 'dsort';

    /**
     * @param array $filter Extra filter statements to add
     * @return void
     */
    public function addToFilter(array $filter): void
    {
        $this->extraFilter = array_merge($this->extraFilter, $filter);
    }
    
    /**
     * @param array $sort Extra sort statements to add
     * @return void
     */
    public function addToSort(array $sort): void
    {
        $this->extraSort = array_merge($this->extraSort, $sort);
    }
}