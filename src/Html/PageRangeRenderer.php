<?php

/**
 *
 * @package    MUtil
 * @subpackage Html
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 */

namespace MUtil\Html;

/**
 *
 * @package    MUtil
 * @subpackage Html
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since \MUtil version 1.0
 */
class PageRangeRenderer implements \MUtil\Html\HtmlInterface, \MUtil\Lazy\Procrastinator
{
    protected $_current;
    protected $_element;
    protected $_glue;
    protected $_lazy;
    protected $_panel;

    public $page;

    public function __construct(\MUtil\Html\PagePanel $panel, $glue = ' ', $args_array = null)
    {
        $args = \MUtil\Ra::args(func_get_args(), array('panel' => '\\MUtil\\Html\\PagePanel', 'glue'), array('glue' => ' '));

        if (isset($args['panel'])) {
            $this->_panel = $args['panel'];
            unset($args['panel']);
        } else {
            throw new \MUtil\Html\HtmlException('Illegal argument: no panel passed to ' . __CLASS__ . ' constructor.');
        }

        if (isset($args['glue'])) {
            $this->setGlue($args['glue']);
            unset($args['glue']);
        } else {
            $this->setGlue($glue);
        }

        $page = $this->toLazy()->page;
        $args = array($page) + $args;

        // We create the element here as this creates as an element using the specifications at this moment.
        // If created at render time the settings might have changed, introducing hard to trace bugs.
        $this->_element = $panel->createPageLink($this->toLazy()->notCurrent(), $page, $args);
    }

    public function getGlue()
    {
        return $this->_glue;
    }

    public function notCurrent()
    {
        // \MUtil\EchoOut\EchoOut::r($this->page, $this->_current);
        return $this->page != $this->_current;
    }

    /**
     * Echo the content.
     *
     * The $view is not used but required by the interface definition
     *
     * @param \Zend_View_Abstract $view
     * @return string Correctly encoded and escaped html output
     */
    public function render(\Zend_View_Abstract $view)
    {
        $html  = '';
        $glue  = $this->getGlue();
        $pages = $this->_panel->getPages();

        $this->_current = $pages->current;

        foreach ($pages->pagesInRange as $page) {
            $this->page = $page;

            $html .= $glue;
            $html .= $this->_element->render($view);
        }

        return substr($html, strlen($glue));

    }

    public function setGlue($glue)
    {
        $this->_glue = $glue;

        return $this;
    }

    public function toLazy()
    {
        if (! $this->_lazy) {
            $this->_lazy = new \MUtil\Lazy\ObjectWrap($this);
        }

        return $this->_lazy;
    }
}

