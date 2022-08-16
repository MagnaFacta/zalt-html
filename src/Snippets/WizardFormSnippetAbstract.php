<?php

/**
 *
 * @package    MUtil
 * @subpackage WizardFormSnippetAbstract
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2015 Erasmus MC
 * @license    New BSD License
 */

namespace MUtil\Snippets;

/**
 * Generic wizard snippet.
 *
 * All the elements in the model are hidden except those set by addFormElementsFor()
 *
 * @package    MUtil
 * @subpackage Snippets
 * @copyright  Copyright (c) 2013 Erasmus MC
 * @license    New BSD License
 * @since      Class available since \MUtil version 1.3
 */
abstract class WizardFormSnippetAbstract extends \MUtil\Snippets\ModelFormSnippetAbstract
{
    /**
     *
     * @var \Zend_Form_Element_Submit
     */
    protected $_cancelButton;

    /**
     *
     * @var \Zend_Form_Element_Submit
     */
    protected $_finishButton;

    /**
     *
     * @var array of \Zend_Form's, one for each step (that is initialized)
     */
    protected $_forms = array();

    /**
     *
     * @var \Zend_Form_Element_Submit
     */
    protected $_nextButton;

    /**
     *
     * @var \Zend_Form_Element_Submit
     */
    protected $_previousButton;

    /**
     * The form Id used for the cancel button
     *
     * If empty cancel button is not added
     *
     * @var string
     */
    protected $cancelButtonId = 'cancel_button';

    /**
     * The cancel button label (default is translated 'Cancel')
     *
     * @var string
     */
    protected $cancelLabel = null;

    /**
     * Shortfix to add class attribute
     *
     * @var string
     */
    protected $class = 'wizard form-horizontal';

    /**
     * The current step, starting at 1.
     *
     * @var int
     */
    protected $currentStep = 1;

    /**
     * The form Id used for the finish button
     *
     * If empty button is not added
     *
     * @var string
     */
    protected $finishButtonId = 'finish_button';

    /**
     * The finish button label (default is translated 'Finish')
     *
     * @var string
     */
    protected $finishLabel = null;

    /**
     * The form Id used for the next button
     *
     * If empty button is not added
     *
     * @var string
     */
    protected $nextButtonId = 'next_button';

    /**
     * Should next be disabled even when there is a next item
     *
     * If empty button is not added
     *
     * @var string
     */
    protected $nextDisabled = false;

    /**
     * The next button label (default is translated 'Next')
     *
     * @var string
     */
    protected $nextLabel = null;

    /**
     * The original step, before any clicked button was checked
     *
     * @var int
     */
    protected $originalStep = 1;

    /**
     * The form Id used for the previous button
     *
     * If empty button is not added
     *
     * @var string
     */
    protected $previousButtonId = 'previous_button';

    /**
     * The previous button label (default is translated 'Previous')
     *
     * @var string
     */
    protected $previousLabel = null;

    /**
     * Name of the hidden field storing the current step
     *
     * @var string
     */
    protected $stepFieldName = 'current_step';

    /**
     * When set getTopic() uses this function instead of plural on this.
     *
     * @var callable
     */
    protected $topicCallable;

    /**
     * Default button creation function.
     *
     * @param \Zend_Form_Element $button or null
     * @param string $buttonId
     * @param string $label
     * @param string $defaultLabel
     * @param string $class
     */
    protected function _addButton(&$button, &$buttonId, &$label, $defaultLabel, $class = 'Zend_Form_Element_Submit')
    {
        if ($button && ($button instanceof \Zend_Form_Element)) {
            $buttonId = $button->getName();

        } elseif ($buttonId) {
            //If already there, get a reference button
            $button = $this->_form->getElement($buttonId);

            if (! $button) {
                if (null === $label) {
                    $label = $defaultLabel;
                }

                $button = new $class($buttonId, $label);
                if ($this->buttonClass) {
                    $button->setAttrib('class', $this->buttonClass);
                }

                // Make sure no DD / DT parts are on display
                $button->setDecorators(array('Tooltip', 'ViewHelper'));
            }
        }

        if (!$this->_form->getElement($buttonId)) {
            $this->_form->addElement($button);
        }
    }

    /**
     * Add the cancel button
     */
    protected function addButtons()
    {
        $this->addPreviousButton();
        $this->addNextButton();

        $element = new \MUtil\Form\Element\Html('button_spacer');
        $element->raw('&nbsp;');
        $element->setDecorators(array('ViewHelper'));

        $this->_form->addElement($element);

        $this->addCancelButton();
        $this->addFinishButton();

        $this->_form->addDisplayGroup(array(
            $this->_previousButton,
            $this->_nextButton,
            $element,
            $this->_cancelButton,
            $this->_finishButton,
            ), 'buttons');

        $group = $this->_form->getDisplayGroup('buttons');
        $group->removeDecorator('DtDdWrapper');
        $group->removeDecorator('HtmlTag');
    }

