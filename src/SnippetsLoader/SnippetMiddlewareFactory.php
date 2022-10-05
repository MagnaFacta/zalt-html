<?php

declare(strict_types=1);

/**
 *
 * @package    Zalt
 * @subpackage SnippetsLoader
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 */

namespace Zalt\SnippetsLoader;

use Psr\Container\ContainerInterface;

/**
 *
 * @package    Zalt
 * @subpackage SnippetsLoader
 * @since      Class available since version 1.0
 */
class SnippetMiddlewareFactory
{
    public function __invoke(ContainerInterface $container): SnippetLoader
    {
        $loader = $container->get(SnippetLoader::class);
        
        return new SnippetMiddleware($loader);
    }
}