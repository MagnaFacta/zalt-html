<?php

/**
 *
 * @package    Zalt
 * @subpackage Late
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 */

namespace Zalt\Late;

use Zalt\Late\Stack\ArrayStack;
use Zalt\Late\Stack\BridgeStack;
use Zalt\Late\Stack\EmptyStack;
use Zalt\Late\Stack\ObjectStack;
use Zalt\Late\Stack\RepeatableStack;
use Zalt\Ra;

/**
 * Why get lazy:
 * 1 - You want to use a result later that is not yet known
 * 2 - You want the result repeated for a sequence of items
 * 3 - You want a result on some object but do not have the object yet
 *
 * What is a result you might want:
 * 1 - the object itself
 * 2 - a call to an object method
 * 3 - an object propery
 * 4 - an array object
 *
 * @package    Zalt
 * @subpackage Late
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.0
 */
class Late
{
    /**
     * The default stack to use
     *
     * @var StackInterface
     */
    private static $_stack;

    /**
     * Static variable for debugging purposes. Toggles the echoing of e.g. raised results.
     *
     * @var boolean When true Late objects should start outputting what is happening in them.
     */
    public static $verbose = false;

    /**
     * Returns a lazy object that alternates through all the parameters used
     * to call this function. (At least two , but more is allowed.)
     *
     * @param mixed $value1
     * @param mixed $value2
     * @return Alternate
     */
    public static function alternate(...$args)
    {
        return new Alternate(Ra::flatten($args));
    }

    /**
     * Execute this call later
     *
     * @param callable $callable
     * @param array $args All other arguments are used to call the function at a later time
     * @return LateCall
     */
    public static function call($callable, ...$args)
    {
        return new LateCall($callable, $args);
    }

    /**
     * Create a lazy comparison operation
     *
     * @param mixed $opLeft
     * @param string $oper The operator to use for this comparison
     * @param mixed $opRight
     * @return LateCall
     */
    public static function comp($opLeft, $oper, $opRight)
    {
        switch ($oper) {
            case '==':
                $lambda = function ($a, $b) {return $a == $b;};
                break;

            case '===':
                $lambda = function ($a, $b) {return $a === $b;};
                break;

            case '!=':
            case '<>':
                $lambda = function ($a, $b) {return $a <> $b;};
                break;

            case '!==':
                $lambda = function ($a, $b) {return $a !== $b;};
                break;

            case '<':
                $lambda = function ($a, $b) {return $a < $b;};
                break;

            case '<=':
                $lambda = function ($a, $b) {return $a <= $b;};
                break;

            case '>':
                $lambda = function ($a, $b) {return $a > $b;};
                break;

            case '>=':
                $lambda = function ($a, $b) {return $a >= $b;};
                break;

            case '<=>':
                $lambda = function ($a, $b) {return $a <=> $b;};
                break;

            default:
                $lambda = function ($a, $b) use ($oper) {
                    return eval('return $a ' . $oper . ' $b;');
                };
                break;
        }
        return new LateCall($lambda, array($opLeft, $opRight));
    }

    /**
     * The arguments are flattened lazily into one single array
     * and then joined together without separator.
     *
     * @param mixed $arg_array
     * @return LateCall
     */
    public static function concat(...$args)
    {
        return new LateCall('implode', array('', new LateCall([\Zalt\Ra::class, 'flatten'], [$args])));
    }

    /**
     * Returns the first set value at the time of evaluation
     * 
     * @param mixed $args
     * @return null|LateCall
     */
    public static function first(...$args)
    {
        // First value is evaluated first
        // $result = array_shift($args);
        $result = null;
        
        foreach (array_reverse($args) as $arg) {
            $result = new LateCall([$arg, 'if'], [$arg, $result]);
        }
        return $result;
    }

    /**
     * Get a named call to the lazy stack
     *
     * @return LateGet
     */
    public static function get($name)
    {
        return new LateGet($name);
    }

    /**
     * Get the current stack or none
     *
     * @return StackInterface
     */
    public static function getStack()
    {
        if (! self::$_stack instanceof StackInterface) {
            self::$_stack = new EmptyStack(__CLASS__);
        }

        return self::$_stack;
    }

    /**
     * Late if statement
     *
     * @param mixed $if The value tested during raise
     * @param mixed $then The value after raise when $if is true
     * @param mixed $else The value after raise when $if is false
     * @return LateCall
     */
    public static function iff($if, $then, $else = null)
    {
        return new LateCall(array($if, 'if'), [$then, $else]);
    }

    /**
     * Late if statement
     *
     * @param mixed $if The value tested during raise
     * @param mixed $then The value after raise when $if is true
     * @param mixed $else The value after raise when $if is false
     * @return LateCall
     */
    public static function iif($if, $then, $else = null)
    {
        return new LateCall(array($if, 'if'), [$then, $else]);
    }

