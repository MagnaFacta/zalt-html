<?php

/**
 * A snippet that takes a sequence of snippets as input
 *
 * Only the first snippet that has HTML Output is displayed until it has no
 * longer any HTML output, then the next snippet is used, etcc..
 *
 * @package    MUtil
 * @subpackage Snippets\Standard
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2017, Erasmus MC and MagnaFacta B.V.
 * @license    New BSD License
 */

namespace MUtil\Snippets\Standard;

use MUtil\Request\RequestInfo;

/**
 *
 * @package    MUtil
 * @subpackage Snippets\Standard
 * @copyright  Copyright (c) 2017, Erasmus MC and MagnaFacta B.V.
 * @license    New BSD License
 * @since      Class available since version 1.8.4 30-Jun-2018 16:01:50
 */
class SequenceSnippet extends \MUtil\Snippets\SnippetAbstract
{
    /**
     * Stored in session;
     *
     * @var \Zend_Session_Namespace
     */
    protected $_session;

    /**
     * Html output
     *
     * @var array
     */
    protected $_html = [];

    /**
     *
     * @var \Zend_Controller_Request_Abstract
     */
    protected $request;

    /**
     * A parameter that if true resets the queue
     *
     * @var string
     */
    protected $resetParam;

    /**
     * @var RequestInfo
     */
    protected ?RequestInfo $requestInfo = null;

    /**
     *
     * @var array
     */
    protected $snippetList;

    /**
     *
     * @var \MUtil\Snippets\SnippetLoader
     */
    protected $snippetLoader;

    /**
     * Array of parameters for snippetLoader
     *
     * @var array
     */
    protected $snippetParameters;

    /**
     * Searches and loads a .php snippet file.
     *
     * @param string $snippet Snippet name or array of snippets with optionally extra parameters included
     * @return array Of filename => \MUtil\Snippets\SnippetInterface snippets
     */
    protected function _getSnippets($snippet)
    {
        if (is_array($snippet)) {
            list($snippets, $params) = \MUtil\Ra::keySplit($snippet);

            $extraParams = $params + $this->snippetParameters;
        } else {
            $snippets    = [$snippet];
            $extraParams = $this->snippetParameters;
        }

        $results = array();

        if ($snippets) {
            foreach ($snippets as $filename) {
                $results[$filename] = $this->snippetLoader->getSnippet($filename, $extraParams);
            }
        }

        return $results;
    }

    /**
     * Called after the check that all required registry values
     * have been set correctly has run.
     *
     * This function is no needed if the classes are setup correctly
     *
     * @return void
     */
    public function afterRegistry()
    {
        parent::afterRegistry();

        $sessionId = sprintf('%s_%s_%s',
                __CLASS__,
                $this->requestInfo->getCurrentController(),
                $this->requestInfo->getCurrentAction()
                );

        $this->_session = new \Zend_Session_Namespace($sessionId);

        if ($this->resetParam) {

            $queryParams = $this->requestInfo->getRequestQueryParams();
            $reset = false;

            if (isset($queryParams[$this->resetParam])) {
                $reset = (bool) $queryParams[$this->resetParam];
            }
        } else  {
            $reset = false;
        }
        if ($reset || (! isset($this->_session->list))) {
            $this->_session->list = $this->snippetList;
        }

        if (! $this->snippetLoader) {
            $this->snippetLoader = \MUtil\Html::getSnippetLoader();
        }
    }

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
        return $this->_html;
    }

    /**
     * The place to check if the data set in the snippet is valid
     * to generate the snippet.
     *
     * When invalid data should result in an error, you can throw it
     * here but you can also perform the check in the
     * checkRegistryRequestsAnswers() function from the
     * {@see \MUtil\Registry\TargetInterface}.
     *
     * @return boolean
     */
    public function hasHtmlOutput()
    {
        while ((! $this->_html) && $this->_session->list) {
            $current  = reset($this->_session->list);

            // This can be an array as a single snippet item can be an array
            $snippets = $this->_getSnippets($current);
            foreach ($snippets as $filename => $snippet) {
                if ($snippet instanceof \MUtil\Snippets\SnippetInterface) {
                    if ($snippet->hasHtmlOutput()) {
                        $this->_html[$filename] = $snippet;

                    } elseif ($snippet->getRedirectRoute()) {
                        $this->_session->unsetAll();
                        $snippet->redirectRoute();
                        return false;
                    }
                }
            }
            if ($this->_html) {
                return true;
            }

            // Remove from list, passed without action
            array_shift($this->_session->list);
        }

        return false;
    }
}
