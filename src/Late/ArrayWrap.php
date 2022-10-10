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

/**
 * Wrap lateness around an array.
 *
 * @package    Zalt
 * @subpackage Late
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.0
 */
class ArrayWrap extends ObjectWrap
{
    public function __construct(array $array = array())
    {
        parent::__construct(new ArrayObject($array));
    }

    public function offsetExists(mixed $offset): bool
    {
        return $this->_object->offsetExists($offset);
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->_object->offsetSet($offset, $value);
    }

    public function offsetUnset(mixed $offset): void
    {
        $this->_object->offsetUnset($offset);
    }
}
