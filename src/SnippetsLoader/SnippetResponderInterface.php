<?php

declare(strict_types=1);

/**
 *
 * @package    Zalt
 * @subpackage SnippetsLoader
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 */

namespace Zalt\SnippetsLoader;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 *
 * @package    Zalt
 * @subpackage SnippetsLoader
 * @since      Class available since version 1.0
 */
interface SnippetResponderInterface
{
    /**
     * @param array $snippetNames Array of snippets to load
     * @param mixed $snippetOptions arrau of something that can become an array or a SnippetOptions object
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function getSnippetsResponse(array $snippetNames, mixed $snippetOptions = []): ResponseInterface;

    /**
     * Apply the request for this responder
     * 
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @return void
     */
    public function processRequest(ServerRequestInterface $request): void;
}