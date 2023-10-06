<?php

declare(strict_types=1);

/**
 *
 * @package    Zalt
 * @subpackage SnippetsActions\Form
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 */

namespace Zalt\SnippetsActions\Form;

use Zalt\SnippetsActions\AbstractAction;
use Zalt\SnippetsActions\ModelActionInterface;
use Zalt\SnippetsActions\PostActionInterface;
use Zalt\SnippetsActions\ModelActionTrait;

/**
 *
 * @package    Zalt
 * @subpackage SnippetsActions\Form
 * @since      Class available since version 1.0
 */
class EditActionAbstract extends AbstractAction implements ModelActionInterface, PostActionInterface
{
    use ModelActionTrait;
    
    /**
     * @var string class attribute for buttons
     */
    public string $buttonClass = 'button btn btn-sm btn-primary';

    public ?CacheItemPoolInterface $cache = null;

    /**
     * Variable to set tags for cache cleanup after changes
     * @var array
     */
    public array $cacheTags = [];

    /**
     * True when the form should edit a new model item.
     * @var boolean
     */
    public bool $createData = false;

    /**
     * Field name for crsf protection field.
     *
     * @var string
     */
    public string $csrfName = '__csrf';

    /**
     * The csrf token.
     *
     * @var string
     */
    public ?string $csrfToken = null;

    /**
     * @var string class attribute for labels
     */
    public string $labelClass = 'label';

    /**
     * Output only those elements actually used by the form.
     *
     * When false all fields without a label or elementClass are hidden,
     * when true those are left out, unless they happened to be a key field or
     * needed for a dependency.
     *
     * @var boolean
     */
    public bool $onlyUsedElements = true;

    /**
     * The form Id used for the save button. If empty save button is not added.
     * @var string
     */
    public string $saveButtonId = 'save_button';

    /**
     * The save button label (default is translated 'Save')
     * @var string
     */
    public string $saveLabel = 'OK';

    /**
     * @var string[] Array describing what is saved
     */
    public array $subjects = ['item', 'items'];

    /**
     * @inheritDoc
     */
    public function isEditing() : bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function setSnippetAction(string $action) : void
    {
        parent::setSnippetAction($action);
        
        $this->createData = ('create' == $action);
    }
}