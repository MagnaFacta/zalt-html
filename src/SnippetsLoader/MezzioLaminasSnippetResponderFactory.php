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
class MezzioLaminasSnippetResponderFactory
{
    public function __invoke(ContainerInterface $container): MezzioLaminasSnippetResponder
    {
        return new MezzioLaminasSnippetResponder($container->get(SnippetLoader::class));
    }
}