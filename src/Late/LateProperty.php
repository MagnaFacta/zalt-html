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
class LateProperty extends LateAbstract
{
    /**
     * @var object
     */
    private $_object;

    /**
     * @var string or late
     */
    private $_property;

    public function __construct($object, $property)
    {
        $this->_object = $object;
        $this->_property = $property;
    }

    /**
    * The functions that fixes and returns a value.
    *
    * Be warned: this function may return a late value.
    *
    * @param \Zalt\Late\StackInterface $stack A \Zalt\Late\StackInterface object providing variable data
    * @return mixed
    */
    public function __toValue(\Zalt\Late\StackInterface $stack)
    {
        $object = $this->_object;
        while ($object instanceof LateInterface) {
            $object = $object->__toValue($stack);
        }

        $property = $this->_property;
        while ($property instanceof LateInterface) {
            $property = $property->__toValue($stack);
        }

        if (is_object($object)) {
            if (isset($object->$property)) {
                return $object->$property;
            }
        } 
    }
}
