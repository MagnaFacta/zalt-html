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
 *
 * @package    Zalt
 * @subpackage Html
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 */
class StyleArrayAttribute extends ArrayAttribute
{
    public function __construct(mixed $args)
    {
        parent::__construct('style', $args);
    }

    public function getKeyValue($key, $value): string
    {
        return $key . ': ' . $value . ';';
    }

    public static function styleAttribute(array $styles = null)
    {
        return new self($styles);
    }
}