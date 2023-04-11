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
 * This is just a most simple paginator demonstration class
 *
 * @package    Zalt
 * @subpackage Html\Paginator
 * @since      Class available since version 1.0
 */
class PrintPaginator extends PaginatorAbstract
{
    public  function getHtmlPagelinks(): HtmlInterface
    {
        $start = (($this->pageNumber - 1) * $this->pageItems) + 1;
        $end   = min($this->pageNumber * $this->pageItems, $this->itemCount);
        return new Sequence(sprintf("Showing page %d rows %d-%d out of %d.", $this->pageNumber, $start, $end, $this->itemCount));
    }
}