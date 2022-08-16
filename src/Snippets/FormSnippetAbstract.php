<?php

/**
 *
 * @package    Gems
 * @subpackage Snippets
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2015 Erasmus MC
 * @license    New BSD License
 */

namespace MUtil\Snippets;

use Psr\Cache\CacheItemPoolInterface;

/**
 *
 *
 * @package    Gems
 * @subpackage Snippets
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since 1.7.2
 */
abstract class FormSnippetAbstract extends \MUtil\Snippets\SnippetAbstract
{
    /**
     * Optional csrf element
     *
     * @var \Zend_Form_Element_Hash
     */
    protected $_csrf;

    /**
     *
     * @var \Zend_Form
     */
    protected $_form;

    /**
     *
     * @var \Zend_Form_Element_Submit
     */
    protected $_saveButton;

    /**
     * @see \Zend_Controller_Action_Helper_Redirector
     *
     * @var mixed Nothing or either an array or a string that is acceptable for Redirector->gotoRoute()
     */
    protected $afterSaveRouteUrl;

    /**
     *
     * @var string class attribute for buttons
     */
    protected $buttonClass = 'button btn btn-sm btn-primary';

    /**
     *
     * @var CacheItemPoolInterface
     */
    protected $cache;

    /**
     * Variable to set tags for cache cleanup after changes
     *
     * @var array
     */
    protected $cacheTags;

    /**
     * True when the form should edit a new model item.
     *
     * @var boolean
     */
    protected $createData = false;

    /**
     * Field id for crsf protection field.
     *
     * @var string
     */
    protected $csrfId = 'no_csrf';

    /**
     * The timeout for crsf, 300 is default
     *
     * @var int
     */
    protected $csrfTimeout = 300;

    /**
     * As it is better for translation utilities to set the labels etc. translated,
     * the \MUtil default is to disable translation.
     *
     * However, this also disables the translation of validation messages, which we
     * cannot set translated. The \MUtil form is extended so it can make this switch.
     *
     * @var boolean True
     */
    protected $disableValidatorTranslation = false;

    /**
     *
     * @var array
     */
    protected $formData = array();

    /**
     *
     * @var string class attribute for labels
     */
    protected $labelClass = 'label';

    /**
     * Automatically calculate and set the width of the labels
     *
     * @var int
     */
    protected $layoutAutoWidthFactor = 1;

    /**
     * Set the width of the labels
     *
     * @var int
     */
    protected $layoutFixedWidth;

    /**
     * @var \MUtil\Request\RequestInfo
     */
    protected ?\MUtil\Request\RequestInfo $requestInfo = null;

    /**
     * The name of the action to forward to after form completion
     *
     * @var string
     */
    protected $routeAction = 'index';

    /**
     * The name of the controller to forward to after form completion
     *
     * When empty the current controller is used
     *
     * @var string
     */
    protected $routeController;

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
    protected $saveLabel = null;

    /**
     * Use csrf token on form for protection against Cross Site Request Forgery
     *
     * @var boolean
     */
    public $useCsrf = false;

    /**
     * Simple default function for making sure there is a $this->_saveButton.
     *
     * As the save button is not part of the model - but of the interface - it
     * does deserve it's own function.
     */
    protected function addCsrf()
    {
        if (! $this->_csrf) {
            $this->_form->addElement('hash', $this->csrfId, array(
                'salt' => 'mutil_' . $this->requestInfo->getCurrentController() . '_' . $this->requestInfo->getCurrentAction(),
                'timeout' => $this->csrfTimeout,
                ));
            $this->_csrf = $this->_form->getElement($this->csrfId);
        }

        return $this;
    }

    /**
     * Add the elements to the form
     *
     * @param \Zend_Form $form
     */
    abstract protected function addFormElements(\Zend_Form $form);

    /**
     * Simple default function for making sure there is a $this->_saveButton.
     *
     * As the save button is not part of the model - but of the interface - it
     * does deserve it's own function.
     */
    protected function addSaveButton()
    {
        if ($this->_saveButton) {
            $this->saveButtonId = $this->_saveButton->getName();

            if (! $this->_form->getElement($this->saveButtonId)) {
                $this->_form->addElement($this->_saveButton);
            }
        } elseif ($this->saveButtonId) {
            //If not already there, add a save button
            $this->_saveButton = $this->_form->getElement($this->saveButtonId);

            if (! $this->_saveButton) {
                if (null === $this->saveLabel) {
                    $this->saveLabel = $this->_('Save');
                }

                $options = array('label' => $this->saveLabel);
                if ($this->buttonClass) {
                    $options['class'] = $this->buttonClass;
                }

                $this->_saveButton = $this->_form->createElement('submit', $this->saveButtonId, $options);

                $this->_form->addElement($this->_saveButton);
            }
        }
    }

    /**
     * Hook that allows actions when data was saved
     *
     * When not rerouted, the form will be populated afterwards
     *
     * @param int $changed The number of changed rows (0 or 1 usually, but can be more)
     */
    protected function afterSave($changed)
    {
        if ($changed) {
            // Clean cache on changes
            if ($this->cacheTags && ($this->cache instanceof \Symfony\Contracts\Cache\TagAwareCacheInterface)) {
                $this->cache->invalidateTags((array) $this->cacheTags);
            }
        }
    }

