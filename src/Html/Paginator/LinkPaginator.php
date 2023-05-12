<?php

declare(strict_types=1);

/**
 * @package    Zalt
 * @subpackage Html\Paginator
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 */

namespace Zalt\Html\Paginator;

use Zalt\Html\HtmlInterface;
use Zalt\Html\Sequence;

/**
 * @package    Zalt
 * @subpackage Html\Paginator
 * @since      Class available since version 1.0
 */
class LinkPaginator extends PaginatorAbstract
{
    use CurrentUrlPaginatorTrait;

    public int $maximumItems = 100000;
    public int $minimumItems = 5;

    /**
     * @return string|null Null for not output, string for output
     */
    public function getFirstPageLabel(): ?string
    {
        return '<<';
    }

    public function getHtmlPagelinks(): HtmlInterface
    {
        $items = $this->getItemsList();
        $pages = $this->getPages();

        return new Sequence($pages, $items, ['glue' => ' ']);
    }

    protected function getItems(): array
    {
        return [
            $this->getItemLink($this->getLessItems(), '-'),
            $this->pageItems,
            $this->getItemLink($this->getMoreItems(), '+'),
            ];
    }

    protected function getItemsList(): HtmlInterface
    {
        $output = $this->getItemsHolder();

        foreach ($this->getItems() as $item) {
            $output->append($item);
        }

        return $output;
    }

    protected function getItemsHolder(): HtmlInterface
    {
        $output = new Sequence();
        $output->setGlue(' ');
        return $output;
    }

    /**
     * @return string|null Null for not output, string for output
     */
    public function getLastPageLabel(): ?string
    {
        return '>>';
    }

    public function getLessItems(): int
    {
        $base = intval($this->pageItems / 2);

        if ($base < $this->minimumItems) {
            return $this->minimumItems;
        }

        return $base;
    }

    public function getMoreItems(): int
    {
        $current = (string) $this->pageItems;
        if ('1' === \substr($current, 0, 1)) {
            $base = intval($this->pageItems * 2.5);
        } else {
            $base = intval($this->pageItems * 2);
        }

        if ($base > $this->maximumItems) {
            return $this->maximumItems;
        }
        if ($base > $this->itemCount) {
            return $this->itemCount;
        }
        return $base;
    }

    /**
     * @return string|null Null for not output, string for output
     */
    public function getNextPageLabel(): ?string
    {
        return '>';
    }

    /**
     * @param int $currentPage
     * @param int $pageCount
     * @return array pageNumber => $label
     */
    protected function getPageNumbers(int $currentPage, int $pageCount): array
    {
        $firstLink = max($currentPage - 5, 1);
        $lastLink  = min($currentPage + 5, $pageCount);

        if ($lastLink - $firstLink > 10) {
            $lastLink = $firstLink + 10;
        }
        if ($lastLink === 0) {
            $lastLink = 1;
        }

        $numbers = range($firstLink, $lastLink);

        return array_combine($numbers, $numbers);
    }

    protected function getPages(): HtmlInterface
    {
        $output = $this->getPagesHolder();

        $pageCount = max(intval(ceil($this->itemCount / $this->pageItems)), 1);
        if ($pageCount < $this->pageNumber) {
            $this->pageNumber = max($pageCount, 1);
        }

        $output->append($this->getPageLink(1, $this->getFirstPageLabel(), true));
        $output->append($this->getPageLink(max(1, $this->pageNumber - 1), $this->getPreviousPageLabel(), true));

        foreach ($this->getPageNumbers($this->pageNumber, $pageCount) as $page => $label) {
            $output->append($this->getPageLink($page, (string) $label, false));
        }

        $output->append($this->getPageLink(min($pageCount, $this->pageNumber + 1), $this->getNextPageLabel(), true));
        $output->append($this->getPageLink($pageCount, $this->getLastPageLabel(), true));

        return $output;
    }

    protected function getPagesHolder(): HtmlInterface
    {
        $output = new Sequence();
        $output->setGlue(' ');
        return $output;
    }

    /**
     * @return string|null Null for not output, string for output
     */
    public function getPreviousPageLabel(): ?string
    {
        return '<';
    }
}