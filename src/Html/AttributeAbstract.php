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
 * Basic class for all attributes, does the rendering and attribute name parts,
 * but no value processing.
 *
 * @package    Zalt
 * @subpackage Html
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.0
 */
abstract class AttributeAbstract implements AttributeInterface
{
    /**
     *
     * @var string
     */
    public $name;

    /**
     *
     * @param string $name The name of the attribute
     * @param mixed $value
     */
    public function __construct(string $name, $value = null)
    {
        $this->name = $name;

        if ($value) {
            $this->set($value);
        }
    }

    /**
     * Returns an unescape string version of the attribute
     *
     * Output escaping is done elsewhere, e.g. in \Zend_View_Helper_HtmlElement->_htmlAttribs()
     *
     * If a subclass needs the view for the right output and the view might not be set
     * it must overrule __toString().
     *
     * @return string
     */
    public function __toString()
    {
        return $this->get();
    }

    // public function add($value);
    // public function get();

    /**
     * Returns the attribute name
     *
     * @return string
     */
    public function getAttributeName()
    {
        return $this->name;
    }

    /**
     * Renders the element into a html string
     *
     * The $view is used to correctly encode and escape the output
     *
     * @return string Correctly encoded and escaped html output
     */
    public function render()
    {
        // Output escaping is done in \Zend_View_Helper_HtmlElement->_htmlAttribs()
        //
        // The reason for using render($view) is only in case the attribute needs the view to get the right data.
        // Those attributes must overrule render().
        return $this->get();
    }

    // public function set($value);
}