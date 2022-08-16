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
 * A standaard TBODY element, that puts all contents in TR elements, implements the
 * ColomInterface and allows you to specify a row class.
 *
 * You can alternate row classes by using a lazy value.
 *
 * @see \MUtil\Html\TableElement
 *
 * @package    MUtil
 * @subpackage Html
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.0
 */
class TBodyElement extends \MUtil\Html\HtmlElement implements \MUtil\Html\ColumnInterface
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
     * @return \MUtil\Html\HtmlElement
     */
    protected function _createDefaultTag($value, $offset = null)
    {
        $row = parent::_createDefaultTag($value, $offset = null);

        if ($this->defaultRowClass && (! isset($row->class))) {
            $row->class = $this->defaultRowClass;
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
        $results = array();

        foreach ($this->_content as $row) {
            if ($row instanceof \MUtil\Html\ColumnInterface) {
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
            if ($row instanceof \MUtil\Html\ColumnInterface) {
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
        if (($content instanceof \MUtil\Html\ElementInterface) &&
            ($content->getTagName() ==  $this->_defaultChildTag)) {

            $this->_onEmptyContent = $content;

            if (isset($this->_onEmptyContent[0])) {
                $this->_onEmptyLocal = $this->_onEmptyContent[0];
            } else {
                $this->_onEmptyLocal = $this->_onEmptyContent->td();
            }

        } else {
            $this->_onEmptyContent = \MUtil\Html::create($this->_defaultChildTag);
            $this->_onEmptyLocal   = $this->_onEmptyContent->td($content);

        }

        // Collcount tells us to span the empty content cell
        if ($colcount) {
            if ($colcount instanceof \MUtil\Html\ColumnInterface) {
                // Lazy calculation of number of columns when this is a ColumnInterface
                $this->_onEmptyLocal->colspan = \MUtil\Lazy::method($colcount, 'getColumnCount');

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
     * @param mixed $repeater \MUtil\Lazy\RepeatableInterface or something that can be made into one.
     * @param mixed $onEmptyContent Optional. When not null the content to display when the repeater does not result in data is set.
     * @param boolean $repeatTags Optional when not null the repeatTags switch is set.
     * @param mixed $colcount \MUtil\Html\ColumnInterface or intefer. Span the onEmpty content over $colcount cells
     * @return \MUtil\Html\TBodyElement (continuation pattern)
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
     * Static helper function for creation, used by @see \MUtil\Html\Creator.
     *
     * @param mixed $arg_array Optional \MUtil\Ra::args processed settings
     * @return \MUtil\Html\TBodyElement with tag 'tbody'
     */
    public static function tbody($arg_array = null)
    {
        $args = func_get_args();
        return new self(__FUNCTION__, $args);
    }

    /**
     * Static helper function for creation, used by @see \MUtil\Html\Creator.
     *
     * @param mixed $arg_array Optional \MUtil\Ra::args processed settings
     * @return \MUtil\Html\TBodyElement with tag 'tfoot'
     */
    public static function tfoot($arg_array = null)
    {
        $args = func_get_args();
        return new self(__FUNCTION__, $args);
    }

    /**
     * Static helper function for creation, used by @see \MUtil\Html\Creator.
     *
     * @param mixed $arg_array Optional \MUtil\Ra::args processed settings
     * @return \MUtil\Html\TBodyElement with tag 'thead'
     */
    public static function thead($arg_array = null)
    {
        $args = func_get_args();
        return new self(__FUNCTION__, array('DefaultRowChildTag' => 'th'), $args);
    }

    /**
     * Add a row with a class and the correct type of default child tag to this element
     *
     * @param mixed $arg_array \MUtil::args() values
     * @return \MUtil\Html\TrElement
     */
    public function tr($arg_array = null)
    {
        $args = func_get_args();

        // Set default child tag first and het because otherwise
        // the children are created first and the default child tag
        // is set afterwards.
        if (! array_key_exists('DefaultChildTag', $args)) {
            array_unshift($args, array('DefaultChildTag' => $this->getDefaultRowChildTag()));
        }

        $tr = \MUtil\Html::createArray('tr', $args);

        $this[] = $tr;

        if ((! isset($tr->class)) && ($class = $this->getDefaultRowClass())) {
            $tr->class = $class;
        }

        return $tr;
    }
}