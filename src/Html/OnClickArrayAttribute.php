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
 * Default attribute for onclicks with extra functions for common tasks
 *
 * @package    MUtil
 * @subpackage Html
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since \MUtil version 1.0
 */
class OnClickArrayAttribute extends \MUtil\Html\JavascriptArrayAttribute
{
    /**
     *
     * @param mixed $arg_array \MUtil\Ra::args
     */
    public function __construct($arg_array = null)
    {
        $args = func_get_args();
        parent::__construct('onclick', $args);
    }

    /**
     * 
     * @param array $commands
     * @return \self
     */
    public static function onclickAttribute(array $commands = null)
    {
        return new self($commands);
    }
}