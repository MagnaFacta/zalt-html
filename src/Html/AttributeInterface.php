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
 * Standard interface for adding attributes to objects in this package.
 *
 * The interface ensure the ability to not only get and set the
 * value, but also the attribute name and the ability to add to
 * the content in a manner as defined by the attribute itself.
 *
 * E.g. adding to a class attribute usually involves seperating
 * the new addition with a space.
 *
 * @package    Zalt
 * @subpackage Html
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.0
 */
interface AttributeInterface extends HtmlInterface
{
    /**
     * Returns an unescape string version of the attribute
     *
     * @return string
     */
    public function __toString();

    /**
     * Add to the attribute
     *
     * @param mixed $value
     * @return AttributeInterface (continuation pattern)
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

    // inherited: public function render();

    /**
     * Set the value of this attribute.
     *
     * @return AttributeInterface (continuation pattern)
     */
    public function set($value);
}