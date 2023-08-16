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
 *
 * @package    Zalt
 * @subpackage Html
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since \Zalt version 1.0
 */
class ClassArrayAttribute extends ArrayAttribute
{
    /**
     * String used to glue array items together
     *
     * @var string
     */
    protected $_separator = ' ';

    public function __construct(...$args)
    {
        parent::__construct('class', $args);
    }

    public function getKeyValue($key, $value): string
    {
        return $value;
    }
}