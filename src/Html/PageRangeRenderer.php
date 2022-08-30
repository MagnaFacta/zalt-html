<?php

/**
 *
 * @package    Zalt
 * @subpackage Html
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 */

namespace Zalt\Html;

use Zalt\Ra\Ra;
use Zalt\Late\ObjectWrap;
use Zalt\Late\Procrastinator;

/**
 *
 * @package    Zalt
 * @subpackage Html
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since \Zalt version 1.0
 */
class PageRangeRenderer implements HtmlInterface, Procrastinator
{
    protected $_current;
    protected $_element;
    protected $_glue;
    protected $_late;
    protected $_panel;

    public $page;

    public function __construct(PagePanel $panel, $glue = ' ', $args_array = null)
    {
        $args = Ra::args(func_get_args(), array('panel' => PagePanel::class, 'glue'), array('glue' => ' '));

        if (isset($args['panel'])) {
            $this->_panel = $args['panel'];
            unset($args['panel']);
        } else {
            throw new HtmlException('Illegal argument: no panel passed to ' . __CLASS__ . ' constructor.');
        }

        if (isset($args['glue'])) {
            $this->setGlue($args['glue']);
            unset($args['glue']);
        } else {
            $this->setGlue($glue);
        }

        $page = $this->toLate()->page;
        $args = array($page) + $args;

        // We create the element here as this creates as an element using the specifications at this moment.
        // If created at render time the settings might have changed, introducing hard to trace bugs.
        $this->_element = $panel->createPageLink($this->toLate()->notCurrent(), $page, $args);
    }

    public function getGlue()
    {
        return $this->_glue;
    }

    public function notCurrent()
    {
        return $this->page != $this->_current;
    }

    /**
     * Echo the content.
     *
     * The $view is not used but required by the interface definition
     *
     * @return string Correctly encoded and escaped html output
     */
    public function render()
    {
        $html  = '';
        $glue  = $this->getGlue();
        $pages = $this->_panel->getPages();

        $this->_current = $pages->current;

        foreach ($pages->pagesInRange as $page) {
            $this->page = $page;

            $html .= $glue;
            $html .= $this->_element->render();
        }

        return substr($html, strlen($glue));
    }

    public function setGlue($glue)
    {
        $this->_glue = $glue;

        return $this;
    }

    public function toLate()
    {
        if (! $this->_late) {
            $this->_late = new ObjectWrap($this);
        }

        return $this->_late;
    }
}

