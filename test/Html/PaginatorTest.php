<?php

declare(strict_types=1);

/**
 *
 * @package    Zalt
 * @subpackage Html
 * @author     Roel van Meer <roel.van.meer@peercode.nl>
 * @copyright  Copyright (c) 2023, Peercode B.V.
 * @license    New BSD License
 */

namespace Zalt\Html;

use Zalt\Html\Paginator\LinkPaginator;

/**
 *
 * @package    Zalt
 * @subpackage Html
 * @since      Class available since version 1.0
 */
class PaginatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider paginationItemsProvider
     * @param int pageItems How many items are we currently showing
     * @param int lessItems How many items to show when decreasing the page size
     * @param int moreItems How many items to show when increasing the page size
     */
    public function testPagination(int $pageItems, int $lessItems, int $moreItems): void
    {
        $paginator = new LinkPaginator;

        $paginator->setPageItems($pageItems);

        $this->assertEquals($lessItems, $paginator->getLessItems());
        $this->assertEquals($moreItems, $paginator->getMoreItems());
    }

    public static function paginationItemsProvider()
    {
        // pageItems, lessItems, moreItems
        return [
            [ 5, 5, 10 ],
            [ 10, 5, 20 ],
            [ 20, 10, 30 ],
            [ 30, 20, 50 ],
            [ 50, 30, 100 ],
            [ 100, 50, 200 ],
            [ 200, 100, 500 ],
            [ 500, 200, 1000 ],
            [ 1000, 500, 1000 ],
        ];
    }
}
