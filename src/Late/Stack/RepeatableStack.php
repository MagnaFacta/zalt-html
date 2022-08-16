<?php

/**
 *
 * @package    Zalt
 * @subpackage Late\Stack
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2014 Erasmus MC
 * @license    New BSD License
 */

namespace Zalt\Late\Stack;

use Zalt\Late\Late;
use Zalt\Late\LateInterface;
use Zalt\Late\Repeatable;
use Zalt\Late\StackInterface;

/**
 *
 *
 * @package    Zalt
 * @subpackage Late\Stack
 * @copyright  Copyright (c) 2014 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.0
 */
class RepeatableStack implements StackInterface
{
    /**
     * The object to get properties from
     *
     * @var Repeatable
     */
    protected $_object;

    /**
     *
     * @param Repeatable $object
     */
    public function __construct(Repeatable $object)
    {
        $this->_object = $object;
    }

    /**
     * Returns a value for $name
     *
     * @param string $name A name indentifying a value in this stack.
     * @return A value for $name
     */
    public function LateGet($name)
    {
        $value = $this->_object->__get($name);
        if ($value instanceof LateInterface) {
            return Late::rise($value);
        }
        return $value;
    }

    /**
     * Set this stack to throw an exception
     *
     * @param bool $throw boolean
     * @return RepeatableStack (continuation pattern_
     */
    public function setThrowOnMiss(bool $throw = true)
    {
        // Do nothing any exceptions will be thrown elsewhere
        return $this;
    }
}
