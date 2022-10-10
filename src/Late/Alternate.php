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
 *
 * @package    Zalt
 * @subpackage Late
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.0
 */
class Alternate extends LateAbstract
{
    private $_values;
    private $_count;
    private $_current;

    public function __construct(array $values)
    {
        $this->_values  = array_values($values);
        $this->_count   = count($values);
        $this->_current = 0;

        if (0 == $this->_count) {
            throw new LateException('Class ' . __CLASS__ . ' needs at least one value as an argument.');

        } elseif (1 == $this->_count) {
            $this->_count++;
            $this->_values[] = null;
        }
    }

    /**
    * The functions that fixes and returns a value.
    *
    * Be warned: this function may return a late value.
    *
    * @param StackInterface $stack A StackInterface object providing variable data
    * @return mixed
    */
    public function __toValue(StackInterface $stack)
    {
        if ($this->_current >= $this->_count) {
            $this->_current = 0;
        }

        return $this->_values[$this->_current++];
    }
}
