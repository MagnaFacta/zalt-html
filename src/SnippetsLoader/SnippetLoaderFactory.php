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
        $config = $container->has('config') ? $container->get('config') : [];
        if (isset($config['overLoaderPaths'])) {
            $dirs = (array) $config['overLoaderPaths'];
        } else {
            $dirs = ['Zalt'];
        }
        
        $output = new SnippetLoader($container, $dirs);
        
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