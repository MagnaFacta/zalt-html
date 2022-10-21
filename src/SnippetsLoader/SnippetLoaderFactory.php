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
        $overloader = $container->get(ProjectOverloader::class);
        
        $output = new SnippetLoader($overloader->createSubFolderOverloader('Snippets'));
        
        // Preparing the other parts
        if (! Html::hasSnippetLoader()) {
            Html::setSnippetLoader($output);
        }
        
        $renderer = Html::getRenderer();
        if ($container->has('Zend_View') && ! $renderer->hasView()) {
            $renderer->setView($container->get('Zend_View'));
        }
        
        return $output;
    }
}