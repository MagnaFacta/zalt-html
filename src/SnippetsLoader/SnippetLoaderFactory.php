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
        $config = $container->get('config');
        if (isset($config['snippetLoader']['directories'])) {
            $dirs = (array) $config['snippetLoader']['directories'];
        } else {
            $dirs = ['Zalt'];
        }
        
        $output = new SnippetLoader($container, $dirs);
        
        if (! Html::hasSnippetLoader()) {
            Html::setSnippetLoader($output);
        }
        
        return $output;
    }
}