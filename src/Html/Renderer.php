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

use Zalt\Base\BaseUrl;
use Zalt\Lists\ClassList;
use Zalt\Late\Late;
use Zalt\Late\LateInterface;

/**
 * Render output for a view.
 *
 * This object handles \Zalt\Html\HtmlInterface and LateInterface
 * objects natively, as well as array, scalar values and objects with a
 * __toString function.
 *
 * All other object types passed to the renderer should have a render function
 * defined for them in the ClassRenderList.
 *
 * @package    Zalt
 * @subpackage Html
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.0
 */
class Renderer
{
    protected BaseUrl $_baseUrl;
    
    /**
     *
     * @var ClassList
     */
    protected $_classRenderFunctions;

    /**
     * Default array of objects rendering functions.
     *
     * The \Zalt\Html\Renderer::doNotRender function allows items to be passed
     * as content without triggering error messages.
     *
     * This is usefull if you want to pass an item to sub objects, but are not
     * sure that it will be used in every case.
     *
     * @var array classname => static output function
     */
    protected $_initialClassRenderFunctions = array(
        'Zend_Db_Adapter_Abstract'         => [Renderer::class => 'doNotRender'],
        'Zend_Controller_Request_Abstract' => [Renderer::class => 'doNotRender'],
        'Zend_Form'                        => [InputRenderer::class => 'renderForm'],
        'Zend_Form_DisplayGroup'           => [InputRenderer::class => 'renderDisplayGroup'],
        'Zend_Form_Element'                => [InputRenderer::class => 'renderElement'],
        'Zend_Translate'                   => [Renderer::class => 'doNotRender'],
    );

    /**
     * @var \Zend_View_Abstract
     */
    private $_view;

    /**
     * Create the renderer
     *
     * @param mixed $classRenderFunctions Array of classname => renderFunction or \Zalt\Util\ClassList
     * @param boolean $append Replace when false, append to default definitions otherwise
     */
    public function __construct($classRenderFunctions = null, $append = true)
    {
        $this->setClassRenderList($classRenderFunctions, $append);
        $this->_baseUrl = new BaseUrl();
    }

    /**
     * Check if the value can be rendered by this object
     *
     * @param mixed $value
     * @return boolean True when the object can be rendered
     */
    public function canRender($value)
    {
        if (is_object($value)) {
            if (($value instanceof LateInterface) ||
                ($value instanceof HtmlInterface) ||
                method_exists($value, '__toString')) {
                return true;
            }

            return $this->_classRenderFunctions->get($value);

        }  else {
            if (is_array($value)) {
                foreach ($value as $key => $val) {
                    if (! $this->canRender($val)) {
                        return false;
                    }
                }
            }

            return true;
        }
    }

    /**
     * Static helper function used for object types that should
     * not produce output when (accidently) rendered.
     *
     * Eg \Zend_Translate or \Zend_Db_Adapter_Abstract
     *
     * @param mixed $content
     * @return null
     */
    public static function doNotRender($content)
    {
        if (Html::$verbose) {
            // \Zalt\EchoOut\EchoOut::r('Did not render ' . get_class($content) . ' object.');
        }
        return null;
    }

    public function getBaseUrl(): BaseUrl
    {
        return $this->_baseUrl;
    }

    public function getBaseUrlString(): string
    {
        return $this->_baseUrl->getBaseUrl();
    }

    /**
     * Get the classlist containing render functions for non-builtin objects
     *
     * @return ClassList
     */
    public function getClassRenderList()
    {
        return $this->_classRenderFunctions;
    }

    public function getView(): \Zend_View_Abstract
    {
        if (! $this->_view) {
            throw new HtmlException("View is missing while rendering element using old Zend view.");
        }

        return $this->_view;
    }

    public function hasView(): bool
    {
        return (bool) $this->_view;
    }

    /**
     * Renders the $content so that it can be used as output.
     *
     * This functions handles \Zalt\Html\HtmlInterface and \Zalt\Late\LateInterface
     * objects natively, as well as array, scalar values and objects with a
     * __toString function.
     *
     * Other objects a definition should have a render function in getClassRenderList().
     *
     * All Late variabables are raised.
     *
     * @param mixed $content Anything HtmlInterface, number, string, array, object with __toString
     *                      or an object that has a defined render function in getClassRenderList().
     * @return string Output to echo to the user
     */
    public function renderAny($content)
    {
        $stack = null;

        // Resolve first as this function as recursion heavy enough as it is.
        if ($content instanceof LateInterfacee) {
            if (! $stack) {
                $stack = Late::getStack();
            }
            while ($content instanceof LateInterface) {
                $content = $content->__toValue($stack);
            }
        }

        if ($content) {
            if (is_scalar($content)) {
                $output = Html::escape((string) $content);

            } elseif ($content instanceof HtmlInterface) {
                $output = $content->render();

            } elseif (is_object($content)) {
                if ($function = $this->_classRenderFunctions->get($content)) {
                    // \Zalt\EchoOut\EchoOut::track($function);
                    $output = call_user_func($function, $content);
                } elseif (method_exists($content, '__toString')) {
                    $output = Html::escape($content->__toString());
                } else {
                    throw new HtmlException('WARNING: Object of type ' . get_class($content) . ' cannot be converted to string.');
                }

            } elseif (is_array($content)) {
                $output = $this->renderArray($content, '', $stack);

            } else {
                 if ($content instanceof __PHP_Incomplete_Class) {
                    // \Zalt\EchoOut\EchoOut::r($content, __CLASS__ . '->' .  __FUNCTION__);
                    $output = '';

                } else {
                    $output = Html::escape($content);
                }
            }
        } elseif (is_array($content)) { // I.e. empty array
            $output = '';
        } else {
            $output = (string) $content;  // Returns 0 (zero) and '' when that is the value of $content
        }

        return $output;
    }

