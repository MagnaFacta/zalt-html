<?php

/**
 *
 *
 * @package    MUtil
 * @subpackage Html
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2014 Erasmus MC
 * @license    New BSD License
 */

namespace MUtil\Html;

/**
 * A Div displayer using bootstrap element classes
 *
 * @package    MUtil
 * @subpackage Html
 * @copyright  Copyright (c) 2014 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.6.4
 */
class DivFormElement extends \MUtil\Html\HtmlElement implements \MUtil\Html\FormLayout
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
     *
     * @var boolean Should subforms be flattened
     */
    protected $_flattenSubs = true;

    /**
     * Should have content
     *
     * @var boolean The element is rendered even without content when true.
     */
    public $renderWithoutContent = false;

    public function __construct($arg_array = null)
    {
        $args = \MUtil\Ra::args(func_get_args());

        parent::__construct('div', array('class' => 'form-group'), $args);
    }

    /**
     * Static helper function for creation, used by @see \MUtil\Html\Creator.
     *
     * @param mixed $arg_array Optional \MUtil\Ra::args processed settings
     * @return \MUtil\Html\PFormElement
     */
    public static function divForm($arg_array = null)
    {
        $args = func_get_args();
        return new self($args);
    }

    /**
     *
     * @return boolean $flatten Should subforms be flattened as tables
     */
    public function getFlattenSubs()
    {
        return $this->_flattenSubs;
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
    public function setAsFormLayout(\Zend_Form $form, $width = null, $order = array('label', 'element', 'errors', 'description'), $errorClass = 'errors')
    {
        $this->_repeatTags = true;
        $prependErrors     = $errorClass;

        // Make a Lazy repeater for the form elements and set it as the element repeater
        $formrep = new \MUtil\Lazy\RepeatableFormElements($form);
        $formrep->setSplitHidden(true); // These are treated separately
        if ($this->getFlattenSubs()) {
            $formrep->setFlattenSubs(true); // And flatten the output
        }
        $this->setRepeater($formrep);

        if (null === $width) {
            $attr = array();
        } else {
            $attr['style'] = array('display' => 'inline-block', 'width' => $width);
        }

        $inputGroup = null;

        // Place the choosen renderers
        foreach ($order as $renderer) {
            switch ($renderer) {
                case 'label':
                    $this->label($formrep->element, $attr); // Set label with optional width
                    break;

                case 'error':  // Old versions deprecatd
                case 'errors':
                    $prependErrors = false;
                    $this->append($formrep->errors);
                    break;

                case 'description':
                    $this->append($formrep->description);
                    break;

                default:
                    if (! $inputGroup) {
                        $inputGroup = $this->div(array('class' => 'input-group'));
                    }
                    $inputGroup->append($formrep->$renderer);
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
     * @param float $factor To multiply the widest nummers of letters in the labels with to calculate the width in em
     * at drawing time
     * @param array $order The display order of the elements
     * @return \MUtil\Html\PFormElement
     */
    public function setAutoWidthFormLayout(\Zend_Form $form, $factor = 1,
            array $order = array('label', 'element', 'errors', 'description'))
    {
        // Lazy call becase the form might not be completed at this stage.
        return $this->setAsFormLayout(
                $form,
                \MUtil\Lazy::call(array('\\MUtil\\Html\\DlElement', 'calculateAutoWidthFormLayout'), $form, $factor),
                $order
                );
    }

    /**
     *
     * @param boolean $flatten Should subforms be flattened as tables
     * @return \MUtil\Html\DivFormElement
     */
    public function setFlattenSubs($flatten = true)
    {
        $this->_flattenSubs = $flatten;
        return $this;
    }

}