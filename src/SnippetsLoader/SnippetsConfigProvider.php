<?php

declare(strict_types=1);

/**
 *
 * @package    Zalt
 * @subpackage SnippetsLoader
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 */

namespace Zalt\SnippetsLoader;

# use Mezzio\Flash\FlashMessageMiddleware;

/**
 *
 * @package    Zalt
 * @subpackage SnippetsLoader
 * @since      Class available since version 1.0
 */
class SnippetsConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencies(),
        ];
    }

    public function getDependencies(): array
    {
        return [
            // Legacy MUtil Framework aliases
            'aliases'    => [
                \MUtil\Snippets\SnippetLoaderInterface::class => SnippetLoader::class,
            ],
            'factories' => [
                SnippetLoader::class => SnippetLoaderFactory::class,
                SnippetMiddleware::class => SnippetMiddleware::class,
                SnippetResponderInterface::class => MezzioLaminasSnippetResponderFactory::class,
            ],
        ];
    }
}