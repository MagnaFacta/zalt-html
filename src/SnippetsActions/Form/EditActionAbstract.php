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
    public $buttonClass = 'button btn btn-sm btn-primary';

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
    public $csrfId = 'no_csrf';

    /**
     * The timeout for crsf, 300 is default
     * @var int
     */
    public $csrfTimeout = 300;

    /**
     * @var string class attribute for labels
     */
    public $labelClass = 'label';

    /**
     * The form Id used for the save button. If empty save button is not added.
     * @var string
     */
    public $saveButtonId = 'save_button';

    /**
     * The save button label (default is translated 'Save')
     * @var string
     */
    public $saveLabel = 'OK';

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