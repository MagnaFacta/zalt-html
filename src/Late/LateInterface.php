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

use ArrayAccess;

/**
 *
 * @package    Zalt
 * @subpackage Late
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.0
 */
interface LateInterface extends ArrayAccess
{
    /**
     * Return a late version of the call
     *
     * @return LateInterface
     */
    public function __call($name, array $arguments);

    /**
     * Return a late version of the property retrieval
     *
     * @return LateInterface
     */
    public function __get($name);

    /**
     * Every Late Interface implementation has to try to
     * change the result to a string or return an error
     * message as a string.
     *
     * @return string
     */
    public function __toString();

    /**
     * The functions that fixes and returns a value.
     *
     * Be warned: this function may return a late value.
     *
     * @param StackInterface $stack A StackInterface object providing variable data
     * @return mixed
     */
     public function __toValue(StackInterface $stack);

    // public function offsetExists($offset);
    // public function offsetGet($offset);
    // public function offsetSet($offset, $value);
    // public function offsetUnset($offset);
}