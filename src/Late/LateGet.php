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
 * Creates a late get to returns a parameter set in the $stack used in \Zalt\Late::rise().
 *
 * @package    Zalt
 * @subpackage Late
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.0
 */
class LateGet extends LateAbstract
{
    /**
     * The name of the stack value to get
     *
     * @var string
     */
    private $_name;

    /**
     *
     * @param string $name The name of the stack value to get
     */
    public function __construct($name)
    {
        $this->_name = $name;
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
        return $stack->LateGet($this->_name);
    }
}
