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

use Zalt\Ra\Ra;

/**
 * Sprintf class is used to use sprintf with renderable content .
 *
 * @package    Zalt
 * @subpackage Html
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since \Zalt version 1.0
 */
class Sprintf extends \ArrayObject implements ElementInterface
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
     * Adds an HtmlElement to this element
     *
     * @see \Zalt\Html\Creator
     *
     * @param string $name Function name becomes tagname (unless specified otherwise in \Zalt\Html\Creator)
     * @param array $arguments The content and attributes values
     * @return \Zalt\Html\HtmlElement With '$name' tagName
     */
    public function __call($name, array $arguments)
    {
        $elem = Html::createArray($name, $arguments);

        $this[] = $elem;

        return $elem;
    }

    /**
     *
     * @param mixed $arg_array \Zalt\Ra\Ra::args parameter passing
     */
    public function __construct($arg_array = null)
    {
        parent::__construct();

        $args = Ra::args(func_get_args());

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
     * @return string Correctly encoded and escaped html output
     */
    public function render()
    {
        $params = Html::getRenderer()->renderArray($this->getIterator(), false);

        if ($params) {
            return call_user_func_array('sprintf', $params);
        }

        return '';
    }

    /**
     *
     * @param mixed $arg_array \Zalt\Ra\Ra::args parameter passing
     */
    public static function sprintf($arg_array = null)
    {
        $args = Ra::args(func_get_args());

        return new self($args);
    }
}
