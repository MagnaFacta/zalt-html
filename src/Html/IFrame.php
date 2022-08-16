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
 * Class for IFRAME element. Assumes first passed argument is the src attribute,
 * unless specified otherwise. Always specifies closing tag.
 *
 * @package    MUtil
 * @subpackage Html
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.5.2
 */
class IFrame extends \MUtil\Html\HtmlElement
{
    /**
     * Some elements, e.g. iframe elements, must always be rendered with a closing
     * tag because otherwise some poor browsers get confused.
     *
     * Overrules $renderWithoutContent: the element is always rendered when
     * $renderClosingTag is true.
     *
     * @see $renderWithoutContent
     *
     * @var boolean The element is always rendered with a closing tag.
     */
    public $renderClosingTag = true;

    /**
     * An iframe element.
     *
     * Any extra parameters are added as either content, attributes or handled
     * as special types, if defined as such for this element.
     *
     * @param mixed $src We assume the first element is src, unless a later element is explicitly specified as such
     * @param mixed $arg_array \MUtil\Ra::args arguments
     */
    public function __construct($src, $arg_array = null)
    {
        $args = \MUtil\Ra::args(func_get_args(), array('src' => '\\MUtil\\Html\\SrcArrayAttribute'));

        if (isset($args['src']) && (! $args['src'] instanceof \MUtil\Html\AttributeInterface)) {
            $args['src'] = new \MUtil\Html\SrcArrayAttribute($args['src']);
        }

        parent::__construct('iframe', $args);
    }

    /**
     * Static helper function to create an iframe element.
     *
     * Any extra parameters are added as either content, attributes or handled
     * as special types, if defined as such for this element.
     *
     * @param mixed $src We assume the first element is src, unless a later element is explicitly specified as such
     * @param mixed $arg_array \MUtil\Ra::args arguments
     */
    public static function iFrame($src, $arg_array = null)
    {
        $args = \MUtil\Ra::args(func_get_args(), array('src' => '\\MUtil\\Html\\SrcArrayAttribute'));
        return new self($args);
    }
}
