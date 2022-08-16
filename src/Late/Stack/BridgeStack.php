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
class BridgeStack implements StackInterface
{
    /**
     * The object to get properties from
     *
     * @var \MUtil\Model\Bridge\TableBridgeAbstract
     */
    protected $_object;

    /**
     * Should we throw an exception on a missing value?
     *
     * @var bool
     */
    private $_throwOnMiss = false;

    /**
     *
     * @param Object $object
     */
    public function __construct(\MUtil\Model\Bridge\TableBridgeAbstract $object)
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
        return $this->_object->getLateValue($name);
    }

    /**
     * Set this stack to throw an exception
     *
     * @param mixed $throw bool
     * @return BridgeStack (continuation pattern_
     */
    public function setThrowOnMiss(bool $throw = true)
    {
        $this->_throwOnMiss = $throw;
        return $this;
    }
}
