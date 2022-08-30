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

use Zalt\Ra\Ra;

/**
 * Zend style form decorator the uses \Zalt\Html
 *
 * @package    Zalt
 * @subpackage Html
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.0
 */
class ElementDecorator extends \Zend_Form_Decorator_Abstract
{
    /**
     *
     * @var \Zalt\Html\HtmlInterface
     */
    protected $_html_element;

    /**
     * When existing prepends all error messages before the form elements.
     *
     * When a \Zalt\Html\HtmlElement the errors are appended to the element,
     * otherwise an UL is created
     *
     * @var mixed
     */
    protected $_prepend_errors;

    /**
     * Any content to be displayed before the visible elements
     *
     * @var mixed
     */
    protected $_prologue;

    /**
     * The element used to display the (visible) form elements.
     *
     * @return \Zalt\Html\HtmlInterface
     */
    public function getHtmlElement()
    {
        return $this->_html_element;
    }

    /**
     * Must the form prepend all error messages before the visible form elements?
     *
     * When a \Zalt\Html\HtmlElement the errors are appended to the element,
     * otherwise an UL is created
     *
     * @return mixed false, true or \Zalt\Html\HtmlElement
     */
    public function getPrependErrors()
    {
        return $this->_prepend_errors;
    }

    /**
     * Any content to be displayed before the visible elements
     *
     * @return mixed
     */
    public function getPrologue()
    {
        return $this->_prologue;
    }

    /**
     * Render the element
     *
     * @param  string $content Content to decorate
     * @return string
     */
    public function render($content)
    {
        if ((null === ($element = $this->getElement())) ||
            (null === ($view = $element->getView())) ||
            (null === ($htmlelement = $this->getHtmlElement()))) {
            return $content;
        }

        if ($prologue = $this->getPrologue()) {
            if ($prologue instanceof \Zalt\Lazy\RepeatableFormElements) {
                // Not every browser can handle empty divs (e.g. IE 6)
                if ($hidden = $prologue->getHidden()) {
                    $prologue = \Zalt\Html::create()->div($hidden);
                } else {
                    $prologue = null;
                }
            }
            if ($prologue instanceof \Zalt\Html\HtmlInterface) {
                $prologue = $prologue->render($view);
            } else {
                $prologue = \Zalt\Html::getRenderer()->renderAny($view, $prologue);
            }
        } else {
            $prologue = '';
        }
        if ($prependErrors = $this->getPrependErrors()) {
            $form = $this->getElement();
            if ($errors = $form->getMessages()) {
                $errors = Ra::flatten($errors);
                $errors = array_unique($errors);

                if ($prependErrors instanceof \Zalt\Html\ElementInterface) {
                    $html = $prependErrors;
                } else {
                    $html = \Zalt\Html::create('ul');
                }
                foreach ($errors as $error) {
                    $html->append($error);
                }

                $prologue .= $html->render($view);
            }
        }

        $result = $this->renderElement($htmlelement, $view);

        if (parent::APPEND == $this->getPlacement()) {
            return $prologue . $result . $content;
        } else {
            return $content . $prologue . $result;
        }
    }


    /**
     * Render the html element
     *
     * Override this method rather than render() as this
     * is saver and the default logic is handled.
     *
     * @param  string $content Content to decorate
     * @return string
     */
    public function renderElement(\Zalt\Html\HtmlInterface $htmlElement, \Zend_View $view)
    {
        return $htmlElement->render($view);
    }

    /**
     * Set the default
     *
     * @param \Zalt\Html\HtmlInterface $htmlElement
     * @return \Zalt\Html\ElementDecorator (continuation pattern)
     */
    public function setHtmlElement(\Zalt\Html\HtmlInterface $htmlElement)
    {
        $this->_html_element = $htmlElement;
        return $this;
    }

    /**
     * Set the form to prepends all error messages before the visible form elements.
     *
     * When a \Zalt\Html\HtmlElement the errors are appended to the element,
     * otherwise an UL is created
     *
     * @param mixed $prepend false, true or \Zalt\Html\HtmlElement
     * @return \Zalt\Html\ElementDecorator (continuation pattern)
     */
    public function setPrependErrors($prepend = true)
    {
        $this->_prepend_errors = $prepend;
        return $this;
    }

    /**
     * Hidden elements should be displayed at the start of the form.
     *
     * If the prologue is a \Zalt\Lazy\RepeatableFormElements repeater then all the hidden elements are
     * displayed in a div at the start of the form.
     *
     * @param mixed $prologue E.g. a repeater or a html element
     * @return \Zalt\Html\ElementDecorator
     */
    public function setPrologue($prologue)
    {
        $this->_prologue = $prologue;
        return $this;
    }
}

