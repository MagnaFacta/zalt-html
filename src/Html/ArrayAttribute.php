<?php

/**
 *
 * @package    Zalt
 * @subpackage Html
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 */

namespace Zalt\Html;

use ArrayAccess;
use Countable;
use IteratorAggregate;
use Traversable;
use Zalt\Late\LateInterface;

/**
 * Parent class for all array based attribute classes.
 *
 * Useable as is, using spaces as value separators by default.
 *
 * Parameter setting checks for the addition of special types,
 * just as \Zalt\Html\HtmlElement.
 *
 * @package    Zalt
 * @subpackage Html
 * @since      Class available since version 1.0
 */
class ArrayAttribute extends AttributeAbstract implements ArrayAccess, Countable, IteratorAggregate
{
    /**
     * String used to glue array items together
     *
     * @var string
     */
    protected $_separator = ' ';

    /**
     * Specially treated types for a specific subclass
     *
     * @var array function name => class
     */
    protected $_specialTypes = [];

    /**
     * The actual values
     *
     * @var array
     */
    protected $_values = [];

    /**
     *
     * @param string $name The name of the attribute
     * @param mixed $args
     */
    public function __construct($name, mixed $args)
    {
        parent::__construct($name, $args);
    }

    /**
     * Returns the rendered values of th earray elements
     *
     * @return array
     */
    protected function _getArrayRendered()
    {
        return Html::getRenderer()->renderArray($this->getArray(), false);
    }

    /**
     * Certain types must always be processed in a special manner.
     * This is independent of whether the type is passed as an
     * attribute or element content.
     *
     * @param $value mixed The value to check
     * @param $key optional The key used to add the value.
     * @return true|false True if nothing was done, false if the $value was processed.
     */
    private function _notSpecialType($value, $key = null)
    {
        if ($key) {
            if (method_exists($this, $fname = 'set' . $key)) {
                $this->$fname($value);

                return false;
            }
        }

        foreach ($this->_specialTypes as $method => $class) {
            if ($value instanceof $class) {
                $this->$method($value, $key);

                return false;
            }
        }

        return true;
    }

    /**
     * Set the item in questions (with guard for special types)
     *
     * @param mixed $key
     * @param mixed $value
     * @return ArrayAttribute (continuation pattern)
     */
    protected function _setItem($key, $value)
    {
        if ($this->_notSpecialType($value, $key)) {
            if (null === $key) {
                $this->_values[] = $value;
            } else {
                $this->_values[$key] = $value;
            }
        }

        return $this;
    }

    /**
     * Add to the attribute
     *
     * @param mixed $keyOrValue The key if a second parameter is specified, otherwise a value
     * @param mixed $valueIfKey Optional, the value if a key is specified
     * @return ArrayAttribute (continuation pattern)
     */
     public function add($keyOrValue, $valueIfKey = null)
    {
        // Key is specified first, but when no key it is the value.
        if (null == $valueIfKey) {
            $offset = null;
            $value  = $keyOrValue;
        } else {
            $offset = $keyOrValue;
            $value  = $valueIfKey;
        }

        if (is_array($value) || (($value instanceof Traversable) && (! $value instanceof LateInterface))) {
            foreach ($value as $key => $item) {
                $this->_setItem($key, $item);
            }
        } else {
            $this->_setItem($offset, $value);
        }

        return $this;
    }

    /**
     * \Countable implementation, the number of array items
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->_values);
    }

    /**
     * Get the scalar value of this attribute.
     *
     * @return string | int | null
     */
    public function get()
    {
        $results = array();

        foreach ($this->_getArrayRendered() as $key => $value) {
            $results[] = $this->getKeyValue($key, $value);
        }

        if ($results) {
            return trim(implode($this->getSeparator(), $results), $this->getSeparator());
        }

        return null;
    }

    /**
     * Returns the base array. Overrule for attribute specific changes
     *
     * @return array
     */
    protected function getArray()
    {
        return (array) $this->_values;
    }

    /**
     * \IteratorAggregate implementation
     *
     * @return \ArrayIterator
     */
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->_values);
    }

    /**
     * Function that allows subclasses to define their own
     * mechanism for redering the key/value combination.
     *
     * E.g. key=value instead of just the value.
     *
     * @param scalar $key
     * @param string $value Output escaped value
     * @return string
     */
    public function getKeyValue($key, $value): string
    {
        return $value;
    }

    /**
     * String used to glue items together
     *
     * @return string
     */
    public function getSeparator()
    {
        return $this->_separator;
    }

    /**
     * Does the item exist in this object
     *
     * @param scalar $offset
     * @return boolean
     */
    public function offsetExists(mixed $offset): bool
    {
        return array_key_exists($offset, $this->_values);
    }

    /**
     * Get the item from this object
     *
     * Generates notice if $offset does not exist
     *
     * @param scalar $offset
     * @return mixed
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->_values[$offset];
    }

    /**
     * Set the value for this item
     *
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (null === $offset) {
            $this->_values[] = $value;
        } else {
            $this->_values[$offset] = $value;
        }
    }

    /**
     * Remove an item from this object
     *
     * @param scalar $offset
     */
    public function offsetUnset(mixed $offset): void
    {
        unset($this->_values[$offset]);
    }

    /**
     * Set the values of this attribute.
     *
     * @param mixed $keyOrValue The key if a second parameter is specified, otherwise a value
     * @param mixed $valueIfKey Optional, the value if a key is specified
     * @return \Zalt\Html\ArrayAttribute (continuation pattern)
     */
   public function set($keyOrValue, $valueIfKey = null)
    {
        if ($this->_values) {
            $this->_values = array();
        }

        return $this->add($keyOrValue, $valueIfKey);
    }

    /**
     * Set the String used to glue items together
     *
     * @param string $separator
     * @return \Zalt\Html\ArrayAttribute (continuation pattern)
     */
    public function setSeparator($separator)
    {
        $this->_separator = $separator;
        return $this;
    }
}