    /**
     * Add the cancel button
     */
    protected function addCancelButton()
    {
        $class = '\\MUtil\\Form\\Element\\FakeSubmit';
        $this->_addButton($this->_cancelButton, $this->cancelButtonId, $this->cancelLabel, $this->_('Cancel'), $class);
    }

    /**
     * Adds elements from the model to the bridge that creates the form.
     *
     * Overrule this function to add different elements to the browse table, without
     * having to recode the core table building code.
     *
     * @param \MUtil\Model\Bridge\FormBridgeInterface $bridge
     * @param \MUtil\Model\ModelAbstract $model
     * @param int $step The current step
     */
    protected function addFormElementsFor(\MUtil\Model\Bridge\FormBridgeInterface $bridge, \MUtil\Model\ModelAbstract $model, $step)
    {
        //Get all elements in the model if not already done
        $this->initItems();

        // Store the current step
        $bridge->addHidden($this->stepFieldName);

        $this->addStepElementsFor($bridge, $model, $step);

        //And any remaining item
        $this->addItemsHidden($bridge, $this->_items);
    }

    /**
     * Add the finish button
     */
    protected function addFinishButton()
    {
        $last  = $this->currentStep == $this->getStepCount();
        $class = $last ? 'Zend_Form_Element_Submit' : '\\MUtil\\Form\\Element\\FakeSubmit';

        $this->_addButton($this->_finishButton, $this->finishButtonId, $this->finishLabel, $this->_('Finish'), $class);
        if ($this->nextDisabled || !$last) {
            $this->_finishButton->setAttrib('disabled', 'disabled');
        } else {
            $this->_finishButton->setAttrib('disabled', null);
        }
    }

    /**
     * Add items in hidden form to the bridge, and remove them from the items array
     *
     * @param \MUtil\Model\Bridge\FormBridgeInterface $bridge
     * @param string $element1
     *
     * @return void
     */
    protected function addItemsHidden(\MUtil\Model\Bridge\FormBridgeInterface $bridge, $element1)
    {
        $args = func_get_args();
        if (count($args)<2) {
            throw new \Gems\Exception\Coding('Use at least 2 arguments, first the bridge and then one or more individual items');
        }

        $bridge   = array_shift($args);
        $elements = \MUtil\Ra::flatten($args);
        $form     = $bridge->getForm();

        //Remove the elements from the _items variable
        $this->_items = array_diff($this->_items, $elements);

        // And add them to the bridge
        foreach($elements as $name) {
            // Use $bridge->addHidden as that adds validators and filters.
            $bridge->addHidden($name);
        }
    }

    /**
     * Add the next button
     */
    protected function addNextButton()
    {
        $last  = $this->currentStep == $this->getStepCount();
        $class = !$last ? 'Zend_Form_Element_Submit' : '\\MUtil\\Form\\Element\\FakeSubmit';

        $this->_addButton($this->_nextButton, $this->nextButtonId, $this->nextLabel, $this->_("Next >"), $class);

        if ($last || $this->nextDisabled) {
            $this->_nextButton->setAttrib('disabled', 'disabled');
        } else {
            $this->_nextButton->setAttrib('disabled', null);
        }
    }

    /**
     * Add the previous button
     */
    protected function addPreviousButton()
    {
        $class = '\\MUtil\\Form\\Element\\FakeSubmit';
        $this->_addButton(
                $this->_previousButton,
                $this->previousButtonId,
                $this->previousLabel,
                $this->_('< Previous'),
                $class
                );
        if (1 == $this->currentStep) {
            $this->_previousButton->setAttrib('disabled', 'disabled');
        } else {
            $this->_previousButton->setAttrib('disabled', null);
        }
    }

    /**
     * Add the elements from the model to the bridge for the current step
     *
     * @param \MUtil\Model\Bridge\FormBridgeInterface $bridge
     * @param \MUtil\Model\ModelAbstract $model
     * @param int $step The current step
     */
    abstract protected function addStepElementsFor(\MUtil\Model\Bridge\FormBridgeInterface $bridge, \MUtil\Model\ModelAbstract $model, $step);

    /**
     * Overrule this function for any activities you want to take place
     * after the form has successfully been validated, but before any
     * buttons are processed.
     *
     * @param int $step The current step
     */
    protected function afterFormValidationFor($step)
    {
    }

    /**
     * Perform some actions on the form, right before it is displayed but already populated
     *
     * Here we add the table display to the form.
     *
     * @return \Zend_Form
     */
    protected function beforeDisplay()
    {
        $this->beforeDisplayFor($this->currentStep);
    }

    /**
     * Overrule this function for any activities you want to take place
     * before the actual form is displayed.
     *
     * This means the form has been validated, step buttons where processed
     * and the current form will be the one displayed.
     *
     * @param int $step The current step
     */
    protected function beforeDisplayFor($step)
    { }

