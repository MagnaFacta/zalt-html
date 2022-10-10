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

use Zalt\Ra\Ra;

/**
 *
 * @package    Zalt
 * @subpackage Late
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.0
 */
class ParallelRepeater implements RepeatableInterface
{
    protected $repeatables = array();

    public function __construct(...$args)
    {
        $args = Ra::args($args);
        foreach ($args as $id => $repeatable) {
            if (null != $repeatable) {
                $this->addRepeater($repeatable, $id);
            }
        }
    }

    /**
     * Returns the current item. Starts the loop when needed.
     *
     * return mixed The current item
     */
    public function __current()
    {
        $results = array();
        foreach ($this->repeatables as $id => $repeater) {
            if ($result = $repeater->__curent()) {
                $results[$id] = $result;
            }
        }
        return $results;
    }

    /**
     * Return a late version of the property retrieval
     *
     * @return LateInterface
     */
    public function __get($name)
    {
        $results = array();
        foreach ($this->repeatables as $id => $repeater) {
            if ($result = $repeater->$name) {
                $results[$id] = $result;
            }
        }
        return $results;
    }

    /**
     * Return the core data in the Repeatable in one go
     *
     * @return \Iterator|array
     */
    public function __getRepeatable()
    {
        $results = array();
        foreach ($this->repeatables as $id => $repeater) {
            if ($result = $repeater->__getRepeatable()) {
                $results[$id] = $result;
            }
        }
        return $results;
    }

    /**
     * Returns the current item. Starts the loop when needed.
     *
     * return mixed The current item
     */
    public function __next()
    {
        $results = array();
        foreach ($this->repeatables as $id => $repeater) {
            if ($result = $repeater->__next()) {
                $results[$id] = $result;
            }
        }
        return $results;
    }

    /**
     * Return a late version of the property retrieval
     *
     * @return LateProperty
     */
    public function __set($name, $value)
    {
        throw new LateException("You cannot set a Late object property like '$name'.");
    }

    /**
     * The functions that starts the loop from the beginning
     *
     * @return mixed True if there is data.
     */
    public function __start()
    {
        $result = false;
        foreach ($this->repeatables as $repeater) {
            $result = $repeater->__start() || $result;
        }
        return $result;
    }

    public function addRepeater($repeater, $id = null)
    {
        if (! $repeater instanceof RepeatableInterface) {
            $repeater = new Repeatable($repeater);
        }
        if (null === $id) {
            $this->repeatables[] = $repeater;
        } else {
            $this->repeatables[$id] = $repeater;
        }

        return $repeater;
    }

    public function offsetExists($offset)
    {
        foreach ($this->repeatables as $repeater) {
            if ($repeater->offsetExists($offset)) {
                return true;
            }
        }

        return false;
    }

    public function offsetGet($offset)
    {
        $results = array();
        foreach ($this->repeatables as $id => $repeater) {
            if ($result = $repeater[$offset]) {
                $results[$id] = $result;
            }
        }
        return $results;
    }

    public function offsetSet($offset, $value)
    {
        throw new LateException("You cannot set a Late object offset like '$offset'.");
    }

    public function offsetUnset($offset)
    {
        throw new LateException("You cannot unset a Late object offset like '$offset'.");
    }
}