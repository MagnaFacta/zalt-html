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
     * Field id for crsf protection field.
     * @var string
     */
    public string $csrfId = 'no_csrf';

    /**
     * The timeout for crsf, 300 is default
     * @var int
     */
    public int $csrfTimeout = 300;

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
    protected bool $onlyUsedElements = true;

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
    protected array $subjects = ['item', 'items'];

    /**
     * Use csrf token on form for protection against Cross Site Request Forgery
     * @var boolean
     */
    public $useCsrf = false;

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