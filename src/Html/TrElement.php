<?php

/**
 *
 * @package    MUtil
 * @subpackage Html
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2012 Erasmus MC
 * @license    New BSD License
 */

namespace MUtil\Html;

/**
 *
 *
 * @package    MUtil
 * @subpackage Html
 * @copyright  Copyright (c) 2012 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.0
 */
class TrElement extends \MUtil\Html\HtmlElement implements \MUtil\Html\ColumnInterface
{
    /**
     * Most elements must be rendered even when empty, others should - according to the
     * xhtml specifications - only be rendered when the element contains some content.
     *
     * $renderWithoutContent controls this rendering. By default an element tag is output
     * but when false the tag will only be present if there is some content in it.
     *
     * @var boolean The element is rendered even without content when true.
     */
    public $renderWithoutContent = false;

    /**
     * In some elements only certain elements are allowed as content. By specifying
     * $_allowedChildTags the element automatically ensures this is the case.
     *
     * At construction the $_defaultChildTag of the object is added (when needed) to
     * the $_allowedChildTags.
     *
     * @var string|array A string or array of string values of the allowed element tags.
     */
    protected $_allowedChildTags = array('td', 'th');

    /**
     * Usually no text is appended after an element, but for certain elements we choose
     * to add a "\n" newline character instead, to keep the output readable in source
     * view.
     *
     * @var string Content added after the element.
     */
    protected $_appendString = "\n";

    /**
     * When repeating content using $_repeater you may want to output the content only when it has
     * changed.
     *
     * @see $_repeater
     *
     * @var boolean Do not output if the output is identical to the last time the element was rendered.
     */
    protected $_onlyWhenChanged = false;


    /**
     * @see $_onlyWhenChanged
     *
     * @var string Cache for last output for comparison
     */
    protected $_onlyWhenChangedValueStore = null;


    /**
     * Returns the cell or a \MUtil\MultiWrapper containing cells that occupy the column position, taking colspan and other functions into account.
     *
     * @param int $col The numeric column position, starting at 0;
     * @return \MUtil\Html\HtmlElement Probably an element of this type, but can also be something else, posing as an element.
     */
    public function getColumn($col)
    {
        $results = $this->getColumnArray($col);

        switch (count($results)) {
            case 0:
                return null;

            case 1:
                return reset($results);

            default:
                return new \MUtil\MultiWrapper($results);
        }
    }

    /**
     * Returns the cells that occupies the column position, taking colspan and other functions into account, in an array.
     *
     * @param int $col The numeric column position, starting at 0;
     * @return array Of probably one \MUtil\Html\HtmlElement
     */
    public function getColumnArray($col)
    {
        return array($this->getColumn($col));
    }

    /**
     * Return the number of columns, taking such niceties as colspan into account
     *
     * @return int
     */
    public function getColumnCount()
    {
        $count = 0;

        foreach ($this->_content as $cell) {
            $count += self::getCellWidth($cell);
        }

        return $count;
    }

    /**
     * Returns the cell's column width. A utility function.
     *
     * @param mixed $cell \MUtil\Html\ColumnInterface
     * @return int
     */
    public static function getCellWidth($cell)
    {
        if ($cell instanceof \MUtil\Html\ColumnInterface) {
            return $cell->getColumnCount();
        }

        if (isset($cell->colspan) && is_int($cell->colspan)) {
            return  intval($cell->colspan);
        }

        // Assume it is a single column
        return 1;
    }

    /**
     * When repeating content using $_repeater you may want to output the content only when it has
     * changed.
     *
     * @return boolean
     */
    public function getOnlyWhenChanged()
    {
        return $this->_onlyWhenChanged;
    }

    /**
     * Function to allow overloading  of tag rendering only
     *
     * Renders the element tag with it's content into a html string
     *
     * The $view is used to correctly encode and escape the output
     *
     * @param \Zend_View_Abstract $view
     * @return string Correctly encoded and escaped html output
     */
    protected function renderElement(\Zend_View_Abstract $view)
    {
        $result = parent::renderElement($view);

        if ($this->_onlyWhenChanged) {
            if ($result == $this->_onlyWhenChangedValueStore) {
                return null;
            }
            $this->_onlyWhenChangedValueStore = $result;
        }

        return $result;
    }
    
    /**
     * When repeating content using $_repeater you may want to output the content only when it has
     * changed.
     *
     * @see $_repeater
     *
     * @return \MUtil\Html\HtmlElement (continuation pattern)
     */
    public function setOnlyWhenChanged($value)
    {
        $this->_onlyWhenChanged = $value;
        return $this;
    }

    /**
     * Static helper function for creation, used by @see \MUtil\Html\Creator.
     *
     * @param mixed $arg_array Optional \MUtil\Ra::args processed settings
     * @return \MUtil\Html\TrElement
     */
    public static function tr($arg_array = null)
    {
        $args = func_get_args();
        return new self(__FUNCTION__, $args);
    }
}