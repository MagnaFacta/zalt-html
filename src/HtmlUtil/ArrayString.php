<?php

/**
 *
 *
 * @package    Zalt
 * @subpackage HtmlUtil
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 */

namespace Zalt\HtmlUtil;

use ArrayObject;

/**
 * Simple extension of ArrayObject allowing casting to string with (optionally) a specified glue.
 *
 * @package    Zalt
 * @subpackage HtmlUtil
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since Zalt-html version 1.0
 */
class ArrayString extends ArrayObject
{
    /**
     * The glue to insert between the array pieces when casting to string.
     *
     * @var string
     */
    private $glue = '';

    /**
     *
     * @return string
     */
    public function __toString()
    {
        return implode($this->getGlue(), $this->getArrayCopy());
    }

    /**
     *
     * @return string
     */
    public function getGlue()
    {
        return $this->glue;
    }

    /**
     *
     * @param string $glue The glue to use
     * @return ArrayString
     */
    public function setGlue($glue)
    {
        $this->glue = $glue;

        return $this;
    }
}