<?php

/**
 *
 *
 * @package    MUtil
 * @subpackage Snippets
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 201e Erasmus MC
 * @license    New BSD License
 */

namespace MUtil\Snippets\Standard;

/**
 *
 *
 * @package    MUtil
 * @subpackage Snippets
 * @copyright  Copyright (c) 2013 Erasmus MC
 * @license    New BSD License
 * @since      Class available since \MUtil version 1.3
 */
class TabSnippetGeneric extends \MUtil\Snippets\TabSnippetAbstract
{
    /**
     *
     * @var array
     */
    private $_tabs;

    /**
     *
     * @param array $tabs
     */
    public function __construct(array $tabs)
    {
        $this->_tabs = $tabs;
    }

    /**
     * Function used to fill the tab bar
     *
     * @return array tabId => label
     */
    protected function getTabs()
    {
        return $this->_tabs;
    }
}
