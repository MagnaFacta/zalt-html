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
 * Href attribute, i.e the name is fixed.
 *
 * Behaves as parent class otherwise
 *
 * @see UrlArrayAttribute
 *
 * @package    Zalt
 * @subpackage Html
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.0
 */
class HrefArrayAttribute extends UrlArrayAttribute
{
    public function __construct(...$args)
    {
        parent::__construct('href', $args);
    }

    public static function hrefAttribute(array $commands = null)
    {
        return new self($commands);
    }
}
