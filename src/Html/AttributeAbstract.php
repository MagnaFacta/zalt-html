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
 * Basic class for all attributes, does the rendering and attribute name parts,
 * but no value processing.
 *
 * @package    MUtil
 * @subpackage Html
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.0
 */
abstract class AttributeAbstract implements \MUtil\Html\AttributeInterface
{
    /**
     *
     * @var type
     */
    public $name;

    /**
     *
     * @var \Zend_Controller_Request_Abstract
     */
    public $request;

    /**
     *
     * @var \Zend_View_Abstract
     */
    public $view;

    /**
     *
     * @param string $name The name of the attribute
     * @param mixed $value
     */
    public function __construct($name, $value = null)
    {
        $this->name = $name;

        if ($value) {
            $this->set($value);
        }
    }

    /**
     * Returns an unescape string version of the attribute
     *
     * Output escaping is done elsewhere, e.g. in \Zend_View_Helper_HtmlElement->_htmlAttribs()
     *
     * If a subclass needs the view for the right output and the view might not be set
     * it must overrule __toString().
     *
     * @return string
     */
    public function __toString()
    {
        return $this->get();
    }

    // public function add($value);
    // public function get();

    /**
     * Returns the attribute name
     *
     * @return string
     */
    public function getAttributeName()
    {
        return $this->name;
    }

    /**
     *
     * @return \Zend_Controller_Request_Abstract
     */
    public function getRequest()
    {
        if (! $this->request) {
            $this->request = \MUtil\Controller\Front::getRequest();
        }

        return $this->request;
    }

    /**
     *
     * @return \Zend_View_Abstract
     */
    public function getView()
    {
        if (! $this->view) {
            require_once 'Zend/Controller/Action/HelperBroker.php';
            $viewRenderer = \Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
            $this->setView($viewRenderer->view);
        }

        return $this->view;
    }

    /**
     * Renders the element into a html string
     *
     * The $view is used to correctly encode and escape the output
     *
     * @param \Zend_View_Abstract $view
     * @return string Correctly encoded and escaped html output
     */
    public function render(\Zend_View_Abstract $view)
    {
        $this->setView($view);

        // Output escaping is done in \Zend_View_Helper_HtmlElement->_htmlAttribs()
        //
        // The reason for using render($view) is only in case the attribute needs the view to get the right data.
        // Those attributes must overrule render().
        return $this->get();
    }

    // public function set($value);

    /**
     *
     * @param \Zend_Controller_Request_Abstract $request
     * @return \MUtil\Html\AttributeAbstract  (continuation pattern)
     */
    public function setRequest(\Zend_Controller_Request_Abstract $request)
    {
        $this->request = $request;
        return $this;
    }

    /**
     *
     * @param \Zend_View_Abstract $view
     */
    public function setView(\Zend_View_Abstract $view)
    {
        $this->view = $view;
    }
}