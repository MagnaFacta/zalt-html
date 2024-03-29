<?php

/**
 *
 * @package    Zalt
 * @subpackage Html
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 */

namespace Zalt\Html\Zend;

use Zalt\Html\Html;
use Zalt\Html\Zend\ZendLabelElement;
use Zalt\Html\One;
use Zalt\Late\Late;
use Zalt\Late\LateInterface;

/**
 * This class handles the rendering of input elements.
 *
 * If a \Zend_Form object is passed as first parameter, then it is rendered appropriately.
 * Otherwise the constructor tries to handle it as an attempt to create a raw HtmlElement
 * input element.
 *
 * @package    Zalt
 * @subpackage Html
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.0
 */
class ZendInputRenderer implements \Zalt\Html\HtmlInterface
{
    const MODE_COMPLETE = 'complete';
    const MODE_DISPLAY_GROUP = 'displayGroup';
    const MODE_ELEMENT = 'except';
    const MODE_EXCEPT = 'except';
    const MODE_FORM = 'form';
    const MODE_HTML = 'html';
    const MODE_ONLY = 'only';
    const MODE_UNTIL = 'until';
    const MODE_UPTO = 'upto';

    const ARGUMENT_ERROR = 'Invalid argument of type %s in %s. Only \Zend_Form and \Zend_Form_Element objects are allowed and \Zalt\Late\LateInterface objects as long as they devolve to \Zend_Form or \Zend_Form_Element.';

    private $_decorators;
    private $_element;
    private $_mode;

    /**
     *
     * @param array|\Zend_Form|\Zend_Form_Element|\Zalt\Late\LateInterface $element
     * @param $mode One of the class MODE_ constants
     * @param array $decorators An array of string|array|\Zend_Form_Decorator_Interface Optional An arrya that contains values
     * that are either a string value that identifies an existing decorator or an array that creates an new decorator
     * or a decorator instance.
     */
    public function __construct($element, $mode = self::MODE_ELEMENT, array $decorators = array())
    {
        if (($element instanceof \Zend_Form_Element) ||
                ($element instanceof \Zend_Form_DisplayGroup) ||
                ($element instanceof \Zend_Form) ||
                ($element instanceof LateInterface)) {

            switch ($mode) {
                case self::MODE_COMPLETE:
                case self::MODE_DISPLAY_GROUP:
                case self::MODE_ELEMENT:
                case self::MODE_FORM:
                    if ($decorators) {
                        throw new \Zalt\Html\HtmlException('Invalid mode for ' . __CLASS__ .
                                ' constructor. With decorators the mode argument ' . $mode . ' is not allowed.');
                    }
                    break;

                case self::MODE_EXCEPT:
                case self::MODE_ONLY:
                case self::MODE_UNTIL:
                case self::MODE_UPTO:
                    if (! $decorators) {
                        throw new \Zalt\Html\HtmlException('Invalid mode ' . $mode . ' for ' . __CLASS__ .
                                ' constructor. Without decorators the only allowed mode argument is ' .
                                self::MODE_COMPLETE . '.');
                    }
                    break;

                default:
                    throw new \Zalt\Html\HtmlException('Unknown mode ' . $mode . ' for ' . __CLASS__ .
                            ' constructor.');
            }
            $this->_element    = $element;
            $this->_decorators = $decorators;
            $this->_mode       = $mode;

        } else {
            if (self::MODE_ELEMENT === $mode) {
                // Was the second argument not specified?
                // Then the arguments should be passed in $element.
                $args = $element;
            } else {
                // Use all args
                $args = func_get_args();
            }
            // Treat this as a standard Html Element
            $this->_element = new \Zalt\Html\HtmlElement('input', $args);
            $this->_mode = self::MODE_HTML;
        }
    }

    private static function _checkElement($element, $function)
    {
        if ($element instanceof LateInterface) {
            $element = Late::rise($element);
        }

        if (($element instanceof \Zend_Form_Element) ||
            ($element instanceof \Zend_Form_DisplayGroup) ||
            ($element instanceof \Zend_Form)) {

            return $element;
        }

        throw new \Zalt\Html\HtmlException(sprintf(self::ARGUMENT_ERROR, get_class($element), __CLASS__ . '::' .
                $function . '()'));
    }

