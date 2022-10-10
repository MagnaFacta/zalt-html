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
 * While not Late of itself this object makes it easy to make a static call
 *
 * @package    Zalt
 * @subpackage Late
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.0
 */
class StaticCall
{
    protected $_className;

    /**
     * Return a late version of the call
     *
     * @return LateCall
     */
    public function __call($name, array $arguments)
    {
        return new LateCall(array($this->_className, $name), $arguments);
    }

    public function  __construct($className)
    {
        $this->_className = $className;
    }

    /**
     * Return a callable for this function
     *
     * @return callable
     */
    public function __get($name)
    {
        return array($this->_className, $name);
    }
}
