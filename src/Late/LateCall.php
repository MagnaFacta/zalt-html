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
 *
 * @package    Zalt
 * @subpackage Late
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.0
 */
class LateCall extends LateAbstract
{
    /**
     * @var array (possibly Late)
     */
    private $_callable;

    /**
     * @var array 
     */
    private $_params;

    /**
     * @param       $callable
     * @param array $params
     */
    public function __construct($callable, array $params = array())
    {
        $this->_callable = $callable;
        $this->_params   = $params;
    }

    /**
     * The functions that fixes and returns a value.
     *
     * Be warned: this function may return a late value.
     *
     * @param StackInterface $stack A StackInterface object providing variable data
     * @return mixed 
     * @throws LateException              
     */
    public function __toValue(StackInterface $stack)
    {
        $params = $this->_params;

        if (is_array($this->_callable)) {
            list($object, $method) = $this->_callable;
            while ($object instanceof LateInterface) {
                $object = $object->__toValue($stack);
            }
            $callable = array($object, $method);

            if (! (is_object($object) && (method_exists($object, $method) || method_exists($object, '__call')))) {
                if (function_exists($method)) {
                    // Add the object as the first parameter
                    array_unshift($params, $object);
                    $callable = $method;

                } elseif ('if' == strtolower($method)) {
                    if ($object) {
                        return isset($params[0]) ? Late::rise($params[0]) : null;
                    } else {
                        return isset($params[1]) ? Late::rise($params[1]) : null;
                    }
                }
            }

        } else {
            $method   = $this->_callable; // For error message
            $callable = $this->_callable;
        }

        if (is_callable($callable)) {
            $params = Late::riseRa($params, $stack);
            return call_user_func_array($callable, $params);
        }

        throw new LateException('Late execution exception! "' . $method . '" is not a valid callable.');
    }
}
