<?php

/**
 *
 * @package    Zalt
 * @subpackage HtmlUtil
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 */

namespace Zalt\HtmlUtil;

use ArrayAccess;
use Traversable;

/**
 * Magic object for enabling multiple objects to be treated as one object.
 *
 * This objects passed all object manipulations on to the array of objects contained in it
 * allowing you to handle multiple objects as if they were a single object.
 *
 * No the creators of object orientation do not tumble in their graves. First most of them
 * all still alive and second this is acutally just an extension of generics. PHP does
 * not have generics at this time and those languages that do, do not support this type of
 * generics, but there is no mathematical reason why this should not work.
 *
 * @package    Zalt
 * @subpackage HtmlUtil
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since \Zalt version 1.0
 */
class MultiWrapper implements ArrayAccess
{
    /**
     * The items that will be treated as one.
     *
     * @var array
     */
    protected $_array;

    /**
     * The class name used to create new class instances for function call results
     *
     * @var string
     */
    protected $_class = __CLASS__;

    /**
     * Pass on functions calls ans return the results as a new Wrapper
     *
     * @param string $name
     * @param array $arguments
     * @return MultiWrapper
     */
    public function __call($name, array $arguments)
    {
        $result = array();

        foreach ($this->_array as $key => $obj) {
            $result[$key] = call_user_func_array(array($obj, $name), $arguments);
        }

        return new $this->_class($result);
    }

    /**
     *
     * @param array|Traversable $array
     */
    public function __construct($array)
    {
        $this->_array = $array;
    }

    public function __get($name)
    {
        $result = array();

        foreach ($this->_array as $key => $obj) {
            // Return only for those that have the property
            if (isset($obj->$name)) {
                $result[$key] = $obj->$name;
            }
        }

        return $result;
    }

    public function __isset($name)
    {
        // Return on first one found
        foreach ($this->_array as $key => $obj) {
            if (isset($obj->$name)) {
                return true;
            }
        }

        return false;
    }

    public function __set($name, $value)
    {
        foreach ($this->_array as $obj) {
            $obj->$name = $value;
        }
    }

    public function __unset($name)
    {
        foreach ($this->_array as $obj) {
            unset($obj->$name);
        }
    }

    public function offsetExists(mixed $offset): bool
    {
        // Return on first one found
        foreach ($this->_array as $key => $obj) {
            if (array_key_exists($offset, $obj)) {
                return true;
            }
        }

        return false;
    }

    public function offsetGet(mixed $offset): mixed
    {
        $result = array();

        foreach ($this->_array as $key => $obj) {
            // Return only for those that have the item
            if (array_key_exists($offset, $obj)) {
                $result[$key] = $obj[$offset];
            }
        }

        return $result;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        foreach ($this->_array as $obj) {
            $obj[$offset] = $value;
        }
    }

    public function offsetUnset(mixed $offset): void
    {
        foreach ($this->_array as $obj) {
            unset($obj[$offset]);
        }
    }
}
