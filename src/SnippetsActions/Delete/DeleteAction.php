<?php

declare(strict_types=1);

/**
 *
 * @package    Zalt
 * @subpackage SnippetsActions\Delete
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 */

namespace Zalt\SnippetsActions\Delete;

use Psr\Cache\CacheItemPoolInterface;
use Zalt\Snippets\ModelYesNoDeleteSnippet;
use Zalt\SnippetsActions\AbstractAction;
use Zalt\SnippetsActions\ModelActionInterface;
use Zalt\SnippetsActions\ParameterActionInterface;

/**
 *
 * @package    Zalt
 * @subpackage SnippetsActions\Delete
 * @since      Class available since version 1.0
 */
class DeleteAction extends AbstractAction implements ModelActionInterface, ParameterActionInterface
{
    /**
     * @var array Of snippet class names
     */
    protected array $_snippets = [
        ModelYesNoDeleteSnippet::class,
    ];
    
    /**
     * @var string Nothing or an url string where to go to on 'cancel'.
     */
    public string $abortUrl = '';

    /**
     * @var string aN url string where to go to after deletion
     */
    public string $afterDeleteUrl = '';

    /**
     * @var string Optional class for use on buttons, overruled by $buttonNoClass and $buttonYesClass
     */
    public ?string $buttonClass = null;

    /**
     * @var string Optional class for use on No button
     */
    public ?string $buttonNoClass = null;

    /**
     * @var ?string Optional class for use on Yes button
     */
    public ?string $buttonYesClass = null;

    public CacheItemPoolInterface $cache;

    /**
     * @var array Variable to set tags for cache cleanup after changes
     *
     */
    public array $cacheTags;

    /**
     * @var string The request parameter used to store the confirmation
     */
    public string $confirmParameter = 'confirmed';

    /**
     * @var ?string Optional question to ask the user.
     */
    public ?string $deleteQuestion;
}