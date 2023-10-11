<?php

declare(strict_types=1);

/**
 *
 * @package    Zalt
 * @subpackage SnippetsActions
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 */

namespace Zalt\SnippetsActions;

use Zalt\SnippetsLoader\SnippetOptions;

/**
 *
 * @package    Zalt
 * @subpackage SnippetsActions
 * @since      Class available since version 1.0
 */
abstract class AbstractAction implements SnippetActionInterface
{
    protected ?string $_action = null;
    
    /**
     * @var array Of snippet class names
     */
    protected array $_snippets = [];
    
    /**
     * @var array Annay html attributes that should be added to the main HtmlElement output of the snippet;
     */
    public array $attributes = [];

    /**
     * @var string A calssname to append to the class attribute of the main HtmlElement output of the snippet;
     */
    public string $class = '';

    public function appendSnippet(string $snippetClass)
    {
        $this->_snippets[] = $snippetClass;
    }

    /**
     * Filters the names that should not be requested.
     *
     * Can be overriden.
     *
     * @param string $name
     * @return boolean
     */
    protected function filterOptionNames($name)
    {
        return '_' !== $name[0];
    }

    /**
     * @inheritDoc
     */
    public function getSnippetAction(): ?string
    {
        return $this->_action;
    }
    
    /**
     * @inheritDoc
     */
    public function getSnippetClasses(): array
    {
        return $this->_snippets;
    }

    /**
     * @inheritDoc
     */
    public function getSnippetOptions(): SnippetOptions
    {
        $output = array_filter(get_object_vars($this), [$this, 'filterOptionNames'], ARRAY_FILTER_USE_KEY);;
        return new SnippetOptions($output);
//        $output = [];
//        $reflection = new \ReflectionObject($this); 
//        foreach ($reflection->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) {
//            if ($property instanceof \ReflectionProperty) {
//                $output[$property->getName()] = $property->getValue($this);
//            }
//        }
//     
//        return new SnippetOptions($output);
    }

    /**
     * @inheritDoc
     */
    public function isDetailed(): bool
    {
        return true;
    }
    
    /**
     * @inheritDoc
     */
    public function isEditing(): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function setSnippetAction(string $action): void
    {
        $this->_action = $action;
    }

    /**
     * Overwrites the current snippets with new ones
     *
     * @param array $snippets
     * @return void
     */
    public function setSnippets(array $snippets): void
    {
        $this->_snippets = $snippets;
    }
}