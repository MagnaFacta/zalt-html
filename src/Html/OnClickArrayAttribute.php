<?php

/**
 *
 *
 * @package    Zalt
 * @subpackage Html
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 */

namespace Zalt\Html;

/**
 * Default attribute for onclicks with extra functions for common tasks
 *
 * @package    Zalt
 * @subpackage Html
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since \Zalt version 1.0
 */
class OnClickArrayAttribute extends \Zalt\Html\JavascriptArrayAttribute
{
    /**
     *
     * @param mixed $args Ra::args
     */
    public function __construct(...$args)
    {
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