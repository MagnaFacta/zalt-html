<?php

declare(strict_types=1);

/**
 *
 * @package    Zalt
 * @subpackage Snippets\Zend
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 */

namespace Zalt\Snippets\Zend;

use Zalt\Snippets\FormSnippetAbstract;

/**
 *
 * @package    Zalt
 * @subpackage Snippets\Zend
 * @since      Class available since version 1.0
 */
abstract class ZendFormSnippetAbstract extends FormSnippetAbstract
{
    use ZendFormSnippetTrait;
}