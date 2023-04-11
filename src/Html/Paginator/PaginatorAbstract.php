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
 * @author     Class available since version 2.0
 */
abstract class PaginatorAbstract implements PaginatorInterface
{
    /**
     * @var int The total number of items
     */
    protected int $itemCount = 0;

    /**
     * @var int Pagenumber starting with offset zero
     */
    protected int $pageItems = 10;

    /**
     * @var int Pagenumber starting with offset ONE
     */
    protected int $pageNumber = 1;

    abstract public function getHtmlPagelinks(): HtmlInterface;

    public function setCount(int $itemCount): PaginatorInterface
    {
        $this->itemCount = $itemCount;
        return $this;
    }

    public function setPageItems(int $pageItems): PaginatorInterface
    {
        $this->pageItems = $pageItems;
        return $this;
    }

    public function setPageNumber(int $pageNumber): PaginatorInterface
    {
        $this->pageNumber = $pageNumber;
        return $this;
    }
}