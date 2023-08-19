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
 * ListElement just inherits from HtmlElement but sets some
 * variables for automatic (x)html correct behaviour.
 *
 * @package    Zalt
 * @subpackage Html
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since \Zalt version 1.0
 */
class ListElement extends \Zalt\Html\HtmlElement
{
    /**
     * 'li' is the only allowed child for all list elements.
     *
     * @var string|array A string or array of string values of the allowed element tags.
     */
    protected $_allowedChildTags = 'li';

    /**
     * Always end with a new line. Makes the html code better readable
     *
     * @var string Content added after the element.
     */
    protected $_appendString = "\n";

    /**
     * 'li' is still the only allowed element
     *
     * @var string The tagname of the element that should be created for content not having an $_allowedChildTags.
     */
    protected $_defaultChildTag = 'li';

    /**
     * Always start with a new line. Makes the html code better readable
     *
     * @var string Content added after the element.
     */
    protected $_prependString = "\n";

    /**
     * When empty a table element should not be output at rendering time as
     * a stand-alone <ol/> or <ul/> tag makes no sense.
     *
     * @see $_repeater
     *
     * @var boolean The element is rendered even without content when true.
     */
    public $renderWithoutContent = false;

    public function appendList(array $list)
    {
        foreach ($list as $item) {
            $this->append($item);
        }
    }

    /**
     * Static helper function for creation, used by @see \Zalt\Html\Creator.
     *
     * @param mixed $args Optional Ra::args processed settings
     * @return \Zalt\Html\ListElement (with dir tagName)
     */
    public static function dir(...$args)
    {
        return new self(__FUNCTION__, $args);
    }

    /**
     * Static helper function for creation, used by @see \Zalt\Html\Creator.
     *
     * @param mixed $args Optional Ra::args processed settings
     * @return ListElement (with menu tagName)
     */
    public static function menu(...$args)
    {
        return new self(__FUNCTION__, $args);
    }

    /**
     * Static helper function for creation, used by @see \Zalt\Html\Creator.
     *
     * @param mixed $args Optional Ra::args processed settings
     * @return ListElement (with ol tagName)
     */
    public static function ol(...$args)
    {
        return new self(__FUNCTION__, $args);
    }

    /**
     * Static helper function for creation, used by @see \Zalt\Html\Creator.
     *
     * @param mixed $args Optional Ra::args processed settings
     * @return ListElement (with ul tagName)
     */
    public static function ul(...$args)
    {
        return new self(__FUNCTION__, $args);
    }
}