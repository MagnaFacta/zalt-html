<?php

/**
 *
 * @package    MUtil
 * @subpackage Snippets
 * @author     Menoo Dekker <menno.dekker@erasmusmc.nl>
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 */

namespace MUtil\Snippets;

/**
 * Handles loading of snippets
 *
 * @package    MUtil
 * @subpackage Snippets
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.5.5
 */
interface SnippetLoaderInterface
{
    /**
     * Sets the source of variables and the first directory for snippets
     *
     * @param mixed $source Something that is or can be made into \MUtil\Registry\SourceInterface, otheriwse \Zend_Registry is used.
     * @param array $dirs prefix => pathname The inital paths to load from
     */
    public function __construct($source = null, array $dirs = array());

    /**
     * Add prefixed paths to the registry of paths
     *
     * @param string $prefix
     * @param string $path
     * @return \MUtil\Snippets\SnippetLoaderInterface
     */
    public function addPrefixPath($prefix, $path);

    /**
     * Searches and loads a .php snippet file.
     *
     * @param string $className The name of the snippet
     * @param array $extraSourceParameters name/value pairs to add to the source for this snippet
     * @return \MUtil\Snippets\SnippetInterface The snippet
     */
    public function getSnippet($className, array $extraSourceParameters = null);

    /**
     * Returns a source of values for snippets.
     *
     * @return \MUtil\Registry\SourceInterface
     */
    public function getSource();

    /**
     * Remove a prefix (or prefixed-path) from the registry
     *
     * @param string $prefix
     * @param string $path OPTIONAL
     * @return \MUtil\Snippets\SnippetLoaderInterface
     */
    public function removePrefixPath($prefix, $path = null);

    /**
     * Sets the source of variables for snippets
     *
     * @param \MUtil\Registry\SourceInterface $source
     * @return \MUtil\Snippets\SnippetLoader (continuation pattern)
     */
    public function setSource(\MUtil\Registry\SourceInterface $source);
}