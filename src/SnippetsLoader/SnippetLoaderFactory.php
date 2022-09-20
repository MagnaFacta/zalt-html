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
use Zalt\Html\Html;
use Zalt\Loader\ProjectOverloader;

/**
 *
 * @package    Zalt
 * @subpackage SnippetsLoader
 * @since      Class available since version 1.0
 */
class SnippetLoaderFactory
{
    public function __invoke(ContainerInterface $container): SnippetLoader
    {
        $overLoader = $container->get(ProjectOverloader::class);
        
        $output = new SnippetLoader($overLoader);
        
        if (! Html::hasSnippetLoader()) {
            Html::setSnippetLoader($output);
        }
        
        return $output;
    }
}