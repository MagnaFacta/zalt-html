<?php

/**
 *
 *
 * @package    MUtil
 * @subpackage Html
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 */

namespace MUtil\Html;

/**
 *
 * @package    MUtil
 * @subpackage Html
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since \MUtil version 1.0
 */
class ClassArrayAttribute extends \MUtil\Html\ArrayAttribute
{
    /**
     * String used to glue array items together
     *
     * @var string
     */
    protected $_separator = ' ';

    public function __construct($arg_array = null)
    {
        $args = func_get_args();
        parent::__construct('class', $args);
    }

    public function getKeyValue($key, $value)
    {
        return $value;
    }
}