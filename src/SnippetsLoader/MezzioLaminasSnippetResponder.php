<?php

declare(strict_types=1);

/**
 *
 * @package    Zalt
 * @subpackage SnippetsLoader
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 */

namespace Zalt\SnippetsLoader;

use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Mezzio\Session\SessionInterface;
use Mezzio\Session\SessionMiddleware;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zalt\Base\RequestInfo;
use Zalt\Base\RequestInfoFactory;
use Zalt\Html\Sequence;
use Zalt\Message\MessengerInterface;
use Zalt\Message\MezzioSessionMessenger;
use Zalt\Ra\Ra;

/**
 * Responder with as input Mezzio Psr Objects and as output Laminas Pst Responses
 * 
 * @package    Zalt
 * @subpackage SnippetsLoader
 * @since      Class available since version 1.0
 */
class MezzioLaminasSnippetResponder implements SnippetResponderInterface
{
    protected ServerRequestInterface $request;
    
    public function __construct(
        protected SnippetLoader $snippetLoader
    ) {
    }

    public function getSnippetsResponse(array $snippetNames, mixed $snippetOptions = [], ?ServerRequestInterface $request = null): ResponseInterface
    {
        if ($request) {
            $this->processRequest($request);
        }
        
        if (! $snippetOptions instanceof SnippetOptions) {
            if (! is_array($snippetOptions)) {
                $snippetOptions = Ra::to($snippetOptions);
            }
            $snippetOptions = new SnippetOptions($snippetOptions);
        }

        $html = new Sequence();
        
        foreach ($snippetNames as $snippetName) {
            $snippet = $this->snippetLoader->getSnippet($snippetName, $snippetOptions);
            
            if ($snippet->hasHtmlOutput()) {
                $html->append($snippet);
            } else {
                $response = $snippet->getResponse();
                
                if ($response) {
                    return $response;                    
                }
                $url = $snippet->getRedirectRoute();
                
                if ($url) {
                    return new RedirectResponse($url);
                }
            }
        }
        
        return new HtmlResponse($html->render());
    }
    
    public function processRequest(ServerRequestInterface $request): RequestInfo
    {
        $this->request = $request;
        
        $requestInfo = RequestInfoFactory::getMezzioRequestInfo($request);
        $this->snippetLoader->addConstructorVariable(RequestInfo::class, $requestInfo);

        $session = $request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE);
        if ($session instanceof SessionInterface) {
            $this->snippetLoader->addConstructorVariable(
                MessengerInterface::class,
                new MezzioSessionMessenger($session));
        }
        
        return $requestInfo;
    }
}