    private static function _throwStopError($element, $decorators, $function)
    {
        $stoppers = '';
        foreach ($decorators as $until_decorator) {
            $stoppers .= ', ';
            if (is_string($until_decorator)) {
                $stoppers .= $until_decorator;
            } else {
                $stoppers .= get_class($until_decorator);
            }
        }
        if ($stoppers) {
            $start = 'None of the stopping decorators found';
            $stoppers  = "<br/>\n<strong>Stopping decorators specified:</strong> " . substr($stoppers, 2);
        } else {
            $start = 'No stopping decorators specified for';
            $stoppers = '';
        }

        $found = '';
        foreach ($element->getDecorators() as $name => $decorator) {
            $found .= ', ' . $name;
        }
        if ($found) {
            $found = "<br/>\n<strong>Decorators found:</strong> " . substr($found, 2);
        } else {
            $found = "<br/>\nNo decorators found in element.";
        }

        $message = $start . ' rendering element <strong>' .
            $element->getName() . '</strong> of type ' . get_class($element) .
            ' in ' . __CLASS__ . '::' . $function . "().<br>\n" . $stoppers . $found;

        // \Zalt\EchoOut\EchoOut::r($message);
        throw new \Zalt\Html\HtmlException($message);

    }

    public static function input($element)
    {
        if ($element instanceof \Zend_Form) {
            return self::inputForm($element);
        }

        if ($element instanceof \Zend_Form_DisplayGroup) {
            return self::inputDisplayGroup($element);
        }

        // Assume all late's to be elements (should be rare in any case.
        return self::inputElement($element);
    }


    public static function inputComplete($element)
    {
        return new self($element, self::MODE_COMPLETE);
    }


    public static function inputDescription($element)
    {
        return new self($element, self::MODE_ONLY, array('Description'));
    }


    public static function inputDisplayGroup($element)
    {
        return new self($element, self::MODE_DISPLAY_GROUP);
    }


    public static function inputElement($element)
    {
        return new self($element, self::MODE_ELEMENT);
    }


    public static function inputErrors($element)
    {
        return new self($element, self::MODE_ONLY, array('Errors'));
    }


    public static function inputExcept($element, $decorator_array)
    {
        $args = func_get_args();
        $decorators = array_slice($args, 1);

        return new self($element, self::MODE_EXCEPT, $decorators);
    }


    public static function inputForm($element)
    {
        return new self($element, self::MODE_FORM);
    }


    public static function inputLabel($arg_array = array())
    {
        $args = func_get_args();
        return new ZendLabelElement($args);
    }


    public static function inputOnly($element, $decorator_array)
    {
        $args = func_get_args();
        $decorators = array_slice($args, 1);

        return new self($element, self::MODE_ONLY, $decorators);
    }


    public static function inputOnlyArray($element, array $decorators)
    {
        return new self($element, self::MODE_ONLY, $decorators);
    }


    public static function inputUntil($element, $decorator_array)
    {
        $args = func_get_args();
        $decorators = array_slice($args, 1);

        return new self($element, self::MODE_UNTIL, $decorators);
    }


    public static function inputUpto($element, $decorator_array)
    {
        $args = func_get_args();
        $decorators = array_slice($args, 1);

        return new self($element, self::MODE_UPTO, $decorators);
    }


    public function render()
    {
        switch ($this->_mode) {
            case self::MODE_COMPLETE:
                return self::renderComplete($this->_element);

            case self::MODE_DISPLAY_GROUP:
                return self::renderDisplayGroup($this->_element);

            case self::MODE_ELEMENT:
                return self::renderElement($this->_element);

            case self::MODE_EXCEPT:
                return self::renderExcept($this->_element, $this->_decorators);

            case self::MODE_FORM:
                return self::renderForm($this->_element);

            case self::MODE_HTML:
                return $this->_element->render(Html::getRenderer()->getView());

            case self::MODE_ONLY:
                return self::renderOnly($this->_element, $this->_decorators);

            case self::MODE_UNTIL:
                return self::renderUntil($this->_element, $this->_decorators);

            case self::MODE_UPTO:
                return self::renderUpto($this->_element, $this->_decorators);

            // default: Not needed, checked in constructor
        }
        return null;
    }

