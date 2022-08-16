<?php


/**
 *
 * @package    Zalt
 * @subpackage Late
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 */

namespace Zalt\Late;

use Iterator;
use Zalt\Html\Html;
use Zalt\Html\Raw;

/**
 * Repeats all elements in a form, so a form can be used as source for e.g. an html element.
 *
 * Allows splitting of all hidden fields and flattening the form.
 *
 * Splitting the hidden fields to a separate repeater makes sure they don't mess up
 * your layout by appearing between fields.
 *
 * Flattening the form enables you treat nested forms as if they are part of
 * the main form.
 *
 * @package    Zalt
 * @subpackage Html
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.0
 */
class RepeatableFormElements extends Repeatable
{
    /**
     * Enable access to the elements in this repeater using $this->element
     *
     * @var LateAbstract
     */
    public $element;

    /**
     * Flatten all sub forms into the main form
     *
     * @var boolean
     */
    public $flattenSubs = false;

    /**
     * Output the hidden fields to a separate location
     *
     * @var boolean
     */
    public $splitHidden = false;

    /**
     * Enable access to the elements in this repeater using $this->element
     *
     * @var LateAbstract
     */
    public $label;

    /**
     * Storage of the hidden elements
     *
     * @var array
     */
    private $_hidden_elements;

    /**
     * Construct the element repeater
     *
     * @param \Zend_Form $form
     */
    public function __construct(\Zend_Form $form)
    {
        parent::__construct($form);

        // Enable access to the elements in this repeater using:
        // $this->element and $this->label.
        //
        // The other access method is: $this->{name of element renderer}
        $this->element =  new LateCall(array($this, '__current'));
        $this->label   = Html::create('label', $this->element);
    }

    /**
     * Get a Late call to the current element's decorator output or property
     * output if the decorator does not exist
     *
     * @param string $name
     * @return LateCall
     */
    public function __get($name)
    {
        // Form elements have few public properties, so usually we use this as a
        // shortcut for a decorator function, however, if the property exists
        // (and no Decorator with the same name exists) the property value will
        // be returned.
        return Late::call(array($this, 'getDecorator'), ucfirst($name));
    }

    /**
     * Return the core data in the Repeatable in one go
     *
     * @return Iterator|array
     */
    public function __getRepeatable()
    {
        $elements = iterator_to_array(parent::__getRepeatable());

        if ($this->flattenSubs) {
            $newElements = array();
            foreach ($elements as $element) {
                $this->_flattenElement($element, $newElements);
            }
            $elements = $newElements;
        }

        if ($this->splitHidden) {
            $filteredElements = array();
            $this->_hidden_elements = array();

            foreach ($elements as $element) {
                if (($element instanceof \Zend_Form_Element_Hidden) || ($element instanceof \Zend_Form_Element_Hash)) {
                    $this->_hidden_elements[] = $element;
                } else {
                    $filteredElements[] = $element;
                }
            }

            return $filteredElements;

        } else {
            $this->_hidden_elements = array();
            return $elements;
        }
    }

    /**
     * Flatten element depending on it's type
     *
     * @param mixed $element
     * @param array $newElements
     */
    private function _flattenElement($element, array &$newElements)
    {
        if ($element instanceof \Zend_Form) {
            $this->_flattenForm($element, $newElements);

        } elseif ($element instanceof \MUtil\Form\Element\SubFocusInterface) {
            foreach ($element->getSubFocusElements() as $sub) {
                $this->_flattenElement($sub, $newElements);
            }

        } else {
            $newElements[] = $element;
        }
    }

    /**
     * Flatten al elements in the form
     *
     * @param \Zend_Form $form
     * @param array $newElements
     */
    private function _flattenForm(\Zend_Form $form, array &$newElements)
    {
        foreach ($form as $id => $element) {
            $this->_flattenElement($element, $newElements);
        }
    }

    /**
     * Get the current element's decorator output or property output if the decorator does not exist
     *
     * @param string $name
     * @return Raw|null
     */
    public function getDecorator($name)
    {
        if ($current = $this->__current()) {
            if ($decorator = $current->getDecorator($name)) {
                $decorator->setElement($current);
                return new Raw($decorator->render(''));
            }

            if (property_exists($current, $name)) {
                return $current->$name;
            }
        }

        return null;
    }

    /**
     * Are the sub forms split off?
     *
     * @return boolean
     */
    public function getFlattenSubs()
    {
        return $this->flattenSubs;
    }

    /**
     * An array containing all the hidden elements
     *
     * @return array
     */
    public function getHidden()
    {
        if ($this->splitHidden) {
            if (! is_array($this->_hidden_elements)) {
                $this->__getRepeatable();
            }

            return $this->_hidden_elements;
        }

        return [];
    }

    /**
     * Are the hidden fields split off?
     *
     * @return bool
     */
    public function getSplitHidden()
    {
        return $this->splitHidden;
    }

    /**
     * Should the sub forms be split off?
     *
     * @param bool $value
     * @return RepeatableFormElements (continuation pattern)
     */
    public function setFlattenSubs(bool $value = true)
    {
        $this->flattenSubs = $value;
        return $this;
    }

    /**
     * Should the hidden fields be split off?
     *
     * @param bool $value
     * @return RepeatableFormElements (continuation pattern)
     */
    public function setSplitHidden(bool $value = true)
    {
        $this->splitHidden = $value;

        return $this;
    }
}