<?php

/**
 *
 * @package    Zalt
 * @subpackage Html
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 */

namespace Zalt\Html;

/**
 * A simple, basic one value attribute
 *
 * @package    Zalt
 * @subpackage Html
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.0
 */

class Attribute extends \Zalt\Html\AttributeAbstract
{
    private $_value;

    public function add($value)
    {
        if (is_numeric($this->_value)) {
            $this->set($this->_value + $value);

        }  elseif (is_string($this->_value)) {
            $this->set($this->_value . $value);

        } else {
            $this->set($value);

        }

        return $this->_value;
    }

    public function get()
    {
        return $this->_value;
    }

    public function set($value)
    {
        if (is_string($value) || is_numeric($value)) {
            $this->_value = $value;

        } else {
            throw new \Zalt\Html\HtmlException('Invalid argument of type ' . get_class($value) . ' for attribute value. Was expecting a string or number.');

        }

        $this->_value = $value;

        return $this;
    }
}