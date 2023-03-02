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
interface SnippetActionInterface
{
    /**
     * @return ?string Get the current action of the snippet
     */
    public function getSnippetAction(): ?string;
    
    /**
     * @return array Of snippet class names or loaded snippets
     */
    public function getSnippetClasses(): array;

    /**
     * @return \Zalt\SnippetsLoader\SnippetOptions
     */
    public function getSnippetOptions(): SnippetOptions;

    /**
     * Is this a snippet that contains detail information or repeating information. True when the main item is not repeated.
     * @return bool
     */
    public function isDetailed(): bool;
    
    /**
     * Is this a snippet that (permanently) edits information?
     * @return bool
     */
    public function isEditing(): bool;

    /**
     * Set the current action
     * @param string $action
     * @return void
     */
    public function setSnippetAction(string $action): void;   
}