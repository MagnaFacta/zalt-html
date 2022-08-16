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
 * Html DL element with functions for applying it to a form.
 *
 * @package    MUtil
 * @subpackage Html
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.0
 */
class DlElement extends \MUtil\Html\HtmlElement implements \MUtil\Html\FormLayout
{
    /**
     * Only dt and dd elements are allowed as content.
     *
     * @var string|array A string or array of string values of the allowed element tags.
     */
    protected $_allowedChildTags = array('dt', 'dd');

    /**
     * Put a Dl element on it's own line
     *
     * @var string Content added after the element.
     */
    protected $_appendString = "\n";

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


    /**
     * Make a DL element
     *
     * Any parameters are added as either content, attributes or handled
     * as special types, if defined as such for this element.
     *
     * @param mixed $arg_array \MUtil\Ra::args arguments
     */
    public function __construct($arg_array = null)
    {
        $args = \MUtil\Ra::args(func_get_args());

        parent::__construct('dl', $args);
    }

    public function addItem($dt = null, $dd = null)
    {
        $ds = $this->addItemArray($dt, $dd);

        if (count($ds) > 1) {
            // Return all objects in a wrapper object
            // that makes sure they are all treated
            // the same way.
            return new \MUtil\MultiWrapper($ds);
        }

        // Return first object only
        return reset($ds);
    }

    public function addItemArray($dt = null, $dd = null)
    {
        $ds = array();

        if ($dt) {
            if (self::alreadyIsA($dt, $this->_allowedChildTags)) {
                $this[] = $dt;
            } else {
                $dt = $this->dt($dt);
            }
            $ds['dt'] = $dt;
        }
        if ($dd) {
            if (self::alreadyIsA($dd, $this->_allowedChildTags)) {
                $this[] = $dd;
            } else {
                $dd = $this->dd($dd);
            }
            $ds['dd'] = $dd;
        }

        return $ds;
    }

    /**
     * Helper function for creating automatically calculated widths.
     *
     * @staticvar \Zend_Form $last_form Prevent recalculation. This function is called for every label
     * @staticvar string $last_factor Last result
     * @param \Zend_Form $form The form to calculate the widest label for
     * @param float $factor The factor to multiple the number of characters with for to get the number of em's
     * @return string E.g.: '10em'
     */
    public static function calculateAutoWidthFormLayout(\Zend_Form $form, $factor = 1)
    {
        static $last_form;
        static $last_factor;

        // No need to repeat the calculations for every element,
        // which would otherwise happen.
        if ($form === $last_form) {
            return $last_factor;
        }

        $maxwidth = 0;

        foreach ($form->getElements() as $element) {
            if ($decorator = $element->getDecorator('Label')) {
                $decorator->setElement($element);
                $len = strlen(strip_tags($decorator->getLabel()));

                if ($len > $maxwidth) {
                    $maxwidth = $len;
                }
            }
        }

        $last_form = $form;
        if ($maxwidth) {
            $last_factor = intval($factor * $maxwidth) . 'em';
        } else {
            // We need to return some usable value.
            $last_factor = 'auto';
        }

        return $last_factor;
    }


    /**
     * Static helper function for creation, used by @see \MUtil\Html\Creator.
     *
     * @param mixed $arg_array Optional \MUtil\Ra::args processed settings
     * @return \MUtil\Html\DlElement
     */
    public static function dl($arg_array = null)
    {
        $args = func_get_args();
        return new self($args);
    }

    public function dtDd($dt = null, $dd = null)
    {
        return $this->addItem($dt, $dd);
    }

    /**
     * Apply this element to the form as the output decorator.
     *
     * @param \Zend_Form $form
     * @param mixed $width The style.width content for the labels
     * @param array $order The display order of the elements
     * @return \MUtil\Html\DlElement
     */
    public function setAsFormLayout(\Zend_Form $form, $width = null,
            array $order = array('element', 'errors', 'description'))
    {
        // Make a Lazy repeater for the form elements and set it as the element repeater
        $formrep = new \MUtil\Lazy\RepeatableFormElements($form);
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

        // $this->dd($formrep->element, ' ', $formrep->errors, ' ', $formrep->description);
        // $this->dd($formrep->element, $formrep->description, $formrep->errors);

        // Set this element as the form decorator
        $decorator = new \MUtil\Html\ElementDecorator();
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
     * @return \MUtil\Html\DlElement
     */
    public function setAutoWidthFormLayout(\Zend_Form $form, $factor = 1,
            array $order = array('element', 'errors', 'description'))
    {
        // Lazy call becase the form might not be completed at this stage.
        return $this->setAsFormLayout(
                $form,
                \MUtil\Lazy::call(array(__CLASS__, 'calculateAutoWidthFormLayout'), $form, $factor),
                $order
                );
    }
}