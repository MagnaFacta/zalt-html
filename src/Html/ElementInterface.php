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
 * The ElementInterface defines an Html Element as not just implementing
 * the HtmlInterface but also as an object that can be accessed as array
 * object through the \ArrayAccess, \Countable and \IteratorAggregate
 * interfaces.
 *
 * Usually you should just extend the HtmlElement class. This interface
 * is actually only used when you want to "fake" the full element, e.g.
 * by having a Sequence of elements (i.e. a document fragment) or an
 * object "posing" as a contained element, e.g. the RepeatRenderer class.
 *
 * @see \MUtil\Html\HtmlInterface
 * @see \MUtil\Html\HtmlElement
 * @see \MUtil\Html\RepeatRenderer
 * @see \MUtil\Html\Sequence
 *
 * @package    MUtil
 * @subpackage Html
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since \MUtil version 1.0
 */
interface ElementInterface extends \MUtil\Html\HtmlInterface, \ArrayAccess, \Countable, \IteratorAggregate
{
    /**
     * Add a value to the element.
     *
     * Depending on the value type the value may be added as an attribute,
     * set a parameter of the element or just be added to the main content.
     *
     * Adding to the main content should be the default action.
     *
     * @param mixe $value
     */
    public function append($value);

    /**
     * Most Html elements have a tag name, but "document fragments" like
     * @see \MUtil\Html\Sequence may return null.
     *
     * @return string The tag name or null if this element does not have one
     */
    public function getTagName();

    // inherited: public function render(\Zend_View_Abstract $view);
}