    public static function renderComplete($element)
    {
        $element = self::_checkElement($element, __FUNCTION__);
        return $element->render(Html::getRenderer()->getView());
    }

    public static function renderDisplayGroup(\Zend_Form_DisplayGroup $displayGroup)
    {
        return self::renderUntil($displayGroup,
            array('Zend_Form_Decorator_Fieldset'));
//        return self::renderOnly($displayGroup,
//            array('Zend_Form_Decorator_FormElements', 'Zend_Form_Decorator_Fieldset'));
    }

    public static function renderElement($element)
    {
        return self::renderUntil($element, array(
            'Zend_Form_Decorator_ViewHelper',
            'Zend_Form_Decorator_File',
            '\\MUtil\\Form\\Decorator\\Table',
            '\\MUtil\\Form\\Decorator\\Subforms',
            ));
    }

    public static function renderExcept($element, array $except_decorators)
    {
        $element = self::_checkElement($element, __FUNCTION__);
        $element->setView(Html::getRenderer()->getView());

        $content = '';
        foreach ($element->getDecorators() as $name => $decorator) {
            $render = true;

            foreach ($except_decorators as $except_decorators) {
                if (($except_decorators == $name) || ($decorator instanceof $except_decorators)) {
                    $render = false;
                    break;
                }
            }

            if ($render) {
                $decorator->setElement($element);
                $content = $decorator->render($content);
            }
        }

        return $content;
    }

    public static function renderForm(\Zend_Form $form)
    {
        if ($form instanceof \MUtil\Form && $form->isLazy()) {
            return self::renderUntil($form, array('Zend_Form_Decorator_Form'));
        } else {
            return self::renderComplete($form);
        }
    }

    public static function renderOnly($element, array $decorators)
    {
        $element = self::_checkElement($element, __FUNCTION__);
        $element->setView(Html::getRenderer()->getView());

        $content = '';
        foreach ($decorators as $decoratorinfo) {

            if ($decoratorinfo instanceof \Zend_Form_Decorator_Interface) {
                $decorator = $decoratorinfo;

            } else {
                if (is_array($decoratorinfo)) {
                    $decoratorname = array_shift($decoratorinfo);
                    if (is_array(reset($decoratorinfo))) {
                        $decoratoroptions = array_shift($decoratorinfo);
                    } else {
                        $decoratoroptions = $decoratorinfo;
                    }
                    $element->addDecorator($decoratorname, $decoratoroptions);

                } else {
                    $decoratorname = $decoratorinfo;
                }

                $decorator = $element->getDecorator($decoratorname);
            }

            if ($decorator) {
                $decorator->setElement($element);
                $content = $decorator->render($content);
            }
        }

        return $content;
    }

    public static function renderUntil($element, array $until_decorators)
    {
        $element = self::_checkElement($element, __FUNCTION__);
        
        $element->setView(Html::getRenderer()->getView());
        
        $content = '';
        foreach ($element->getDecorators() as $name => $decorator) {
            $decorator->setElement($element);
            $content = $decorator->render($content);

            foreach ($until_decorators as $until_decorator) {
                if (($until_decorator == $name) || ($decorator instanceof $until_decorator)) {
                    // \Zalt\EchoOut\EchoOut::r('<strong>' . $element->getName() . ', ' . $until_decorator . '</strong>' . htmlentities($content));
                    return $content;
                }
            }
        }

        self::_throwStopError($element, $until_decorators, __FUNCTION__);
    }

    public static function renderUpto($element, array $until_decorators)
    {
        $element = self::_checkElement($element, __FUNCTION__);
        $element->setView(Html::getRenderer()->getView());

        $content = '';
        foreach ($element->getDecorators() as $name => $decorator) {
            foreach ($until_decorators as $until_decorator) {
                if (($until_decorator == $name) || ($decorator instanceof $until_decorator)) {
                    return $content;
                }
            }

            $decorator->setElement($element);
            $content = $decorator->render($content);
        }

        self::_throwStopError($element, $until_decorators, __FUNCTION__);
    }
}
