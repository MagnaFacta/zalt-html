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

use Zalt\Late\Late;

/**
 * A standaard TBODY element, that puts all contents in TR elements, implements the
 * ColomInterface and allows you to specify a row class.
 *
 * You can alternate row classes by using a late value.
 *
 * @see \Zalt\Html\TableElement
 *
 * @package    Zalt
 * @subpackage Html
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.0
 */
class TBodyElement extends HtmlElement implements ColumnInterface
{
    public $defaultRowClass;

    public $renderWithoutContent = false;

    protected $_addtoLastChild = true;

    protected $_appendString = "\n";

    protected $_defaultChildTag = 'tr';

    protected $_defaultRowChildTag = 'td';

    protected $_onEmptyLocal = null;

    /**
     * Create an default element for content.
     *
     * Some elements put their content in a fixed sub element, e.g. table uses tbody,
     * tbody uses tr and tr uses td or th.
     *
     * @param mixed $value
     * @param string $offset or null
     * @return \Zalt\Html\HtmlElement
     */
    protected function _createDefaultTag($value, $offset = null)
    {
        $row = parent::_createDefaultTag($value, $offset = null);

        if ($this->defaultRowClass && (! isset($row->class))) {
            $row->appendAttrib('class',  $this->defaultRowClass);
        }

        $row->setDefaultChildTag($this->getDefaultRowChildTag());

        return $row;
    }

    /**
     * Make sure a default child tag element exists.
     *
     * Overruled because of the extra actions in $this->tr()
     */
    protected function _ensureDefaultTag()
    {
        if ($this->_defaultChildTag && (! $this->_content)) {
            $this->tr();
        }
    }

    /**
     * Returns the cell or a \Zalt\MultiWrapper containing cells that occupy the column position, taking colspan and other functions into account.
     *
     * @param int $col The numeric column position, starting at 0;
     * @return null|\Zalt\Html\HtmlElement|MultiWrapper Probably an element of this type, but can also be something else, posing as an element.
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
                return new MultiWrapper($results);
        }
    }

    /**
     * Returns the cells that occupies the column position, taking colspan and other functions into account, in an array.
     *
     * @param int $col The numeric column position, starting at 0;
     * @return array Of probably one \Zalt\Html\HtmlElement
     */
    public function getColumnArray($col)
    {
        $results = array();

        foreach ($this->_content as $row) {
            if ($row instanceof \Zalt\Html\ColumnInterface) {
                $results = array_merge($results, $row->getColumnArray($col));
            }
        }

        return $results;
    }

    /**
     * Return the number of columns, taking such niceties as colspan into account
     *
     * @return int
     */
    public function getColumnCount()
    {
        $counts[] = 0;

        foreach ($this->_content as $row) {
            if ($row instanceof \Zalt\Html\ColumnInterface) {
                $counts[] = $row->getColumnCount();
            }
        }

        return max($counts);
    }

    public function getDefaultRowClass()
    {
        return $this->defaultRowClass;
    }

    public function getDefaultRowChildTag()
    {
        return $this->_defaultRowChildTag;
    }

    public function getOnEmpty($colcount = null)
    {
        if (! $this->_onEmptyLocal) {
            $this->setOnEmpty(null, $colcount);
        }

        return $this->_onEmptyLocal;
    }

    public function setDefaultRowClass($class)
    {
        $this->defaultRowClass = $class;
        return $this;
    }

    public function setDefaultRowChildTag($tag)
    {
        $this->_defaultRowChildTag = $tag;
        return $this;
    }

    public function setOnEmpty($content, $colcount = null)
    {
        if (($content instanceof \Zalt\Html\ElementInterface) &&
            ($content->getTagName() ==  $this->_defaultChildTag)) {

            /**
             * @var HtmlElement $content
             */
            $this->_onEmptyContent = $content;

            if (isset($this->_onEmptyContent[0])) {
                $this->_onEmptyLocal = $this->_onEmptyContent[0];
            } else {

                $this->_onEmptyLocal = $this->_onEmptyContent->td();
            }

        } else {
            $this->_onEmptyContent = Html::create($this->_defaultChildTag);
            $this->_onEmptyLocal   = $this->_onEmptyContent->td($content);

        }

        // Collcount tells us to span the empty content cell
        if ($colcount) {
            if ($colcount instanceof \Zalt\Html\ColumnInterface) {
                // Late calculation of number of columns when this is a ColumnInterface
                $this->_onEmptyLocal->colspan = Late::method($colcount, 'getColumnCount');

            } else {
                // Passed fixed number of columns, just set
                $this->_onEmptyLocal->colspan = $colcount;
            }
        } else {

            // Pass the row instead of the cell. Without a colspan
            // the programmer should add extra cells to it.
            $this->_onEmptyLocal = $this->_onEmptyContent;
        }

        return $this->_onEmptyLocal;
    }

    /**
     * Repeat the element when rendering.
     *
     * When repeatTags is false (the default) only the content is repeated but
     * not the element tags. When repeatTags is true the both the tags and the
     * content are repeated.
     *
     * @param mixed $repeater \Zalt\Late\RepeatableInterface or something that can be made into one.
     * @param mixed $onEmptyContent Optional. When not null the content to display when the repeater does not result in data is set.
     * @param boolean $repeatTags Optional when not null the repeatTags switch is set.
     * @param mixed $colcount \Zalt\Html\ColumnInterface or integer. Span the onEmpty content over $colcount cells
     * @return \Zalt\Html\TBodyElement (continuation pattern)
     */
    public function setRepeater($repeater, $onEmptyContent = null, $repeatTags = null, $colcount = null)
    {
        parent::setRepeater($repeater, null, $repeatTags);

        if ($onEmptyContent) {
            $this->setOnEmpty($onEmptyContent, $colcount);
        }

        return $this;
    }

    /**
     * Static helper function for creation, used by @see \Zalt\Html\Creator.
     *
     * @param mixed $args Optional args processed settings
     * @return \Zalt\Html\TBodyElement with tag 'tbody'
     */
    public static function tbody(...$args)
    {
        return new self(__FUNCTION__, $args);
    }

    /**
     * Static helper function for creation, used by @see \Zalt\Html\Creator.
     *
     * @param mixed $args Optional args processed settings
     * @return \Zalt\Html\TBodyElement with tag 'tfoot'
     */
    public static function tfoot(...$args)
    {
        return new self(__FUNCTION__, $args);
    }

    /**
     * Static helper function for creation, used by @see \Zalt\Html\Creator.
     *
     * @param mixed $args Optional args processed settings
     * @return \Zalt\Html\TBodyElement with tag 'thead'
     */
    public static function thead(...$args)
    {
        return new self(__FUNCTION__, array('DefaultRowChildTag' => 'th'), $args);
    }

    /**
     * Add a row with a class and the correct type of default child tag to this element
     *
     * @param mixed $args Optional args processed settings
     * @return \Zalt\Html\TrElement
     */
    public function tr(...$args)
    {
        // Set default child tag first and het because otherwise
        // the children are created first and the default child tag
        // is set afterwards.
        if (! array_key_exists('DefaultChildTag', $args)) {
            array_unshift($args, array('DefaultChildTag' => $this->getDefaultRowChildTag()));
        }

        /**
         * @var TrElement $tr
         */
        $tr = Html::createArray('tr', $args);

        $this->append($tr);

        if ((! isset($tr->class)) && ($class = $this->getDefaultRowClass())) {
            $tr->appendAttrib('class', $class);
        }

        return $tr;
    }
}