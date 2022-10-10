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
 * Wrap lateness around an object.
 *
 * Calls to methods and properties return a late object that will be
 * evaluated only when forced to a string value or when called using ->__toValue().
 *
 * <code>
 * $arrayObj = new \ArrayObject();
 * $arrayObj->setFlags(\ArrayObject::ARRAY_AS_PROPS);
 *
 * $arrayObj['a'] = 'old';
 *
 * $lateObj = new \Zalt\Late\ObjectWrap($arrayObj);
 * $output = array($arrayObj->a, $lateObj->a, $arrayObj->count(), $lateObj->count());
 *
 * echo $output[0] . ' -> ' . $output[1] . ' | ' . $output[2] . ' -> ' . $output[3];
 * // Result old -> old | 1 -> 1
 *
 * $arrayObj->a = 'new';
 * $arrayObj[] = 2;
 *
 * echo $output[0] . ' -> ' . $output[1] . ' | ' . $output[2] . ' -> ' . $output[3];
 * // Result old -> new | 1 -> 2
 * </code>
 *
 * @package    Zalt
 * @subpackage Late
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.0
 */
class ObjectWrap extends LateAbstract
{
    protected $_object;

    public function __construct($object)
    {
        $this->_object = $object;
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
        return $this->_object;
    }
}