    /**
     * Renders the $content so that it can be used as output.
     *
     * This functions handles \Zalt\Html\HtmlInterface and \Zalt\Late\LateInterface
     * objects natively, as well as array, scalar values and objects with a
     * __toString function.
     *
     * Other objects a definition should have a render function in getClassRenderList().
     *
     * All Late variabables are raised.
     *
     * @param mixed $content Anything HtmlInterface, number, string, array, object with __toString
     *                      or an object that has a defined render function in getClassRenderList().
     * @return string Output to echo to the user
     */
    public function renderArray($content, $glue = '', $stack = null)
    {
        // \Zalt\EchoOut\EchoOut::timeFunctionStart(__FUNCTION__);

        $output = array();

        // \Zalt\EchoOut\EchoOut::countOccurences('render');
        foreach ($content as $key => $value) {
            // Resolve first as this function as recursion heavy enough as it is.
            if ($value instanceof LateInterface) {
                if (! $stack) {
                    $stack = Late::getStack();
                }
                while ($value instanceof LateInterface) {
                    $value = $value->__toValue($stack);
                }
            }

            if (is_scalar($value)) {
                $output[$key] = Html::escape((string) $value);

            } elseif ($value instanceof HtmlInterface) {
                $output[$key] = $value->render();

            } elseif (null === $value) {
                // Do nothing

            } elseif (is_object($value)) {
                $function = $this->_classRenderFunctions->get($value);

                if ($function) {
                    $output[$key] = call_user_func($function, $value);
                } elseif (method_exists($value, '__toString')) {
                    $output[$key] = Html::escape($value->__toString());
                } else {
                    // $output[$key] = 'WARNING: Object of type ' . get_class($value) . ' cannot be converted to string.';
                    throw new HtmlException('WARNING: Object of type ' . get_class($value) . ' cannot be converted to string.');
                }

            } elseif (is_array($value)) {
                $output[$key] = $this->renderArray($value, '', $stack);

            } elseif ($value instanceof \__PHP_Incomplete_Class) {
                // \Zalt\EchoOut\EchoOut::r($value, __CLASS__ . '->' .  __FUNCTION__);
                $output[$key] = '';

            } else { // Mop up, should not occur
                // \Zalt\EchoOut\EchoOut::countOccurences('scalar else');
                $output[$key] = Html::escape((string) $value);
            }
        }

        if ((false === $glue) || (null === $glue)) {
            // \Zalt\EchoOut\EchoOut::timeFunctionStop(__FUNCTION__);
            return $output;
        }
        $output = implode($glue, $output);
        // \Zalt\EchoOut\EchoOut::timeFunctionStop(__FUNCTION__);
        return $output;
    }

    public function setBaseUrl(string|BaseUrl $baseUrl): void
    {
        if ($baseUrl instanceof BaseUrl) {
            // Set the existing object here, so that all classes that reference this object will reference the correct one
            $this->_baseUrl->setBaseUrl($baseUrl->getBaseUrl());
        } else {
            $this->_baseUrl->setBaseUrl($baseUrl);
        }
    }

    /**
     * Change the list of non-builtin objects that can be rendered by this renderer.
     *
     * @param mixed $classRenderFunctions Array of classname => renderFunction or \Zalt\Util\ClassList
     * @param boolean $append Replace when false, append otherwise
     */
    public function setClassRenderList($classRenderFunctions = null, $append = false): void
    {
        if ($classRenderFunctions instanceof ClassList) {
            $this->_classRenderFunctions = $classRenderFunctions;
        } else {
            $this->_classRenderFunctions = new ClassList($this->_initialClassRenderFunctions);

            if ($classRenderFunctions) {
                if ($append) {
                    $this->_classRenderFunctions->add((array) $classRenderFunctions);
                } else {
                    $this->_classRenderFunctions->set((array) $classRenderFunctions);
                }
            }
        }
    }

    public function setView(\Zend_View_Abstract $view): void
    {
        $this->_view = $view;
    }
}
