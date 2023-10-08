<?php

declare(strict_types=1);


/**
 * @package    Zalt
 * @subpackage Html\Paginator
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 */

namespace Zalt\Html\Paginator;

use Zalt\Html\HtmlInterface;

/**
 * @package    Zalt
 * @subpackage Html\Paginator
 * @since      Class available since version 1.0
 */
interface PaginatorInterface
{
    const REQUEST_ITEMS = 'items';
    const REQUEST_PAGE = 'page';

    /**
     * @return HtmlInterface The Html renderable output of the paginator
     */
    public function getHtmlPagelinks(): HtmlInterface;

    /**
     * @param int $itemCount The total number of items
     * @return PaginatorInterface (continuation pattern)
     */
    public function setCount(int $itemCount): PaginatorInterface;

    /**
     * @param int $pageItems Set the number of page items
     * @return PaginatorInterface (continuation pattern)
     */
    public function setPageItems(int $pageItems): PaginatorInterface;

    /**
     * @param int $pageNumber The current page
     * @return PaginatorInterface (continuation pattern)
     */
    public function setPageNumber(int $pageNumber): PaginatorInterface;

    /**
     * Ensure that the value of pagenumber is valid, based on the number of
     * items there are (itemCount) and the number of items that is shown
     * on a page (pageItems);
     *
     * @return PaginatorInterface (continuation pattern)
     */
    public function validatePageNumber(): PaginatorInterface;
}