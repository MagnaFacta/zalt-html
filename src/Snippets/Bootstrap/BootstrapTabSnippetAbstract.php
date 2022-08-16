<?php

/**
 *
 *
 * @package    MUtil
 * @subpackage Snippets_Bootstrap
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2014 Erasmus MC
 * @license    New BSD License
 */

namespace MUtil\Snippets\Bootstrap;

/**
 *
 * @package    MUtil
 * @subpackage Snippets_Bootstrap
 * @copyright  Copyright (c) 2014 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.5
 */
abstract class BootstrapTabSnippetAbstract extends \MUtil\Snippets\TabSnippetAbstract
{
    /**
     *
     * @var string Class attribute for all tabs
     */
    protected $tabClass = 'nav navtabs';

    /**
     * Create the snippets content
     *
     * This is a stub function either override getHtmlOutput() or override render()
     *
     * @param \Zend_View_Abstract $view Just in case it is needed here
     * @return \MUtil\Html\HtmlInterface Something that can be rendered
     */
    public function getHtmlOutput(\Zend_View_Abstract $view)
    {
        $tabs = $this->getTabs();

        if ($tabs && ($this->displaySingleTab || count($tabs) > 1)) {
            // Set the correct parameters
            $this->getCurrentTab($tabs);

            // Let loose
            if (is_array($this->baseUrl)) {
                $this->href = $this->href + $this->baseUrl;
            }

            $tabRow = \MUtil\Html::create()->ul();
            $tabRow->class = $this->tabClass;

            foreach ($tabs as $tabId => $content) {

                $li = $tabRow->li();

                $li->a($this->getParameterKeysFor($tabId) + $this->href, $content);

                if ($this->currentTab == $tabId) {
                    $li->appendAttrib('class', $this->tabActiveClass);
                }
            }

            return $tabRow;
        } else {
            return null;
        }
    }


}