    /**
     * Returns a Late version of the parameter
     *
     * @param mixed $var
     * @return LateInterface
     */
    public static function L($var)
    {
        if (is_object($var)) {
            if ($var instanceof LateInterface) {
                return $var;
            } elseif ($var instanceof Procrastinator) {
                return $var->toLate();
            }

            return new ObjectWrap($var);

        } elseif(is_array($var)) {
            return new ArrayWrap($var);

        } else {
            return new LateGet($var);
        }
    }

    /**
     * Return a lazy callable to an object
     *
     * @param Object $object
     * @param string $method Method of the object
     * @param mixed $arg_array1 Optional, first of any arguments to the call
     * @return \Zalt\Late\LateCall
     */
    public static function method($object, $method, $arg_array1 = null)
    {
        $args = array_slice(func_get_args(), 2);
        return new LateCall(array($object, $method), $args);
    }

    /**
     * Perform a lazy call to an array
     *
     * @param mixed $array
     * @param mixed $offset
     * @return ArrayAccessor
     */
    public static function offsetGet($array, $offset)
    {
        return new ArrayAccessor($array, $offset);
    }

    /**
     * Return a lazy retrieval of an object property
     *
     * @param Object $object
     * @param string $property Property of the object
     * @return LateProperty
     */
    public static function property($object, $property): LateProperty
    {
        return new LateProperty($object, $property);
    }

    /**
     * Raises a \Zalt\Late\LateInterface one level, but may still
     * return a \Zalt\Late\LateInterface.
     *
     * This function is usually used to perform a e.g. filter function on object that may e.g.
     * contain Repeater objects.
     *
     * @param mixed $object Usually an object of type \Zalt\Late\LateInterface
     * @param mixed $stack Optional variable stack for evaluation
     * @return mixed
     */
    public static function raise($object, $stack = null)
    {
        //\Zalt\EchoOut\EchoOut::countOccurences(__FUNCTION__);
        if ($object instanceof LateInterface) {
            if (! $stack instanceof StackInterface) {
                $stack = self::getStack();
            }
            return $object->__toValue($stack);
        } else {
            return $object;
        }
    }

    /**
     *
     * @param mixed $repeatable
     * @return RepeatableInterface
     */
    public static function repeat($repeatable)
    {
        if ($repeatable instanceof RepeatableInterface) {
            return $repeatable;
        }

        return new Repeatable($repeatable);
    }

    /**
     * Raises a \Zalt\Late\LateInterface until the return object is not a
     * \Zalt\Late\LateInterface object.
     *
     * @param mixed $object Usually an object of type \Zalt\Late\LateInterface
     * @param mixed $stack Optional variable stack for evaluation
     * @return mixed Something not lazy
     */
    public static function rise($object, $stack = null)
    {
        if ($object instanceof LateInterface || is_array($object)) {
            if (! $stack instanceof StackInterface) {
                $stack = self::getStack();
            }
            if (is_array($object)) {
                $object = self::riseRa($object, $stack);
            } else {
                while ($object instanceof LateInterface) {
                    $object = $object->__toValue($stack);
                }
            }
        }
        return $object;
    }

    public static function riseObject(LateInterface $object, StackInterface $stack)
    {
        while ($object instanceof LateInterface) {
            $object = $object->__toValue($stack);
        }

        if ($object && is_array($object)) {
            return self::riseRa($object, $stack);
        }

        return $object;
    }

    public static function riseRa(array $object, StackInterface $stack)
    {
        foreach ($object as $key => &$val) {
            while ($val instanceof LateInterface) {
                $val = $val->__toValue($stack);
            }
            if (is_array($val)) {
                $val = self::riseRa($val, $stack);
            }
        }

        return $object;
    }

    /**
     * Set the current stack
     *
     * @param mixed $stack Value to be turned into stack for evaluation
     * @return StackInterface
     */
    public static function setStack($stack)
    {
        if ($stack instanceof StackInterface) {
            self::$_stack = $stack;

        } elseif ($stack instanceof \MUtil\Model\Bridge\TableBridgeAbstract) {
            self::$_stack = new BridgeStack($stack);

        } elseif ($stack instanceof Repeatable) {
            self::$_stack = new RepeatableStack($stack);

        } elseif (Ra::is($stack)) {
            $stack = Ra::to($stack);

            self::$_stack = new ArrayStack($stack);

        } elseif (is_object($stack)) {
            self::$_stack = new ObjectStack($stack);

        } else {
            throw new LateException("Late stack set to invalid scalar type.");
        }

        return self::$_stack;
    }
}
