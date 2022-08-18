<?php

/**
 *
 *
 * @package    Zalt
 * @subpackage Html
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 */

namespace Zalt\Html;

use Zalt\HtmlUtil\MultiWrapper;

/**
 *
 * @package    Zalt
 * @subpackage Html
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since \Zalt version 1.0
 */
interface ColumnInterface
{
    /**
     * Returns the cell or a \Zalt\MultiWrapper containing cells that occupy the column position, taking colspan and other functions into account.
     *
     * @param int $col The numeric column position, starting at 0;
     * @return HtmlElement|MultiWrapper Probably an element of this type, but can also be something else, posing as an element.
     */
    public function getColumn($col);

    /**
     * Returns the cells that occupies the column position, taking colspan and other functions into account, in an array.
     *
     * @param int $col The numeric column position, starting at 0;
     * @return array Of probably one \Zalt\Html\HtmlElement
     */
    public function getColumnArray($col);

    /**
     * Return the number of columns, taking such niceties as colspan into account
     *
     * @return int
     */
    public function getColumnCount();
}
