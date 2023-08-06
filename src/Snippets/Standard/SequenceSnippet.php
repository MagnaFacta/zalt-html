<?php

/**
 * A snippet that takes a sequence of snippets as input
 *
 * Only the first snippet that has HTML Output is displayed until it has no
 * longer any HTML output, then the next snippet is used, etcc..
 *
 * @package    Zalt
 * @subpackage Snippets\Standard
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2017, Erasmus MC and MagnaFacta B.V.
 * @license    New BSD License
 */

namespace Zalt\Snippets\Standard;

use Mezzio\Session\SessionInterface;
use Zalt\Base\RequestInfo;
use Zalt\Html\Html;
use Zalt\Ra\Ra;
use Zalt\SnippetsLoader\SnippetLoader;
use Zalt\SnippetsLoader\SnippetOptions;

/**
 *
 * @package    Zalt
 * @subpackage Snippets\Standard
 * @copyright  Copyright (c) 2017, Erasmus MC and MagnaFacta B.V.
 * @license    New BSD License
 * @since      Class available since version 1.8.4 30-Jun-2018 16:01:50
 */
class SequenceSnippet extends \Zalt\Snippets\SnippetAbstract
{
    /**
     * Html output
     *
     * @var array
     */
    protected $_html = [];

    /**
     * A parameter that if true resets the queue
     *
     * @var string
     */
    protected $resetParam;

    /**
     * @var string
     */
    protected string $sessionId;

    /**
     *
     * @var array
     */
    protected $snippetList;

    /**
     * Array of parameters for snippetLoader
     *
     * @var array
     */
    protected $snippetParameters;

    public function __construct(
        SnippetOptions $snippetOptions,
        RequestInfo $requestInfo,
        protected SessionInterface $session,
        protected SnippetLoader $snippetLoader
        )
    {
        parent::__construct($snippetOptions, $requestInfo);

        if ($this->sessionId) {
            $this->sessionId = get_class($this) . '_list';
        }
    }

    /**
     * Searches and loads a .php snippet file.
     *
     * @param string $snippet Snippet name or array of snippets with optionally extra parameters included
     * @return array Of filename => \Zalt\Snippets\SnippetInterface snippets
     */
    protected function _getSnippets($snippet)
    {
        if (is_array($snippet)) {
            list($snippets, $params) = Ra::keySplit($snippet);

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
     * Create the snippets content
     *
     * This is a stub function either override getHtmlOutput() or override render()
     *
     * @return \Zalt\Html\HtmlInterface Something that can be rendered
     */
    public function getHtmlOutput()
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
     * {@see \Zalt\Registry\TargetInterface}.
     *
     * @return boolean
     */
    public function hasHtmlOutput(): bool
    {
        $sessionId = sprintf('%s_%s_%s',
            __CLASS__,
            $this->requestInfo->getCurrentController(),
            $this->requestInfo->getCurrentAction()
        );

        if ($this->resetParam) {
            $queryParams = $this->requestInfo->getRequestQueryParams();
            $reset = false;

            if (isset($queryParams[$this->resetParam])) {
                $reset = (bool) $queryParams[$this->resetParam];
            }
        } else  {
            $reset = false;
        }
        if ($reset || (! $this->session->has($this->sessionId))) {
            $this->session->set($this->sessionId, $this->snippetList);
        }

        if (! $this->snippetLoader) {
            $this->snippetLoader = Html::getSnippetLoader();
        }

        while ((! $this->_html) && $this->session->has($this->sessionId)) {
            $snippets = $this->session->get($this->sessionId);
            $current  = reset($snippets);

            // This can be an array as a single snippet item can be an array
            $snippets = $this->_getSnippets($current);
            foreach ($snippets as $filename => $snippet) {
                if ($snippet instanceof \Zalt\Snippets\SnippetInterface) {
                    if ($snippet->hasHtmlOutput()) {
                        $this->_html[$filename] = $snippet;

                    } elseif ($snippet->getRedirectRoute()) {
                        $this->session->unset($this->sessionId);
                        $snippet->redirectRoute();
                        return false;
                    }
                }
            }
            if ($this->_html) {
                return true;
            }

            // Remove from list, passed without action
            array_shift($snippets);
            if ($snippets) {
                $this->session->set($this->sessionId, $snippets);
            } else {
                $this->session->unset($this->sessionId);
            }

        }

        return false;
    }
}
