<?php

/**
 *
 * @package    Zalt
 * @subpackage Html
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2012 Erasmus MC
 * @license    New BSD License
 */

namespace Zalt\Html;

/**
 *
 *
 * @package    Zalt
 * @subpackage Html
 * @copyright  Copyright (c) 2012 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.0
 */
class HnElement extends HtmlElement
{
    /**
     * Most elements must be rendered even when empty, others should - according to the
     * xhtml specifications - only be rendered when the element contains some content.
     *
     * $renderWithoutContent controls this rendering. By default an element tag is output
     * but when false the tag will only be present if there is some content in it.
     *
     * @var bool The element is rendered even without content when true.
     */
    public $renderWithoutContent = false;

    /**
     * Static helper function for creation, used by @see \Zalt\Html\Creator.
     *
     * @param mixed $args Optional Ra::args processed settings
     * @return HnElement
     */
    public static function h1(...$args)
    {
        return new self(__FUNCTION__, $args);
    }

    /**
     * Static helper function for creation, used by @see \Zalt\Html\Creator.
     *
     * @param mixed $args Optional Ra::args processed settings
     * @return HnElement
     */
    public static function h2(...$args)
    {
        return new self(__FUNCTION__, $args);
    }

    /**
     * Static helper function for creation, used by @see \Zalt\Html\Creator.
     *
     * @param mixed $args Optional Ra::args processed settings
     * @return HnElement
     */
    public static function h3(...$args)
    {
        return new self(__FUNCTION__, $args);
    }

    /**
     * Static helper function for creation, used by @see \Zalt\Html\Creator.
     *
     * @param mixed $args Optional Ra::args processed settings
     * @return HnElement
     */
    public static function h4(...$args)
    {
        return new self(__FUNCTION__, $args);
    }

    /**
     * Static helper function for creation, used by @see \Zalt\Html\Creator.
     *
     * @param mixed $args Optional Ra::args processed settings
     * @return HnElement
     */
    public static function h5(...$args)
    {
        return new self(__FUNCTION__, $args);
    }

    /**
     * Static helper function for creation, used by @see \Zalt\Html\Creator.
     *
     * @param mixed $args Optional Ra::args processed settings
     * @return HnElement
     */
    public static function h6(...$args)
    {
        return new self(__FUNCTION__, $args);
    }
}
