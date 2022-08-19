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
     * @param mixed $source Something that is or can be made into ContainerInterface, otheriwse \Zend_Registry is used.
     * @param array $dirs prefix => pathname The inital paths to load from
     */
    public function __construct($source = null, array $dirs = [])
    {
        if (! $source instanceof ContainerInterface) {
            // $source = new \Zalt\Registry\Source($source);
        }
        $this->setSource($source);
        $this->loader = new ProjectOverloader($source, $dirs, true);
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
        $className = $this->loader->create($className);

        $snippet = new $className();

        if ($snippet instanceof SnippetInterface) {
            // Add extra parameters when specified
            if ($extraSourceParameters) {
                // $this->snippetsSource->addRegistryContainer($extraSourceParameters, 'tmpContainer');
            }

//            if ($this->snippetsSource->applySource($snippet)) {
//                if ($extraSourceParameters) {
//                    // Can remove now, it was applied
//                    // $this->snippetsSource->removeRegistryContainer('tmpContainer');
//                }
//
//                return $snippet;
//
//            }
            return $$snippet;
            // throw new \Exception("Not all parameters set for html snippet: '$className'. \n\nRequested variables were: " . implode(", ", $snippet->getRegistryRequests()));
        }
        
        throw new \Exception("The snippet: '$className' does not implement the \Zalt\Snippets\SnippetInterface interface.");
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
