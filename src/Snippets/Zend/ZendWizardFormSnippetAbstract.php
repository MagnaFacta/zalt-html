<?php

declare(strict_types=1);

/**
 * @package    Zalt
 * @subpackage Snippets\Zend
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 */

namespace Zalt\Snippets\Zend;

use Zalt\Model\Bridge\FormBridgeInterface;
use Zalt\Model\Data\DataReaderInterface;

/**
 * @package    Zalt
 * @subpackage Snippets\Zend
 * @since      Class available since version 1.0
 */
abstract class ZendWizardFormSnippetAbstract extends \Zalt\Snippets\WizardFormSnippetAbstract
{
    use ZendFormSnippetTrait;
}