<?php

declare(strict_types=1);

/**
 *
 * @package    Zalt
 * @subpackage SnippetsHandler
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 */

namespace Zalt\SnippetsHandler;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zalt\Base\RequestInfo;
use Zalt\Base\TranslateableTrait;
use Zalt\Late\Late;
use Zalt\Model\MetaModellerInterface;
use Zalt\Model\MetaModelLoader;
use Zalt\SnippetsActions\Browse\BrowseTableAction;
use Zalt\SnippetsActions\SnippetActionInterface;
use Zalt\SnippetsLoader\SnippetResponderInterface;

/**
 *
 * @package    Zalt
 * @subpackage SnippetsHandler
 * @since      Class available since version 1.0
 */
class SnippetHandler implements RequestHandlerInterface
{
    use TranslateableTrait;

    /**
     * Url parameter to reset searches
     */
    const SEARCH_RESET = 'reset';

    private MetaModellerInterface $_model;

    /**
     * @var string[classname|SnippetActionInterface] 
     */
    public static $actions = ['index' => BrowseTableAction::class];

    protected ServerRequestInterface $request;
    
    protected RequestInfo $requestInfo;

    public function __construct(
        protected SnippetResponderInterface $responder,
        protected MetaModelLoader $metaModelLoader,
        TranslatorInterface $translate,
    ) {
        $this->translate = $translate;
    }
    
    public function getAction($actionKey): SnippetActionInterface
    {
        $currentClass = get_class($this);
        if (! isset($currentClass::$actions[$actionKey])) {
            throw new ActionNotFoundException("Action $actionKey not found in actions list.");
        }
        
        $actionName = $currentClass::$actions[$actionKey];
        if ($actionName instanceof SnippetActionInterface) {
            $action = $actionName;
        } else {
            $action = $this->responder->getSnippetsAction($actionName);
        }
        $action->setSnippetAction($actionKey);
        
        $this->prepareAction($action);
        
        return $action;
    }

    /**
     * @inheritDoc
     */
    public function handle(ServerRequestInterface $request) : ResponseInterface
    {
        $this->request     = $request;
        $this->requestInfo = $this->responder->processRequest($request);

        // Add all params to the Late stack (for e.g. routing
        Late::addStack('request', $this->requestInfo->getParams());

        $action = $this->getAction($this->requestInfo->getCurrentAction() ?: 'index');

        $params   = $action->getSnippetOptions();
        $snippets = $action->getSnippetClasses();

        if ((! $snippets)) {
            $snippets[] = 'HtmlContentSnippet';
        }
        return $this->responder->getSnippetsResponse($snippets, $params);
    }
    
    public function prepareAction(SnippetActionInterface $action): void
    {
        $valueFunctions = [
            'getBrowseColumns' => 'columns',
            ];
        
        foreach ($valueFunctions as $functon => $property) {
            if (method_exists($this, $functon) && property_exists($action, $property)) {
                $action->$property = $this->$functon();
            }
        }
    }
}