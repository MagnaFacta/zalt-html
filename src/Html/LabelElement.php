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

use Zalt\Late\Late;
use Zalt\Late\LateInterface;

/**
 *
 * @package    Zalt
 * @subpackage Html
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since \Zalt version 1.0
 */
class LabelElement extends \Zalt\Html\HtmlElement
{
    /**
     * Declaring $class a public property, ensures the attribute
     * 'class' is never directly set in the $_attribs array, so
     * it can be set by the code later on.
     */
    public $class;

    public $optionalClass = 'optional';
    public $optionalPostfix;
    public $optionalPrefix;

    public $renderWithoutContent = false;

    public $requiredClass = 'required';
    public $requiredPostfix;
    public $requiredPrefix;

    private $_currentContent;

    protected $_onEmptyContent;

    public function getOptionalClass()
    {
        return $this->optionalClass;
    }

    public function getOptionalPostfix()
    {
        return $this->optionalPostfix;
    }

    public function getOptionalPrefix()
    {
        return $this->optionalPrefix;
    }

    public function getRequiredClass()
    {
        return $this->requiredClass;
    }

    public function getRequiredPostfix()
    {
        return $this->requiredPostfix;
    }

    public function getRequiredPrefix()
    {
        return $this->requiredPrefix;
    }

    public static function label($arg_array = array())
    {
        $args = func_get_args();
        return new self(__FUNCTION__, $args);
    }

    protected function renderContent()
    {
        if ($content = Html::getRenderer()->renderAny($this->_currentContent)) {
            return $content;

        } elseif ($this->_onEmptyContent) {
            return Html::getRenderer()->renderAny($this->_onEmptyContent);

        } else {
            return '&nbsp;';
        }
    }

    /**
     * Function to allow overloading  of tag rendering only
     *
     * Renders the element tag with it's content into a html string
     *
     * @return string Correctly encoded and escaped html output
     */
    protected function renderElement()
    {
        $this->_currentContent = array();

        // If the label was assigned an element late,
        // now is the time to get it's value.
        foreach ($this->_content as $key => $value) {
            if ($value instanceof LateInterface) {
                $value = Late::rise($value);
            }
            if ($value instanceof \Zend_Form_Element) {
                if ($value instanceof \Zend_Form_Element_Hidden) {
                    return null;
                }

                // Only a label when a label decorator exists, but we do not use that decorator
                $decorator = $value->getDecorator('Label');
                if ($decorator) {
                    if (false === $decorator->getOption('escape')) {
                        $label = Html::raw($value->getLabel());
                    } else {
                        $label = $value->getLabel();
                    }
                    $class = $this->class ? Html::getRenderer()->renderAny($this->class) . ' ' : '';
                    if ($value->isRequired()) {
                        $class .= $this->getRequiredClass();
                        $this->_currentContent[$key] = array($this->getRequiredPrefix(), $label, $this->getRequiredPostfix());
                    } else {
                        $class .= $this->getOptionalClass();
                        $this->_currentContent[$key] = array($this->getOptionalPrefix(), $label, $this->getOptionalPostfix());
                    }
                    parent::__set('class', $class); // Bypass existing property for drawing

                    if ($id = $value->getId()) {
                        parent::__set('for', $id); // Always overrule
                    } else {
                        parent::__unset('for');
                    }
                }
            } elseif ($value instanceof \Zend_Form_DisplayGroup) {
                return null;
            } else {
                $this->_currentContent[$key] = $value;
            }
        }

        return parent::renderElement();
    }

    public function setElement($element, $key = null)
    {
        $this[$key] = $element;

        return $this;
    }

    public function setOptionalClass($class)
    {
        $this->optionalClass = $class;
        return $this;
    }

    public function setOptionalPostfix($postfix)
    {
        $this->optionalPostfix = $postfix;
        return $this;
    }

    public function setOptionalPrefix($prefix)
    {
        $this->optionalPrefix = $prefix;
        return $this;
    }

    public function setRequiredClass($class)
    {
        $this->requiredClass = $class;
        return $this;
    }

    public function setRequiredPostfix($postfix)
    {
        $this->requiredPostfix = $postfix;
        return $this;
    }

    public function setRequiredPrefix($prefix)
    {
        $this->requiredPrefix = $prefix;
        return $this;
    }
}