    /**
     * Creates from the model a \Zend_Form using createForm and adds elements
     * using addFormElements().
     *
     * @param int $step The current step
     * @return \Zend_Form
     */
    protected function getFormFor($step)
    {
        $model    = $this->getModel();
        $baseform = $this->createForm();
        $baseform->setAttrib('class', $this->class);

        $bridge = $model->getBridgeFor('form', $baseform);

        $this->_items = null;
        $this->initItems();

        $this->addFormElementsFor($bridge, $model, $step);

        return $baseform;
    }

    /**
     * The number of steps in this form
     *
     * @return int
     */
    abstract protected function getStepCount();

    /**
     * Helper function to allow generalized statements about the items in the target model to specific item names.
     *
     * @param int $count
     * @return $string
     */
    public function getTopic($count = 1)
    {
         if (is_callable($this->topicCallable)) {
            return call_user_func($this->topicCallable, $count);
        } else {
              return $this->plural('item', 'items', $count);
        }
    }

    /**
     * True when the user clicked the finished button
     *
     * @return boolean
     */
    public function isFinishedClicked()
    {
        if ($this->_finishButton) {
            return $this->_finishButton->isChecked();
        }
        $queryParams = $this->requestInfo->getRequestQueryParams();
        if (isset($queryParams[$this->finishButtonId])) {
            return $queryParams[$this->finishButtonId];
        }
        return false;
    }

    /**
     * True when the user clicked the next button
     *
     * @return boolean
     */
    public function isNextClicked()
    {
        if ($this->_nextButton) {
            return $this->_nextButton->isChecked();
        }
        $queryParams = $this->requestInfo->getRequestQueryParams();
        if (isset($queryParams[$this->nextButtonId])) {
            return $queryParams[$this->nextButtonId];
        }
        return false;
    }

    /**
     * True when the user clicked the previous button
     *
     * @return boolean
     */
    public function isPreviousClicked()
    {
        if ($this->_previousButton) {
            return $this->_previousButton->isChecked();
        }
        $queryParams = $this->requestInfo->getRequestQueryParams();
        if (isset($queryParams[$this->previousButtonId])) {
            return $queryParams[$this->previousButtonId];
        }
        return false;
    }

    /**
     * Makes sure there is a form.
     *
     * @param int $step The current step
     */
    protected function loadFormFor($step)
    {
        $this->currentStep                    = $step;
        $this->formData[$this->stepFieldName] = $step;

        if (! isset($this->_forms[$step])) {
            $this->nextDisabled = false;
            $this->_forms[$step] = $this->getFormFor($step);
        }
        $this->_form = $this->_forms[$step];

        $this->addButtons();

        // Use Csrf when enabled
        if ($this->useCsrf) {
            if ($this->_csrf) {
                $this->_form->addElement($this->_csrf);
            } else {
                $this->addCsrf();
            }
        }

        $this->populateForm();
    }

    /**
     * True when we are on the orginal step where the user posted the data
     * @return boolean
     */
    public function onStartStep()
    {
        return $this->currentStep === $this->originalStep;
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
        if (isset($this->formData[$this->stepFieldName])) {
            $this->currentStep = $this->formData[$this->stepFieldName];
        }
        $this->originalStep = $this->currentStep;

        // Make sure there is a $this->_form
        $this->loadFormFor($this->currentStep);

        if ($this->requestInfo->isPost()) {
            // \MUtil\EchoOut\EchoOut::track($this->formData);
            if ($this->_cancelButton && $this->_cancelButton->isChecked()) {
                $this->setAfterSaveRoute();

            } elseif ($this->isPreviousClicked()) {
                $this->loadFormFor($this->currentStep - 1);

            } else {
                if ($this->validateForm()) {
                    $this->afterFormValidationFor($this->currentStep);

                    if ($this->isNextClicked()) {
                        $this->loadFormFor($this->currentStep + 1);

                    } else  {
                        /*
                         * Now that we validated, the form is populated. But I think the step
                         * below is not needed as the values in the form come from the data array
                         * but performing a getValues() cleans the data array so data in post but
                         * not in the form is removed from the data variable.
                         */
                        $this->formData = $this->_form->getValues();

                        if ($this->_finishButton && $this->_finishButton->isChecked()) {
                            // Save
                            $this->saveData();

                            // Reroute (always, override function otherwise)
                            $this->setAfterSaveRoute();
                        }
                    }
                } else {
                    $this->onInValid();
                }
            }
        }

        return ! $this->getRedirectRoute();
    }

    /**
     * Set what to do when the form is 'finished' or 'cancelled'.
     *
     * @return \MUtil\Snippets\Standard\ModelImportSnippet
     */
    protected function setAfterSaveRoute()
    {
        parent::setAfterSaveRoute();

        // Hapes when routeActions is same as current action
        if (! $this->afterSaveRouteUrl) {
            $this->afterSaveRouteUrl[$this->stepFieldName] = 1;
        }
    }
}
