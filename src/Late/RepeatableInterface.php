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
 * An object implementing the RepeatableInterface can be called
 * repeatedly and sequentially with the content of the properties,
 * function calls and array access methods changing until each
 * value of a data list has been returned.
 *
 * This interface allows you to specify an action only once instead
 * of repeatedly in a loop.
 *
 * @package    Zalt
 * @subpackage Late
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.0
 */
interface RepeatableInterface extends ArrayAccess
{
    /**
     * Returns the current item. Starts the loop when needed.
     *
     * return mixed The current item
     */
    public function __current();

    /**
     * Return a late version of the property retrieval
     *
     * @return LateInterface
     */
    public function __get($name);

    /**
     * Return the core data in the Repeatable in one go
     *
     * @return \Iterator|array
     */
    public function __getRepeatable();

    /**
     * Returns the current item. Starts the loop when needed.
     *
     * return mixed The current item
     */
    public function __next();

    /**
     * The functions that starts the loop from the beginning
     *
     * @return mixed True if there is data.
     */
    public function __start();

    // public function offsetExists($offset);
    // public function offsetGet($offset);
    // public function offsetSet($offset, $value);
    // public function offsetUnset($offset);
}
