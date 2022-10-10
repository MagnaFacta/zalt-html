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
 * A lates object for when you want to access an array but either the array
 * itself and/or the offset is a late object.
 *
 * @package    Zalt
 * @subpackage Late
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.0
 */

class ArrayAccessor extends \Zalt\Late\LateAbstract
{
    /**
     *
     * @var mixed Possibly late array
     */
    private $_array;

    /**
     *
     * @var mixed Possibly late offset
     */
    private $_offset;

    /**
     *
     * @param mixed Possibly late array
     * @param mixed Possibly late offset
     */
    public function __construct($array, $offset)
    {
        $this->_array  = $array;
        $this->_offset = $offset;
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
        $array  = $this->_array;
        $offset = $this->_offset;

        while ($offset instanceof LateInterface) {
            $offset = $offset->__toValue($stack);
        }
        while ($array instanceof LateInterface) {
            $array = $array->__toValue($stack);
        }

        if (Late::$verbose) {
//            \Zalt\EchoOut\EchoOut::header('Late offset get for offset: <em>' . $offset . '</em>');
//            \Zalt\EchoOut\EchoOut::classToName($array);
        }

        if (null === $offset) {
            if (isset($array[''])) {
                $value = $array[''];
            } else {
                $value = null;
            }
        } elseif (is_array($offset)) {
            // When the offset is itself an array, return an
            // array of values applied to this offset.
            $value = array();
            foreach (Late::riseRa($offset, $stack) as $key => $val) {
                if (isset($array[$val])) {
                    $value[$key] = $val;
                }
            }
        } elseif (isset($array[$offset])) {
            $value = $array[$offset];
        } else {
            $value = null;
        }

        while ($value instanceof LateInterface) {
            $value = $value->__toValue($stack);
        }
        if (is_array($value)) {
            $value = Late::riseRa($value, $stack);
        }
        return $value;
    }

}
