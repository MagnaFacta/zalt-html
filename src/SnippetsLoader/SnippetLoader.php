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
use Zalt\Loader\DependencyResolver\ConstructorDependencyParametersResolver;
use Zalt\Loader\ProjectOverloader;
use Zalt\Ra\Ra;

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
     * @var array Extra parameters that might be used in constructors
     */
    protected $constructorVariables = [];
    
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
     */
    public function __construct(protected ProjectOverloader $loader)
    {
        $this->loader->setDependencyResolver(new ConstructorDependencyParametersResolver());
        $this->setSource($this->loader->getContainer());
    }

    public function addConstructorVariable(string $key, mixed $value): void
    {
        $this->constructorVariables[$key] = $value;
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
     * @param mixed $snippetOptions name/value pairs options for this snippet
     * @return \Zalt\Snippets\SnippetInterface The snippet
     */
    public function getSnippet(string $className, mixed $snippetOptions = []): SnippetInterface
    {
        $sm = $this->loader->getContainer();

        if (! $snippetOptions instanceof SnippetOptions) {
            if (! is_array($snippetOptions)) {
                $snippetOptions = Ra::to($snippetOptions);
            }
            $snippetOptions = new SnippetOptions($snippetOptions);
        }
        
        $snippet = $this->loader->create($className, $snippetOptions, ...$this->constructorVariables);

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
        return $this->loader->getContainer();
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
