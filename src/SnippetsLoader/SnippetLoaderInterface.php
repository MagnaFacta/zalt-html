<?php

/**
 *
 * @package    Zalt
 * @subpackage Snippets
 * @author     Menoo Dekker <menno.dekker@erasmusmc.nl>
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 */

namespace Zalt\SnippetsLoader;

use Psr\Container\ContainerInterface;
use Zalt\Snippets\SnippetInterface;

/**
 * Handles loading of snippets
 *
 * @package    Zalt
 * @subpackage Snippets
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.5.5
 */
interface SnippetLoaderInterface
{
    /**
     * @param string $key
     * @param mixed  $value
     * @return void
     */
    public function addConstructorVariable(string $key, mixed $value): void;

    /**
     * Add prefixed paths to the registry of paths
     *
     * @param string $prefix
     * @return SnippetLoaderInterface
     */
    public function addPrefix(string $prefix): SnippetLoaderInterface;

    /**
     * Searches and loads a .php snippet file.
     *
     * @param string $className The name of the snippet
     * @param mixed $extraSourceParameters name/value pairs to add to the source for this snippet
     * @return \Zalt\Snippets\SnippetInterface The snippet
     */
    public function getSnippet(string $className, mixed $extraSourceParameters = []): SnippetInterface;

    /**
     * Returns a source of values for snippets.
     *
     * @return ContainerInterface
     */
    public function getSource(): ContainerInterface;

    /**
     * Sets the source of variables for snippets
     *
     * @param ContainerInterface $source
     * @return SnippetLoaderInterface (continuation pattern)
     */
    public function setSource(ContainerInterface $source): SnippetLoaderInterface;
}