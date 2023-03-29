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
     * One of the BridgeInterface MODE constants
     *
     * @var int
     */
    public int $bridgeMode = BridgeInterface::MODE_ROWS;

    /**
     * Sets pagination on or off.
     *
     * @var boolean
     */
    public bool $browse = false;

    /**
     * Optional table caption.
     *
     * @var string
     */
    public string $caption = '';

    /**
     * An array of nested arrays, each defining the input for setMultiSort
     *
     * @var array
     */
    public array $columns = [];

    /**
     * Content to show when there are no rows. May be an object or string or null
     *
     * Null shows '&hellip;'
     *
     * @var mixed
     */
    public mixed $onEmpty = null;

    /**
     * When true (= default) the headers get sortable links.
     *
     * @var boolean
     */
    public bool $sortableLinks = true;

    /**
     * When true query only the used columns
     *
     * @var boolean
     */
    public bool $trackUsage = true;

    public function isDetailed() : bool
    {
        return false;
    }
}