<?php

/**
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
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since \MUtil version 1.0
 */
class LabelElement extends \MUtil\Html\HtmlElement
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

    /**
     * Function to allow overloading  of tag rendering only
     *
     * Renders the element tag with it's content into a html string
     *
     * The $view is used to correctly encode and escape the output
     *
     * @param \Zend_View_Abstract $view
     * @return string Correctly encoded and escaped html output
     */
    protected function renderElement(\Zend_View_Abstract $view)
    {
        $this->_currentContent = array();

        // If the label was assigned an element lazy,
        // now is the time to get it's value.
        foreach ($this->_content as $key => $value) {
            if ($value instanceof \MUtil\Lazy\LazyInterface) {
                $value = \MUtil\Lazy::rise($value);
            }
            if ($value instanceof \Zend_Form_Element) {
                if ($value instanceof \Zend_Form_Element_Hidden) {
                    return null;
                }

                // Only a label when a label decorator exists, but we do not use that decorator
                $decorator = $value->getDecorator('Label');
                if ($decorator) {
                    if (false === $decorator->getOption('escape')) {
                        $label = \MUtil\Html::raw($value->getLabel());
                    } else {
                        $label = $value->getLabel();
                    }
                    $class = $this->class ? \MUtil\Html::renderAny($view, $this->class) . ' ' : '';
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

        return parent::renderElement($view);
    }

    protected function renderContent(\Zend_View_Abstract $view)
    {
        if ($content = \MUtil\Html::getRenderer()->renderAny($view, $this->_currentContent)) {
            return $content;

        } elseif ($this->_onEmptyContent) {
            return \MUtil\Html::getRenderer()->renderAny($view, $this->_onEmptyContent);

        } else {
            return '&nbsp;';
        }
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