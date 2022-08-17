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

use Zalt\HtmlUtil\Ra;

/**
 * Class for IFRAME element. Assumes first passed argument is the src attribute,
 * unless specified otherwise. Always specifies closing tag.
 *
 * @package    Zalt
 * @subpackage Html
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.5.2
 */
class IFrame extends HtmlElement
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
     * @param mixed $argArray Ra::args arguments
     */
    public function __construct($src, ...$argArray)
    {
        $args = Ra::args(func_get_args(), array('src' => '\\Zalt\\Html\\SrcArrayAttribute'));

        if (isset($args['src']) && (! $args['src'] instanceof \Zalt\Html\AttributeInterface)) {
            $args['src'] = new \Zalt\Html\SrcArrayAttribute($args['src']);
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
     * @param mixed $argArray Ra::args arguments
     */
    public static function iFrame($src, ...$argArray)
    {
        $args = Ra::args(func_get_args(), array('src' => '\\Zalt\\Html\\SrcArrayAttribute'));
        return new self($args);
    }
}
