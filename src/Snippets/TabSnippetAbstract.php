<?php

/**
 *
 * @package    Zalt
 * @subpackage Snippets
 * @author     Menoo Dekker <menno.dekker@erasmusmc.nl>
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 */

namespace Zalt\Snippets;

use Zalt\Html\Html;

/**
 * Abstract class for quickly creating a tabbed bar, or rather a div that contains a number
 * of links, adding specific classes for display.
 *
 * @package    Zalt
 * @subpackage Snippets
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.1
 */
abstract class TabSnippetAbstract extends TranslatableSnippetAbstract
{
    protected ?string $baseUrl = null;

    /**
     * Shortfix to add class attribute
     *
     * @var string
     */
    protected $class = 'tabrow nav nav-tabs';

    /**
     *
     * @var string Id of the current tab
     */
    protected ?string $currentTab = null;

    /**
     *
     * @var string Id of default tab
     */
    protected ?string $defaultTab = null;

    /**
     * Show bar when there is only a single tab
     *
     * @var boolean
     */
    protected bool $displaySingleTab = false;

    /**
     * Default href parameter values
     *
     * @var string|null
     */
    protected ?string $href = null;

    protected ?string $linkActiveClass = 'active';

    protected ?string $linkClass = null;

    protected ?string $parameterKey = null;

    /**
     *
     * @var string Class attribute for active tab
     */
    protected ?string $tabActiveClass = 'active';

    /**
     *
     * @var string Class attribute for all tabs
     */
    protected string $tabClass       = 'tab';

    /**
     * Sets the default and current tab and returns the current
     *
     * @return string The current tab
     */
    public function getCurrentTab(): string
    {
        $tabs = $this->getTabs();

        // When empty, first is default
        if (null === $this->defaultTab) {
            reset($tabs);
            $this->defaultTab = key($tabs);
        }
        if (null === $this->currentTab) {
            $this->currentTab = null;
            $parameterKey = $this->getParameterKey();
            $params = $this->requestInfo->getParams();
            if (isset($params[$parameterKey])) {
                $this->currentTab = $params[$parameterKey];
            }
        }

        // Param can exist and be empty or can have a false value
        if (! ($this->currentTab && isset($tabs[$this->currentTab])))  {
            $this->currentTab = $this->defaultTab;
        }

        return $this->currentTab;
    }

    protected function getHref($currentTab): string
    {
        return $this->baseUrl . $this->href . $currentTab;
    }

    public function getHtmlOutput()
    {
        $tabs = $this->getTabs();

        if ($tabs && ($this->displaySingleTab || count($tabs) > 1)) {
            // Set the correct parameters
            $currentTab = $this->getCurrentTab();

            $tabRow = Html::create()->ul();

            foreach ($tabs as $tabId => $content) {

                $li = $tabRow->li(['class' => $this->tabClass]);

                $href = $this->getHref($tabId);

                $link = $li->a($href, $content);

                if ($this->linkClass) {
                    $link->appendAttrib('class', $this->linkClass);
                }

                if ($currentTab == $tabId) {
                    if ($this->tabActiveClass) {
                        $li->appendAttrib('class', $this->tabActiveClass);
                    }
                    if ($this->linkActiveClass) {
                        $link->appendAttrib('class', $this->linkActiveClass);
                    }
                }
            }

            return $tabRow;
        } else {
            return null;
        }
    }

    /**
     * Return optionally the single parameter key which should left out for the default value,
     * but is added for all other tabs.
     *
     * @return string|int|null
     */
    protected function getParameterKey(): string|int|null
    {
        return $this->parameterKey;
    }

    /**
     * Return the parameters that should be used for this tabId
     *
     * @param string $tabId
     * @return array
     */
    protected function getParameterKeysFor(string $tabId): array
    {
        $paramKey = $this->getParameterKey();

        if ($paramKey) {
            return [$paramKey => $tabId];
        }

        return [];
    }

    /**
     * Function used to fill the tab bar
     *
     * @return array tabId => label
     */
    abstract protected function getTabs(): array;
}
