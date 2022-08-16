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
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 */
class StyleArrayAttribute extends \MUtil\Html\ArrayAttribute
{
    public function __construct($arg_array = null)
    {
        $args = func_get_args();
        parent::__construct('style', $args);
    }

    public function getKeyValue($key, $value)
    {
        return $key . ': ' . $value . ';';
    }

    public static function styleAttribute(array $styles = null)
    {
        return new self($styles);
    }
}