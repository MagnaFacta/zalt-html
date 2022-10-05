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
class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencies(),
            'snippetLoader' => ['directories' => ['Zalt']],
        ];
    }

    public function getDependencies(): array
    {
        return [
            // Legacy MUtil Framework aliases
            'aliases'    => [
                \MUtil\Snippets\SnippetLoaderInterface::class => SnippetLoader::class,
            ],
            'invokables' => [
                SnippetLoader::class => SnippetLoaderFactory::class,
                SnippetMiddleware::class => SnippetMiddleware::class,
            ],
        ];
    }
}