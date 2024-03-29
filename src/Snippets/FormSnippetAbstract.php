<?php

/**
 *
 * @package    Gems
 * @subpackage Snippets
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2015 Erasmus MC
 * @license    New BSD License
 */

namespace Zalt\Snippets;

use Psr\Cache\CacheItemPoolInterface;
use Zalt\Late\Late;

/**
 *
 *
 * @package    Gems
 * @subpackage Snippets
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since 1.7.2
 */
abstract class FormSnippetAbstract extends MessageableSnippetAbstract
{
    /**
     * var \Zend_Form The form of some type
     */
    protected $_form;

    /**
     * @var string Nothing or an url string to be used when we have saved successfully
     */
    protected string $afterSaveRouteUrl = '';

    /**
     *
     * @var string class attribute for buttons
     */
    protected $buttonClass = 'button btn btn-sm btn-primary';

    protected ? CacheItemPoolInterface $cache = null;

    /**
     * Variable to set tags for cache cleanup after changes
     *
     * @var array
     */
    protected array $cacheTags = [];

    /**
     * True when the form should edit a new model item.
     *
     * @var boolean
     */
    protected $createData = false;

    /**
     * Field name for crsf protection field.
     *
     * @var string
     */
    protected string $csrfName = '__csrf';

    /**
     * The csrf token.
     *
     * @var string
     */
    protected ?string $csrfToken = null;

    /**
     *
     * @var array
     */
    protected array $formData = [];

    /**
     * @var string The actual redirect route to use
     */
    protected string $redirectRoute = '';    
    
    /**
     *
     * @var string class attribute for labels
     */
    protected $labelClass = 'label';

    /**
     * The form Id used for the save button
     *
     * If empty save button is not added
     *
     * @var string
     */
    protected $saveButtonId = 'save_button';

    /**
     * The save button label (default is translated 'Save')
     *
     * @var string
     */
    protected $saveLabel = 'OK';

    /**
     * @var array Strings describing what is edited / saved for 1 item and more than 1 item
     */
    protected array $subjects = ['item', 'items'];

    /**
     * Simple function adding the actual crsf hidden field
     *
     * @param string $csrfName
     * @param string|null $csrfToken
     * @param mixed $form
     * @return void
     */
    abstract protected function addCsrf(string $csrfName, ?string $csrfToken, mixed $form): void;

    /**
     * Add the elements to the form
     */
    abstract protected function addFormElements(mixed $form);

    /**
     * Simple default function for making sure there is a saveButton.
     *
     * As the save button is not part of the model - but of the interface - it
     * does deserve its own function.
     * 
     * @param string $saveButtonId
     * @param ?string $saveLabel
     * @param string $buttonClass
     * @return mixed
     */
    abstract protected function addSaveButton(string $saveButtonId, ?string $saveLabel, string $buttonClass);

    /**
     * Hook that allows actions when data was saved
     *
     * When not rerouted, the form will be populated afterwards
     *
     * @param int $changed The number of changed rows (0 or 1 usually, but can be more)
     */
    protected function afterSave($changed)
    {
        Late::addStack('post', $this->formData);
        
        if ($changed) {
            // Clean cache on changes
            if ($this->cacheTags && ($this->cache instanceof \Symfony\Contracts\Cache\TagAwareCacheInterface)) {
                $this->cache->invalidateTags($this->cacheTags);
            }
        }
    }

    /**
     * Perform some actions on the form, right before it is displayed but already populated
     *
     * Here we add the table display to the form.
     */
    public function beforeDisplay()
    { }

    /**
     * Perform some actions to the data before it is saved to the database
     */
    protected function beforeSave()
    { }

    /**
     * After validation we clean the form data to remove all
     * entries that do not have elements in the form (and
     * this filters the data as well).
     */
    public function cleanFormData()
    {  }

    /**
     * Creates an empty form. Allows overruling in sub-classes.
     *
     * @param array $options
     * @return mixed
     */
    abstract protected function createForm(array $options = []);

    /**
     * Return the default values for the form
     *
     * @return array
     */
    protected function getDefaultFormValues(): array
    {
        return [];
    }

    /**
     * Create the snippets content
     *
     * This is a stub function either override getHtmlOutput() or override render()
     *
     * @return mixed Something that can be rendered / output
     */
    public function getHtmlOutput()
    {
        // Again, just to be sure all changes are set on the form
        $this->populateForm();

        $this->beforeDisplay();

        return $this->getFormOutput();
    }

    abstract public function getFormOutput(): mixed;

