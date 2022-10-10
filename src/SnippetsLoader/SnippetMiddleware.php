<?php

declare(strict_types=1);

/**
 *
 * @package    Zalt
 * @subpackage SnippetsLoader
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 */

namespace Zalt\SnippetsLoader;

use Mezzio\Flash\FlashMessageMiddleware;
use Mezzio\Flash\FlashMessagesInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zalt\Base\RequestInfo;
use Zalt\Base\RequestInfoFactory;
use Zalt\Message\MessengerInterface;
use Zalt\Message\MezzioFlashMessenger;

/**
 *
 * @package    Zalt
 * @subpackage SnippetsLoader
 * @since      Class available since version 1.0
 */
class SnippetMiddleware implements MiddlewareInterface
{
    public function __construct(
        private SnippetLoader $snippetLoader
    ) {
    }


    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->snippetLoader->addConstructorVariable(
            RequestInfo::class, 
            RequestInfoFactory::getMezzioRequestInfo($request)
        );
        
        $flash = $request->getAttribute(FlashMessageMiddleware::FLASH_ATTRIBUTE);
        if ($flash instanceof FlashMessagesInterface) {
            $this->snippetLoader->addConstructorVariable(
                MessengerInterface::class, 
                new MezzioFlashMessenger($flash));
        }
        
        return $handler->handle($request);
    }
}
