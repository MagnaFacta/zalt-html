<?php

declare(strict_types=1);

/**
 *
 * @package    Zalt
 * @subpackage SnippetsActions
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 */

namespace Zalt\SnippetsActions\Browse;

use Zalt\Model\Bridge\BridgeInterface;
use Zalt\Snippets\ModelTableSnippet;
use Zalt\SnippetsActions\AbstractAction;
use Zalt\SnippetsActions\ModelActionInterface;
use Zalt\SnippetsActions\ModelActionTrait;

/**
 *
 * @package    Zalt
 * @subpackage SnippetsActions
 * @since      Class available since version 1.0
 */
class BrowseTableAction extends AbstractAction implements ModelActionInterface
{
    use ModelActionTrait;
    
    protected array $_snippets = [ModelTableSnippet::class];

    /**
     * @var int One of the BridgeInterface MODE constants
     */
    public int $bridgeMode = BridgeInterface::MODE_ROWS;

    /**
     * @var boolean Sets pagination on or off.
     */
    public bool $browse = false;

    /**
     * @var string Optional table caption.
     */
    public string $caption = '';

    /**
     * @var array An array of nested arrays, each defining the input for setMultiSort
     */
    public array $columns = [];

    /**
     * @var mixed Content to show when there are no rows. Can be an object or string or null. Null shows '&hellip;'
     */
    public mixed $onEmpty = null;

    /**
     * @var boolean When true (= default) the headers get sortable links.
     */
    public bool $sortableLinks = true;

    /**
     * @var boolean When true query only the used columns
     */
    public bool $trackUsage = true;

    public function isDetailed() : bool
    {
        return false;
    }
}