    public function getInvalidFormMessage(): mixed
    {
        return sprintf($this->_('Input error! Changes to %s not saved!'), $this->getTopic());
    }

    /**
     * When hasHtmlOutput() is false a snippet user should check
     * for a redirectRoute.
     *
     * When hasHtmlOutput() is true this functions should not be called.
     * 
     * @return string|null Nothing or an url string
     */
    public function getRedirectRoute(): ?string
    {
        return $this->redirectRoute;
    }

    /**
     * Helper function to allow generalized statements about the items in the model to used specific item names.
     *
     * @param int $count
     * @return string
     */
    public function getTopic($count = 1)
    {
        return $this->plural($this->subjects[0], $this->subjects[1], $count);
    }

    /**
     * The place to check if the data set in the snippet is valid
     * to generate the snippet.
     *
     * @return boolean
     */
    public function hasHtmlOutput(): bool
    {
        if (parent::hasHtmlOutput()) {
            return $this->processForm();
        }
        return false;
    }

    public function isPost(): bool
    {
        return $this->requestInfo->isPost();
    }
    
    abstract public function isSaveClicked(): bool;

    protected function loadCsrfData(): array
    {
        if ($this->csrfName && $this->csrfToken) {
            return [$this->csrfName => $this->csrfToken];
        }
        return [];
    }
    
    /**
     * Makes sure there is a form.
     */
    protected function loadForm()
    {
        $options['class'] = 'form-horizontal';
        $options['role'] = 'form';

        $this->_form = $this->createForm($options);

        $this->addFormElements($this->_form);
    }

    /**
     * Hook that loads the form data from $_POST or the model
     *
     * Or from whatever other source you specify here.
     */
    protected function loadFormData(): array
    {
        if ($this->isPost()) {
            $this->formData = $this->loadCsrfData() + $this->requestInfo->getRequestPostParams() + $this->requestInfo->getRequestMatchedParams();
            return $this->formData;
        }

        $this->formData = $this->loadCsrfData() + $this->getDefaultFormValues() + $this->requestInfo->getRequestMatchedParams();
        return $this->formData;
    }

    /**
     * Hook that allows actions when the form is submitted, but it was not the submit button that was checked
     *
     * When not rerouted, the form will be populated afterwards
     */
    protected function onFakeSubmit()
    { }

    /**
     * Hook that allows actions when the input is invalid
     *
     * When not rerouted, the form will be populated afterwards
     */
    protected function onInValid()
    {
        $this->addMessage($this->getInvalidFormMessage());
    }

    /**
     * Hook for setting the data on the form.
     */
    abstract protected function populateForm();

    /**
     * Step by step form processing
     *
     * Returns false when $this->afterSaveRouteUrl is set during the
     * processing, which happens by default when the data is saved.
     *
     * @return boolean True when the form should be displayed
     */
    protected function processForm()
    {
        // Make sure there is $this->formData
        $data = $this->loadFormData();
        Late::addStack('post', $data);

        // Make sure there is a from
        $this->loadForm();

        // Create a saveButton
        $this->addSaveButton($this->saveButtonId, $this->saveLabel, $this->buttonClass);

        // Use Csrf when enabled
        if ($this->csrfName && $this->csrfToken) {
            $this->addCsrf($this->csrfName, $this->csrfToken, $this->_form);
        }

        if ($this->isPost()) {
            //First populate the form, otherwise the saveButton will never be 'checked'!
            $this->populateForm();

            // If there is a save button it should be checked, otherwise just validate
            if ($this->isSaveClicked()) {

                if ($this->validateForm($this->formData)) {
                    // Remove all unwanted data
                    $this->cleanFormData();

                    // Save
                    $this->beforeSave();
                    $this->afterSave($this->saveData());

                    // Reroute (always, override function otherwise)
                    $this->setAfterSaveRoute();
                } else {
                    $this->onInValid();
                }
            } else {
                //The default save button was NOT used, so we have a fakesubmit button
                $this->onFakeSubmit();
            }
        }

        return ! $this->getRedirectRoute();
    }

    /**
     * Hook containing the actual save code.
     *
     * @return int The number of "row level" items changed
     */
    protected function saveData(): int
    {
        return 0;
    }

    /**
     * Set what to do when the form is 'finished'.
     */
    protected function setAfterSaveRoute()
    {
        $this->redirectRoute = $this->afterSaveRouteUrl;
    }

    /**
     * Performs the validation.
     *
     * @return boolean True if validation was OK and data should be saved.
     */
    abstract protected function validateForm(array $formData): bool;
}