    /**
     * Perform some actions on the form, right before it is displayed but already populated
     *
     * Here we add the table display to the form.
     *
     * @return \Zend_Form
     */
    public function beforeDisplay()
    {
        if ($this->_csrf) {
            $this->_csrf->initCsrfToken();
        }

        if ($this->layoutAutoWidthFactor || $this->layoutFixedWidth) {
            $div = new \MUtil\Html\DivFormElement();

            if ($this->layoutFixedWidth) {
                $div->setAsFormLayout($this->_form, $this->layoutFixedWidth);
            } else {
                $div->setAutoWidthFormLayout($this->_form, $this->layoutAutoWidthFactor);
            }
        }
    }

    /**
     * After validation we clean the form data to remove all
     * entries that do not have elements in the form (and
     * this filters the data as well).
     */
    public function cleanFormData()
    {
        $this->formData = $this->_form->getValues();
    }

    /**
     * Creates an empty form. Allows overruling in sub-classes.
     *
     * @param mixed $options
     * @return \Zend_Form
     */
    protected function createForm($options = null)
    {
        $form = new \MUtil\Form($options);

        return $form;
    }

    /**
     * Return the default values for the form
     *
     * @return array
     */
    protected function getDefaultFormValues()
    {
        return array();
    }

    /**
     * Create the snippets content
     *
     * This is a stub function either override getHtmlOutput() or override render()
     *
     * @param \Zend_View_Abstract $view Just in case it is needed here
     * @return \MUtil\Html\HtmlInterface Something that can be rendered
     */
    public function getHtmlOutput(\Zend_View_Abstract $view)
    {
        // Again, just to be sure all changes are set on the form
        $this->populateForm();

        $this->beforeDisplay();

        return $this->_form;
    }

    /**
     * When hasHtmlOutput() is false a snippet user should check
     * for a redirectRoute.
     *
     * When hasHtmlOutput() is true this functions should not be called.
     *
     * @see \Zend_Controller_Action_Helper_Redirector
     *
     * @return mixed Nothing or either an array or a string that is acceptable for Redector->gotoRoute()
     */
    public function getRedirectRoute()
    {
        return $this->afterSaveRouteUrl;
    }

    /**
     * Helper function to allow generalized statements about the items in the model to used specific item names.
     *
     * @param int $count
     * @return $string
     */
    public function getTopic($count = 1)
    {
        return $this->plural('item', 'items', $count);
    }

    /**
     * The place to check if the data set in the snippet is valid
     * to generate the snippet.
     *
     * When invalid data should result in an error, you can throw it
     * here but you can also perform the check in the
     * checkRegistryRequestsAnswers() function from the
     * {@see MUtil\Registry\TargetInterface}.
     *
     * @return boolean
     */
    public function hasHtmlOutput()
    {
        if (parent::hasHtmlOutput()) {
            return $this->processForm();
        }
    }

    /**
     * Makes sure there is a form.
     */
    protected function loadForm()
    {
        if (! $this->_form) {
            $options = array();

            $options['class'] = 'form-horizontal';
            $options['role'] = 'form';

            $this->_form = $this->createForm($options);

            $this->addFormElements($this->_form);
        }
    }

    /**
     * Hook that loads the form data from $_POST or the model
     *
     * Or from whatever other source you specify here.
     */
    protected function loadFormData()
    {
        if ($this->isPost()) {
            $this->formData = $this->getPostData();
            return $this->formData;
        }

        $this->formData = $this->getDefaultFormValues() + $this->getPostData();
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
        $this->addMessage(sprintf($this->_('Input error! Changes to %s not saved!'), $this->getTopic()));

        if ($this->_csrf) {
            if ($this->_csrf->getMessages()) {
                $this->addMessage($this->_('The form was open for too long or was opened in multiple windows.'));
            }
        }
    }

    /**
     * Hook for setting the data on the form.
     */
    protected function populateForm()
    {
        $this->_form->populate($this->formData);
    }

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
        $this->loadFormData();

        // Make sure there is a $this->_form
        $this->loadForm();

        // Create $this->_saveButton
        $this->addSaveButton();

        // Use Csrf when enabled
        if ($this->useCsrf) {
            $this->addCsrf();
        }

        if ($this->isPost()) {
            //First populate the form, otherwise the saveButton will never be 'checked'!
            $this->populateForm();

            // If there is a save button it should be checked, otherwise just validate
            if ((! $this->_saveButton) || $this->_saveButton->isChecked()) {

                if ($this->validateForm($this->formData)) {
                    // Remove all unwanted data
                    $this->cleanFormData();

                    // Save
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
    protected function saveData()
    {
        return 0;
    }

    /**
     * Set what to do when the form is 'finished'.
     *
     * #param array $params Url items to set for this route
     * @return self (continuation pattern)
     */
    protected function setAfterSaveRoute(array $params = array())
    {
        // Only reroute when it is to a different url
        /* if ($params
                || ($this->routeAction && ($this->request->getActionName() !== $this->routeAction))
                || ($this->routeController && ($this->request->getControllerName() !== $this->routeController))) {

            if ($this->routeController) {
                $controllerName = $this->routeController;
            } else {
                $controllerName = $this->request->getControllerName();
            }

            $this->afterSaveRouteUrl = $params + array(
                $this->request->getControllerKey() => $controllerName,
                $this->request->getActionKey() => $this->routeAction,
                'RouteReset' => true,
                );
        } */

        return $this;
    }

    /**
     * Performs the validation.
     *
     * @return boolean True if validation was OK and data should be saved.
     */
    protected function validateForm()
    {
        // Note we use an \MUtil\Form
        return $this->_form->isValid($this->formData, $this->disableValidatorTranslation);
    }
}
