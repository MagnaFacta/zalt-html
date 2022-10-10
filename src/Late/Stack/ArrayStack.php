<?php

/**
 *
 *
 * @package    Zalt
 * @subpackage Late\Stack
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2014 Erasmus MC
 * @license    New BSD License
 */

namespace Zalt\Late\Stack;

use ArrayObject;
use Zalt\Late\Late;
use Zalt\Late\StackInterface;

/**
 * Get a simple array stack implemenation
 *
 * @package    Zalt
 * @subpackage Late\Stack
 * @copyright  Copyright (c) 2014 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.0
 */
class ArrayStack extends ArrayObject implements StackInterface
{
    /**
     * Should we throw an exception on a missing value?
     *
     * @var boolean
     */
    private $_throwOnMiss = false;

    /**
     * Returns a value for $name
     *
     * @param string $name A name indentifying a value in this stack.
     * @return A value for $name
     */
    public function LateGet($name)
    {
        if ($this->offsetExists($name)) {
            return $this->offsetGet($name);
        }
        if ($this->_throwOnMiss) {
            throw new LateStackException("No late stack variable defined for '$name' parameter.");
        }
        if (Late::$verbose) {
            // \Zalt\EchoOut\EchoOut::header("No late stack variable defined for '$name' parameter.");
        }

        return null;
    }

    /**
     * Set this stack to throw an exception
     *
     * @param mixed $throw bool
     * @return ArrayStack (continuation pattern_
     */
    public function setThrowOnMiss(bool $throw = true)
    {
        $this->_throwOnMiss = $throw;
        return $this;
    }
}
