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
 * Sprintf class is used to use sprintf with renderable content .
 *
 * @package    MUtil
 * @subpackage Html
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since \MUtil version 1.2
 */
class Sprintf extends \ArrayObject implements \MUtil\Html\ElementInterface
{
    /**
     * Object classes that should not be added to the core array, but should be set using
     * a setXxx() function.
     *
     * This parameter enables sub-classes to define their own special types.
     *
     * @var array Null or array containing className => setFunction()
     */
    protected $_specialTypes;

    /**
     * The default special types that are always valid for children of this class.
     *
     * @var array
     */
    private $_specialTypesDefault = array(
        'Zend_View' => 'setView',
        );

    /**
     * View object
     *
     * @var \Zend_View_Interface
     */
    public $view = null;

    /**
     * Adds an HtmlElement to this element
     *
     * @see \MUtil\Html\Creator
     *
     * @param string $name Function name becomes tagname (unless specified otherwise in \MUtil\Html\Creator)
     * @param array $arguments The content and attributes values
     * @return \MUtil\Html\HtmlElement With '$name' tagName
     */
    public function __call($name, array $arguments)
    {
        $elem = \MUtil\Html::createArray($name, $arguments);

        $this[] = $elem;

        return $elem;
    }

    /**
     *
     * @param mixed $arg_array \MUtil\Ra::args parameter passing
     */
    public function __construct($arg_array = null)
    {
        parent::__construct();

        $args = \MUtil\Ra::args(func_get_args());

        $this->init();

        // Passing the $args  to parent::__construct()
        // means offsetSet() is not called.
        foreach ($args as $key => $arg) {
            $this->offsetSet($key, $arg);
        }
    }

    /**
     * Interface required function, not in real use
     *
     * @return null
     */
    public function getTagName()
    {
        return null;
    }

    /**
     * Get the current view
     *
     * @return \Zend_View
     */
    public function getView()
    {
        return $this->view;
    }

    /**
     * Initiator functions - to prevent constructor overloading
     */
    protected function init()
    {
        if ($this->_specialTypes) {
            $this->_specialTypes = $this->_specialTypes + $this->_specialTypesDefault;
        } else {
            $this->_specialTypes = $this->_specialTypesDefault;
        }
    }

    public function offsetSet(mixed $index,mixed $newval): void
    {
        if ($index && (! is_numeric($index))) {
            if (method_exists($this, $fname = 'set' . $index)) {
                $this->$fname($newval);

                return;
            }
        }

        /*
        if (! $this->_specialTypes) {
            \MUtil\EchoOut\EchoOut::backtrace();
        } // */
        foreach ($this->_specialTypes as $class => $method) {
            if ($newval instanceof $class) {
                $this->$method($newval, $index);

                return;
            }
        }

        parent::offsetSet($index, $newval);
    }

    /**
     * Renders the element into a html string
     *
     * The $view is used to correctly encode and escape the output
     *
     * @param \Zend_View_Abstract $view
     * @return string Correctly encoded and escaped html output
     */
    public function render(\Zend_View_Abstract $view)
    {
        if (null === $view) {
            $view = $this->getView();
        } else {
            $this->setView($view);
        }

        $params = \MUtil\Html::getRenderer()->renderArray($view, $this->getIterator(), false);

        if ($params) {
            return call_user_func_array('sprintf', $params);
        }

        return '';
    }

    /**
     * Set the View object
     *
     * @param  \Zend_View_Interface $view
     * @return \Zend_View_Helper_Abstract
     */
    public function setView(\Zend_View_Interface $view)
    {
        $this->view = $view;
        return $this;
    }

    /**
     *
     * @param mixed $arg_array \MUtil\Ra::args parameter passing
     */
    public static function sprintf($arg_array = null)
    {
        $args = \MUtil\Ra::args(func_get_args());

        return new self($args);
    }
}
