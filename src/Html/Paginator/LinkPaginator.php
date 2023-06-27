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

    public array $itemProgression = [5, 10, 20, 30, 50, 100, 200, 500, 1000];

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

    public function getFirstItem(): int
    {
        if ($this->itemCount == 0) {
            return 0;
        }
        return (($this->pageNumber - 1) * $this->pageItems) + 1;
    }

    public function getLastItem(): int
    {
        return min($this->pageNumber * $this->pageItems, $this->itemCount);
    }

    /**
     * Decrease the number of items to show on a page. This must never be
     * a value not in our item progression, or strange things will happen.
     *
     * @return int Number of items to show.
     */
    public function getLessItems(): int
    {
        $less = $this->itemProgression[0];
        foreach (array_reverse($this->itemProgression) as $count) {
            if ($count < $this->pageItems) {
                $less = $count;
                break;
            }
        }

        return $less;
    }

    /**
     * Increase the number of items to show on a page. This must never be
     * a value not in our item progression, or strange things will happen.
     *
     * @return int Number of items to show.
     */
    public function getMoreItems(): int
    {
        $more = $this->itemProgression[array_key_last($this->itemProgression)];
        foreach ($this->itemProgression as $count) {
            if ($count > $this->pageItems) {
                $more = $count;
                break;
            }
        }

        return $more;
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

    /**
     * Get the number of pages we have. We always have one page, even
     * if we don't have any items.
     *
     * @return int Number of pages with items.
     */
    protected function getPageCount(): int
    {
        if ($this->itemCount > 0 && $this->pageItems > 0) {
            $pageCount = intval(ceil($this->itemCount / $this->pageItems));
        } else {
            $pageCount = 1;
        }

        return $pageCount;
    }

    protected function getPages(): HtmlInterface
    {
        $output = $this->getPagesHolder();

        $pageCount = $this->getPageCount();

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
