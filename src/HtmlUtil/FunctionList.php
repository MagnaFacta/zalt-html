<?php

/**
 *
 *
 * @package    Zalt
 * @subpackage Util
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 */

namespace Zalt\HtmlUtil;

/**
 * Return a function value using a scalar key.
 *
 * @package    Zalt
 * @subpackage Util
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.0
 */
class FunctionList extends LookupList
{
    /**
     * Item lookup function.
     *
     * This is a separate function to allow overloading by subclasses.
     *
     * @param scalar $key
     * @param mixed $default
     * @return mixed
     */
    protected function _getItem($key, $default = null)
    {
        if (isset($this->_elements[$key])) {
            $function = $this->_elements[$key];

            if (is_callable($function)) {
                return $function;
            } else {
                return $default;
            }
        } else {
            return $default;
        }
    }
}