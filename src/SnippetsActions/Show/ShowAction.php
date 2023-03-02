<?php

declare(strict_types=1);

/**
 *
 * @package    Zalt
 * @subpackage SnippetsActions
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 */

namespace Zalt\SnippetsActions\Show;

use Zalt\Snippets\ModelDetailTableSnippet;
use Zalt\SnippetsActions\AbstractAction;
use Zalt\SnippetsActions\ModelActionInterface;
use Zalt\SnippetsActions\ModelActionTrait;

/**
 *
 * @package    Zalt
 * @subpackage SnippetsActions
 * @since      Class available since version 1.0
 */
class ShowAction extends AbstractAction implements ModelActionInterface
{
    use ModelActionTrait;
    
    /**
     * @inheritDoc
     */
    protected array $_snippets = [ModelDetailTableSnippet::class,];
}