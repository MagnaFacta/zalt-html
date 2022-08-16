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
 * Href attribute, i.e the name is fixed.
 *
 * Behaves as parent class otherwise
 *
 * @see \MUtil\Html\UrlArrayAttribute
 *
 * @package    MUtil
 * @subpackage Html
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.0
 */
class HrefArrayAttribute extends \MUtil\Html\UrlArrayAttribute
{
    public function __construct($args_array = null)
    {
        $args = func_get_args();
        parent::__construct('href', $args);
    }

    public static function hrefAttribute(array $commands = null)
    {
        return new self($commands);
    }
}
