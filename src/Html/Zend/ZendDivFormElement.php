<?php

/**
 *
 * @package    Zalt
 * @subpackage Html
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2014 Erasmus MC
 * @license    New BSD License
 */

namespace Zalt\Html\Zend;

use Zalt\Html\AttributeInterface;
use Zalt\Html\HtmlElement;
use Zalt\Html\ListElement;
use Zalt\Html\StyleArrayAttribute;
use Zalt\Late\Late;
use Zalt\Late\RepeatableFormElements;
use Zalt\Ra\Ra;

/**
 * A Div displayer using bootstrap element classes
 *
 * @package    Zalt
 * @subpackage Html
 * @copyright  Copyright (c) 2014 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.6.4
 */
class ZendDivFormElement extends \Zalt\Html\HtmlElement implements ZendFormLayout
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
     * @var bool The element is rendered even without content when true.
     */
    public $renderWithoutContent = false;

    public function __construct(...$args)
    {
        $args = Ra::args($args);

        parent::__construct('div', array('class' => 'form-group'), $args);
    }

    protected function div(array $attr)
    {
        $div = new HtmlElement('div', $attr);

        $this->append($div);

        return $div;
    }

    /**
     * Static helper function for creation, used by @param mixed $args Optional args processed settings
     *
     * @see \Zalt\Html\Creator.
     *
     */
    public static function divForm(...$args)
    {
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

    protected function label(mixed $element, array $attr)
    {
        $label = ZendLabelElement::label($element, $attr);

        $this->append($label);

        return $label;
    }

    public function render()
    {
        return parent::render();
    }
    

    /**
     * Apply this element to the form as the output decorator.
     *
     * @param \Zend_Form $form
     * @param mixed $width The style.width content for the labels
     * @param array $order The display order of the elements
     * @param string $errorClass Class name to display all errors in
     * @return ZendDivFormElement
     */
    public function setAsFormLayout(\Zend_Form $form, $width = null, $order = array('label', 'element', 'errors', 'description'), $errorClass = 'errors')
    {
        $this->_repeatTags = true;
        $prependErrors     = $errorClass;

        // Make a Late repeater for the form elements and set it as the element repeater
        $formrep = new RepeatableFormElements($form);
        $formrep->setSplitHidden(true); // These are treated separately
        if ($this->getFlattenSubs()) {
            $formrep->setFlattenSubs(true); // And flatten the output
        }
        $this->setRepeater($formrep);

        if (null === $width) {
            $attr = [];
        } else {
            $attr['style'] = new StyleArrayAttribute(['display' => 'inline-block', 'width' => $width]);
        }

        $inputGroup = null;

        // Place the chosen renderers
        foreach ($order as $renderer) {
            switch ($renderer) {
                case 'label':
                    $this->label($formrep->element, $attr); // Set label with optional width
                    break;

                case 'error':  // Old versions deprecatd
                case 'errors':
                    $prependErrors = false;
                    // @phpstan-ignore property.notFound
                    $this->append($formrep->errors);
                    break;

                case 'description':
                    // @phpstan-ignore property.notFound
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
        $decorator = new ZendElementDecorator();
        $decorator->setHtmlElement($this);
        $decorator->setPrologue($formrep);  // Renders hidden elements before this element
        if ($prependErrors) {
            $decorator->setPrependErrors(ListElement::ul(
                    array('class' => $errorClass, 'style' => array('margin-left' => $width))
                    ));
        }
        $form->setDecorators([$decorator, 'Form']);

        return $this;
    }

    /**
     * Apply this element to the form as the output decorator with automatically calculated widths.
     *
     * @param \Zend_Form $form
     * @param float $factor To multiply the widest nummers of letters in the labels with to calculate the width in em
     * at drawing time
     * @param array $order The display order of the elements
     * @return \Zalt\Html\Zend\ZendDivFormElement
     */
    public function setAutoWidthFormLayout(\Zend_Form $form, $factor = 1,
            array $order = array('label', 'element', 'errors', 'description'))
    {
        // Late call becase the form might not be completed at this stage.
        return $this->setAsFormLayout(
                $form,
                Late::call(array('\\Zalt\\Html\\DlElement', 'calculateAutoWidthFormLayout'), $form, $factor),
                $order
                );
    }

    /**
     *
     * @param boolean $flatten Should subforms be flattened as tables
     * @return ZendDivFormElement
     */
    public function setFlattenSubs($flatten = true)
    {
        $this->_flattenSubs = $flatten;
        return $this;
    }

}