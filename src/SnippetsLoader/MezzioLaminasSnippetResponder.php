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
use Mezzio\Flash\FlashMessageMiddleware;
use Mezzio\Flash\FlashMessagesInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zalt\Base\RequestInfo;
use Zalt\Base\RequestInfoFactory;
use Zalt\Html\Sequence;
use Zalt\Message\MessengerInterface;
use Zalt\Message\MezzioFlashMessenger;
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

        $flash = $request->getAttribute(FlashMessageMiddleware::FLASH_ATTRIBUTE);
        if ($flash instanceof FlashMessagesInterface) {
            $this->snippetLoader->addConstructorVariable(
                MessengerInterface::class,
                new MezzioFlashMessenger($flash));
        }
        
        return $requestInfo;
    }
}