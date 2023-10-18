<?php

/**
 *
 * @package    Zalt
 * @subpackage Html
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 */

namespace Zalt\Html;

/**
 *
 * @package    Zalt
 * @subpackage Html
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since \Zalt version 1.0
 */
class ColElement extends HtmlElement implements ColumnInterface
{
    /**
     * Returns the cell or a \Zalt\MultiWrapper containing cells that occupy the column position, taking colspan and other functions into account.
     *
     * @param int $col The numeric column position, starting at 0;
     * @return null|\Zalt\Html\HtmlElement Probably an element of this type, but can also be something else, posing as an element.
     */
    public function getColumn($col)
    {
        // this element is not part of the "real" column
        return null;
    }

    /**
     * Returns the cells that occupies the column position, taking colspan and other functions into account, in an array.
     *
     * @param int $col The numeric column position, starting at 0;
     * @return array Of probably one \Zalt\Html\HtmlElement
     */
    public function getColumnArray($col)
    {
        // this element is not part of the "real" column
        return array();
    }

    /**
     * Return the number of columns, taking such niceties as colspan into account
     *
     * @return int
     */
    public function getColumnCount()
    {
        if (isset($this->span) && is_int($this->span)) {
            return intval($this->span);
        }

        return 1;
    }

    /**
     * Static helper function for creation, used by @see \Zalt\Html\Creator.
     *
     * @param mixed $args Optional Ra::args processed settings
     * @return \Zalt\Html\ColElement
     */
    public static function col(...$args)
    {
        return new self(__FUNCTION__, $args);
    }
}