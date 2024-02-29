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
 * Marker interface for actions implementing ModelActionTrait
 * 
 * @package    Zalt
 * @subpackage SnippetsActions
 * @since      Class available since version 1.0
 */
interface ModelActionInterface
{
    /**
     * @param array $filter Extra filter statements to add
     * @return void
     */
    public function addToFilter(array $filter): void;

    /**
     * @param array $sort Extra sort statements to add
     * @return void
     */
    public function addToSort(array $sort): void;
}
