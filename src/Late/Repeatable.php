<?php

/**
 *
 *
 * @package    Zalt
 * @subpackage Late
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 */

namespace Zalt\Late;

use ArrayObject;
use Iterator;
use IteratorAggregate;
use Zalt\Late\Stack\EmptyStack;

/**
 *
 * @package    Zalt
 * @subpackage Late
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.0
 */
class Repeatable implements RepeatableInterface
{
    /**
     * When true array, otherwise interator
     *
     * @var boolean
     */
    protected $_arrayMode;

    /**
     * The current item as changing for each iteration
     *
     * @var mixed
     */
    protected $_currentItem;

    /**
     *
     * @var mixed The actual array or Iterator or IteratorAggregate or other item to repeat
     */
    protected $_repeatable;

    /**
     *
     * @var mixed The array or Iterator that repeats
     */
    protected $_repeater = null;

    /**
     *
     * @param mixed $repeatable The array or Iterator or IteratorAggregate or other item to repeat
     */
    public function __construct($repeatable)
    {
        $this->_repeatable  = $repeatable;
    }

    /**
     * Returns the current item. Starts the loop when needed.
     *
     * return mixed The current item
     */
    public function __current()
    {
        if (null === $this->_currentItem) {
            return $this->__next();
        }

        return $this->_currentItem;
    }

    /**
     * Return a late version of the property retrieval
     *
     * @return LateProperty
     */
    public function __get($name)
    {
        $this->$name = new GetRepeatableLate($this, $name);

        return $this->$name;
    }

    /**
     * Returns the repeating data set.
     *
     * return \Traversable|array
     */
    public function __getRepeatable()
    {
        $value = $this->_repeatable;
        $stack = Late::getStack();
        while ($value instanceof LateInterface) {
            $value = $value->__toValue($stack);
        }

        return $value;
    }

    /**
     * Returns the current item. Starts the loop when needed.
     *
     * return mixed The current item
     */
    public function __next()
    {
        if (null === $this->_repeater) {
            if (! $this->__start()) {
                return null;
            }
        }

        if ($this->_arrayMode) {
            if (null === $this->_currentItem) {
                $this->_currentItem = reset($this->_repeater);
            } else {
                $this->_currentItem = next($this->_repeater);
            }
        } else {
            if (null === $this->_currentItem) {
                if (! $this->_repeater->valid()) {
                    $this->_repeater->rewind();
                }
            } else {
                $this->_repeater->next();
            }
            if ($this->_repeater->valid()) {
                $this->_currentItem = $this->_repeater->current();
            } else {
                $this->_currentItem = false;
            }
        }

        if (is_array($this->_currentItem)) {
            // Make the array elements accessible as properties
            $this->_currentItem = new ArrayObject($this->_currentItem, ArrayObject::ARRAY_AS_PROPS);
        }

        return $this->_currentItem;
    }

    /**
     * The functions that starts the loop from the beginning
     *
     * @return mixed True if there is data.
     */
    public function __start()
    {
        $value = $this->__getRepeatable();

        // \Zalt\EchoOut\EchoOut::r($value);

        if (is_array($value)) {
            $this->_repeater  = $value;
            $this->_arrayMode = true;

        } elseif ($value instanceof Iterator) {
            $this->_repeater  = $value;
            $this->_arrayMode = false;

        } elseif ($value instanceof IteratorAggregate) {
            $this->_repeater = $value->getIterator();
            while ($this->_repeater instanceof IteratorAggregate) {
                // Won't be used often, but hey! better be sure
                $this->_repeater = $this->_repeater->getIterator();
            }
            $this->_arrayMode = false;

        } else {
            $this->_repeater  = array($value);
            $this->_arrayMode = true;
        }

        $this->_currentItem = null;

        if ($this->_arrayMode) {
            return (boolean) count($this->_repeater);

        } else {
            if ($this->_repeater->valid()) {
                return true;
            }
            // Some Interators require a rewind before they are valid
            $this->_repeater->rewind();
            return $this->_repeater->valid();
        }
    }

    public function offsetExists(mixed $offset): bool
    {
        return true;
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->$offset;
    }

    public function offsetSet(mixed $offset,mixed $value): void
    {
        throw new LateException("You cannot set a Late object item like '$offset'.");
    }

    public function offsetUnset(mixed $offset): void
    {
        throw new LateException("You cannot unset a Late objectt item like '$offset'.");
    }
}
