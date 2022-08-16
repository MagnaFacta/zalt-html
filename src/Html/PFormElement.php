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
 * Interface extensions that allows HtmlElements to define how to display
 * form elements.
 *
 * @package    MUtil
 * @subpackage Html
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.5.3
 */
class PFormElement extends \MUtil\Html\HtmlElement implements \MUtil\Html\FormLayout
{
    /**
     * Can process form elements
     *
     * @var array
     */
    protected $_specialTypes = array(
        'Zend_Form' => 'setAsFormLayout',
        );

    /**
     * Should have content
     *
     * @var boolean The element is rendered even without content when true.
     */
    public $renderWithoutContent = false;

    public function __construct($arg_array = null)
    {
        $args = \MUtil\Ra::args(func_get_args());

        parent::__construct('p', $args);
    }

    /**
     * Static helper function for creation, used by @see \MUtil\Html\Creator.
     *
     * @param mixed $arg_array Optional \MUtil\Ra::args processed settings
     * @return \MUtil\Html\PFormElement
     */
    public static function pForm($arg_array = null)
    {
        $args = func_get_args();
        return new self($args);
    }

    /**
     * Apply this element to the form as the output decorator.
     *
     * @param \Zend_Form $form
     * @param mixed $width The style.width content for the labels
     * @param array $order The display order of the elements
     * @param string $errorClass Class name to display all errors in
     * @return \MUtil\Html\DlElement
     */
    public function setAsFormLayout(\Zend_Form $form, $width = null,
            $order = array('label', 'element', 'description'), $errorClass = 'errors')
    {
        $this->_repeatTags = true;
        $prependErrors     = $errorClass;

        // Make a Lazy repeater for the form elements and set it as the element repeater
        $formrep = new \MUtil\Lazy\RepeatableFormElements($form);
        $formrep->setSplitHidden(true); // These are treated separately
        $this->setRepeater($formrep);

        if (null === $width) {
            $attr = array();
        } else {
            $attr['style'] = array('display' => 'inline-block', 'width' => $width);
        }

        // Place the choosen renderers
        foreach ($order as $renderer) {
            switch ($renderer) {
                case 'label':
                    $this->label($formrep->element, $attr); // Set label with optional width
                    break;
                case 'error':
                    $prependErrors = false;
                    // Intentional fall through

                default:
                    $this->append($formrep->$renderer);
            }
        }

        // Set this element as the form decorator
        $decorator = new \MUtil\Html\ElementDecorator();
        $decorator->setHtmlElement($this);
        $decorator->setPrologue($formrep);  // Renders hidden elements before this element
        if ($prependErrors) {
            $decorator->setPrependErrors(\MUtil\Html\ListElement::ul(
                    array('class' => $errorClass, 'style' => array('margin-left' => $width))
                    ));
        }
        $form->setDecorators(array($decorator, 'AutoFocus', 'Form'));

        return $this;
    }

    /**
     * Apply this element to the form as the output decorator with automatically calculated widths.
     *
     * @param \Zend_Form $form
     * @param float $factor To multiply the widest nummers of letters in the labels with to calculate the width in em at drawing time
     * @param array $order The display order of the elements
     * @return \MUtil\Html\PFormElement
     */
    public function setAutoWidthFormLayout(\Zend_Form $form, $factor = 1,
            array $order = array('label', 'element', 'description'))
    {
        // Lazy call becase the form might not be completed at this stage.
        return $this->setAsFormLayout(
                $form,
                \MUtil\Lazy::call(array('\\MUtil\\Html\\DlElement', 'calculateAutoWidthFormLayout'), $form, $factor),
                $order
                );
    }
}