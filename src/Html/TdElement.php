<?php

/**
 *
 *
 * @package    Zalt
 * @subpackage Html
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2013 Erasmus MC
 * @license    New BSD License
 */

namespace Zalt\Html;

/**
 * Td and Th elements should always render a closing tag
 *
 * @package    Zalt
 * @subpackage Html
 * @copyright  Copyright (c) 2013 Erasmus MC
 * @license    New BSD License
 * @since      Class available since \Zalt version 1.3
 */
class TdElement extends HtmlElement
{
    /**
     * When repeating content using $_repeater you may want to output the content only when it has
     * changed.
     *
     * @see $_repeater
     *
     * @var boolean Do not output if the output is identical to the last time the element was rendered.
     */
    protected $_onlyWhenChanged = false;


    /**
     * @see $_onlyWhenChanged
     *
     * @var string Cache for last output for comparison
     */
    protected $_onlyWhenChangedValueStore = null;

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
     * Static helper function for creation, used by @see \Zalt\Html\Creator.
     *
     * @param mixed $args Optional Ra::args processed settings
     * @return TdElement
     */
    public static function createTh(...$args)
    {
        return new self('th', $args);
    }

    /**
     * Static helper function for creation, used by @see \Zalt\Html\Creator.
     *
     * @param mixed $args Optional Ra::args processed settings
     * @return TdElement
     */
    public static function createTd(...$args)
    {
        return new self('td', $args);
    }

    /**
     * When repeating content using $_repeater you may want to output the content only when it has
     * changed.
     *
     * @return boolean
     */
    public function getOnlyWhenChanged()
    {
        return $this->_onlyWhenChanged;
    }

    /**
     * Function to allow overloading of content rendering only
     *
     * @return string Correctly encoded and escaped html output
     */
    protected function renderContent()
    {
        $result = parent::renderContent();

        if ($this->_onlyWhenChanged) {
            if ($result == $this->_onlyWhenChangedValueStore) {
                return null;
            }
            $this->_onlyWhenChangedValueStore = $result;
        }

        return $result;
    }

    /**
     * When repeating content using $_repeater you may want to output the content only when it has
     * changed.
     *
     * @see $_repeater
     *
     * @return \Zalt\Html\HtmlElement (continuation pattern)
     */
    public function setOnlyWhenChanged($value)
    {
        $this->_onlyWhenChanged = $value;
        return $this;
    }
}
