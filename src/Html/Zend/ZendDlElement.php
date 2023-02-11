<?php

declare(strict_types=1);

/**
 *
 * @package    Zalt
 * @subpackage Html\Zend
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 */

namespace Zalt\Html\Zend;

use Zalt\Html\DlElement;
use Zalt\Late\Late;
use Zalt\Late\RepeatableFormElements;

/**
 *
 * @package    Zalt
 * @subpackage Html\Zend
 * @since      Class available since version 1.0
 */
class ZendDlElement extends \Zalt\Html\DlElement implements ZendFormLayout
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
     * Apply this element to the form as the output decorator.
     *
     * @param \Zend_Form $form
     * @param mixed $width The style.width content for the labels
     * @param array $order The display order of the elements
     * @return DlElement
     */
    public function setAsFormLayout(\Zend_Form $form, $width = null,
                                    array $order = array('element', 'errors', 'description'))
    {
        // Make a Late repeater for the form elements and set it as the element repeater
        $formrep = new RepeatableFormElements($form);
        $formrep->setSplitHidden(true); // These are treated separately
        $this->setRepeater($formrep);

        if (null === $width) {
            $attr = array();
        } else {
            $attr['style'] = array('width' => $width);
        }
        $this->dt()->label($formrep->element, $attr);  // Set label dt with optional width
        $dd = $this->dd();
        foreach ($order as $renderer) {
            $dd[] = $formrep->$renderer;
        }

        // Set this element as the form decorator
        $decorator = new ZendElementDecorator();
        $decorator->setHtmlElement($this);
        $decorator->setPrologue($formrep);  // Renders hidden elements before this element
        $form->setDecorators(array($decorator, 'AutoFocus', 'Form'));

        return $this;
    }

    /**
     * Apply this element to the form as the output decorator with automatically calculated widths.
     *
     * @param \Zend_Form $form
     * @param float $factor To multiply the widest nummers of letters in the labels with to calculate the width in
     * em at drawing time
     * @param array $order The display order of the elements
     * @return DlElement
     */
    public function setAutoWidthFormLayout(\Zend_Form $form, $factor = 1,
                                           array $order = array('element', 'errors', 'description'))
    {
        // Late call becase the form might not be completed at this stage.
        return $this->setAsFormLayout(
            $form,
            Late::call(array(self::class, 'calculateAutoWidthFormLayout'), $form, $factor),
            $order
        );
    }

}