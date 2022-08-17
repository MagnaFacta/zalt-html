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
use Zalt\Late\Late;
use Zalt\Late\RepeatableFormElements;

/**
 * Interface extensions that allows HtmlElements to define how to display
 * form elements.
 *
 * @package    Zalt
 * @subpackage Html
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.5.3
 */
class PFormElement extends HtmlElement implements FormLayout
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

    public function __construct(...$args)
    {
        parent::__construct('p', $args);
    }

    /**
     * Static helper function for creation, used by @see \Zalt\Html\Creator.
     *
     * @param mixed $args Optional Ra::args processed settings
     * @return PFormElement
     */
    public static function pForm(...$args)
    {
        return new self($args);
    }

    /**
     * Apply this element to the form as the output decorator.
     *
     * @param \Zend_Form $form
     * @param mixed $width The style.width content for the labels
     * @param array $order The display order of the elements
     * @param string $errorClass Class name to display all errors in
     * @return DlElement
     */
    public function setAsFormLayout(\Zend_Form $form, $width = null,
            $order = array('label', 'element', 'description'), $errorClass = 'errors')
    {
        $this->_repeatTags = true;
        $prependErrors     = $errorClass;

        // Make a late repeater for the form elements and set it as the element repeater
        $formrep = new RepeatableFormElements($form);
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
        $decorator = new ElementDecorator();
        $decorator->setHtmlElement($this);
        $decorator->setPrologue($formrep);  // Renders hidden elements before this element
        if ($prependErrors) {
            $decorator->setPrependErrors(ListElement::ul(
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
     * @return PFormElement
     */
    public function setAutoWidthFormLayout(\Zend_Form $form, $factor = 1,
            array $order = array('label', 'element', 'description'))
    {
        // Late call becase the form might not be completed at this stage.
        return $this->setAsFormLayout(
                $form,
                Lazy::call([DlElement::class, 'calculateAutoWidthFormLayout'], $form, $factor),
                $order
                );
    }
}