<?php

/**
 *
 * @package    Zalt
 * @subpackage Snippets
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 */

namespace Zalt\SnippetsLoader;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zalt\Snippets\SnippetInterface;
use Zalt\Loader\ProjectOverloader;

/**
 * This class handles the loading and processing of snippets.
 *
 * @package    Zalt
 * @subpackage Snippets
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.4
 */
class SnippetLoader implements SnippetLoaderInterface
{
    /**
     *
     * @var ProjectOverloader
     */
    protected $loader;

    /**
     * The file locations where to look for snippets.
     *
     * Can be overruled in descendants
     *
     * @var array
     */
    protected $snippetsDirectories;

    /**
     * The information source for snippets.
     *
     * @var ContainerInterface
     */
    protected $snippetsSource;

    /**
     * Sets the source of variables and the first directory for snippets
     *
     * @param ContainerInterface $source Something that is or can be made into ContainerInterface, otheriwse \Zend_Registry is used.
     * @param array $overloaders New overloaders, first overloader is tried first, \Snippets is added automatically if not in the overloader directory name
     */
    public function __construct(ContainerInterface $source, array $overloaders = [])
    {
        foreach ($overloaders as &$overloader) {
            if (! str_contains($overloader, 'Snippets')) {
                $overloader .= '\\Snippets';
            }
        }
        $this->setSource($source);
        $this->loader = new ProjectOverloader($source, $overloaders, false);
    }

    /**
     * Add prefixed paths to the registry of paths
     *
     * @param string $prefix
     * @return SnippetLoaderInterface
     */
    public function addPrefix(string $prefix): SnippetLoaderInterface
    {
        $this->loader->addOverloaders([$prefix]);

        return $this;
    }

    /**
     * Searches and loads a .php snippet file.
     *
     * @param string $className The name of the snippet
     * @param array $extraSourceParameters name/value pairs to add to the source for this snippet
     * @return \Zalt\Snippets\SnippetInterface The snippet
     */
    public function getSnippet(string $className, array $extraSourceParameters = []): SnippetInterface
    {
        $sm = $this->loader->getContainer();

        $snippet = $this->loader->create($className, $extraSourceParameters);

        if ($snippet instanceof SnippetInterface) {
            return $snippet;
        }
        
        $interface = SnippetInterface::class; 
        throw new SnippetNotSnippetException("The snippet: '$className' does not implement the $interface interface.");
    }

    /**
     * Returns a source of values for snippets.
     *
     * @return ContainerInterface
     */
    public function getSource(): ContainerInterface
    {
        return $this->snippetsSource;
    }

    /**
     * Sets the source of variables for snippets
     *
     * @param ContainerInterface $source
     * @return SnippetLoaderInterface (continuation pattern)
     */
    public function setSource(ContainerInterface $source): SnippetLoaderInterface
    {
        $this->snippetsSource = $source;
        return $this;
    }
}
