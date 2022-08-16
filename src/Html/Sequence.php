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
 * The Sequence class is for sequentional Html content, kind of like a DOM document fragment.
 *
 * It usual use is where you should return a single ElementInterface object but want to return a
 * sequence of objects. While implementing the \MUtil\Html\ElementInterface it does have attributes
 * nor does it return a tagname so it is not really an element, just treated as one.
 *
 * This object also contains functions for processing parameters of special types. E.g. when a
 * \Zend_View object is passed it should be stored in $this->view, not added to the core array.
 *
 * @package    MUtil
 * @subpackage Html
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since \MUtil version 1.0
 */
class Sequence extends \MUtil\ArrayString implements \MUtil\Html\ElementInterface
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
     * Extra array with special types for subclasses.
     *
     * When an object of one of the key types is used, then use
     * the class method defined as the value.
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
     * Return a sequence with the items concatened without spaces or breajs
     *
     * @param mixed $args_array \MUtil\Ra::args input
     * @return \self
     */
    public static function createSequence($args_array = null)
    {
        // BUG FIX: this function used to be called sequence() just
        // like all other static HtmlInterface element creation
        // functions, but as a sequence can contain a sequence
        // this lead to unexpected behaviour.

        $args = \MUtil\Ra::args(func_get_args());

        $seq = new self($args);

        if (! isset($args['glue'])) {
            $seq->setGlue('');
        }

        return $seq;
    }

    /**
     * Return a sequence with the items separated by spaces
     *
     * @param mixed $args_array \MUtil\Ra::args input
     * @return \self
     */
    public static function createSpaced($args_array = null)
    {
        // BUG FIX: this function used to be called spaced() just
        // like all other static HtmlInterface element creation
        // functions, but as a sequence can contain a sequence
        // this lead to unexpected behaviour.

        $args = \MUtil\Ra::args(func_get_args());

        $seq = new self($args);

        if (! isset($args['glue'])) {
            $seq->setGlue(' ');
        }

        return $seq;
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

    /**
     * Set the item in the sequence, unless a set{Index} function
     * exists or the new value is an instance of a special type.
     *
     * @param mixed $index scalar
     * @param mixed $newval
     * @return void
     */
    public function offsetSet(mixed $index, mixed $newval): void
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
        return;
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
        //*
        if (null === $view) {
            $view = $this->getView();
        } else {
            $this->setView($view);
        }
        // \MUtil\EchoOut\EchoOut::r($this->count(), $glue);

        $glue = $this->getGlue();
        if ($glue instanceof \MUtil\Html\HtmlInterface) {
            $glue = $glue->render($view);
        }
        return \MUtil\Html::getRenderer()->renderArray($view, $this->getIterator(), $glue);
    }

    /**
     * Set the View object
     *
     * @param  \Zend_View_Interface $view
     * @return \MUtil\Html\Sequence (continuation pattern)
     */
    public function setView(\Zend_View_Interface $view)
    {
        $this->view = $view;
        return $this;
    }
}
