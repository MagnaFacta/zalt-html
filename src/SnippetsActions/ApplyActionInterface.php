<?php

declare(strict_types=1);


/**
 * @package    Zalt
 * @subpackage SnippetsActions
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 */

namespace Zalt\SnippetsActions;

/**
 * @package    Zalt
 * @subpackage SnippetsActions
 * @since      Class available since version 1.0
 */
interface ApplyActionInterface
{
    /**
     * @param SnippetActionInterface $action Apply the current action to this object
     * @return void
     */
    public function applyAction(SnippetActionInterface $action): void;
}
