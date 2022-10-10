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

use Zalt\Late\Late;
use Zalt\Late\StackInterface;

/**
 * Get an object property get object implementation
 *
 * @package    Zalt
 * @subpackage Late\Stack
 * @copyright  Copyright (c) 2014 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.0
 */
class ObjectStack implements StackInterface
{
    /**
     * The object to get properties from
     *
     * @var Object
     */
    protected $_object;

    /**
     * Should we throw an exception on a missing value?
     *
     * @var boolean
     */
    private $_throwOnMiss = false;

    /**
     *
     * @param Object $object
     */
    public function __construct($object)
    {
        $this->_object = $object;
    }

    /**
     * Returns a value for $name
     *
     * @param string $name A name indentifying a value in this stack.
     * @return mixed A value for $name
     */
    public function LateGet($name)
    {
        if (property_exists($this->_object, $name)) {
            return $this->_object->$name;
        }
        if ($this->_throwOnMiss) {
            throw new LateStackException("No late stack variable defined for '$name' parameter.");
        }
        if (Late::$verbose) {
            $class = get_class($this->_object);
            // \Zalt\EchoOut\EchoOut::header("No late stack variable defined for '$name' parameter using a '$class' object.");
        }

        return null;
    }

    /**
     * Set this stack to throw an exception
     *
     * @param bool $throw
     * @return ObjectStack (continuation pattern_
     */
    public function setThrowOnMiss(bool $throw = true)
    {
        $this->_throwOnMiss = $throw;
        return $this;
    }
}
