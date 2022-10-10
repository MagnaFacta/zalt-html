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

/**
 * The basic workhorse function for all Late objects.
 *
 * It returns a new Late object for every call, property get or array offsetGet
 * applied to the sub class Late object and implements the Late interface
 *
 * @package    Zalt
 * @subpackage Late
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.0
 */
abstract class LateAbstract implements LateInterface
{
    /**
     * Return a late version of the call
     *
     * @return LateCall
     */
    public function __call($name, array $arguments)
    {
        return new LateCall(array($this, $name), $arguments);
    }

    /**
     * Return a late version of the property retrieval
     *
     * @return LateProperty
     */
    public function __get($name)
    {
        // WARNING
        //
        // I thought about caching properties. Always useful when a property is
        // used a lot. However, this would mean that every LateAbstract value
        // would have to store a cache, just in case this happens.
        //
        // All in all I concluded the overhead is probably not worth it, though I
        // did not test this.
        return new LateProperty($this, $name);
    }

    /**
     * You cannot set a Late object.
     *
     * throws \Zalt\Late\LateException
     */
    public function __set($name, $value)
    {
        throw new LateException('You cannot set a Late object.');
    }

    /**
     * Every Late Interface implementation has to try to
     * change the result to a string or return an error
     * message as a string.
     *
     * @return string
     */
    public function __toString()
    {
        try {
            $stack = Late::getStack();
            $value = $this;

            while ($value instanceof LateInterface) {
                $value = $this->__toValue($stack);
            }

            if (is_string($value)) {
                return $value;
            }

            // TODO: test
            if (is_object($value) && (! method_exists($value, '__toString'))) {
                return 'Object of type ' . get_class($value) . ' cannot be converted to string value.';
            }

            return (string) $value;

        } catch (\Exception $e) {
            // Cannot pass exception from __toString().
            //
            // So catch all exceptions and return error message.
            // Make sure to use @see get() if you do not want this to happen.
            return $e->getMessage();
        }
    }

    /**
     * Returns a late call where this object is the first parameter
     *
     * @param $callableOrObject object|callable
     * @param mixed $nameOrArg1 optional method|mixed
     * @param mixed $argn optional mixed
     * @return LateInterface
     */
    public function call($callableOrObject, $nameOrArg1 = null, $argn = null)
    {
        $args = func_get_args();
        $callable = array_shift($args);

        if (is_callable($callable)) {
            // Put $this as the first parameter
            array_unshift($args, $this);

        } elseif (is_object($callable)) {
            // Second argument should be string that is function name
            $callable = array($callable, array_shift($args));

            // Put $this as the first parameter
            array_unshift($args, $this);

        } else {
            // First argument should be method of this object.
            $callable = array($this, $callable);
        }

        return new LateCall($callable, $args);
    }

    public function offsetExists(mixed $offset): bool
    {
        return true;
    }

    public function offsetGet(mixed $offset): mixed
    {
        return new ArrayAccessor($this, $offset);
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new LateException('You cannot set a Late object.');
    }

    public function offsetUnset(mixed $offset): void
    {
        throw new LateException('You cannot unset a Late object.');
    }
}
