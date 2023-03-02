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
use Zalt\Base\RequestInfo;
use Zalt\Model\MetaModellerInterface;
use Zalt\SnippetsActions\SnippetActionInterface;
use Zalt\SnippetsHandler\ActionNotSnippetActionException;

/**
 *
 * @package    Zalt
 * @subpackage SnippetsLoader
 * @since      Class available since version 1.0
 */
interface SnippetResponderInterface
{
    /**
     * @param string $className
     * @return \Zalt\SnippetsActions\SnippetActionInterface
     * @throws ActionNotSnippetActionException 
     */
    public function getSnippetsAction(string $className): SnippetActionInterface;

    /**
     * @param array $snippetNames Array of snippets to load
     * @param mixed $snippetOptions arrau of something that can become an array or a SnippetOptions object
     * @param ServerRequestInterface $request
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function getSnippetsResponse(array $snippetNames, mixed $snippetOptions = [], ?ServerRequestInterface $request = null): ResponseInterface;

    /**
     * Optional function to add the model ad a constructor variable to the loader
     * 
     * @param \Zalt\Model\MetaModellerInterface $model
     * @return void
     */
    public function processModel(MetaModellerInterface $model): void;

    /**
     * Apply the request for this responder, required to run
     * 
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @return \Zalt\Base\RequestInfo
     */
    public function processRequest(ServerRequestInterface $request): RequestInfo;
}