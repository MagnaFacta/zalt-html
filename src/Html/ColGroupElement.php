<?php

/**
 *
 *
 * @package    MUtil
 * @subpackage Html
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 */

namespace MUtil\Html;

/**
 *
 * @package    MUtil
 * @subpackage Html
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since \MUtil version 1.0
 */
class ColGroupElement extends \MUtil\Html\HtmlElement implements \MUtil\Html\ColumnInterface
{
    protected $_defaultChildTag = 'col';

    /**
     * Returns the cell or a \MUtil\MultiWrapper containing cells that occupy the column position, taking colspan and other functions into account.
     *
     * @param int $col The numeric column position, starting at 0;
     * @return \MUtil\Html\HtmlElement Probably an element of this type, but can also be something else, posing as an element.
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
     * @return array Of probably one \MUtil\Html\HtmlElement
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
        if (isset($this->span)) {
            return $this->span;
        }

        $count = 0;
        foreach ($this->_content as $col) {
            if ($col instanceof \MUtil\Html\ColumnInterface) {
                $count += $col->getColumnCount();
            } else {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Static helper function for creation, used by @see \MUtil\Html\Creator.
     *
     * @param mixed $arg_array Optional \MUtil\Ra::args processed settings
     * @return \MUtil\Html\ColGroupElement
     */
    public static function colgroup($arg_array = null)
    {
        $args = func_get_args();
        return new self(__FUNCTION__, $args);
    }
}