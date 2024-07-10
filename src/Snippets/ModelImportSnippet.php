<?php

/**
 *
 * @package    Zalt
 * @subpackage Snippets
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2012 Erasmus MC
 * @license    New BSD License
 */

namespace Zalt\Snippets;

use Zalt\Snippets\Zend\ZendFormSnippetTrait;

/**
 * Generic import wizard.
 *
 * Set the targetModel (directly or through $this->model) and the
 * importTranslators and it should work.
 *
 * @package    Zalt
 * @subpackage Snippets
 * @copyright  Copyright (c) 2012 Erasmus MC
 * @license    New BSD License
 * @since      Class available since \Zalt version 1.3
 */
class ModelImportSnippet extends ModelImportSnippetAbstract
{
    protected function loadImportTranslators(): void
    {

    }

    protected function addCsrf(string $csrfName, ?string $csrfToken, mixed $form): void
    {
        // TODO: Implement addCsrf() method.
    }

    protected function createForm(array $options = [])
    {
        // TODO: Implement createForm() method.
    }

    protected function validateForm(array $formData): bool
    {
        // TODO: Implement validateForm() method.
    }
}
