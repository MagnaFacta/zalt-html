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
 * Standard interface for attributes in this package.
 *
 * The interface ensure the ability to not only get and set the
 * value, but also the attribute name and the ability to add to
 * the content in a manner as defined by the attribute itself.
 *
 * E.g. adding to a class attribute usually involves seperating
 * the new addition with a space.
 *
 * @package    MUtil
 * @subpackage Html
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.0
 */
interface AttributeInterface extends \MUtil\Html\HtmlInterface
{
    /**
     * Returns an unescape string version of the attribute
     *
     * Output escaping is done elsewhere, e.g. in \Zend_View_Helper_HtmlElement->_htmlAttribs()
     *
     * @return string
     */
    public function __toString();

    /**
     * Add to the attribute
     *
     * @param mixed $value
     * @return \MUtil\Html\AttributeInterface (continuation pattern)
     */
    public function add($value);

    /**
     * Get the scalar value of this attribute.
     *
     * @return string | int | null
     */
    public function get();

    /**
     * Returns the attribute name
     *
     * @return string
     */
    public function getAttributeName();

    // inherited: public function render(\Zend_View_Abstract $view);

    /**
     * Set the value of this attribute.
     *
     * @return \MUtil\Html\AttributeInterface (continuation pattern)
     */
    public function set($value);
}