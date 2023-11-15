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

    /**
     * @var bool Whether or not to show the counter and '>>' links.
     */
    protected bool $showCount = true;

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

    public function setShowCount(bool $showCount): PaginatorInterface
    {
        $this->showCount = $showCount;
        return $this;
    }

    /**
     * Ensure that the value of pagenumber is valid, based on the number of
     * items there are (itemCount) and the number of items that is shown
     * on a page (pageItems);
     */
    public function validatePageNumber(): PaginatorInterface
    {
        // Assumes itemCount and pageItems have been set.
        if ($this->pageItems > 0) {
            $maxPage = intval(ceil($this->itemCount / $this->pageItems));
            if ($this->pageNumber > $maxPage) {
                $this->pageNumber = max($maxPage, 1);
            }
        } else {
            $this->pageNumber = 1;
        }
       
        return $this;
    }
}
